# Utilisation de l'image PHP 8.2 FPM officielle
FROM php:8.2-fpm

# Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Configuration et installation des extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    zip \
    intl \
    opcache \
    gd

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration de l'environnement de travail
WORKDIR /var/www/html

# Copie des fichiers de l'application
COPY ./app /var/www/html/

# Installation des dépendances avec Composer
RUN composer install --no-interaction --optimize-autoloader

# Configuration des permissions
RUN chown -R www-data:www-data /var/www/html/var

# Exposition du port PHP-FPM
EXPOSE 9000

# Commande par défaut pour démarrer PHP-FPM
CMD ["php-fpm"] 