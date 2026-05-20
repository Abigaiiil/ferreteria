FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx libsqlite3-dev curl && docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www/html

COPY . .

RUN echo '#!/bin/bash\nphp-fpm -D\nnginx -g "daemon off;"' > /entrypoint.sh && chmod +x /entrypoint.sh

RUN echo 'server { listen 10000; server_name _; root /var/www/html; index index.php index.html; location / { try_files $uri $uri/ /index.php?$query_string; } location ~ \.php$ { fastcgi_pass 127.0.0.1:9000; fastcgi_index index.php; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; include fastcgi_params; } }' > /etc/nginx/sites-available/default

RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 10000

CMD ["/bin/bash", "/entrypoint.sh"]
