# Acme Donations

## <ins>Sections</ins>

### Welcome page — what you can do:

- Browse public campaigns (search, filter, and paginate).
- Open a campaign to view details and progress toward its goal.
- Click Donate to open the donation modal, choose an amount and a payment method, and complete a donation (currently a dummy payment flow).
- After donating, you will receive a confirmation email.
- If a capaign reaches its goal it will set to 'Completed' status

### User dashboard — available actions:

- Create, edit, and manage your campaigns (set goal, dates, description).
- View a list of your campaigns and track progress.
- Check your personal donation history and details.
- Edit your profile name (email and company are visible but not editable from the profile page).
- Update your password.
- Delete your account.

### Admin tools — additional capabilities:

- View full lists of all campaigns and donations for reporting and moderation.
- Edit application settings from the Settings page (for example: welcome headlines, predefined donation amounts, available statuses). These settings are stored in the database.
- Manage site-level content and preferences.

### Login / Registration

Users must be logged in to donate.  
Since the application is B2B-focused, registration is not required, but is available for testing purposes.

## <ins>Data</ins>

Dummy data is automatically seeded into the database during the initial migration for convenient testing.

## <ins>Structure</ins>

### Frontend / backend communication

- The frontend is built with Vue 3 + Inertia.js. Instead of a separate JSON API, pages are rendered through Inertia responses returned by Laravel controllers (for example: controllers call `Inertia::render('Pages/Name', $props)`).
- Page data (props) comes from controller methods (e.g., route handlers in `routes/web.php` or controllers such as `DonationController`, `ProfileController`) and from global shares defined in `app/Providers/AppServiceProvider::boot()` (for example `SettingsService::getAll()` and `predefinedAmounts` are shared via `Inertia::share`).
- On the client, components call `usePage()` to access shared props and `useForm()` (from `@inertiajs/vue3`) to submit forms and perform patch/post requests to backend routes defined in `routes/web.php`.

### Data flow and patterns

- Request → Controller → DB: A user navigates or submits a form; the browser sends a request to a Laravel route; the controller prepares data (queries the database or services), then returns an Inertia response. The Inertia client receives the page props and mounts the corresponding Vue page.
- Shared state: frequently used data (site settings, predefined amounts, authenticated user) is shared globally using `Inertia::share()` so pages/components can rely on `page.props` without extra requests.
- Forms: front-end forms use `useForm()` which binds input state and handles submission lifecycle (onStart, onSuccess, onError, onFinish). This integrates with Laravel validation and redirects.

### Where key components live in the codebase

- Page components live under `resources/js/Pages` (e.g., `Dashboard`, `Welcome.vue`, `Campaign/Index.vue`).
- Reusable UI components live under `resources/js/Components` (e.g., `Modal.vue`, `DonationModal.vue`, `PaymentMethodModal.vue`, `PrimaryButton.vue`).
- Page-specific partials and feature components live under nested directories (for example `resources/js/Pages/Dashboard/Settings/Partials`).
- Controllers live in `app/Http/Controllers` (e.g., `DonationController.php`, `ProfileController.php`, and auth controllers under `app/Http/Controllers/Auth`).
- Form validation logic lives in `app/Http/Requests` (for example `ProfileUpdateRequest.php`).
- Settings handling is centralized in `app/Services/SettingsService.php` and persisted in the `settings` table.

### Database system

- The project includes a bundled SQLite database (`database/database.sqlite`) for convenient local development.
- The app supports other database drivers (MySQL, Postgres) via `config/database.php` and environment variables — the migrations under `database/migrations` define the schema.

### Payment flow (simulation)

- The code introduces a backend payment contract: `app/Payments/PaymentInterface.php` and a dummy implementation `app/Payments/DummyPayment.php`.
- The service container binds `PaymentInterface` to `DummyPayment` in `app/Providers/AppServiceProvider.php` so controllers can type-hint the interface and call `createCharge()` without coupling to a specific provider.
- `DonationController::store()` calls the payment adapter when `payment_method` is provided; the dummy adapter returns a simulated success response (transaction ID, success flag). The controller persists donation records immediately. In production, this adapter would be replaced with a real payment provider and optionally webhooks and idempotency.

### Authentication and authorization

- Authentication follows the Laravel Breeze structure with Inertia: routes are defined in `routes/auth.php` and controllers live in `app/Http/Controllers/Auth`. The frontend auth pages live under `resources/js/Pages/Auth`.
- The application uses Laravel authentication middleware (`auth`, `guest`) and supports email verification via `MustVerifyEmail`.
- The `users` table includes a `role` column (`user` or `admin`), and admin-only routes are protected by an `admin` middleware in `routes/web.php`.
