<?php

use App\Support\WordPressImport;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Titles imported before the fix could still contain <strong> and similar
 * tags. Clean them once; titles typed in the admin without tags are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('posts')
            ->where(fn ($q) => $q->where('title', 'like', '%<%')->orWhere('title', 'like', '%&lt;%')->orWhere('title', 'like', '%&amp;%')->orWhere('title', 'like', '%&#%'))
            ->orderBy('id')
            ->each(function ($post) {
                $clean = WordPressImport::cleanTitle($post->title);
                if ($clean !== '' && $clean !== $post->title) {
                    DB::table('posts')->where('id', $post->id)->update(['title' => $clean]);
                }
            });
    }

    public function down(): void
    {
        // Nothing to undo.
    }
};
