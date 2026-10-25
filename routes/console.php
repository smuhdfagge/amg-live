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

/*
| Import news from the old WordPress website from the command line. Handy for
| a very large import, because it has no time limit:
|   php artisan news:import https://aminumagashigarbafoundation.org
|   php artisan news:import --file=/path/to/wordpress-export.xml
*/
Artisan::command('news:import {site? : Address of the old WordPress website} {--file= : A WordPress export (.xml) file instead} {--update : Update stories imported before} {--no-photos : Skip copying photos}', function (\App\Support\WordPressImport $importer) {
    $update = (bool) $this->option('update');
    $photos = ! $this->option('no-photos');
    $counts = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

    $save = function (array $items) use ($importer, $update, $photos, &$counts) {
        foreach ($items as $item) {
            try {
                $result = $importer->importItem($item, $update, $photos);
                $counts[$result]++;
                $this->line(str_pad($result, 8).' '.$item['title']);
            } catch (\Throwable $e) {
                $counts['failed']++;
                $this->error('failed   '.$item['title'].': '.$e->getMessage());
            }
        }
    };

    try {
        if ($file = $this->option('file')) {
            $save($importer->readExportFile($file));
        } elseif ($site = $this->argument('site')) {
            $site = \App\Support\WordPressImport::normaliseSiteUrl($site);
            $page = 1;
            do {
                $result = $importer->fetchPage($site, $page, 20);
                $save($result['items']);
                $page++;
            } while ($result['items'] && $page <= $result['pages']);
        } else {
            $this->error('Give the old website address, or --file=export.xml');

            return 1;
        }
    } catch (\RuntimeException $e) {
        $this->error($e->getMessage());

        return 1;
    }

    $this->info("Done. {$counts['created']} added, {$counts['updated']} updated, {$counts['skipped']} skipped, {$counts['failed']} failed.");

    return 0;
})->purpose('Import news posts from a WordPress website or export file');
