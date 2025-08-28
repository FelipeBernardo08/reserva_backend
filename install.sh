#!/bin/bash

set -x

composer install

sleep 1;

php artisan key:generate

sleep 1;

php artisan l5-swagger:generate

sleep 1;

php artisan jwt:secret

sleep 1;

php artisan migrate

sleep 1;

php artisan db:seed
