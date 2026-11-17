# --- Assets (SASS, JS, imagenes) ---
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY gulpfile.js ./
COPY src ./src
RUN npx gulp build

# --- Dependencias PHP ---
FROM composer:2 AS composer
WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# --- Imagen final ---
FROM php:8.3-apache
RUN docker-php-ext-install mysqli \
 && a2enmod rewrite headers expires \
 && sed -ri 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
 && printf '<Directory /var/www/html/public>\n  AllowOverride All\n</Directory>\n' > /etc/apache2/conf-enabled/public.conf
WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
COPY --from=composer --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
EXPOSE 80
