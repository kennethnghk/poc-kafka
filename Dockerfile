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
 
ADD . /var/www/easyswoole
WORKDIR /var/www/easyswoole
 
RUN composer install --no-dev\
    && composer dump-autoload -o \
    && composer clearcache
 
RUN php bin/easyswoole install
 
EXPOSE 80
 
CMD ["php", "/var/www/easyswoole/bin/easyswoole", "start", "-d"]