FROM php:8.2.0-fpm-bullseye

# 必要なディレクトリを作成
WORKDIR /workspace

# Nginxの設定ファイルをコピー
COPY ./nginx/default.conf /etc/nginx/conf.d/default.conf

# プロジェクトルートに docker ディレクトリが存在しない場合
# COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
# COPY ./docker/nginx/default.conf /etc/nginx/conf.d/default.conf
# COPY ./docker/nginx/default.sandbox.conf /etc/nginx/conf.d/default.sandbox.conf
# COPY ./docker/nginx/default.stage.conf /etc/nginx/conf.d/default.stage.conf
# COPY ./docker/nginx/.htpasswd /etc/nginx/.htpasswd
# COPY ./docker/php/php.deploy.ini /usr/local/etc/php/php.ini

# プロジェクトルートに src ディレクトリが存在しない場合
# COPY ./src /workspace

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