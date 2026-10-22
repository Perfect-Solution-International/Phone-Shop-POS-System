# Shared-database enterprise SPA architecture

One Laravel 10 deployment serves a versioned REST API, Blade public pages and a Vue.js 2.6 SPA. MySQL/MariaDB is the production database. Eloquent models and migrations define persistence; Form Requests, Sanctum and Spatie permissions provide the HTTP security boundary.

## Request and session flow

Blade serves the login and application shells. Mix 6 builds separate login and main bundles, shared styles and lazy page chunks. Vue Router 3.5 handles application history navigation under /app; Laravel routes deep links to the application shell. Vuex 3 modules own authentication, HRM table data and compact-sidebar state.

Login validates credentials through Laravel hashing and issues an eight-hour Sanctum bearer token. The Vuex auth module persists the token in localStorage; Axios reads the current value for each request. Logout deletes the current server token. HTTP 401 clears local state and reloads the login shell. The server returns permissions during session restoration; client navigation checks improve presentation while server middleware remains authoritative. Sanctum tokens are bearer credentials rather than an OAuth2 authorization server.

## Shop isolation

A nullable users.shop_id preserves existing accounts during migration. HRM access requires a non-null assignment associated with a real shops record. The ResolveShop middleware runs after authentication and before implicit route binding. It establishes a request-scoped shop identifier and removes it when the request finishes.

Employee, attendance, leave, payroll and setting models use a shop scope that returns no rows without trusted context. New HTTP records receive their shop ID from this context. Update fields never include shop_id. Employee references and unique validation include the current shop, and cross-shop route bindings return 404. Database foreign keys and composite uniqueness protect record relationships and duplicate entries. Shop identifiers in request payloads are prohibited.

Background processing must receive an explicit trusted shop ID or immutable content rather than inherit HTTP state. Payroll mailables contain only a name and period snapshot, are queued after commit and expose no salary information. The synchronous spreadsheet report captures the authorized shop ID explicitly. Future jobs that query shop records must establish and clear their own context.

## Modules and contracts

- Controllers in app/Http/Controllers/Api expose auth, employees, attendance, leave, payroll, reports and settings under /api/v1.
- Resource lists accept page, per_page (at most 100), search, sort and direction, returning data, links and pagination meta. Sort identifiers are selected from a per-resource allow-list.
- Vue Single File Components in resources/src/views/app/hrm reuse the table/form components and dedicated Vuex modules. vee-validate gives immediate feedback; backend Form Requests enforce the actual contract.
- Attendance accepts timestamps with an explicit UTC offset, persists them in UTC and retains an explicit work date. Date-only leave and payroll values remain calendar dates.
- Payroll stores salary components and derives net salary using integer minor-unit arithmetic. Months use their first date. Statutory calculation, payment processing, leave balances and attendance policy rules need separate business specifications.
- ECharts renders dashboard summaries. Dompdf and Laravel Excel provide server reports; jsPDF and AutoTable provide current-page exports.
- Employee barcode and QR components identify employees by their shop-scoped employee code. The camera selector never accepts a scanned shop identifier as authorization.
- Public pages use Blade for indexable metadata. Vue Meta manages SPA page titles; private shells are marked noindex.

## Reserved product areas

Super Admin, Shop Management and Customer & Service Portal retain their controller, service, model and route directories. The area route files still contain no product endpoints. These reservations are separate from implemented HRM and do not imply POS, subscription billing, tenant administration, payment integration, customer-service flows or messaging functionality.

Historical data import and enterprise-specific approval, payroll, attendance and ownership contracts cannot be inferred from the original foundation. New migrations do not reset a populated database or assign existing users to shops. Operators provision account assignments and role grants explicitly.
