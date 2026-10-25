<?php

namespace App\Support;

class Text
{
    /**
     * Turn plain text from the admin editor into simple blocks.
     * Blank lines separate paragraphs, lines starting with "- " form a list,
     * and lines starting with "## " become sub-headings.
     *
     * @return array<int, array{type: string, content: mixed}>
     */
    public static function blocks(?string $text): array
    {
        if (blank($text)) {
            return [];
        }

        $blocks = [];
        $chunks = preg_split("/\R{2,}/", trim(str_replace("\r\n", "\n", $text)));

        foreach ($chunks as $chunk) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", $chunk)), 'strlen'));

            if ($lines === []) {
                continue;
            }

            if (str_starts_with($lines[0], '## ')) {
                $blocks[] = ['type' => 'heading', 'content' => substr(array_shift($lines), 3)];
                if ($lines === []) {
                    continue;
                }
            }

            $isList = collect($lines)->every(fn ($l) => str_starts_with($l, '- '));

            $blocks[] = $isList
                ? ['type' => 'list', 'content' => array_map(fn ($l) => substr($l, 2), $lines)]
                : ['type' => 'paragraph', 'content' => implode(' ', $lines)];
        }

        return $blocks;
    }
}
