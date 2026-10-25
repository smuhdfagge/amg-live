# SWATCH website

The website of Support for Women and Teenage Children (SWATCH), a women-led NGO in Kano, Nigeria. Built with Laravel 13.

## What is included

**Public pages**

- Home, with the programme record, reach figures, thematic areas, recent programmes and news
- About us: history, vision and mission, governance, team, registration details
- Our work: ten thematic areas, each with its own page, plus how SWATCH works
- Programmes: the full programme record and a page for each programme
- Partners: every partner organisation and the work done together
- News, with support for stories written on the site and links to press coverage
- Get involved, with a volunteer application form
- Support our work, with ways to give (bank details appear once you add them)
- Contact, with an enquiry form and map
- Privacy notice, sitemap.xml, custom error pages, and redirects from the old WordPress addresses

**Admin area** at `/admin`

- Dashboard with unread messages and new volunteer applications
- Add, edit and delete news (with photos, drafts and scheduled publishing)
- Add, edit and reorder programmes (with photos and a "feature on home page" switch)
- Read and reply to contact messages
- Review volunteer applications and download them as CSV
- View and export newsletter subscribers
- Change your own name, email and password

Every form is protected against spam with a hidden field and rate limits, and sends an email alert to `SWATCH_NOTIFY_EMAIL`.

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

Use the deploy script. It finds a suitable PHP binary, checks extensions, pulls the latest code, installs packages, runs migrations, adds the starting content on the first deploy only, links storage and caches everything. The site goes into maintenance mode during the update and always comes back up, even if a step fails.

### First deploy

```bash
git clone https://github.com/smuhdfagge/swatch-live.git ~/swatch
cd ~/swatch
bash deploy.sh
```

The first run creates `.env` and stops. Open it and fill in:

- `APP_URL=https://swatch.org.ng`
- the database settings (`DB_CONNECTION=mysql`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`)
- mail settings (`MAIL_MAILER=smtp` and your SMTP details) so form alerts are delivered
- `ADMIN_EMAIL` and a strong `ADMIN_PASSWORD`

Then run `bash deploy.sh` again.

### Where the site is served from

- **VPS, or cPanel where you can set the document root:** point the domain to `~/swatch/public` and run `bash deploy.sh`.
- **cPanel where the site must live in `public_html`:** run `bash deploy.sh --public-html ~/public_html`. The script copies the public files there, writes an `index.php` that loads the app from `~/swatch`, and links the uploads folder.

### Updating the live site

Push your changes to GitHub, then on the server run the same command again (`bash deploy.sh`, or with `--public-html` if you use it). Re-running is safe. It never resets the admin password or overwrites content edited in the admin. Use `--seed` only if you deliberately want to restore the starting content.

Other options: `--no-pull` (when you upload files by FTP), `--branch NAME`, and `--php /path/to/php` to choose the PHP binary yourself. Run `bash deploy.sh --help` for the full list.

For cPanel's Git Version Control, see `deploy/cpanel.yml.example`.

### Deploying through a hosting panel or platform

If your host runs the build for you, set its PHP version to 8.3 or 8.4 and use:

- Build: `composer install --no-dev --optimize-autoloader`
- Deploy: `php artisan migrate --force && php artisan swatch:seed-if-empty && php artisan storage:link && php artisan optimize`

### About the lock file and PHP versions

`composer.json` pins the platform to PHP 8.3 (`config.platform.php`), so `composer update` always produces a lock file that installs on PHP 8.3, 8.4 and 8.5, whatever PHP version you run it on. Do not remove that setting. Without it, a lock built on PHP 8.4 pulls in Symfony 8 packages that need PHP 8.4.1+, and the server fails with "Your lock file does not contain a compatible set of packages".

If you ever see that error, check which PHP Composer is really using with `php -v` (on cPanel the command-line PHP is often older than the website's PHP). The deploy script finds the right one for you.

## Editing content

- **News and programmes:** use the admin area.
- **Organisation details** (address, phone, emails, registration numbers, vision, mission, team numbers, thematic areas): edit `config/swatch.php`. Contact details, social links and bank details can also be set from `.env`.
- **Logo and colours:** the logo is in `public/images/`. Brand colours are at the top of `public/css/site.css`.

Programme figures come from SWATCH's own records. Please check them, and the "Most recent" period for SHARP Task Order 3, before launch.
