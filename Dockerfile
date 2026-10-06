FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    libldap2-dev \
    libonig-dev \
    libpq-dev \
    libmemcached-dev \
    libssl-dev \
    zlib1g-dev \
    unzip \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap --with-libdir=lib/$(dpkg-architecture -qDEB_HOST_MULTIARCH) \
    && docker-php-ext-install -j$(nproc) \
        gd \
        intl \
        mysqli \
        pdo \
        pdo_mysql \
        soap \
        zip \
        opcache \
        exif \
        ldap \
    && pecl install redis \
    && docker-php-ext-enable redis

# PHP configuration for Moodle
RUN { \
    echo 'max_input_vars = 5000'; \
    echo 'post_max_size = 256M'; \
    echo 'upload_max_filesize = 256M'; \
    echo 'memory_limit = 512M'; \
    echo 'max_execution_time = 300'; \
    echo 'display_errors = Off'; \
    echo 'opcache.enable = 1'; \
    echo 'opcache.memory_consumption = 128'; \
    echo 'opcache.max_accelerated_files = 10000'; \
    } > /usr/local/etc/php/conf.d/moodle.ini

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Download Moodle 4.5
ENV MOODLE_VERSION=4.5
RUN curl -fsSL https://download.moodle.org/download.php/direct/stable405/moodle-latest-405.tgz \
    -o /tmp/moodle.tgz \
    && tar -xzf /tmp/moodle.tgz -C /var/www/html --strip-components=1 \
    && rm /tmp/moodle.tgz \
    && chown -R www-data:www-data /var/www/html

# Copy custom EdTech theme
COPY theme/edtech /var/www/html/theme/edtech/
RUN chown -R www-data:www-data /var/www/html/theme/edtech

# Create moodledata directory
RUN mkdir -p /var/moodledata && chown www-data:www-data /var/moodledata && chmod 0777 /var/moodledata

# Apache config for Moodle
RUN { \
    echo '<Directory /var/www/html>'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    } > /etc/apache2/conf-enabled/moodle.conf

# Copy entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy one-shot data seeders used after the Moodle site is installed.
COPY scripts/seed-english-technical-courses.php /usr/local/bin/seed-english-technical-courses.php
RUN chmod +x /usr/local/bin/seed-english-technical-courses.php

COPY scripts/seed-arabic-islamic-courses.php /usr/local/bin/seed-arabic-islamic-courses.php
RUN chmod +x /usr/local/bin/seed-arabic-islamic-courses.php

# Apply student access settings after Moodle is installed.
COPY scripts/configure-student-access.php /usr/local/bin/configure-student-access.php
RUN chmod +x /usr/local/bin/configure-student-access.php

# One-shot demo course content seeder.
COPY scripts/seed-course-content.php /usr/local/bin/seed-course-content.php
RUN chmod +x /usr/local/bin/seed-course-content.php

VOLUME ["/var/moodledata"]

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
