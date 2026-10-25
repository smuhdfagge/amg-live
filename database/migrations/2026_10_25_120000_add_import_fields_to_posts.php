<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('gallery')->nullable()->after('cover_image');
            $table->unsignedBigInteger('legacy_id')->nullable()->index()->after('source');   // post ID on the old site
            $table->string('legacy_path')->nullable()->index()->after('legacy_id');           // address on the old site, e.g. 2023/05/my-post
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['gallery', 'legacy_id', 'legacy_path']);
        });
    }
};
