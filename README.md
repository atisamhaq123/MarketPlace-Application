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
- Up to 8 JPG, PNG, or WebP photos per listing (4 MB each), with previews, primary-photo selection, and a detail-page gallery.
- Ownership policies, server-side validation, CSRF protection, and hashed passwords.
- Seller phone contact for logged-in users.
- Responsive interface, keyboard focus states, and empty states.

This is a classifieds starter. It does not include checkout, online payments, chat, password reset, or moderation.

## Listing photos

Select several files under **Add photos**, then select **Primary photo** on the photo you want displayed in marketplace cards. You can add more files by opening the picker again and remove new previews before saving. On the edit page, check **Remove photo** to delete a saved photo when you save changes. Removing the primary automatically selects another remaining photo. Removing every photo restores the category illustration.

Buyers can click the thumbnails on a listing to switch photos, or use the arrow keys while a thumbnail is focused. Existing single-photo listings are preserved by the new migration. Run `php artisan migrate` when pulling this update on another machine.

The Windows startup script allows multipart requests up to 40 MB. When using WAMP/Apache or another PHP server, configure `upload_max_filesize=4M`, `post_max_size=40M`, and `max_file_uploads=20` in that server's PHP configuration, then restart it. The application enforces the 8-photo limit.

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

- `app/Models`: User, Category, Listing, ListingImage and their relationships. `listings.image_path` stores the current primary path for cards; `listing_images` stores the full gallery.
- `app/Services/ListingPhotoService.php`: transactional photo updates and file cleanup.
- `app/Http/Controllers`: authentication and marketplace flows.
- `app/Http/Requests/ListingRequest.php`: listing validation and authorization.
- `app/Policies/ListingPolicy.php`: ownership permissions.
- `database/migrations`, `database/factories`, `database/seeders`: schema and sample data.
- `resources/views`: layout, reusable components, authentication, and marketplace pages.
- `routes/web.php`: named web routes.
- `tests/Feature/MarketplaceTest.php`: functional and permission tests.

Serve the `public` directory when configuring WAMP/Apache. Use the standard [Laravel deployment guidance](https://laravel.com/docs/13.x/deployment) for production.
