# Dockerfile
FROM php:8.2.0-fpm-bullseye

# 必要なディレクトリを作成
WORKDIR /workspace

# 必要なファイルをコピー
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/default.sandbox.conf /etc/nginx/conf.d/default.sandbox.conf
COPY ./docker/nginx/default.stage.conf /etc/nginx/conf.d/default.stage.conf
COPY ./docker/nginx/.htpasswd /etc/nginx/.htpasswd
COPY ./docker/php/php.deploy.ini /usr/local/etc/php/php.ini
COPY ./src /workspace

# 他のビルド手順
COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get update \
    && apt-get -y install --no-install-recommends \
        vim \
        locales \
        git \
        unzip \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        nginx

# 開発用設定
COPY ./docker/php/php.development.ini /usr/local/etc/php/php.ini
RUN git clone https://github.com/xdebug/xdebug.git \
    && cd xdebug \
    && git checkout xdebug_3_2 \
    && phpize \
    && ./configure \
    && make \
    && make install
COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
