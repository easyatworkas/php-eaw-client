FROM composer AS deps

WORKDIR /app

ADD composer.* .

RUN composer install

FROM php:7.4-cli

ADD . /app

COPY --from=deps /app/vendor /app/vendor

WORKDIR  /cwd

ENTRYPOINT [ "php", "/app/eaw.php" ]
