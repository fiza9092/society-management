FROM php:8.1-apache

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo_mysql && docker-php-ext-enable mysqli

# Disable conflicting MPM modules and enable prefork
RUN a2dismod mpm_event mpm_worker || true && \
    a2enmod mpm_prefork rewrite

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]