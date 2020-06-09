# Installation

### Setup
* Clone project `$ git@github.com:user/my-api.git`
* Don't forget to add this line `127.0.0.1 my-api.loc` to `/etc/hosts` on your machine
* Copy customizable configs .dist files `$ bash ./env/copyconf.sh` and override your params: ports, passwords, hostname, enable\disable xdebug, etc.

### Build and Run-Up Docker containers
* `$ docker-compose up -d --build`

### Create Database in db container
* Enter in db container and connect to postgres server `$ docker exec -it my_postgres bash -c "psql -h localhost -p 5432 -d postgres -U my --password"`
* DB_PASSWORD = secret
* See all databases by run `\l`
* Create database If not exist `create database "my";`
* Add user Privileges `grant all privileges on database "my" to "my";`
* Create Test database `create database "my_test";`
* Add user Privileges `grant all privileges on database "my_test" to "my";`

### Enter in workspace container and run all necessary commands:
* Re-Build containers, composer install, generate jwt secret, migrate, run cron and supervisor, generate API-docs etc
* `$ bash ./env/build.sh`

### Enter in workspace container and run all necessary commands:
* enter in workspace container `$ bash ./env/workspace.sh`
* Or Drop all tables and re-run all migrations (if need) `php artisan migrate:fresh --env=local`
* Or Reset and re-run all migrations (if need) `php artisan migrate:refresh --env=local`
* Run fixtures (if need) `php artisan db:seed --env=local`
* `npm install`
* `npm run dev` To run laravel mix and to combine all css files in one file and custom JS files in one file

### MEDIA Spatie library (handle images, files) [Spatie Docs](https://docs.spatie.be/laravel-medialibrary/v7/installation-setup/)
* enter in workspace container `$ bash ./env/workspace.sh`
* Optional if not exists migration file in /database/migrations `php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"`
* Optional `php artisan migrate`
* Optional if not exists config file /config/medialibrary.php`php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"`
* edit configs in .env files

### TELESCOPE monitoring&debugging (only on local, staging envs)
* enter in workspace container `$ bash ./env/workspace.sh`
* `php artisan telescope:install`
* Remove `App\Providers\TelescopeServiceProvider::class` from `config/app.php` (for dont load service automaticly, only depend-on APP_ENV)
* `php artisan telescope:publish`
* `php artisan migrate`
* edit configs in .env files

### Run PHPUnit tests in workspace container:
* enter in workspace container `$ bash ./env/workspace.sh`
* Run migration for test DB (if need) `php artisan migrate --env=testing`
* `$ composer test`

### Generate a public/private key pair:
* `$ mkdir -p resources/keys`
* `$ ssh-keygen -t rsa -b 4096 -m PEM -f resources/keys/jwt.key`
* `$ openssl rsa -in resources/keys/jwt.key -pubout -outform PEM -out resources/keys/jwt.key.pub`

### Generate API docs:
* `docker exec -it my_workspace php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"`
* `docker exec -it my_workspace php artisan l5-swagger:generate`
* `sudo chown -R $(whoami):www-data ./storage`
* `sudo chmod -R 775 ./storage`

sudo chown -R $(whoami):www-data ./storage
sudo chmod -R 775 ./storage
sudo chmod -R 777 ./storage/volumes

