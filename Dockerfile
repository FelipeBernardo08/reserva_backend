FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    cron \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring xml opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY install.sh /var/www/html/install.sh

RUN chmod +x /var/www/html/install.sh

CMD ["/bin/bash", "-c", "/var/www/html/install.sh && apache2-foreground"]
