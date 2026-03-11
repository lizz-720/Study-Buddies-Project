FROM php:8.2-apache
WORKDIR /var/www/html/
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN mv mainscreen.html index.html

RUN chown -R www-data:www-data /var/www/html/