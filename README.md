# Laravel Project

This is a Laravel project built with Laravel 12.x.

## Requirements

- PHP >= 8.2
- Composer
- Node.js & NPM
- SQLite (or your preferred database)

## Installation

1. Clone the repository
```bash
git clone <your-repository-url>
cd <project-directory>
```

2. Install PHP dependencies
```bash
composer install
```

3. Install NPM dependencies
```bash
npm install
```

4. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

5. Database Setup
- The project is configured to use SQLite by default
- The database file is located at `database/database.sqlite`
- To use a different database, update the `.env` file with your database credentials

6. Run Migrations
```bash
php artisan migrate
```

7. Start the Development Server
```bash
php artisan serve
```

8. In a separate terminal, compile assets
```bash
npm run dev
```

## Features

- User Authentication (Ready to use)
- Database migrations
- Laravel Mix for asset compilation
- Testing environment configured

## Directory Structure

- `app/` - Contains the core code of your application
- `bootstrap/` - Contains files that bootstrap the framework
- `config/` - Contains all configuration files
- `database/` - Contains database migrations and seeds
- `public/` - Contains the front controller and assets
- `resources/` - Contains views and uncompiled assets
- `routes/` - Contains all route definitions
- `storage/` - Contains compiled Blade templates, file uploads, etc.
- `tests/` - Contains test cases
- `vendor/` - Contains Composer dependencies

## Testing

```bash
php artisan test
```

## Security

If you discover any security related issues, please email your-email@example.com instead of using the issue tracker.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
