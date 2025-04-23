FROM php:8.2-fpm

# Installer dépendances système
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    sqlite3 \
    libsqlite3-dev \
    git

# Installer extensions PHP requises
RUN docker-php-ext-install pdo pdo_sqlite mbstring exif pcntl bcmath gd

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Créer dossier app
WORKDIR /var/www

# Copier les fichiers du projet
COPY . .

# Installer les dépendances Laravel
RUN composer install --optimize-autoloader --no-dev

# Donner les permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Générer clé d'app (en runtime)
CMD php artisan config:cache && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=8080\
    touch /var/www/database/database.sqlite
