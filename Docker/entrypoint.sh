#!/bin/bash

# Iniciar PHP-FPM en background
php-fpm -D

# Iniciar Nginx (foreground)
nginx -g "daemon off;"