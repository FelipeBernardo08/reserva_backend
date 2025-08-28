#!/bin/bash

set -x

composer install

sleep 4;

php artisan migrate

sleep 1;

php artisan db:seed

sleep 1;

php artisan l5-swagger:generate

sleep 1;

php artisan jwt:secret
