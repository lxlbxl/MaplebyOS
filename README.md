# MaplebyOS

MaplebyOS is the operating system for Mapleby's car export business. Cars are bought in North America, loaded four to a container, shipped (Hapag-Lloyd, MSC, Grimaldi) to Lagos, cleared and delivered to customers. The app replaces the "MAPLEBY TRACKING SYSTEM" spreadsheet with one place to manage every car, container, customer and naira/dollar owed.

It has three faces:

| Area | URL | Who uses it |
| --- | --- | --- |
| Public website and tracking | `/`, `/tracking` | Customers and prospects |
| Staff app | `/app` | Day-to-day operations, sales and finance |
| Admin panel (Filament) | `/admin` | Super Admins: users, roles, settings |

For putting it on a server, see **[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)**.

---

## Contents

- [What it does](#what-it-does)
- [How data is kept clean (smart suggestions)](#how-data-is-kept-clean-smart-suggestions)
- [Everyday workflows](#everyday-workflows)
- [Tech stack](#tech-stack)
- [Local development](#local-development)
- [Importing the tracking sheet](#importing-the-tracking-sheet)
- [Testing](#testing)
- [Project structure](#project-structure)
- [Roles and access](#roles-and-access)
- [Useful commands](#useful-commands)
- [Known gaps and follow-ups](#known-gaps-and-follow-ups)

---

## What it does

### Vehicles (`/app/operations/shipments`)
Every car is a record with a **tracking ID** issued automatically in the format `MAPYY-MM-NNN` (for example `MAP26-10-023`: the 23rd car added in October 2026). A car carries:

- VIN (validated: 17 characters, check digit, model year decoded, duplicate VINs blocked), year, make, model, colour
- Customer, destination city, date added, assigned staff member
- Purchase details: platform (Copart, IAA, Manheim…), lot number, purchase date and price
- Status, moving through a fixed pipeline:

  `Added → Purchased → Picked up → At yard → Loaded → Sailed → Arrived → Offloaded → Cleared → Delivered` (plus `On hold` and `Issue`)

- A **history** of every status change with date, note and who made it
- **Money**: cost lines (auction fees, towing, ocean freight…) and charge lines (what the customer is billed), giving profit per car. The car's share of its container's freight is shown alongside.

The vehicles list has saved views (In transit, Not loaded, No VIN, No customer, Delivered but no offload date, Unassigned), filters by status, shipping line and month, sortable columns, and **bulk actions**: change status with a date, add to a container, remove from a container.

### Containers (`/app/operations/containers`)
A container has a number (ISO 6346 check digit validated, shipping line guessed from the prefix, e.g. `HLCU` → Hapag-Lloyd), booking and B/L numbers, vessel and voyage, ports, capacity (default 4), slots, freight cost and key dates (loaded, ETD, sailed, ETA, arrived, offloaded, released).

- **Status cascade**: change a container to Sailed and every car inside becomes Sailed, with the same date written to each car's history. Arrived, Offloaded and Released (→ cars Cleared) work the same way.
- The board view groups containers by status with ETA countdowns and average transit days per shipping line.
- A container cannot be over-filled, and a second open container with the same number is blocked.
- A car added to a container that has already sailed catches up to the container's status automatically.

### Customers (`/app/crm/clients`)
Customer profile with their cars, status breakdown, invoices and balance due. Leads live under `/app/crm/leads`.

### Quotes (`/app/operations/quotes`)
The original quoting module (unchanged by the redesign).

### Invoices and payments (`/app/finance/invoices`, `/app/finance/payments`)
- Charge lines that have not been billed yet are grouped by customer; pick the lines and create an invoice (`INV-YYMM-NNNN`).
- Invoices can be marked Sent or Void (voiding releases the lines so they can be billed again) and printed.
- Payments are recorded against an invoice (or, for older records, a quote). The invoice balance and status (Sent → Partial → Paid) and each car's payment status update automatically.

### Catalog (`/app/operations/catalog`)
The master lists behind smart suggestions: makes and models, shipping lines (with SCAC, container prefixes and tracking URL), purchase platforms and ports. Each entry can have **aliases** (known misspellings or alternative names).

### Public tracking (`/tracking`)
A customer enters their exact tracking ID or full VIN and sees the vehicle, current status, progress steps, container and shipping dates, and history. It never shows customer names, phone numbers, full VINs or money. The lookup is rate-limited (30 per minute per visitor).

---

## How data is kept clean (smart suggestions)

The old sheet had hundreds of spellings like `HYUDAI`, `LEXUX`, `CARMY`, `CORROLA`, `HAPALIOYD`. The app makes those mistakes hard to make:

1. **Type-ahead from the catalog.** Make, model (filtered by the chosen make), shipping line, platform, port and customer fields suggest from the catalog as you type.
2. **Auto-correct on leaving the field.** If what you typed is an exact match, a known alias, or a clear typo of one entry, it snaps to the canonical spelling and shows "Corrected CARMY to CAMRY".
3. **"Did you mean…?"** If the input is unknown or could be several entries, suggestions are shown as chips, plus an **Add** button to create a new catalog entry (after a confirmation).
4. **Server-side enforcement.** Even if someone bypasses the form, the server rejects values that are not in the catalog, with a message listing the closest matches.
5. **Near-duplicate guard.** Adding `Toyotta` to the catalog is refused because it is too close to `TOYOTA`, unless you tick "it's really different".
6. **Import normalization.** The sheet importer uses the same matching, so imported data ends up with the same spellings.

How matching works (`Modules/Operations/Services/SmartMatcher.php`): values are compared on a key made of uppercase letters and digits only (`Hapag-Lloyd` → `HAPAGLLOYD`). It tries, in order: exact name, alias, prefix, contains, then typo distance (Damerau–Levenshtein, allowing roughly one edit per four characters). A typo is only auto-accepted when one candidate is clearly the best; otherwise the result is "ambiguous" and a human chooses.

To teach it a new misspelling, add it as an alias in the Catalog screen.

---

## Everyday workflows

**Add a car**
Vehicles → *Add vehicle*. Paste the VIN first: it is checked live, the year and make are decoded (also via the free NHTSA vPIC service when reachable), and you are warned if the VIN already exists. Pick make and model from the suggestions, choose the customer, save. A tracking ID is issued.

**Load a container**
Containers → *New container*. Type the number (the check digit is verified and the line is guessed), pick the cars to load (up to the free slots), save. Or select cars on the Vehicles list and use the bulk *Add to container* action.

**Move a container along**
Open the container, choose the new status and date (e.g. Sailed, 21 Oct). Every car inside updates.

**Bill a customer**
On each car's page add charge lines (and cost lines for your own profit view). Then Invoices → pick the customer's unbilled lines → *Create invoice*. Record payments against it as they arrive.

**Give a customer their tracking link**
Send them `https://<your-domain>/tracking` and their tracking ID.

---

## Tech stack

- **Backend:** PHP 8.2+ (8.3 recommended), Laravel 11, [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules) (code lives in `Modules/*`), spatie/laravel-permission (roles), Filament 3 (admin panel at `/admin`)
- **Frontend (staff app):** Inertia.js + React (JSX), Tailwind CSS, shadcn/ui components, Ziggy for named routes in JS, sonner toasts. Built with Vite.
- **Website:** Blade templates under `resources/views/website` with static assets in `public/website`
- **Database:** MySQL/MariaDB in production; SQLite works for local development and is used by the test suite
- **Queue / cache / sessions:** database drivers by default

---

## Local development

Requirements: PHP 8.2+ with `pdo_sqlite` (or `pdo_mysql`), `mbstring`, `intl`, `fileinfo`, `zip`; Composer 2; Node.js 20+ and npm.

```bash
git clone https://github.com/lxlbxl/MaplebyOS.git
cd MaplebyOS

composer install
cp .env.example .env
php artisan key:generate

# SQLite is the default in .env.example
touch database/database.sqlite
php artisan migrate          # also seeds the catalog (makes, models, lines, ports, platforms)
php artisan db:seed          # roles, permissions, settings and two starter users

npm install
npm run dev                  # or: npm run build
php artisan serve
```

Then open:
- Staff app: http://localhost:8000/app/login
- Admin panel: http://localhost:8000/admin
- Website: http://localhost:8000

The seeder creates `admin@adgod-os.com` (Super Admin) and `staff@adgod-os.com` (Staff), both with password `password`. **Change these on any shared or live system.**

`composer dev` runs the web server, queue worker, log viewer and Vite together.

To use MySQL locally instead, set `DB_CONNECTION=mysql` and the `DB_*` values in `.env`, create the database, then run the same migrate and seed commands.

---

## Importing the tracking sheet

The importer reads a CSV export of the Google Sheet (*File → Download → Comma-separated values*). It needs at least the `TRACKING ID` and `VIN` columns and also understands `DATE ADDED`, `CUSTOMER NAME`, `CITY`, `CAR MAKE`, `CAR MODEL`, `YEAR`, `DATE LOADED`, `CONTAINER NUMBER`, `SHIPPING LINE`, `DATE OFFLOADED`, `STATUS`, `PURCHASE PLATFORM` and `NOTE`.

```bash
# Preview: nothing is saved, every fix and warning is listed
php artisan operations:import-tracking-sheet /path/to/sheet.csv --dry-run

# Import for real
php artisan operations:import-tracking-sheet /path/to/sheet.csv
```

Staff can do the same from the Vehicles page with **Import sheet** (preview first, then confirm).

What the importer does:
- Corrects make, model, shipping line, platform and city spellings using the catalog, and lists each correction
- Fixes broken dates (e.g. year `0206` → `2026`) and accepts several date formats
- Keeps valid tracking IDs; re-issues duplicates and IDs whose year/month does not match the date added
- Uses the VIN's model year when the sheet's year disagrees and the VIN check digit is valid
- Creates customers that do not exist yet (matching existing ones by name first)
- Creates one container per container number and trip (the same number reused more than 21 days later is treated as a new trip), and warns when a container would hold more than 4 cars
- Is **safe to run again**: existing cars are matched by VIN (or, for cars without a VIN, by tracking ID or by the same vehicle and date) and updated, not duplicated

Rows listed under "Needs a look" should be checked by a person.

Do not commit real exports of the sheet to the repository: they contain customer data.

---

## Testing

```bash
php artisan test
```

The suite uses an in-memory SQLite database (see `phpunit.xml`) and covers:
- Smart matching of real misspellings, scoping models to makes, rejecting unknown values
- VIN, container number and tracking ID validation
- The importer (on the synthetic fixture `tests/fixtures/tracking-sheet.csv`), including re-import without duplicates
- Container status cascade, over-fill guard, cars catching up to a moving container
- Bulk actions, lookups and catalog endpoints
- Charges, invoices, payments and voiding
- Public tracking privacy
- That the main pages render

Build the frontend to check for JS errors: `npm run build`.

---

## Project structure

```
app/                         Core Laravel app: auth, dashboard, users, settings, Filament admin
Modules/
  CRM/                       Clients and leads
  Operations/                Vehicles (shipments), containers, catalog, importer, public tracking
    Entities/                Shipment, Container, ShipmentMilestone, VehicleMake, VehicleModel,
                             ShippingLine, PurchasePlatform, Port
    Services/                SmartMatcher, CanonicalInput, VinDecoder, ContainerNumber,
                             TrackingIdGenerator, TrackingSheetImporter
    Database/Migrations/     Catalog, containers, tracking fields, milestones, catalog seed
    Database/Seeders/        OperationsCatalogSeeder (canonical names + known misspellings)
    Console/                 operations:import-tracking-sheet
  Finance/                   Shipment charges, invoices, payments
  Quoting/                   Quotes
  Inventory/                 Vehicles for sale shown on the website
  Tasks/, Communications/, Reports/, Core/, Website/
resources/js/
  Pages/                     Inertia pages (Operations, CRM, Finance, …)
  Components/Smart/          SmartInput (type-ahead + auto-correct), VinInput
  Components/Operations/     ShipmentForm, ContainerForm
  lib/operations.js          Status colours, date helpers, container number checks
resources/views/website/     Public website (Blade)
routes/web.php               Website, staff app and finance routes
routes/console.php           Scheduled tasks
tests/                       PHPUnit tests and fixtures
docs/DEPLOYMENT.md           Server setup and release steps
```

Module migrations are loaded by each module's service provider from `Modules/<Name>/Database/Migrations` (the folder name is case-sensitive on Linux).

---

## Roles and access

| Role | Can do |
| --- | --- |
| Super Admin | Everything, including `/admin`, users, roles, settings and backup downloads |
| Staff | The staff app (`/app`) |
| User | Basic login |

Roles and permissions are managed in `/admin` (spatie/laravel-permission). Any logged-in user can use the operations screens (email verification is not enforced); tighten this if non-staff accounts are ever created.

---

## Useful commands

| Command | Purpose |
| --- | --- |
| `php artisan migrate` | Apply database changes (also seeds the catalog the first time) |
| `php artisan db:seed` | Roles, permissions, settings, starter users |
| `php artisan db:seed --class="Modules\Operations\Database\Seeders\OperationsCatalogSeeder"` | Re-apply the catalog defaults (merges, never deletes) |
| `php artisan operations:import-tracking-sheet <csv> [--dry-run]` | Import the tracking sheet |
| `php artisan optimize` / `optimize:clear` | Cache / clear config, routes and views |
| `php artisan schedule:run` | Run scheduled tasks (called by cron every minute) |
| `php artisan queue:work` | Process queued jobs (notifications) |
| `php artisan test` | Run the test suite |

---

## Known gaps and follow-ups

- `database/dumps/*.sql` contain customer data and should be removed from the repository (and from git history if the repo is ever made public).
- The scheduled `backup:run` / `backup:clean` tasks in `routes/console.php` need the `spatie/laravel-backup` package, which is not installed. Until it is, use hosting backups or the cron `mysqldump` in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md#backups).
- The Core module's migrations are not loaded (enabling them would conflict on `users.role`).
- Older helper commands in `app/Console/Commands` (`app:debug-*`, `app:import-legacy-shipments`, …) predate the redesign; prefer `operations:import-tracking-sheet` for imports.
