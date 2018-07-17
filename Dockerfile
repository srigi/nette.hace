FROM php:7.2-fpm
# build-args defaults to a production image variant

ENV DEBIAN_FRONTEND=noninteractive

ARG IS_PROD_BUILD=true
ENV IS_PROD_BUILD=$IS_PROD_BUILD
ARG TIMEZONE=Europe/Prague
ENV TIMEZONE=$TIMEZONE
RUN ln -sf "/usr/share/zoneinfo/$TIMEZONE" /etc/localtime \
	&& dpkg-reconfigure tzdata \
	&& apt-get update \
	&& apt-get install -y --no-install-recommends \
		libicu-dev \
		libpng-dev \
		libpq-dev \
		unzip \
	&& docker-php-ext-enable \
		opcache \
	&& docker-php-ext-install \
		gd \
		intl \
		pdo_pgsql \
	&& if [ "$IS_PROD_BUILD" != true ]; then \
			pecl install xdebug; \
			docker-php-ext-enable xdebug; \
		fi \
	&& sed -e 's/access.log/;access.log/' -i /usr/local/etc/php-fpm.d/docker.conf \
	&& php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
		&& php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
		&& php composer-setup.php --filename=composer --install-dir=/usr/local/bin \
		&& php -r "unlink('composer-setup.php');" \
		&& chown www-data:www-data /var/www

COPY ./.docker/bin/wait-for-it /usr/local/bin/
COPY ./.docker/php.ini /usr/local/etc/php/

# Prepare app workdir & tools, switch to unprivileged user
WORKDIR /app
RUN mkdir -p \
		var/logs \
		var/temp \
	&& chown -R www-data:www-data /app

USER www-data
RUN composer global require hirak/prestissimo

# Install app dependencies
ARG APP_DEBUG=0
ENV APP_DEBUG=$APP_DEBUG
COPY ./composer.json ./composer.lock ./
RUN composer install --no-autoloader --no-interaction --no-scripts --no-suggest \
	&& composer clearcache

# Copy app sources & initialize app
COPY ./bin ./bin/
COPY ./config ./config/
COPY ./migrations ./migrations/
COPY ./src ./src/
COPY ./www ./www/
COPY ./coding-standards.xml ./
RUN composer dump-autoload --optimize \
	&& composer clearcache

ARG TAG
ENV TAG=$TAG
