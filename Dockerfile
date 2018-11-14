FROM php:7.1-fpm

## Ref: https://blog.csdn.net/encircles/article/details/80017609 

RUN apt-get update \
    && apt-get install -y \
        vim \
        curl \
        wget \
        git \
        zip \
        libz-dev \
        libssl-dev \
        libnghttp2-dev \
        telnet \
        iputils-ping \
    && apt-get clean \
    && apt-get autoremove
 

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update --clean-backups
 

RUN wget https://github.com/swoole/swoole-src/archive/v4.2.5.tar.gz -O swoole.tar.gz \
    && mkdir -p swoole \
    && tar -xf swoole.tar.gz -C swoole --strip-components=1 \
    && rm swoole.tar.gz \
    && ( \
        cd swoole \
        && phpize \
        && ./configure --enable-coroutine --enable-openssl --enable-http2 \
        && make -j$(nproc) \
        && make install \
    ) \
    && rm -r swoole \
    && docker-php-ext-enable swoole

## install librdkafka
RUN git clone https://github.com/edenhill/librdkafka.git \
    && cd librdkafka \
    && ./configure \
    && make && make install

RUN pecl install rdkafka

## install protobuf
RUN PROTOC_ZIP=protoc-3.3.0-linux-x86_64.zip \
    && curl -OL https://github.com/google/protobuf/releases/download/v3.3.0/$PROTOC_ZIP \
    && unzip -o $PROTOC_ZIP -d /usr/local bin/protoc \
    && rm -f $PROTOC_ZIP

RUN pecl install protobuf-3.6.1

COPY ./php.ini /usr/local/etc/php

ADD . /var/www/app
WORKDIR /var/www/app

RUN cd /var/www/app && mkdir -p protobuf/build \
    && protoc --php_out=protobuf/build protobuf/src/*.proto
 
RUN composer install --no-dev\
    && composer dump-autoload -o \
    && composer clearcache

ENV SWOOLE_HTTP_PORT 80
ENV SWOOLE_HTTP_HOST "0.0.0.0"

#ENTRYPOINT php artisan swoole:http start

EXPOSE 80