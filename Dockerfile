FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy all application files
COPY . /var/www/html/

# Set permissions so SQLite database and uploads can be written to
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/database

# Adjust Apache port for Render dynamic $PORT
RUN sed -i 's/Listen 80/Listen 80/g' /etc/apache2/ports.conf

EXPOSE 80

CMD ["apache2-foreground"]
