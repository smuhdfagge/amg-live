<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\WordPressImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

/**
 * Imports news from the old WordPress website, ten posts at a time, so a long
 * import never runs into the server's time limit. Progress lives in the session.
 */
class NewsImportController extends Controller
{
    private const BATCH = 10;

    public function show(Request $request): View
    {
        return view('admin.posts.import', ['job' => $request->session()->get('news_import')]);
    }

    public function start(Request $request, WordPressImport $importer): RedirectResponse
    {
        $data = $request->validate([
            'source' => ['required', 'in:site,file'],
            'site_url' => ['required_if:source,site', 'nullable', 'string', 'max:255'],
            'file' => ['required_if:source,file', 'nullable', 'file', 'extensions:xml', 'max:102400'],
            'photos_zip' => ['nullable', 'file', 'extensions:zip'],
            'update' => ['nullable', 'boolean'],
            'photos' => ['nullable', 'boolean'],
        ], [
            'site_url.required_if' => 'Enter the address of the old website.',
            'file.required_if' => 'Choose the export file you downloaded from WordPress.',
            'file.extensions' => 'Upload the .xml file that WordPress gives you under Tools, then Export.',
            'photos_zip.extensions' => 'The photos must be a .zip file.',
            'photos_zip.uploaded' => 'The photos zip is bigger than this server accepts. Zip one year of photos at a time, or ask for the command-line import.',
        ]);

        $job = [
            'source' => $data['source'],
            'update' => $request->boolean('update'),
            'photos' => $request->boolean('photos'),
            'position' => 0,      // page number already done (site) or posts done (file)
            'total' => null,
            'counts' => ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0],
            'errors' => [],
            'done' => false,
            'started_at' => now()->toIso8601String(),
        ];

        try {
            if ($data['source'] === 'site') {
                $job['site_url'] = WordPressImport::normaliseSiteUrl($data['site_url']);
                $first = $importer->fetchPage($job['site_url'], 1, self::BATCH);
                $job['ipv4'] = $importer->forceIpv4;
                $job['total'] = $first['total'] ?: count($first['items']);
                $job['pages'] = $first['pages'];
            } else {
                $path = $request->file('file')->storeAs('imports', 'wordpress-'.Str::random(12).'.xml', 'local');
                $job['file'] = $path;
                $job['total'] = count($importer->readExportFile(Storage::disk('local')->path($path)));

                if ($request->hasFile('photos_zip')) {
                    $zip = $request->file('photos_zip')->getRealPath();
                    $dir = 'imports/photos-'.Str::random(12);
                    $job['uploads_dir'] = $dir;
                    $job['photos_found'] = WordPressImport::extractUploads($zip, Storage::disk('local')->path($dir));
                }
            }
        } catch (RuntimeException $e) {
            $this->cleanUp($job);

            return back()->withInput()->withErrors([$data['source'] === 'site' ? 'site_url' : 'file' => $e->getMessage()]);
        }

        if (! $job['total']) {
            $this->cleanUp($job);

            return back()->withInput()->withErrors([$data['source'] === 'site' ? 'site_url' : 'file' => 'No published news posts were found.']);
        }

        $request->session()->put('news_import', $job);

        return redirect()->route('admin.news-import.show');
    }

    /** Import the next batch. The progress page calls this again until done. */
    public function step(Request $request, WordPressImport $importer): RedirectResponse
    {
        $job = $request->session()->get('news_import');
        if (! $job || $job['done']) {
            return redirect()->route('admin.news-import.show');
        }

        @set_time_limit(300);
        $importer->deadHosts = $job['dead_hosts'] ?? [];
        $importer->forceIpv4 = $job['ipv4'] ?? false;
        if (! empty($job['uploads_dir'])) {
            $importer->localUploads = Storage::disk('local')->path($job['uploads_dir']);
        }

        try {
            if ($job['source'] === 'site') {
                $page = $job['position'] + 1;
                $items = $importer->fetchPage($job['site_url'], $page, self::BATCH)['items'];
                $job['position'] = $page;
                $finished = $items === [] || $page >= ($job['pages'] ?? $page);
            } else {
                $all = $importer->readExportFile(Storage::disk('local')->path($job['file']));
                $items = array_slice($all, $job['position'], self::BATCH);
                $job['position'] += count($items);
                $finished = $job['position'] >= count($all);
            }
        } catch (RuntimeException $e) {
            $job['errors'][] = $e->getMessage();
            $job['done'] = true;
            $this->cleanUp($job);
            $request->session()->put('news_import', $job);

            return redirect()->route('admin.news-import.show');
        }

        foreach ($items as $item) {
            try {
                $result = $importer->importItem($item, $job['update'], $job['photos']);
                $job['counts'][$result]++;
            } catch (\Throwable $e) {
                $job['counts']['failed']++;
                $job['errors'][] = '"'.Str::limit($item['title'] ?? 'Untitled', 60).'": '.Str::limit($e->getMessage(), 200);
                report($e);
            }
        }

        $job['dead_hosts'] = array_values(array_unique($importer->deadHosts));
        $job['ipv4'] = $importer->forceIpv4;

        if ($finished) {
            $job['done'] = true;
            $this->cleanUp($job);
        }
        $job['errors'] = array_slice($job['errors'], -20);
        $request->session()->put('news_import', $job);

        return redirect()->route('admin.news-import.show');
    }

    public function reset(Request $request): RedirectResponse
    {
        $this->cleanUp($request->session()->pull('news_import') ?? []);

        return redirect()->route('admin.news-import.show');
    }

    private function cleanUp(array $job): void
    {
        if (! empty($job['file'])) {
            Storage::disk('local')->delete($job['file']);
        }
        if (! empty($job['uploads_dir'])) {
            Storage::disk('local')->deleteDirectory($job['uploads_dir']);
        }
    }
}
