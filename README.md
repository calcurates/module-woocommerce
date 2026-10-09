### Calcurates WooCommerce module

Calcurates is a cloud-based entire shipping solution that calculates and shows the right shipping methods and rates to customers at checkout based on multiple conditions. It helps e-commerce to seamlessly integrate with major shipping carriers (DHL, UPS, FedEx, USPS, Purolator, Canada Post and more) in order to display real-time rates at checkout.

Calcurates helps you to reduce the gap between true shipping costs you bear and shipping rates your customers pay at the checkout. Bring your shipping to a new level and make it cost-effective and sales-stimulating.


#### Requirements
- PHP >= 7.4
- MySQL >= 8.0
- Web-Server (Nginx/Angie/Apache/etc...)
- Wordpress >= 5.3
- WooCommerce >= 4.3

#### Docker setup
Copy `.env.example` to `.env`. Set env vars as you need.

```bash
docker-compose up -d
cd wp-content/plugins/calcurates-for-woocommerce
composer install
```

#### Setup a virtual host with NGINX as a proxy
Instruction is written for Debian based Linux OS
Host NGINX proxies `woocommerce.loc` to the WooCommerce container. The site config is `.docker/nginx/woocommerce.loc.conf`. It forwards to `http://127.0.0.1:8800`, so set `WEBSERVER_PORT=8800` in `.env` before `docker-compose up`. If you keep another port, change `proxy_pass` in that file to the same port, and if you would like to use something else instead of `woocommerce.loc` also change it in config file.

From the repository root on the host:

1. Install the site and enable it.

```bash
sudo cp .docker/nginx/woocommerce.loc.conf /etc/nginx/sites-available/woocommerce
sudo ln -sfn /etc/nginx/sites-available/woocommerce /etc/nginx/sites-enabled/woocommerce
```

2. Point the hostname at this machine.

```bash
grep -q 'woocommerce.loc' /etc/hosts || echo '127.0.0.1 woocommerce.loc www.woocommerce.loc' | sudo tee -a /etc/hosts
```

3. Reload NGINX.

```bash
sudo nginx -t && sudo systemctl reload nginx
```

4. Point WordPress at the hostname. The container install sets the site URL to `http://localhost:${WEBSERVER_PORT}`, and WordPress redirects the browser there until these options match the virtual host.

```bash
docker exec woocommerce-php php /wp-cli.phar option update home 'http://woocommerce.loc' --allow-root --path=/var/www/wordpress
docker exec woocommerce-php php /wp-cli.phar option update siteurl 'http://woocommerce.loc' --allow-root --path=/var/www/wordpress
```

Open http://woocommerce.loc OR http://localhost:8000 if you did not pass the part above(Setup a virtual host)

In the following instruction replace HOST_NAME with you host name configrued by NGINX virtual host or localhost as written above.

#### Activate plugin
- Go to HOST_NAME/wp-admin/plugins.php
- Activate Calcurates for WooCommerce

#### Configure plugin
- Go to HOST_NAME/wp-admin/admin.php?page=wc-settings&tab=shipping
- Press `Add shipping zone` button
- Add Calcurates Shipping Method to shipping zone
- Go to HOST_NAME/wp-admin/admin.php?page=wc-settings&tab=shipping&section=calcurates
- Set API URL and API key, copy Plugin API Key to your Calcurates panel, save changes

#### Check plugin work
- Add some product categories HOST_NAME/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product
- Add some product tags HOST_NAME/wp-admin/edit-tags.php?taxonomy=product_tag&post_type=product
- Add some product Attributes and Configure (fill) their terms with some data HOST_NAME/wp-admin/edit.php?post_type=product&page=product_attributes
- Add some products HOST_NAME/wp-admin/edit.php?post_type=product
- Go to HOST_NAME/shop/ and add some products to cart
- Go to HOST_NAME/cart/. It's possible to add some shipping data if you wish. Press `Proceed to checkout`
- Check it out
- Check email log after order HOST_NAME/wp-admin/tools.php?page=wpml_plugin_log. Need WP Mail Logging plugin to be activated.

#### Dev tools
```bash
cd wp-content/plugins/calcurates-for-woocommerce
lib/bin/php-cs-fixer fix
```
```bash
cd wp-content/plugins/calcurates-for-woocommerce
php -d=memory_limit=-1 lib/bin/phpstan
```
