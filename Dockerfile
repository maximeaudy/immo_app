FROM php:7.4-apache

RUN apt-get update -y && apt-get install git nano -y --no-install-recommends && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install zip
SHELL ["/bin/bash", "-o", "pipefail", "-c"]
RUN apt-get update && apt-get install -y --fix-missing --no-install-recommends apt-utils gnupg && \
    echo "deb http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list && \
    echo "deb-src http://packages.dotdeb.org jessie all" >> /etc/apt/sources.list && \
    curl -sS --insecure https://www.dotdeb.org/dotdeb.gpg | apt-key add - && \
    apt-get install -y --no-install-recommends \
    zlib1g-dev \
    libzip-dev && \
    docker-php-ext-install zip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql

# Install intl
RUN apt-get update && apt-get install -y --no-install-recommends libicu-dev && \
    docker-php-ext-install -j$(nproc) intl && \
    docker-php-ext-install intl && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer

# Install yarn and node.js
RUN apt-get update && apt-get install -y --no-install-recommends nodejs && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && apt-get update -y && apt-get install yarn -y --no-install-recommends && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Symfony
RUN curl https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony/bin/symfony /usr/local/bin/symfony && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Rewrite url
RUN a2enmod rewrite && \
    a2dissite 000-default.conf
ADD apache.conf /etc/apache2/sites-available/
RUN a2ensite apache.conf

WORKDIR /var/www/html

COPY . /var/www/html
