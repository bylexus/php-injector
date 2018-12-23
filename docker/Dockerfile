# DEV Environment container
# Contains all necessary programs / tools for developers.
#
# Build the (local) image:
# docker build --pull -t php-injector .
#
# Create a container (example):
# docker run -d --name php-injector -v /Users/alex/dev/php-injector:/var/www/html -p 8080:80 php-injector
#
# NOTES:
# * PWD is set to /var/www, so all commands you execute with docker exec are started in /var/www
#
FROM php:7.0-apache
LABEL maintainer="Alexander Schenkel <aschenkel@kadenpartner.ch>"

VOLUME ["/var/www/html"]
# fixing a bug in the debian dependency tree:
RUN mkdir -p /usr/share/man/man1
RUN mkdir -p /usr/share/man/man7
RUN yes ''| pecl install apcu-5.1.16 && docker-php-ext-enable apcu
RUN apt-get update && apt-get install -y \
    curl \
    git \
    gnupg \
    graphviz \
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
    && docker-php-ext-install -j$(nproc) iconv mcrypt \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pgsql pdo_mysql pdo_pgsql xsl

RUN yes ''| pecl install xdebug-2.6.1 && docker-php-ext-enable xdebug

# install nodejs and node link, grunt
RUN sh -c 'curl -sL https://deb.nodesource.com/setup_8.x | bash -'
RUN apt-get update && apt-get install -y nodejs
RUN npm install -g grunt-cli

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/bin --filename=composer && \
    php -r "unlink('composer-setup.php');"

# Enable mod rewrite in apache
RUN a2enmod rewrite


WORKDIR /var/www/html
RUN mkdir -p /var/www/html/profiler_output && chmod a+w /var/www/html/profiler_output

COPY php.ini /usr/local/etc/php/

# Set the locale
RUN echo "de_CH.UTF-8 UTF-8" > /etc/locale.gen && locale-gen
ENV LANG de_CH.UTF-8
ENV LANGUAGE de_CH:en
ENV LC_ALL de_CH.UTF-8
RUN cp /usr/share/zoneinfo/Europe/Zurich /etc/localtime
