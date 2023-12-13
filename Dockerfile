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
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Copiar el código fuente de la aplicación al contenedor
COPY converter/ /var/www/html
COPY php.ini /usr/local/etc/php/conf.d/custom-php.ini

# Configurar Apache (opcional, según tus necesidades)
# COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

# Exponer el puerto 80
EXPOSE 80