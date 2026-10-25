# Aminu Magashi Garba Foundation website

The website of the Aminu Magashi Garba Foundation (AMG Foundation), a non-profit based in Kano, Nigeria. Built with Laravel 13.

## What is included

**Public pages**

- Home, with the mission, the groups the Foundation serves, focus areas, objectives, programmes and news
- About us: who we are, vision, mission, objectives, office and registration details
- Our work: ten focus areas, each with its own page, plus how the Foundation works
- Programmes: added through the admin area, each with its own page
- Partners: who the Foundation works with, plus a list built from the programmes
- Resources: radio programmes, audio jingles and videos, with players on the page, filters by type and optional downloads
- News, with support for stories written on the site and links to press coverage
- Get involved, with a volunteer application form
- Support our work, with ways to give (bank details appear once you add them)
- Contact, with an enquiry form and map
- Privacy notice, sitemap.xml and custom error pages

Sections with nothing to show yet (programmes, news, phone, email, registration numbers) stay hidden until you add them.

**Admin area** at `/admin`

- Dashboard with unread messages and new volunteer applications
- Add, edit and delete news (with photos, drafts and scheduled publishing)
- Add, edit and reorder programmes (with photos and a "feature on home page" switch)
- Add radio programmes, jingles and videos: upload the file, or paste a YouTube, Vimeo, SoundCloud or other link
- Read and reply to contact messages
- Review volunteer applications and download them as CSV
- View and export newsletter subscribers
- Change your own name, email and password

Every form is protected against spam with a hidden field and rate limits, and sends an email alert to `AMG_NOTIFY_EMAIL`.

## Logo

`public/images/` holds the logo in several forms:

- `amg-logo.svg`: the full logo as a vector. It stays sharp at any size, so use it for print and large banners.
- `amg-logo.png`: the same logo at 2700 pixels wide, with a transparent background.
- `amg-logo-white.svg` / `.png`: white lettering for dark backgrounds (used in the footer).
- `amg-mark.svg` / `amg-mark-512.png`: a square AMG icon, used for the favicon and the admin area.

## Requirements

- PHP 8.3 or newer (8.4 recommended), with the `pdo_mysql` (or `pdo_sqlite`), `mbstring`, `openssl`, `fileinfo` and `gd` extensions
- Composer
- MySQL 8 / MariaDB 10.6, or SQLite

No Node.js build step is needed. Styles, scripts and fonts are plain files in `public/`.

## Run it locally

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite        # or set the DB_* values for MySQL
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open http://localhost:8000. Sign in at http://localhost:8000/admin with the `ADMIN_EMAIL` and `ADMIN_PASSWORD` from `.env`, then change the password under **My account**.

Run the tests with `php artisan test`.

## Deploy to a server

Use the deploy script. It finds a suitable PHP binary, checks extensions, pulls the latest code, installs packages, runs migrations, creates the first admin account on the first deploy only, links storage and caches everything. The site goes into maintenance mode during the update and always comes back up, even if a step fails.

### First deploy

```bash
git clone https://github.com/smuhdfagge/amg-live.git ~/amg
cd ~/amg
bash deploy.sh
```

The first run creates `.env` and stops. Open it and fill in:

- `APP_URL=https://aminumagashigarbafoundation.org`
- the database settings (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
- mail settings (`MAIL_MAILER=smtp` and your SMTP details) so form alerts are delivered
- `ADMIN_EMAIL` and a strong `ADMIN_PASSWORD`
- the Foundation's phone, email and registration details (`AMG_PHONE`, `AMG_EMAIL`, `AMG_CAC` and so on)

Then run `bash deploy.sh` again.

### Where the site is served from

- **VPS, or cPanel where you can set the document root:** point the domain to `~/amg/public` and run `bash deploy.sh`.
- **cPanel where the site must live in `public_html`:** run `bash deploy.sh --public-html ~/public_html`. The script copies the public files there, writes an `index.php` that loads the app from `~/amg`, and links the uploads folder.

### Updating the live site

Push your changes to GitHub, then on the server run the same command again (`bash deploy.sh`, or with `--public-html` if you use it). Re-running is safe. It never resets the admin password or overwrites content edited in the admin. Use `--seed` only if you deliberately want to re-run the database seeder.

Other options: `--no-pull` (when you upload files by FTP), `--branch NAME`, and `--php /path/to/php` to choose the PHP binary yourself. Run `bash deploy.sh --help` for the full list.

For cPanel's Git Version Control, see `deploy/cpanel.yml.example`.

### Deploying through a hosting panel or platform

If your host runs the build for you, set its PHP version to 8.3 or 8.4 and use:

- Build: `composer install --no-dev --optimize-autoloader`
- Deploy: `php artisan migrate --force && php artisan amg:seed-if-empty && php artisan storage:link && php artisan optimize`

### About the lock file and PHP versions

`composer.json` pins the platform to PHP 8.3 (`config.platform.php`), so `composer update` always produces a lock file that installs on PHP 8.3, 8.4 and 8.5, whatever PHP version you run it on. Do not remove that setting. Without it, a lock built on PHP 8.4 pulls in Symfony 8 packages that need PHP 8.4.1+, and the server fails with "Your lock file does not contain a compatible set of packages".

If you ever see that error, check which PHP Composer is really using with `php -v` (on cPanel the command-line PHP is often older than the website's PHP). The deploy script finds the right one for you.

## Importing news from the old website

In the admin, go to **News** and select **Import from old website**. Either enter the old site's address, or upload the export file from WordPress (**Tools → Export → Posts**). The import brings across titles, dates, text, categories, the featured image and the photos inside each story, ten stories at a time. Stories already imported are skipped, so it is safe to run again.

Run it while the old site is still online, because the photos are copied from it. Once the new site is live on the old address, old story links redirect to the new pages.

For a very large import you can use the command line instead:

```bash
php artisan news:import https://aminumagashigarbafoundation.org
php artisan news:import --file=/path/to/export.xml --uploads=/path/to/uploads.zip
```

If the new server cannot connect to the old site, the import screen says whether the new host is blocking outgoing connections or the old host is blocking the new server. Either way, the export file plus a zip of the old `wp-content/uploads` folder works without any connection.

## Radio programmes, jingles and videos

Add them under **Resources** in the admin. Each one is either an uploaded file or a link:

- **Uploaded audio** (MP3, M4A, WAV, OGG) plays on the page, and can be downloaded if you tick the box.
- **Uploaded video** (MP4, WEBM, MOV) plays on the page too.
- **YouTube or Vimeo links** play on the page. YouTube videos use their own picture as the cover.
- **Any other link** (SoundCloud, Facebook, a radio station) shows a Listen or Watch button.

Long videos are best put on YouTube, because they take a lot of hosting space and most shared hosting limits upload size.

Upload limits are 50 MB for audio and 200 MB for video (`AMG_MAX_AUDIO_MB`, `AMG_MAX_VIDEO_MB` in `.env`). The server's PHP settings must allow that too. `public/.user.ini` raises them to 256 MB on most cPanel hosting. If the admin form still shows a lower limit, raise `upload_max_filesize` and `post_max_size` in cPanel's **MultiPHP INI Editor**, or ask your host.

## Editing content

- **News and programmes:** use the admin area.
- **Organisation details** (address, vision, mission, objectives, focus areas, ways of working): edit `config/amg.php`. Phone, emails, registration numbers, social links and bank details are set from `.env`.
- **Logo and colours:** the logo is in `public/images/`. Brand colours are at the top of `public/css/site.css`.

The focus area descriptions in `config/amg.php` are written from the Foundation's mission and objectives. Please review them before launch.
