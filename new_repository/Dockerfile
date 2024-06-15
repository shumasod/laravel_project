FROM php:8.2.0-fpm-bullseye AS base

WORKDIR /workspace

# timezone environment
ENV TZ=Asia/Tokyo \
    # locale
    LANG=en_US.UTF-8 \
    LANGUAGE=en_US:en \
    LC_ALL=en_US.UTF-8 \
    # composer environment
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer

COPY --from=composer:2.4 /usr/bin/composer /usr/bin/composer

# npmは比較的新しい方でないとだめ
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
        nodejs \
        nginx \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && locale-gen en_US.UTF-8 \
    && localedef -f UTF-8 -i en_US en_US.UTF-8 \
    && docker-php-ext-install \
    intl \
    pdo_mysql \
    zip \
    bcmath \
    && composer config -g process-timeout 3600 \
    && composer config -g repos.packagist composer https://packagist.org \
    && sed -i "s/CipherString = DEFAULT@SECLEVEL=2/CipherString = DEFAULT@SECLEVEL=0/g" /etc/ssl/openssl.cnf

COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY ./docker/nginx/default.sandbox.conf /etc/nginx/conf.d/default.sandbox.conf
COPY ./docker/nginx/default.stage.conf /etc/nginx/conf.d/default.stage.conf
COPY ./docker/nginx/.htpasswd /etc/nginx/.htpasswd

COPY ./docker/php/php.deploy.ini /usr/local/etc/php/php.ini
COPY ./src /workspace

RUN composer install --optimize-autoloader --no-dev \
    && chmod -R 777 storage bootstrap/cache \
    && php artisan optimize:clear \
    && npm install \
    && npm run build

FROM base AS deploy
COPY ./docker/php/entrypoint/entrypoint.sh /workspace/entrypoint.sh
RUN chmod +x /workspace/entrypoint.sh
ENTRYPOINT ["/workspace/entrypoint.sh"]


FROM base AS development

COPY ./docker/php/php.development.ini /usr/local/etc/php/php.ini
# xdebugのインストール
RUN git clone https://github.com/xdebug/xdebug.git \
    && cd xdebug \
    && git checkout xdebug_3_2 \
    && phpize \
    && ./configure \
    && make \
    && make install

COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

