# Deploying MaplebyOS

This guide covers putting MaplebyOS on a live server and releasing updates. It is written for the current setup (cPanel shared hosting with SSH or Terminal access) and also covers a VPS with Nginx.

- [1. What the server needs](#1-what-the-server-needs)
- [2. Before every release (on your computer)](#2-before-every-release-on-your-computer)
- [3. First-time deployment on cPanel](#3-first-time-deployment-on-cpanel)
- [4. First-time deployment on a VPS (alternative)](#4-first-time-deployment-on-a-vps-alternative)
- [5. The `.env` file](#5-the-env-file)
- [6. Database setup, first admin and the tracking sheet](#6-database-setup-first-admin-and-the-tracking-sheet)
- [7. Cron: scheduler and queue](#7-cron-scheduler-and-queue)
- [8. Releasing an update](#8-releasing-an-update)
- [9. Upgrading an existing live site to the redesign (one time)](#9-upgrading-an-existing-live-site-to-the-redesign-one-time)
- [10. Backups](#backups)
- [11. Security checklist](#11-security-checklist)
- [12. Troubleshooting](#12-troubleshooting)
- [13. Rolling back](#13-rolling-back)

---

## 1. What the server needs

| Requirement | Notes |
| --- | --- |
| PHP **8.2 or newer** (8.3 recommended) | Select it in cPanel → *MultiPHP Manager* for the domain, and make sure the **command-line** `php` is the same version (`php -v`). On many cPanel hosts the CLI binary is e.g. `/opt/cpanel/ea-php83/root/usr/bin/php`. |
| PHP extensions | `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `intl` (Filament needs it), `zip`, `gd`, `curl`. Enable in cPanel → *Select PHP Version* → Extensions. |
| MySQL 8 / MariaDB 10.4+ | One database and one user with all privileges on it. |
| Composer 2 | Usually available over SSH as `composer`; otherwise vendor files are uploaded from your computer. |
| SSH or cPanel *Terminal* | Strongly recommended: migrations and the importer are run from the command line. |
| HTTPS | Free AutoSSL in cPanel, or Let's Encrypt on a VPS. |

Node.js is **not** needed on the server: the frontend is built on your computer and the built files are uploaded.

---

## 2. Before every release (on your computer)

```bash
git checkout main
git pull

composer install
npm ci
npm run build          # produces public/build (not stored in git)

php artisan test       # all tests must pass
```

`public/build` is git-ignored, so it **must** be built and uploaded with every release that changes anything under `resources/js` or `resources/css`. Forgetting this is the most common cause of a blank or broken staff app after a deploy.

---

## 3. First-time deployment on cPanel

Recommended layout: the whole app lives in a folder **outside** `public_html`, and only the `public` folder is web-visible.

```
/home/<cpaneluser>/
  maplebyos/            ← the Laravel app (app, Modules, vendor, storage, .env, …)
    public/             ← document root for the domain
  public_html/          ← left alone, or used for something else
```

### Step 1: Upload the code

Option A, with git over SSH (preferred):

```bash
cd ~
git clone https://github.com/lxlbxl/MaplebyOS.git maplebyos
cd maplebyos
composer install --no-dev --optimize-autoloader
```

Then upload your locally built `public/build` folder into `~/maplebyos/public/build` (File Manager or SFTP).

Option B, zip upload (no git or Composer on the server): run `composer install --no-dev --optimize-autoloader` and `npm run build` locally, zip the project **without** `node_modules`, `.git`, `tests`, `storage/logs/*` and your local `.env`, upload the zip to `/home/<cpaneluser>/`, and extract it as `maplebyos`.

### Step 2: Point the domain at `public`

cPanel → *Domains* → the domain → *Manage* → set **Document Root** to `maplebyos/public`.

If your host does not let you change the document root of the primary domain, use the existing split layout that `deploy.bat` produces instead (app files in your home folder, the contents of `public/` in `public_html`). In that layout Laravel looks for its public folder at `~/public`, so also do **one** of the following, otherwise the staff app cannot find its built CSS and JS:

```bash
# either: make ~/public point at public_html
cd ~ && ln -s public_html public
```

or add this line to `public_html/index.php` right after `$app = require_once …`:

```php
$app->usePublicPath(__DIR__);
```

In the split layout the paths in `public_html/index.php` must reach the app folder; with the app directly in your home folder the default `__DIR__.'/../vendor/autoload.php'` and `__DIR__.'/../bootstrap/app.php'` are correct.

### Step 3: Create the database

cPanel → *MySQL Databases*: create a database (e.g. `cpuser_mapleby`), a user with a strong password, and add the user to the database with **All Privileges**.

### Step 4: Create `.env`

```bash
cd ~/maplebyos
cp .env.example .env
php artisan key:generate
```

Edit `.env` (see [section 5](#5-the-env-file)) with the database details and the site URL.

### Step 5: Permissions and storage link

```bash
chmod -R 775 storage bootstrap/cache
php artisan storage:link
```

If `storage:link` fails because symlinks are disabled, the old `create_storage_link.php` helper from `deploy.bat` does the same thing; delete it after use.

### Step 6: Database, roles and cache

Continue with [section 6](#6-database-setup-first-admin-and-the-tracking-sheet), then set up cron ([section 7](#7-cron-scheduler-and-queue)), then:

```bash
php artisan optimize
```

Open `https://<your-domain>/app/login` and `https://<your-domain>/admin` to check.

---

## 4. First-time deployment on a VPS (alternative)

Ubuntu 24.04 with Nginx, PHP-FPM 8.3 and MySQL:

```bash
sudo apt install nginx mysql-server php8.3-fpm php8.3-{mysql,mbstring,xml,bcmath,intl,zip,gd,curl} unzip git
# install Composer from getcomposer.org

sudo mkdir -p /var/www && cd /var/www
sudo git clone https://github.com/lxlbxl/MaplebyOS.git maplebyos
sudo chown -R $USER:www-data maplebyos && cd maplebyos
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
chmod -R 775 storage bootstrap/cache && sudo chgrp -R www-data storage bootstrap/cache
php artisan storage:link
```

Upload `public/build` from your computer (or install Node 20 on the server and run `npm ci && npm run build`).

Nginx site (`/etc/nginx/sites-available/maplebyos`):

```nginx
server {
    listen 80;
    server_name example.com www.example.com;
    root /var/www/maplebyos/public;
    index index.php;
    client_max_body_size 20M;   # sheet uploads

    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ /\.(?!well-known) { deny all; }
}
```

Enable it, add HTTPS with `sudo certbot --nginx`, then follow sections 5 to 7. On a VPS run the queue worker with Supervisor instead of cron (see [section 7](#7-cron-scheduler-and-queue)).

---

## 5. The `.env` file

The values that matter in production:

```dotenv
APP_NAME="MaplebyOS"
APP_ENV=production
APP_KEY=base64:...            # generated by php artisan key:generate, never share or change it
APP_DEBUG=false               # must be false on a live site
APP_URL=https://your-domain.com

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpuser_mapleby
DB_USERNAME=cpuser_mapleby
DB_PASSWORD="strong-password"

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true    # HTTPS only
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp              # for notifications (task overdue etc.)
MAIL_HOST=mail.your-domain.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=no-reply@your-domain.com
MAIL_PASSWORD="..."
MAIL_FROM_ADDRESS=no-reply@your-domain.com
MAIL_FROM_NAME="Mapleby"
```

After any `.env` change run `php artisan optimize` again (config is cached).

`.env` is never committed to git. Keep a copy in your password manager.

---

## 6. Database setup, first admin and the tracking sheet

```bash
cd ~/maplebyos

php artisan migrate --force
php artisan db:seed --force
```

- `migrate` creates every table, including the redesign's catalog, containers, milestones, invoices and charges, and **seeds the catalog** (makes and models with known misspellings, shipping lines with container prefixes, platforms, ports).
- `db:seed` creates the roles (Super Admin, Staff, User), permissions, default settings and two starter users:
  `admin@adgod-os.com` and `staff@adgod-os.com`, both with password **`password`**.

**Immediately** log in to `/admin`, change both passwords (or create real accounts for each staff member and delete the starter ones).

### Load the tracking sheet

Export the Google Sheet as CSV (*File → Download → Comma-separated values*) and upload it to the server outside the web root, e.g. `~/imports/sheet.csv`.

```bash
mkdir -p ~/imports
php artisan operations:import-tracking-sheet ~/imports/sheet.csv --dry-run
```

Read the report: rows, cars to create or update, containers to create, the list of spelling and date fixes, and the "Needs a look" list. When happy:

```bash
php artisan operations:import-tracking-sheet ~/imports/sheet.csv
```

The import is safe to run again later with a newer export: existing cars are updated, not duplicated. Staff can also import from the Vehicles page (**Import sheet**, preview then confirm).

Delete the CSV from the server when done; it contains customer data.

---

## 7. Cron: scheduler and queue

Laravel's scheduler handles hourly overdue-task notifications (and backups, once the backup package is installed). Notifications are queued, so a queue worker is needed too.

### cPanel

cPanel → *Cron Jobs*. Add two jobs (adjust the PHP path and username):

```
* * * * *  cd /home/<cpaneluser>/maplebyos && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
* * * * *  cd /home/<cpaneluser>/maplebyos && /usr/local/bin/php artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

Find the right PHP path with `which php` in Terminal (it must be PHP 8.2+).

### VPS

Use the same `schedule:run` cron line, and run the worker permanently with Supervisor:

```ini
[program:maplebyos-worker]
command=php /var/www/maplebyos/artisan queue:work --sleep=3 --tries=3 --max-time=3600
user=www-data
autostart=true
autorestart=true
stopwaitsecs=3600
```

---

## 8. Releasing an update

1. On your computer: [section 2](#2-before-every-release-on-your-computer) (pull, build, test).
2. On the server:

```bash
cd ~/maplebyos
php artisan down --refresh=15           # maintenance page for visitors

git pull origin main                    # or upload the changed files / zip
composer install --no-dev --optimize-autoloader
# upload the new public/build from your computer now

php artisan migrate --force             # applies only new migrations
php artisan optimize:clear
php artisan optimize
php artisan queue:restart               # workers pick up new code

php artisan up
```

3. Check: log in to `/app`, open Vehicles, a car and a container; open `/tracking` and look up a tracking ID.

If you upload by zip, never overwrite the server's `.env` or `storage/` folder.

---

## 9. Upgrading an existing live site to the redesign (one time)

The redesign (PR #1) changes the shipments table and adds new tables. On a site that already has data:

1. **Back up the database first** (cPanel → *Backup* → download a MySQL database backup, or phpMyAdmin → Export).
2. Deploy as in [section 8](#8-releasing-an-update), including `php artisan migrate --force`. What the migrations do:
   - Create the catalog tables and seed them.
   - Create the containers and milestones tables.
   - Add tracking fields to shipments (tracking ID, container, slot, dates, purchase details), make customer, service type and title optional, and map old statuses to the new pipeline: `pending`/`draft` → Added, `received`/`scheduled` → At yard, `shipped` → Sailed. Every existing shipment gets a tracking ID based on when it was created.
   - Create invoices and shipment charges, and let payments link to invoices.
   - The Quoting and Finance migrations used to sit in a folder the server could not see (a letter-case bug), so on some live databases the `quotes` and `payments` tables may be missing. They are now created if missing and skipped if present.
3. Import the tracking sheet as in [section 6](#load-the-tracking-sheet) (dry run first). Cars already in the database with the same VIN are updated rather than duplicated.
4. Review the "Needs a look" rows with the team.
5. The redesign also deleted old public debug scripts (`public/clear_cache.php`, `public/fix_deployment.php`, `public/deep_debug.php`, `public/laravel_diag.php`). If the server's files were uploaded by zip, **delete those files on the server by hand**, because a zip upload does not remove files.

To undo the database part, restore the backup from step 1 (the status mapping cannot be reversed exactly by `migrate:rollback`).

---

## Backups

`routes/console.php` schedules `backup:run --only-db` (02:00) and `backup:clean` (03:00), but the `spatie/laravel-backup` package they need is **not installed**, so those two tasks currently fail every night (the error shows in the log). Until that package is added, use one of these:

**cPanel built-in backups.** cPanel → *Backup*: download a database backup weekly, or let the host's automatic backups run (check how many days they keep).

**Nightly `mysqldump` via cron** (keeps 14 days):

```
0 2 * * *  mkdir -p ~/backups && mysqldump --single-transaction -u <dbuser> -p'<dbpass>' <dbname> | gzip > ~/backups/mapleby-$(date +\%F).sql.gz && find ~/backups -name 'mapleby-*.sql.gz' -mtime +14 -delete
```

Copy backups off the server regularly (download them, or sync to cloud storage). A backup that only lives on the same server is lost with the server.

To restore: `gunzip < mapleby-YYYY-MM-DD.sql.gz | mysql -u <dbuser> -p <dbname>`.

---

## 11. Security checklist

- [ ] `APP_ENV=production` and `APP_DEBUG=false`
- [ ] HTTPS on, `SESSION_SECURE_COOKIE=true`
- [ ] Starter accounts' passwords changed or accounts removed
- [ ] Document root is the `public` folder; `.env`, `storage` and `vendor` are not reachable from the web (try `https://<domain>/.env`: it must not download)
- [ ] Old debug scripts are not on the server (`public/clear_cache.php`, `fix_deployment.php`, `deep_debug.php`, `laravel_diag.php`, and root `debug_*.php`)
- [ ] No SQL dumps or sheet exports in web-visible folders; `database/dumps/*.sql` removed from the server
- [ ] `storage/` and `bootstrap/cache/` are writable by the web server, nothing else is world-writable
- [ ] Backups run and have been test-restored at least once
- [ ] Only Super Admins have the `manage_settings` permission (it allows downloading backups)

---

## 12. Troubleshooting

| Symptom | Likely cause and fix |
| --- | --- |
| **500 error** on every page | Check `storage/logs/laravel-*.log`. Common causes: missing `APP_KEY` (`php artisan key:generate`), wrong database details, `storage` not writable, PHP older than 8.2. |
| Staff app is blank or shows "Vite manifest not found" | `public/build` was not uploaded or is out of date. Build locally and upload. In the split `public_html` layout, see [step 2](#step-2-point-the-domain-at-public). |
| Changes to `.env` have no effect | Config is cached: `php artisan optimize:clear && php artisan optimize`. |
| `Base table or view not found` | Migrations did not run: `php artisan migrate --force`. `php artisan migrate:status` lists what is pending. |
| Make/model fields never suggest anything | The catalog is empty: `php artisan db:seed --class="Modules\Operations\Database\Seeders\OperationsCatalogSeeder" --force`. |
| Import says "This does not look like the tracking sheet" | The CSV is missing the `TRACKING ID` or `VIN` header. Export the right tab of the sheet. |
| Sheet upload in the browser fails for big files | Raise `upload_max_filesize` and `post_max_size` (cPanel → *MultiPHP INI Editor*), or import over SSH. |
| Images uploaded in the app do not show | `php artisan storage:link` was not run. |
| Overdue-task emails never arrive | Cron jobs missing (section 7) or `MAIL_*` settings wrong. Test with `php artisan schedule:run` and check the log. |
| `php artisan` uses the wrong PHP version | Call the right binary explicitly, e.g. `/opt/cpanel/ea-php83/root/usr/bin/php artisan …`. |
| Public tracking says "Too many requests" | Rate limit of 30 lookups per minute per visitor; wait a minute. |

---

## 13. Rolling back

1. `php artisan down`
2. Return the code to the previous version: `git log --oneline` to find it, then `git checkout <previous-commit>` (or re-upload the previous zip). Upload the matching `public/build`.
3. If the release included migrations, either `php artisan migrate:rollback --step=<number of new migrations>` or, safer for data changes, restore the database backup taken before the release.
4. `php artisan optimize:clear && php artisan optimize && php artisan queue:restart`
5. `php artisan up`

Then return to `main` (`git checkout main`) once the problem is fixed.
