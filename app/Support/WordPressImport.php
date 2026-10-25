<?php

namespace App\Support;

use App\Models\Post;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use SimpleXMLElement;

/**
 * Brings news posts across from a WordPress website, either through the
 * site's own feed (/wp-json) or from a WordPress export file (Tools > Export).
 *
 * Every post is first turned into a plain array:
 *   [id, slug, title, date, html, excerpt, image, categories, link]
 * and then saved with importItem().
 */
class WordPressImport
{
    public const MAX_GALLERY = 20;

    private const IMAGE_MAX_BYTES = 15 * 1024 * 1024;

    private const IMAGE_MAX_WIDTH = 1600;

    /* ------------------------------------------------------------------
     | Reading from the live site
     * ------------------------------------------------------------------ */

    /** Clean up what the admin typed: add https, drop paths like /news/. */
    public static function normaliseSiteUrl(string $url): string
    {
        $url = trim($url);
        if (! preg_match('~^https?://~i', $url)) {
            $url = 'https://'.$url;
        }
        $parts = parse_url($url);
        if (! $parts || empty($parts['host'])) {
            throw new RuntimeException('That does not look like a website address.');
        }

        return strtolower($parts['scheme']).'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }

    /**
     * One page of posts from the site's feed.
     *
     * @return array{items: array<int, array>, total: int, pages: int}
     */
    public function fetchPage(string $siteUrl, int $page, int $perPage = 10): array
    {
        $query = ['per_page' => $perPage, 'page' => $page, '_embed' => 1, 'status' => 'publish', 'orderby' => 'date', 'order' => 'asc'];
        $attempts = [
            $siteUrl.'/wp-json/wp/v2/posts',
            $siteUrl.'/',                // sites without pretty links
        ];

        $lastError = null;
        foreach ($attempts as $i => $endpoint) {
            $params = $i === 0 ? $query : ['rest_route' => '/wp/v2/posts'] + $query;
            try {
                $response = $this->http()->get($endpoint, $params);
            } catch (\Throwable $e) {
                $lastError = 'Could not reach '.$siteUrl.': '.$e->getMessage();

                continue;
            }

            // Asking for a page past the end gives a 400 "invalid page number".
            if ($response->status() === 400 && $page > 1) {
                return ['items' => [], 'total' => 0, 'pages' => $page - 1];
            }

            $data = $response->successful() ? $response->json() : null;
            if (is_array($data) && array_is_list($data)) {
                return [
                    'items' => array_map(fn ($p) => $this->fromRest($p), $data),
                    'total' => (int) $response->header('X-WP-Total'),
                    'pages' => max(1, (int) $response->header('X-WP-TotalPages')),
                ];
            }
            $lastError = 'The site answered '.$response->status().'.';
        }

        throw new RuntimeException(
            'Could not read the news from '.$siteUrl.'. '.$lastError
            .' The site may have its feed switched off. Use the export file option instead.'
        );
    }

    private function fromRest(array $p): array
    {
        $media = $p['_embedded']['wp:featuredmedia'][0] ?? null;
        $image = $media['media_details']['sizes']['large']['source_url']
            ?? $media['media_details']['sizes']['full']['source_url']
            ?? $media['source_url'] ?? null;

        $categories = [];
        foreach ($p['_embedded']['wp:term'] ?? [] as $group) {
            foreach ($group as $term) {
                if (($term['taxonomy'] ?? '') === 'category') {
                    $categories[] = html_entity_decode($term['name'] ?? '', ENT_QUOTES | ENT_HTML5);
                }
            }
        }

        return [
            'id' => (int) ($p['id'] ?? 0),
            'slug' => (string) ($p['slug'] ?? ''),
            'title' => html_entity_decode(strip_tags($p['title']['rendered'] ?? ''), ENT_QUOTES | ENT_HTML5),
            'date' => $p['date_gmt'] ?? $p['date'] ?? null,
            'date_is_gmt' => isset($p['date_gmt']),
            'html' => (string) ($p['content']['rendered'] ?? ''),
            'excerpt' => (string) ($p['excerpt']['rendered'] ?? ''),
            'image' => $image,
            'categories' => $categories,
            'link' => (string) ($p['link'] ?? ''),
        ];
    }

    /* ------------------------------------------------------------------
     | Reading a WordPress export file
     * ------------------------------------------------------------------ */

    /** @return array<int, array> published posts in the file, oldest first */
    public function readExportFile(string $path): array
    {
        $previous = libxml_use_internal_errors(true);
        $xml = simplexml_load_file($path, SimpleXMLElement::class, LIBXML_NOCDATA | LIBXML_NONET | LIBXML_PARSEHUGE);
        libxml_use_internal_errors($previous);

        if (! $xml || ! isset($xml->channel)) {
            throw new RuntimeException('This is not a WordPress export file. In WordPress, go to Tools, then Export, choose Posts, and download the file.');
        }

        $ns = $xml->getDocNamespaces(true);
        $wpNs = $ns['wp'] ?? 'http://wordpress.org/export/1.2/';
        $contentNs = $ns['content'] ?? 'http://purl.org/rss/1.0/modules/content/';
        $excerptNs = $ns['excerpt'] ?? 'http://wordpress.org/export/1.2/excerpt/';

        $attachments = [];
        $posts = [];

        foreach ($xml->channel->item as $item) {
            $wp = $item->children($wpNs);
            $type = (string) $wp->post_type;

            if ($type === 'attachment') {
                $attachments[(string) $wp->post_id] = (string) $wp->attachment_url;

                continue;
            }
            if ($type !== 'post' || (string) $wp->status !== 'publish') {
                continue;
            }

            $thumbId = null;
            foreach ($wp->postmeta as $meta) {
                if ((string) $meta->meta_key === '_thumbnail_id') {
                    $thumbId = (string) $meta->meta_value;
                }
            }

            $categories = [];
            foreach ($item->category as $cat) {
                if ((string) $cat['domain'] === 'category') {
                    $categories[] = html_entity_decode((string) $cat, ENT_QUOTES | ENT_HTML5);
                }
            }

            $gmt = (string) $wp->post_date_gmt;
            $useGmt = $gmt !== '' && ! str_starts_with($gmt, '0000');

            $posts[] = [
                'id' => (int) $wp->post_id,
                'slug' => (string) $wp->post_name,
                'title' => html_entity_decode((string) $item->title, ENT_QUOTES | ENT_HTML5),
                'date' => $useGmt ? $gmt : (string) $wp->post_date,
                'date_is_gmt' => $useGmt,
                'html' => (string) $item->children($contentNs)->encoded,
                'excerpt' => (string) $item->children($excerptNs)->encoded,
                'excerpt_is_manual' => true,
                'image' => null,
                'thumb_id' => $thumbId,
                'categories' => $categories,
                'link' => (string) $item->link,
            ];
        }

        foreach ($posts as &$post) {
            if ($post['thumb_id'] && isset($attachments[$post['thumb_id']])) {
                $post['image'] = $attachments[$post['thumb_id']];
            }
            unset($post['thumb_id']);
        }

        usort($posts, fn ($a, $b) => strcmp((string) $a['date'], (string) $b['date']));

        return $posts;
    }

    /* ------------------------------------------------------------------
     | Saving
     * ------------------------------------------------------------------ */

    /**
     * Save one post. Returns created, updated or skipped.
     */
    public function importItem(array $item, bool $update = false, bool $withPhotos = true): string
    {
        $title = trim($item['title']) ?: 'Untitled';
        $existing = Post::where('legacy_id', $item['id'] ?: -1)->first()
            ?? Post::where('slug', $item['slug'] ?: Str::slug($title))->first();

        if ($existing && ! $update) {
            return 'skipped';
        }

        $text = self::htmlToText($item['html']);
        $images = $withPhotos ? self::imagesIn($item['html']) : [];

        $data = [
            'title' => Str::limit($title, 250, ''),
            'category' => self::category($item['categories'] ?? []),
            'excerpt' => self::excerpt($item, $text),
            'body' => $text !== '' ? $text : null,
            'published_at' => self::date($item),
            'legacy_id' => $item['id'] ?: null,
            'legacy_path' => self::legacyPath($item['link'] ?? ''),
        ];

        // A post with no text at all still needs something, or the admin form
        // would refuse to save it later. Point back to the original.
        if ($data['body'] === null && filled($item['link'])) {
            $data['body'] = 'This story was first published on our previous website.';
        }

        $post = $existing ?? new Post(['slug' => self::uniqueSlug($item['slug'] ?: Str::slug($title))]);
        $post->fill($data);

        if ($withPhotos) {
            $disk = Storage::disk('public');
            $cover = $item['image'] ? $this->downloadImage($item['image']) : null;

            // No featured image: use the first photo in the story instead.
            if (! $cover && $images) {
                $cover = $this->downloadImage(array_shift($images));
            } elseif ($cover && $item['image']) {
                $images = array_values(array_filter($images, fn ($u) => self::sameImage($u, $item['image'])  === false));
            }

            if ($cover) {
                if ($post->cover_image) {
                    $disk->delete($post->cover_image);
                }
                $post->cover_image = $cover;
            }

            $gallery = [];
            foreach (array_slice($images, 0, self::MAX_GALLERY) as $url) {
                if ($path = $this->downloadImage($url)) {
                    $gallery[] = $path;
                }
            }
            if ($gallery || $update) {
                $disk->delete($post->gallery ?? []);
                $post->gallery = $gallery ?: null;
            }
        }

        $wasNew = ! $post->exists;
        $post->save();

        return $wasNew ? 'created' : 'updated';
    }

    /** Download an image, shrink it to a sensible size and store it. */
    public function downloadImage(string $url): ?string
    {
        $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5);
        if (str_starts_with($url, '//')) {
            $url = 'https:'.$url;
        }
        if (! preg_match('~^https?://~i', $url)) {
            return null;
        }

        // Prefer the original over a WordPress thumbnail such as photo-300x200.jpg
        $candidates = array_unique([preg_replace('/-\d{2,5}x\d{2,5}(?=\.\w{3,4}(\?|$))/', '', $url), $url]);

        foreach ($candidates as $candidate) {
            try {
                $response = $this->http()->get($candidate);
            } catch (\Throwable $e) {
                continue;
            }
            $body = $response->body();
            if (! $response->successful() || $body === '' || strlen($body) > self::IMAGE_MAX_BYTES) {
                continue;
            }
            $stored = $this->storeImage($body, $candidate);
            if ($stored) {
                return $stored;
            }
        }

        Log::info('News import: could not download image '.$url);

        return null;
    }

    private function storeImage(string $bytes, string $url): ?string
    {
        $info = @getimagesizefromstring($bytes);
        if (! $info) {
            return null;
        }
        [$width, $height] = $info;
        $mime = $info['mime'];
        $name = 'news/'.Str::random(32);

        // Resize big photos with GD when we can; otherwise keep the original.
        if (function_exists('imagecreatefromstring') && $width > self::IMAGE_MAX_WIDTH && in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $src = @imagecreatefromstring($bytes);
            if ($src) {
                $newH = (int) round($height * self::IMAGE_MAX_WIDTH / $width);
                $dst = imagecreatetruecolor(self::IMAGE_MAX_WIDTH, $newH);
                imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
                imagecopyresampled($dst, $src, 0, 0, 0, 0, self::IMAGE_MAX_WIDTH, $newH, $width, $height);
                ob_start();
                imagejpeg($dst, null, 82);
                $bytes = ob_get_clean();
                imagedestroy($src);
                imagedestroy($dst);
                $mime = 'image/jpeg';
            }
        }

        $ext = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => null,
        };
        if (! $ext) {
            return null;
        }

        Storage::disk('public')->put($name.'.'.$ext, $bytes);

        return $name.'.'.$ext;
    }

    private function http()
    {
        return Http::timeout(30)->connectTimeout(10)->retry(2, 500, throw: false)
            ->withHeaders(['User-Agent' => 'AMG-Foundation-website-importer/1.0', 'Accept' => 'application/json, */*']);
    }

    /* ------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------ */

    public static function category(array $names): string
    {
        $all = strtolower(implode(' ', $names));

        return match (true) {
            str_contains($all, 'press') || str_contains($all, 'media') => 'In the press',
            str_contains($all, 'announce') || str_contains($all, 'event') => 'Announcement',
            str_contains($all, 'field') || str_contains($all, 'outreach') => 'Field update',
            default => 'News',
        };
    }

    private static function date(array $item): Carbon
    {
        try {
            $date = $item['date_is_gmt'] ?? false
                ? Carbon::parse($item['date'], 'UTC')->setTimezone(config('app.timezone'))
                : Carbon::parse($item['date'], config('app.timezone'));
        } catch (\Throwable) {
            return now();
        }

        // Never schedule an imported post in the future by accident.
        return $date->isFuture() ? now() : $date;
    }

    /** Keep the summary only when someone wrote one, not WordPress's automatic one. */
    private static function excerpt(array $item, string $text): ?string
    {
        $excerpt = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($item['excerpt'] ?? ''), ENT_QUOTES | ENT_HTML5)));
        $excerpt = trim(preg_replace('/\s*(\[(&hellip;|…|\.\.\.)\]|Read more|Continue reading.*)$/iu', '', $excerpt));
        if ($excerpt === '') {
            return null;
        }

        $start = mb_substr(preg_replace('/\s+/', ' ', preg_replace('/^(## |- )/m', '', $text)), 0, 60);
        if ($start !== '' && str_starts_with($excerpt, mb_substr($start, 0, min(60, mb_strlen($excerpt))))) {
            return null;   // just the opening words of the story
        }

        return Str::limit($excerpt, 480);
    }

    public static function legacyPath(string $link): ?string
    {
        $parts = parse_url($link);
        if (! $parts) {
            return null;
        }
        $path = trim($parts['path'] ?? '', '/');

        return $path === '' ? null : rawurldecode($path);
    }

    private static function uniqueSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'news';
        $base = Str::limit($base, 180, '');
        $slug = $base;
        $i = 2;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private static function sameImage(string $a, string $b): bool
    {
        $strip = fn ($u) => preg_replace('/-\d{2,5}x\d{2,5}(?=\.\w{3,4}(\?|$))|-scaled(?=\.)/', '', strtok($u, '?'));

        return $strip($a) === $strip($b);
    }

    /** @return array<int, string> photo addresses found in the story, in order */
    public static function imagesIn(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }
        $doc = self::dom($html);
        $urls = [];
        foreach ($doc->getElementsByTagName('img') as $img) {
            /** @var DOMElement $img */
            $src = $img->getAttribute('data-src') ?: $img->getAttribute('src');
            // Use the biggest size listed in srcset when there is one.
            if ($srcset = $img->getAttribute('srcset')) {
                $best = 0;
                foreach (explode(',', $srcset) as $entry) {
                    [$u, $w] = array_pad(preg_split('/\s+/', trim($entry)), 2, '');
                    if ((int) $w > $best) {
                        $best = (int) $w;
                        $src = $u;
                    }
                }
            }
            if ($src && ! str_starts_with($src, 'data:') && ! preg_match('/(emoji|gravatar|spinner|loader|pixel)/i', $src)) {
                $urls[] = $src;
            }
        }

        // Drop repeats of the same photo at different sizes.
        $unique = [];
        foreach ($urls as $u) {
            if (! collect($unique)->contains(fn ($x) => self::sameImage($x, $u))) {
                $unique[] = $u;
            }
        }

        return $unique;
    }

    /**
     * Turn WordPress HTML into the simple text format the site uses:
     * paragraphs separated by blank lines, "## " headings and "- " lists.
     */
    public static function htmlToText(string $html): string
    {
        $html = preg_replace('/\[\/?[a-z_][a-z0-9_-]*(\s[^\]]*)?\]/i', '', $html); // shortcodes
        $html = preg_replace('/<!--.*?-->/s', '', $html);                           // block comments
        if (trim(strip_tags($html)) === '') {
            return '';
        }

        $blocks = [];
        $buffer = '';
        $flush = function () use (&$buffer, &$blocks) {
            $t = trim(preg_replace('/[ \t\x{00A0}]+/u', ' ', $buffer));
            $t = preg_replace('/ *\n */', "\n", $t);
            if ($t !== '') {
                foreach (preg_split('/\n+/', $t) as $line) {
                    if (trim($line) !== '') {
                        $blocks[] = trim($line);
                    }
                }
            }
            $buffer = '';
        };

        $walk = function (DOMNode $node) use (&$walk, &$buffer, &$blocks, $flush) {
            foreach ($node->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $buffer .= str_replace(["\r", "\n"], ' ', $child->nodeValue);

                    continue;
                }
                if (! $child instanceof DOMElement) {
                    continue;
                }
                $tag = strtolower($child->tagName);

                switch ($tag) {
                    case 'script': case 'style': case 'noscript': case 'iframe': case 'form':
                    case 'button': case 'svg': case 'img': case 'video': case 'audio': case 'object':
                        break;
                    case 'br':
                        $buffer .= "\n";
                        break;
                    case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                        $flush();
                        $heading = trim(preg_replace('/\s+/u', ' ', $child->textContent));
                        if ($heading !== '') {
                            $blocks[] = '## '.$heading;
                        }
                        break;
                    case 'ul': case 'ol':
                        $flush();
                        $items = [];
                        foreach ($child->childNodes as $li) {
                            if ($li instanceof DOMElement && strtolower($li->tagName) === 'li') {
                                $t = trim(preg_replace('/\s+/u', ' ', $li->textContent));
                                if ($t !== '') {
                                    $items[] = '- '.$t;
                                }
                            }
                        }
                        if ($items) {
                            $blocks[] = implode("\n", $items);
                        }
                        break;
                    case 'figcaption':
                        $flush();
                        break;   // photo captions add little without the photo
                    case 'p': case 'div': case 'section': case 'article': case 'blockquote':
                    case 'figure': case 'table': case 'tr': case 'header': case 'footer': case 'li': case 'dd': case 'dt':
                        $flush();
                        $walk($child);
                        $flush();
                        break;
                    case 'td': case 'th':
                        $walk($child);
                        $buffer .= ' ';
                        break;
                    default:
                        $walk($child);
                }
            }
        };

        $walk(self::dom($html)->getElementsByTagName('body')->item(0));
        $flush();

        $text = implode("\n\n", $blocks);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);

        // Drop lines that are only sharing buttons or "read more" leftovers.
        $text = preg_replace('/^(share this:?|like this:?|related|read more|continue reading)\s*$/mi', '', $text);

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }

    private static function dom(string $html): DOMDocument
    {
        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $doc;
    }
}
