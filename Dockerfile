# base image
FROM php:7.2-apache

# install lib pdo_mysql
RUN docker-php-ext-install pdo_mysql
# enable mod_rewrite apache
RUN a2enmod rewrite
# copy cwd -> /var/www
ADD ./ /var/www
# copy production environment aja
COPY .env.production /var/www/.env
# hapus folder /var/www/html di container
# nanti kita symlink aja sama folder public laravel
RUN rm -rf /var/www/html
# change permission yada yada
WORKDIR /var/www
RUN chown -R www-data:www-data storage
RUN chmod -R ug+rw storage
# link storage (perlu diset volume di docker-compose)
RUN php artisan optimize:clear
RUN php artisan storage:link
RUN php artisan optimize
RUN php artisan config:clear

# symlink public -> html
RUN ln -s /var/www/public ./html

