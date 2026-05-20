FROM php:8.2-fpm

# Instalar dependencias
RUN apt-get update && apt-get install -y \
    nginx \
    libsqlite3-dev \
    curl \
    && docker-php-ext-install pdo pdo_sqlite

# Crear directorio
WORKDIR /var/www/html

# Copiar archivos
COPY . .

# Permisos para SQLite
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && touch /var/www/html/gorilla_tools.db \
    && chmod 666 /var/www/html/gorilla_tools.db

# Copiar configuración de Nginx (crea la carpeta docker si no existe)
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 10000

CMD ["/entrypoint.sh"]