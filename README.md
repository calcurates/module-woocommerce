### Calcurates WooCommerce module

Calcurates is a cloud-based entire shipping solution that calculates and shows the right shipping methods and rates to customers at checkout based on multiple conditions. It helps e-commerce to seamlessly integrate with major shipping carriers (DHL, UPS, FedEx, USPS, Purolator, Canada Post and more) in order to display real-time rates at checkout.

Calcurates helps you to reduce the gap between true shipping costs you bear and shipping rates your customers pay at the checkout. Bring your shipping to a new level and make it cost-effective and sales-stimulating.


#### Requirements
- PHP >= 7.1.3
- MySQL >= 5.7
- Web-Server (Nginx/Apache/etc...)
- Wordpress >= 5.2
- WooCommerce >= 4.3

#### Docker setup
Copy `.env.example` to `.env`. Set env vars as you need.

```bash
docker-compose up -d
cd wp-content/plugins/calcurates-for-woocommerce
/composer.phar install
```
Go to http://localhost:8000

#### Activate plugin
- Go to http://localhost:8000/wp-admin/plugins.php
- Activate Calcurates for WooCommerce

#### Configure plugin
- Go to http://localhost:8000/wp-admin/admin.php?page=wc-settings&tab=shipping
- Press `Add shipping zone` button
- Add Calcurates Shipping Method to shipping zone
- Go to http://localhost:8000/wp-admin/admin.php?page=wc-settings&tab=shipping&section=calcurates
- Set API URL and API key, copy Plugin API Key to your Calcurates panel, save changes

#### Check plugin work
- Add some product categories http://localhost:8000/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product
- Add some product tags http://localhost:8000/wp-admin/edit-tags.php?taxonomy=product_tag&post_type=product
- Add some product Attributes and Configure (fill) their terms with some data http://localhost:8000/wp-admin/edit.php?post_type=product&page=product_attributes
- Add some products http://localhost:8000/wp-admin/edit.php?post_type=product
- Go to http://localhost:8000/shop/ and add some products to cart
- Go to http://localhost:8000/cart/. It's possible to add some shipping data if you wish. Press `Proceed to checkout`
- Check it out
- Check email log after order http://localhost:8000/wp-admin/tools.php?page=wpml_plugin_log. Need WP Mail Logging plugin to be activated.

#### Dev tools
```bash
cd wp-content/plugins/calcurates-for-woocommerce
lib/bin/php-cs-fixer fix
```
```bash
cd wp-content/plugins/calcurates-for-woocommerce
php -d=memory_limit=-1 lib/bin/phpstan
```
