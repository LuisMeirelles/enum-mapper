FROM php:8.3-cli-alpine

RUN docker-php-ext-install pdo pdo_mysql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN chown -R www-data:www-data /var/www/html

RUN addgroup -g 1000 app && adduser -u 1000 app -G app -D

USER app

WORKDIR /app

COPY . /app

CMD ["top", "-b"]
