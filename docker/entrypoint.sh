#!/bin/bash
chmod 700 /entrypoint.sh

# install dependencies
cp .env.example .env
php artisan storage:link && composer install && npm install && npm run dev

REVERB_SECRET="$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 16)"
sed -i "s/REVERB_APP_SECRET=/REVERB_APP_SECRET=$REVERB_SECRET/" /www/.env

# permission for writing to storage
chown -R www-data:www-data /www/storage

mysqld_safe &
while ! mysqladmin ping -h'localhost' --silent; do echo 'mysqld is down' && sleep .2; done

mysql -u root -e 'CREATE DATABASE IF NOT EXISTS joker;'
DB_PASS="$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 16)"
mysql -u root -e "ALTER USER root@localhost IDENTIFIED BY '$DB_PASS';"
mysql -u root -p"$DB_PASS" -e 'FLUSH PRIVILEGES;'
php /www/artisan key:generate --force
php /www/artisan migrate:fresh --force
php /www/artisan db:seed --force

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf