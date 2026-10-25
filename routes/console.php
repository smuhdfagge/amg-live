<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

/*
| Seeds the starting content (admin account, programmes, first news item)
| only when the database is empty. Safe to run on every deployment: it
| never overwrites an admin password or programme edits made later.
*/
Artisan::command('swatch:seed-if-empty', function () {
    if (User::query()->exists() || Project::query()->exists()) {
        $this->info('Content already in place. Skipping the seeder.');

        return 0;
    }

    $this->call('db:seed', ['--force' => true]);
    $this->info('Starting content added. Sign in at /admin with ADMIN_EMAIL and ADMIN_PASSWORD from .env.');

    return 0;
})->purpose('Add the starting SWATCH content when the database is empty');
