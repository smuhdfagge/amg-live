<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

/*
| Creates the first admin account only when the database is empty. Safe to
| run on every deployment: it never overwrites an admin password or any
| programmes and news added later through the admin area.
*/
Artisan::command('amg:seed-if-empty', function () {
    if (User::query()->exists() || Project::query()->exists()) {
        $this->info('Content already in place. Skipping the seeder.');

        return 0;
    }

    $this->call('db:seed', ['--force' => true]);
    $this->info('Admin account created. Sign in at /admin with ADMIN_EMAIL and ADMIN_PASSWORD from .env.');

    return 0;
})->purpose('Create the first admin account when the database is empty');
