FROM debian:trixie

# install packages
RUN apt update && apt install -y --no-install-recommends \
    nginx php php-fpm php-xml php-dom php-curl php-bcmath \
    php-mysql php-sqlite3 php-zip composer mariadb-server nodejs npm \
    net-tools vim curl supervisor 7zip && rm -rf /var/lib/apt/lists/*

RUN openssl req -nodes -new -x509 -keyout /etc/ssl/certs/joker.local.key -out \
    /etc/ssl/certs/joker.local.crt -subj "/C=GE/ST=State/L=City/O=Organization/OU=Unit/CN=joker"

# copy files
COPY --chown=root:root docker/entrypoint.sh /entrypoint.sh
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/nginx.conf /etc/nginx/sites-available/joker.local

# configure nginx, php and mysql
RUN rm /etc/nginx/sites-enabled/default
RUN ln -s /etc/nginx/sites-available/joker.local /etc/nginx/sites-enabled/joker.local

ARG PHP_VERSION=8.4
RUN sed -i "s/{PHP_VERSION}/${PHP_VERSION}/" \
    /etc/nginx/sites-available/joker.local /etc/supervisor/supervisord.conf
RUN sed -i 's/display_errors = Off/display_errors = On/' "/etc/php/${PHP_VERSION}/fpm/php.ini"
RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' "/etc/php/${PHP_VERSION}/fpm/php.ini"
RUN sed -i 's/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/' /etc/mysql/mariadb.conf.d/50-server.cnf

WORKDIR /www

EXPOSE 80
EXPOSE 443
EXPOSE 3306

RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]