# DEV Environment container
# Contains all necessary programs / tools for developers.
#
# Build the (local) image:
# docker build --pull -t php-injector .
#
# Create a container (example):
# docker run -d --name php-injector -v /Users/alex/dev/php-injector:/src -W /src php-injector bash
#
# NOTES:
# * PWD is set to /src, so all commands you execute with docker exec are started in /src
#
FROM php:8.1-cli
LABEL maintainer="Alexander Schenkel <aschenkel@kadenpartner.ch>"

RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    git

RUN yes ''| pecl install xdebug && \
    docker-php-ext-install zip && \
    docker-php-ext-enable xdebug zip

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

WORKDIR /src
RUN mkdir -p /src/profiler_output && chmod a+w /src/profiler_output

COPY php.ini /usr/local/etc/php/
