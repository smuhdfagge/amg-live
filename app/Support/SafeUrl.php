<?php

namespace App\Support;

/**
 * Links that come from the database (typed by an admin, or brought in by an
 * import) go into href and src attributes. Blade escapes quotes, but it cannot
 * stop a "javascript:" address from running when clicked. Only plain web
 * addresses are let through.
 */
class SafeUrl
{
    public static function web(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        // Strip control characters and spaces that browsers ignore inside a scheme.
        $clean = preg_replace('/[\x00-\x20\x7F]+/', '', $url);
        $scheme = strtolower((string) parse_url($clean, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) && filter_var($clean, FILTER_VALIDATE_URL) ? $url : null;
    }

    /** Validation rule for admin forms: a full http or https address. */
    public static function rule(): array
    {
        return ['url:http,https', function (string $attribute, mixed $value, \Closure $fail) {
            if (filled($value) && self::web($value) === null) {
                $fail('Enter a full web address starting with https:// or http://');
            }
        }];
    }
}
