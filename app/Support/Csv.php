<?php

namespace App\Support;

/**
 * Writes CSV rows that are safe to open in Excel or Google Sheets.
 *
 * Text typed into the public forms could start with "=", "+", "-" or "@".
 * A spreadsheet would treat that as a formula and run it when an admin opens
 * the download (CSV or formula injection). Such cells get a leading apostrophe,
 * which makes the spreadsheet show them as plain text.
 */
class Csv
{
    public static function row($handle, array $cells): void
    {
        fputcsv($handle, array_map([self::class, 'cell'], $cells));
    }

    public static function cell(mixed $value): string
    {
        $value = (string) $value;

        return $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r", '|', '%'], true)
            ? "'".$value
            : $value;
    }
}
