<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();          // radio, jingle, video
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('language', 40)->nullable();
            $table->string('duration', 20)->nullable();
            $table->string('media_file')->nullable();      // uploaded audio or video
            $table->string('media_url', 500)->nullable();  // YouTube, Vimeo, SoundCloud or any link
            $table->string('thumbnail')->nullable();
            $table->boolean('allow_download')->default(true);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
