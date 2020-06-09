#!/usr/bin/env bash

cp .env.example .env
cp .env.testing.example .env.testing
cp docker-compose.yml.dist docker-compose.yml
cp env/build.sh.dist env/build.sh
cp env/docker/nginx/nginx.conf.dist env/docker/nginx/nginx.conf
cp env/docker/nginx/upstream.conf.dist env/docker/nginx/upstream.conf
cp env/docker/nginx/sites/laravel.conf.dist env/docker/nginx/sites/laravel.conf
cp env/docker/php-fpm/php.ini.dist env/docker/php-fpm/php.ini
cp env/docker/php-fpm/xdebug.ini.dist env/docker/php-fpm/xdebug.ini
cp env/docker/php-fpm/xlaravel.pool.conf.dist env/docker/php-fpm/xlaravel.pool.conf
cp env/docker/php-fpm/crontab/laradock.dist env/docker/php-fpm/crontab/laradock
cp env/docker/php-fpm/supervisor/laraworker.conf.dist env/docker/php-fpm/supervisor/laraworker.conf
cp env/docker/workspace/xdebug.ini.dist env/docker/workspace/xdebug.ini
