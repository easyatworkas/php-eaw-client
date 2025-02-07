FROM php:7.4-cli

ADD . /app

WORKDIR  /cwd

ENTRYPOINT [ "php", "/app/eaw.php" ]
