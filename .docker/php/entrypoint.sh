#!/bin/sh
# see https://docs.docker.com/compose/startup-order/

set -e

until mysql --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --host="mysql" --port="$DB_PORT" -e "SELECT VERSION()"; do
  >&2 echo "MySQL is unavailable - waiting"
  sleep 1
done

>&2 echo "MySQL is up"

isSourced=`mysql --silent --skip-column-names --user="$MYSQL_USER" --password="$MYSQL_PASSWORD" --host="mysql" --port="3306" -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$MYSQL_DATABASE';"`

if [ "${isSourced}" -eq "0" ]; then
    echo "Installing the WordPress..."

    # see https://developer.wordpress.org/cli/commands/core/
    php /wp-cli.phar core download --allow-root --version=latest --force
    php /wp-cli.phar config create --allow-root --force --dbname="$MYSQL_DATABASE" --dbuser="$MYSQL_USER" --dbpass="$MYSQL_PASSWORD" --dbhost=mysql --extra-php <<PHP
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
PHP

    php /wp-cli.phar core install --allow-root --url=http://localhost:${NGINX_PORT} --title=Calcurates --admin_user=admin --admin_password=admin --admin_email=info@example.com --skip-email

    # https://github.com/wp-cli/wp-cli/issues/5335
    php /wp-cli.phar option update siteurl "http://localhost:${NGINX_PORT}/" --allow-root
    php /wp-cli.phar option update home "http://localhost:${NGINX_PORT}/" --allow-root

    # --version=5.1.0 if not set it uses stable version
    php /wp-cli.phar plugin install woocommerce --allow-root --activate
    php /wp-cli.phar plugin install wp-mail-logging --allow-root --activate

fi

# avoid the docker initialization
# see https://github.com/docker/compose/issues/1809
exec "$@"
