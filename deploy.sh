#!/bin/bash

git pull
composer install
php artisan migrate --force
php artisan optimize
