# MarketPlace

A basic Laravel 13 classifieds marketplace using Blade, SQLite, and local CSS/JavaScript.

## Run on this Windows machine

PHP 8.4 is detected from the existing WAMP installation. Dependencies and the SQLite database are already installed.

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\start.ps1
```

Open http://127.0.0.1:8000. Stop the server with Ctrl+C. Choose another port with `-Port 8001`.

Local demo account:
- Email: seller@example.com
- Password: MarketPlace123!

You can also register your own account. Demo listings use category illustrations; uploaded photos replace them.

## Features

- Public listings with text search, city search, category filters, price sorting, and pagination.
- Registration, login, remembered sessions, logout, and throttled authentication.
- Seller dashboard with create, edit, mark as sold, and delete.
- JPG, PNG, and WebP uploads up to 4 MB, with photo preview.
- Ownership policies, server-side validation, CSRF protection, and hashed passwords.
- Seller phone contact for logged-in users.
- Responsive interface, keyboard focus states, and empty states.

This is a classifieds starter. It does not include checkout, online payments, chat, password reset, or moderation.

## Install on another machine

Use PHP 8.3 or later with Laravel's required extensions, including PDO SQLite, mbstring, fileinfo, and GD for image tests, and Composer 2.

```sh
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

On Windows, use `Copy-Item .env.example .env` instead of `cp`. SQLite is the default database. Seeding creates local demo data only in the local/testing environments. Keep demo credentials out of a production deployment.

## Styles and JavaScript

CSS source: `resources/css/app.css`. Browser CSS: `public/css/marketplace.css`. JavaScript: `public/js/marketplace.js`.

```sh
npm run build
npm run dev
```

These commands copy/watch the stylesheet using Node's built-in APIs. No npm installation is required. The startup script also copies styles automatically. Refresh the browser after editing while the watcher runs.

## Tests

```sh
php artisan test
```

On this machine, PHP can be invoked with:

```powershell
& 'C:\wamp64\bin\php\php8.4.15\php.exe' artisan test
```

Tests use an isolated in-memory SQLite database and fake upload storage.

## Structure

- `app/Models`: User, Category, Listing and their relationships.
- `app/Http/Controllers`: authentication and marketplace flows.
- `app/Http/Requests/ListingRequest.php`: listing validation and authorization.
- `app/Policies/ListingPolicy.php`: ownership permissions.
- `database/migrations`, `database/factories`, `database/seeders`: schema and sample data.
- `resources/views`: layout, reusable components, authentication, and marketplace pages.
- `routes/web.php`: named web routes.
- `tests/Feature/MarketplaceTest.php`: functional and permission tests.

Serve the `public` directory when configuring WAMP/Apache. Use the standard [Laravel deployment guidance](https://laravel.com/docs/13.x/deployment) for production.
