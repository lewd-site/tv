FROM php:7.4-fpm-alpine

RUN set -ex \
  && apk --no-cache add postgresql-libs postgresql-dev $PHPIZE_DEPS \
  && pecl install xdebug-2.9.4 \
  && docker-php-ext-install pgsql pdo_pgsql \
  && docker-php-ext-enable xdebug \
  && apk del postgresql-dev

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

ARG XDEBUG_REMOTE_ENABLE
ARG XDEBUG_REMOTE_AUTOSTART
ARG XDEBUG_REMOTE_HOST
ARG XDEBUG_REMOTE_PORT
ARG XDEBUG_IDEKEY
ARG XDEBUG_REMOTE_CONNECT_BACK
RUN echo "xdebug.remote_enable=$XDEBUG_REMOTE_ENABLE" > $PHP_INI_DIR/conf.d/xdebug.ini \
  && echo "xdebug.remote_autostart=$XDEBUG_REMOTE_AUTOSTART" >> $PHP_INI_DIR/conf.d/xdebug.ini \
  && echo "xdebug.remote_host=$XDEBUG_REMOTE_HOST" >> $PHP_INI_DIR/conf.d/xdebug.ini \
  && echo "xdebug.remote_port=$XDEBUG_REMOTE_PORT" >> $PHP_INI_DIR/conf.d/xdebug.ini \
  && echo "xdebug.idekey=$XDEBUG_IDEKEY" >> $PHP_INI_DIR/conf.d/xdebug.ini \
  && echo "xdebug.remote_connect_back=$XDEBUG_REMOTE_CONNECT_BACK" >> $PHP_INI_DIR/conf.d/xdebug.ini

WORKDIR /var/www/tv
