FROM nginx:stable-alpine

# Windows環境ではUID/GIDの概念が異なるため、これらの引数は削除します
# ARG UID
# ARG GID

# ENV UID=${UID}
# ENV GID=${GID}

# Alpine Linuxのdialoutグループ削除は保持します
RUN delgroup dialout

# Windowsではユーザー/グループの作成は不要なので、これらの行を削除します
# RUN addgroup -g ${GID} --system laravel
# RUN adduser -G laravel --system -D -s /bin/sh -u ${UID} laravel

# nginx.confファイルの修正も不要になります
# RUN sed -i "s/user  nginx/user laravel/g" /etc/nginx/nginx.conf

# COPY命令は変更なし
COPY ./nginx/default.conf /etc/nginx/conf.d/

# ディレクトリ作成は保持
RUN mkdir -p /var/www/html

# 所有権とパーミッションの設定は不要なので削除します
# RUN chown -R laravel:laravel /var/www/html && \
#     chmod -R 755 /var/www/html

# ポート公開とCMDは変更なし
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]