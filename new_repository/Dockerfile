FROM php:8.2-fpm

# システム依存関係をインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# PHP拡張機能をインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composerのインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.jsとnpmをインストール
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# 作業ディレクトリを設定
WORKDIR /var/www

# Composerのキャッシュディレクトリを作成
RUN mkdir -p /var/www/.composer && chmod -R 777 /var/www/.composer

# アプリケーションファイルをコピー
COPY . /var/www

# Composerの依存関係をインストール
RUN composer install

# npmの依存関係をインストール
RUN npm install

# エントリーポイントを設定
CMD ["php-fpm"]

EXPOSE 9000
