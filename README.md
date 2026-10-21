# Phone Shop POS SaaS

Perfect Solution International (Pvt) Ltd. is preparing a single Laravel application for independent phone shops. The planned product has a SaaS Super Admin area, a Shop Management area, and a Customer & Service Portal. This repository currently contains the application foundation and directory boundaries only; business features and tenant isolation are not implemented.

## Stack and architecture

- PHP 8.5 and Laravel 13, with Pest for tests.
- MySQL is the intended shared database, selected through `DB_CONNECTION=mysql` in the environment.
- Laravel Blade and Vite are the target for new application pages. The existing React/Inertia welcome starter, its Vite configuration, and its home route are retained so the current application continues to run. No new application pages have been added.
- A modular monolith: one deployment and one database, with planned server-side shop identification and authorization. See [the architecture note](docs/architecture/shared-database-tenancy.md).

## Folder organization

| Path | Planned responsibility |
| --- | --- |
| `app/Http/Controllers/{SuperAdmin,Shop,CustomerPortal,Api}` | Area-specific HTTP entry points |
| `app/Http/Requests/` and `app/Http/Resources/` | Validation and response boundaries |
| `app/Models/{Platform,Shop}` | Platform and shop-owned data models |
| `app/Services/` | Shared domain services, grouped by business capability |
| `app/Support/`, `app/Actions/`, `app/DTOs/`, `app/Contracts/` | Cross-cutting code when it becomes necessary |
| `resources/views/{super-admin,shop,customer-portal}` | Future Blade views for each area |
| `routes/{super-admin,shop,customer-portal,api}.php` | Empty route files registered with area prefixes |
| `tests/{Feature,Unit}` | Future behavior and isolation tests |
| `docs/` | Architecture and future project documentation |

Empty directories contain `.gitkeep`. They do not represent implemented functionality. The existing `app/Models/User.php`, framework migrations, test setup, and frontend starter remain in place.

## Local installation

1. Install PHP 8.5 with the extensions required by Laravel and the PDO MySQL extension, Composer, Node.js, and a compatible MySQL server.
2. Run `composer install` and `npm install` from the project root.
3. Copy `.env.example` to `.env` and run `php artisan key:generate` for a new local environment. Do not replace an existing `.env` or application key.
4. Create a local MySQL database and set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`. The example uses `phone_shop_pos_saas`; the database must be created separately. Keep `.env` out of Git.
5. Run `php artisan serve` and `npm run dev` in separate terminals. The existing welcome page uses the retained React/Inertia starter and Vite assets.

`APP_ENV=local` and `APP_DEBUG=true` are for local development only. Set `APP_DEBUG=false` in production. `APP_TIMEZONE` defaults to UTC; shop-specific display timezones can be designed later. The standard Laravel session, cache, queue, mail, and exception configuration remains intact. The existing `composer run setup` command runs Laravel's default migrations; use it only when you intend to create those framework tables.

## Development and verification commands

```text
php artisan about
php artisan route:list
php artisan config:show database.default
php artisan test --compact
npm run build
```

No gateway, SMTP, SMS, or other external credentials are needed for this foundation. Do not add shop business routes before server-side authentication, shop identification, and tenant authorization are implemented and tested.

## Future implementation phases

1. Define the platform and shop data model, migrations, tenant context, and cross-tenant protection tests.
2. Add authentication, roles and permissions, plans, subscriptions, trials, and billing boundaries.
3. Build shop operations and the customer portal in the existing Laravel application, with feature tests for each owned workflow.
