<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        Post::updateOrCreate(
            ['slug' => 'advanced-hiv-disease-among-nigerian-children'],
            [
                'title' => 'Report raises the alarm over advanced HIV disease among Nigerian children',
                'category' => 'In the press',
                'excerpt' => 'Advanced HIV disease in children calls for renewed effort. Leadership newspaper reports on the findings.',
                'external_url' => 'https://leadership.ng/report-raises-the-alarm-over-advanced-hiv-disease-among-nigerian-children/',
                'source' => 'Leadership',
                'published_at' => now(),
            ],
        );
    }
}
