# Usar imagen base de PHP con Apache
FROM php:7.4-apache

# Instalar dependencias del sistema y herramientas
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libwebp-dev \
        libmagickwand-dev \
        ffmpeg \
        pandoc \
        texlive-latex-base \
        texlive-fonts-recommended \
        texlive-latex-extra \
        potrace \
        cron \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Crear la carpeta uploads y asignar permisos
RUN mkdir /var/www/html/uploads && chmod 777 /var/www/html/uploads

# Copiar los archivos a sus respectivos sitios
COPY . /var/www/html
COPY php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY policy.xml /etc/ImageMagick-6/policy.xml
COPY phpDocumentor.phar /usr/local/bin/phpDocumentor

# Dar permiso de ejecución al archivo PHAR de PHPDocumentor
RUN chmod +x /usr/local/bin/phpDocumentor

# Crear un archivo para el cron job
RUN echo "0 * * * * /usr/local/bin/php /var/www/html/clean.php >> /var/log/cron.log 2>&1" > /etc/cron.d/clear-uploads-cron
RUN chmod 0644 /etc/cron.d/clear-uploads-cron
RUN crontab /etc/cron.d/clear-uploads-cron
RUN touch /var/log/cron.log

# Exponer el puerto 80
EXPOSE 80

# Comando para iniciar Apache y cron en el contenedor
CMD cron && apache2-foreground

# Eliminar los archivos resudiales
RUN rm /var/www/html/php.ini
RUN rm /var/www/html/Dockerfile
RUN rm /var/www/html/phpDocumentor.phar
RUN rm /var/www/html/policy.xml