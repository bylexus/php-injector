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
FROM php:7.4-cli
LABEL maintainer="Alexander Schenkel <aschenkel@kadenpartner.ch>"

VOLUME ["/src"]
# fixing a bug in the debian dependency tree:
RUN mkdir -p /usr/share/man/man1
RUN mkdir -p /usr/share/man/man7
RUN yes ''| pecl install apcu-5.1.21 && docker-php-ext-enable apcu
RUN apt-get update && apt-get install -y \
    curl \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libpq-dev \
    libxml2-dev \
    libxslt1-dev \
    locales \
    postgresql-client \
    postgresql-client-common \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pgsql pdo_mysql pdo_pgsql xsl

RUN yes ''| pecl install xdebug-3.1.1 && docker-php-ext-enable xdebug

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

WORKDIR /src
RUN mkdir -p /src/profiler_output && chmod a+w /src/profiler_output

COPY php.ini /usr/local/etc/php/

# Set the locale
RUN echo "de_CH.UTF-8 UTF-8" > /etc/locale.gen && locale-gen
ENV LANG de_CH.UTF-8
ENV LANGUAGE de_CH:en
ENV LC_ALL de_CH.UTF-8
RUN cp /usr/share/zoneinfo/Europe/Zurich /etc/localtime
