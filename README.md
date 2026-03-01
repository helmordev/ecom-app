# EcomApp

A food delivery application built with Laravel 12, PHP 8.5, Blade + Livewire, and Tailwind CSS v4. Supports three user roles: Admin, Customer, and Driver with real-time order tracking.

## Requirements

- PHP 8.5+
- [Bun](https://bun.sh)
- [Composer](https://getcomposer.org)

## Installation

```bash
# Clone the repository
git clone <repository-url> ecom-app
cd ecom-app

# Install PHP dependencies
composer install

# Install JS dependencies
bun install

# Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed

# Build frontend assets
bun run build
```

## Development

```bash
# Start Laravel server, queue worker, log monitoring, and Vite dev server
composer dev
```

Or run services individually:

```bash
php artisan serve          # Laravel dev server
bun run dev                # Vite dev server
php artisan queue:listen   # Queue worker
```

## Code Quality

```bash
# Format code (Rector + Pint + Prettier)
composer lint

# Dry-run lint for CI
composer test:lint
```

## Testing

```bash
# Full test suite (type coverage, unit tests, linting, static analysis)
composer test

# Run all tests
php artisan test --compact

# Run specific test file
php artisan test --compact tests/Feature/Http/Admin/RestaurantControllerTest.php

# Run tests matching a filter
php artisan test --compact --filter=PlaceOrderTest

# Type coverage
composer test:type-coverage

# Static analysis (PHPStan level 9)
composer test:types
```

## Tech Stack

| Layer          | Technology                           |
| -------------- | ------------------------------------ |
| Backend        | Laravel 12, PHP 8.5                  |
| Authentication | Laravel Fortify                      |
| Frontend       | Blade + Livewire + Tailwind CSS v4   |
| Real-time      | Laravel Broadcasting (Reverb) + Echo |
| Database       | SQLite                               |
| Testing        | Pest v4                              |

## Project Documentation

- **[PLAN.md](PLAN.md)** - Full system plan including features, database schema, system flows, folder structure, route structure, and testing strategy.

## License

Built on top of the [Laravel Starter Kit](https://github.com/nunomaduro/laravel-starter-kit) by Nuno Maduro, licensed under the [MIT License](https://opensource.org/licenses/MIT).
