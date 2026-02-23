FROM php:8.1-apache

# Install mysqli
RUN docker-php-ext-install mysqli

# Fix MPM issue
RUN a2dismod mpm_event && a2enmod mpm_prefork rewrite

COPY . /var/www/html/
RUN chmod -R 755 /var/www/html

EXPOSE 80