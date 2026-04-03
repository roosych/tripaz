FROM laravelsail/php84-composer:latest

# Включаем расширение exif
RUN docker-php-ext-install exif
