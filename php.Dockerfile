FROM php:7.4.3-apache

ARG PSR_VERSION=0.7.0
ARG PHALCON_VERSION=4.0.2
ARG PHALCON_EXT_PATH=php7/64bits

RUN set -xe && \
    # Download PSR, see https://github.com/jbboehr/php-psr
    curl -LO https://github.com/jbboehr/php-psr/archive/v${PSR_VERSION}.tar.gz && \
    tar xzf ${PWD}/v${PSR_VERSION}.tar.gz && \
    # Download Phalcon
    curl -LO https://github.com/phalcon/cphalcon/archive/v${PHALCON_VERSION}.tar.gz && \
    tar xzf ${PWD}/v${PHALCON_VERSION}.tar.gz && \
    docker-php-ext-install -j $(getconf _NPROCESSORS_ONLN) \
        ${PWD}/php-psr-${PSR_VERSION} \
        ${PWD}/cphalcon-${PHALCON_VERSION}/build/${PHALCON_EXT_PATH} \
    && a2enmod rewrite \
    && \
    # Remove all temp files
    rm -r \
        ${PWD}/v${PSR_VERSION}.tar.gz \
        ${PWD}/php-psr-${PSR_VERSION} \
        ${PWD}/v${PHALCON_VERSION}.tar.gz \
        ${PWD}/cphalcon-${PHALCON_VERSION} \
    && \
    php -m

    RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
    RUN docker-php-ext-install mysqli pdo pdo_mysql
    #upload
    RUN echo "file_uploads = On\n" \
            "memory_limit = 500M\n" \
            "upload_max_filesize = 500M\n" \
            "post_max_size = 500M\n" \
            "max_execution_time = 600\n" \
            > /usr/local/etc/php/conf.d/uploads.ini
    # 5. composer
    COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer