FROM debian:bookworm

# install packages
RUN apt update && \
    apt install -y nginx php php-fpm php-xml php-dom php-curl \
    php-bcmath php-mysql php-sqlite3 composer mariadb-server \
    golang-go nodejs npm net-tools vim curl supervisor

RUN go install github.com/mailhog/MailHog@latest

RUN openssl req -nodes -new -x509 -keyout /etc/ssl/certs/joker.local.key -out \
    /etc/ssl/certs/joker.local.crt -subj "/C=GE/ST=State/L=City/O=Organization/OU=Unit/CN=joker"

# copy files
COPY docker/bashrc /root/.bashrc
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY --chown=root:root docker/entrypoint.sh /entrypoint.sh
COPY docker/nginx.conf /etc/nginx/sites-available/joker.local

# configure nginx, php and mysql
RUN rm /etc/nginx/sites-enabled/default
RUN ln -s /etc/nginx/sites-available/joker.local /etc/nginx/sites-enabled/joker.local
RUN sed -i 's/display_errors = Off/display_errors = On/' /etc/php/8.2/fpm/php.ini
RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.2/fpm/php.ini
RUN sed -i 's/^bind-address\s*=\s*127.0.0.1/bind-address = 0.0.0.0/' /etc/mysql/mariadb.conf.d/50-server.cnf

WORKDIR /www

EXPOSE 80
EXPOSE 443
EXPOSE 3306

RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]