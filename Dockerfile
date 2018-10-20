FROM php:7.2-fpm
# build-args (ARG) defaults to a production image variant!


ARG IS_PROD_BUILD=true
ARG TIMEZONE=Europe/Prague
ENV DEBIAN_FRONTEND=noninteractive \
	IS_PROD_BUILD=$IS_PROD_BUILD \
	TIMEZONE=$TIMEZONE

	# properly setup timezone
RUN ln -sf "/usr/share/zoneinfo/$TIMEZONE" /etc/localtime \
	&& dpkg-reconfigure tzdata \

	# install all runtime deps.
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

	# eventually install/setup Xdebug
	&& if [ "$IS_PROD_BUILD" != true ]; then \
			pecl install xdebug; \
			docker-php-ext-enable xdebug; \
		fi \
	&& sed -e 's/access.log/;access.log/' -i /usr/local/etc/php-fpm.d/docker.conf \

	# install Composer
	&& php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
		&& php -r "if (hash_file('SHA384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
		&& php composer-setup.php --filename=composer --install-dir=/usr/local/bin \
		&& php -r "unlink('composer-setup.php');" \
		# make writtable global composer installation folder before switch to unprivileged user
		&& chown www-data:www-data /var/www
COPY ./.docker/bin/wait-for-it /usr/local/bin/

ARG PHP_INI=php.ini
COPY ./.docker/$PHP_INI /usr/local/etc/php/php.ini

# prepare app workdir & tools, switch to unprivileged user
WORKDIR /app
RUN mkdir -p \
		var/temp \
	&& chown -R www-data:www-data /app

USER www-data

# make Composer quick
RUN composer global require hirak/prestissimo

# install app dependencies
ARG APP_DEBUG=0
ENV APP_DEBUG=$APP_DEBUG
COPY ./composer.json ./composer.lock ./
RUN composer install --no-autoloader --no-interaction --no-scripts --no-suggest \
	&& composer clearcache

# copy app sources & initialize app
COPY ./bin ./bin/
COPY ./config ./config/
COPY ./migrations ./migrations/
COPY ./src ./src/
COPY ./www ./www/
COPY ./coding-standards.xml ./phpstan.neon ./
RUN composer dump-autoload --optimize \
	&& composer clearcache

ARG TAG
ENV TAG=$TAG
