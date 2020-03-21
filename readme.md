<p align="center"><img src="https://tasing.pe/images/logo1-07.svg" width="250"></p>


# TASING! API

_Laravel project for TASING! API._

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Requirements

This is a Laravel 6.x project, so you must meet its requirements.

### Installing

Clone the project

```bash
git clone git@github.com:juliorafaelr/t-dashboard-api.git
cd t-dashboard-api
composer install
cp .env.example .env
php artisan key:generate
```

Edit .env and put credentials, indicate environment, url and other settings.

Run migrations and seeders

```bash
php artisan migrate
php artisan passport:install
php artisan db:seed
```

## Crontab settings

Since the project uses Laravel's command scheduler, only a single Cron entry is needed on your server.

```bash
* * * * * cd /path-to-the-project && php artisan schedule:run >> /dev/null 2>&1
```

This Cron will call the Laravel command scheduler every minute. When the ```schedule:run command``` is executed, Laravel will evaluate the scheduled tasks and runs the tasks that are due. 

The scheduled tasks can be found in the ```schedule``` method of the ```App\Console\Kernel``` class, and custom commands in there are stored in the ```app/Console/Commands``` directory.

For more information see [Artisan Console](https://laravel.com/docs/6.x/artisan#writing-commands) and [Task Scheduling](https://laravel.com/docs/6.x/scheduling#scheduling-artisan-commands) Laravel documentations.
