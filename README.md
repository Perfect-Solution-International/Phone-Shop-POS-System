# Phone Shop POS SaaS

Laravel 10 REST API with a Vue.js 2.6 SPA, Vue Router 3.5, Vuex 3, Bootstrap 4 and BootstrapVue. MySQL or MariaDB stores application data through Eloquent migrations. Laravel Mix 6 compiles separate application and login bundles and lazy-loaded page chunks.

## Installation

1. Install PHP 8.1+ with PDO MySQL, GD, DOM, Fileinfo, Mbstring, XML and ZIP, Composer, Node.js 22 and MySQL/MariaDB. PHP 8.1 dependency compatibility is enforced in Composer's platform configuration.
2. Run `composer install` and `npm ci`. Copy `.env.example` to `.env` only for a new installation, then run `php artisan key:generate`. Keep existing environment files, keys and databases.
3. Configure your database and run `php artisan migrate --no-interaction`. Existing framework migration files retain their identities; new migrations add shop ownership, HRM records, permissions and tokens.
4. Run `php artisan db:seed --no-interaction` to create permission definitions and roles. This does not create login accounts or sample HR records.
5. Create an administrator with `php artisan app:create-administrator admin@example.com --name="Administrator" --shop="My shop"`. Enter a strong password at the hidden prompt. This creates a new shop; existing accounts are never promoted or reassigned.
6. Run `npm run build` and `php artisan serve`. For asset development, run `npm run watch` separately. `npm run dev` compiles once.
7. Start `php artisan queue:work --tries=3 --timeout=60` for queued payroll notifications. Schedule `php artisan schedule:run` every minute for expired-token cleanup. The default mailer writes locally to logs; configure SMTP before expecting delivery.

## Application boundaries

- `/` renders a Blade marketing page with server-rendered metadata.
- `/login` mounts `resources/src/login.js`; `/app/` mounts `resources/src/main.js` and supports Vue Router history deep links.
- REST endpoints live under `/api/v1`. Sanctum bearer tokens expire after eight hours. Axios attaches the current token; a 401 clears it and returns to login. Logout revokes the current token on the server.
- Passwords use Laravel's hashing service (Bcrypt by default, Argon2 configurable). Form Requests enforce server validation; Vue forms use vee-validate 3 and BootstrapVue controls.
- HRM supports employee create/edit/archive, attendance create/edit/delete, leave create/edit/delete and payroll create/edit/delete. Tables use server pagination, search and allow-listed sorting.
- Payroll records store monthly salary components and compute the net amount on the server using integer minor units. These records do not calculate statutory taxes, process payments, or replace an approved payroll engine. A transition to ready queues an informational email.
- Payroll reports use Dompdf and Laravel Excel. Full PDF reports are capped at 2,000 rows; spreadsheet reports read records in chunks. Client PDF exports cover the current table page.
- Employee codes support Code 128 barcodes and QR badges. Attendance/leave/payroll employee selectors can scan QR codes; camera scanning needs HTTPS or localhost and browser permission.
- Dashboard charts use vue-echarts/Apache ECharts. Settings store a shop timezone and currency. Timestamps persist in UTC, with workspace display formatting. Attendance form inputs use the device timezone.
- The messaging directory is reserved; there is no messaging inbox implementation or migrated legacy data in this checkout.

## Ownership and permissions

The authenticated user's stored shop assignment establishes tenant context before route binding. Every HRM query, mutation, report and employee reference is limited to that shop. Client shop IDs are rejected. Queries outside a shop context fail closed. Existing users receive no automatic shop assignment or permissions; provision these deliberately before granting HRM access.

Roles seeded are Administrator, HR Manager and Viewer. Permissions are `hrm.view`, `hrm.manage`, `payroll.view`, `payroll.manage`, `reports.view`, and `settings.manage`. Financial reports require both payroll viewing and report permission. Payroll entry also needs HR viewing to select employees. Authorization is checked by Laravel regardless of Vue navigation visibility.

The existing platform, shop and customer-portal directory boundaries remain reserved for the phone-shop product. They do not implement POS, subscriptions, payments or customer-service workflows. See [the architecture specification](docs/architecture/shared-database-tenancy.md).

## Source and assets

Application controllers, requests, models and mailables live in `app/`; schema and seeders in `database/`; API routing in `routes/api.php`; Blade shells in `resources/views/`. The SPA lives in `resources/src/{views,components,store,api}`. `webpack.mix.js` builds `public/js/main.min.js`, `public/js/login.min.js`, `public/js/chunks/`, `public/css/app.min.css` and the versioned Mix manifest. Generated assets are ignored and must be built for deployment.

## Verification and compatibility

Run `npm test`, `npm run build` and `php artisan test --compact`. Tests use an isolated in-memory SQLite database; they do not migrate the configured development database. CI installs PHP 8.1 with the required extensions and builds the SPA before running tests.

The requested framework and UI versions are legacy releases. The resolved Laravel 10 and Pest 2 dependency tree currently has Composer advisories affecting Laravel and PHPUnit; Pest 2 caps PHPUnit at 10.5.36. Review `composer audit` and `npm audit` before production use. Do not expose debug mode publicly. GD is required by spreadsheet dependencies even when basic report generation does not use image rendering.
