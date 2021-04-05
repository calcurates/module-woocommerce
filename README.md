# Calcurates WooCommerce module

### Requirements

- PHP >= 7.1
- MySQL >= 5.7
- Web-Server (Nginx/Apache/etc...)

### Docker setup

- Copy `.env.example` to `.env`. Set env vars as you need.
- Copy `.docker/php/wordpress-bedrock/development.php.example` to `.docker/php/wordpress-bedrock/development.php`. Set vars as you need.
- Use `XDEBUG_REMOTE_HOST=host.docker.internal` for Windows or Mac.
- Find IP in Linux for XDEBUG_REMOTE_HOST variable.

```bash
ip -4 addr show docker0 | grep -Po 'inet \K[\d.]+'
```

- Run `composer install`

### Run

```bash
docker-compose up
```

Go to http://localhost:8000

### Stop

```bash
docker-compose stop
```

### Activate plugin

- Go to http://localhost:8000/wp/wp-admin/plugins.php
- Activate WooCommerce, WooCommerce Calcurates, WP Mail Logging (not required)

### Configure plugin

- Go to http://localhost:8000/wp/wp-admin/admin.php?page=wc-settings&tab=shipping
- Press `Add shipping zone` button
- Add Calcurates Shipping Method to shipping zone
- Go to http://localhost:8000/wp/wp-admin/admin.php?page=wc-settings&tab=shipping&section=calcurates
- Set API URL and API key, copy Plugin API Key to your Calcurates panel, save changes

### Check plugin work

- Add some product categories http://localhost:8000/wp/wp-admin/edit-tags.php?taxonomy=product_cat&post_type=product
- Add some product tags http://localhost:8000/wp/wp-admin/edit-tags.php?taxonomy=product_tag&post_type=product
- Add some product Attributes and Configure (fill) their terms with some data http://localhost:8000/wp/wp-admin/edit.php?post_type=product&page=product_attributes
- Add some products http://localhost:8000/wp/wp-admin/edit.php?post_type=product
- Go to http://localhost:8000/shop/ and add some products to cart
- Go to http://localhost:8000/cart/. It's possible to add some shipping data if you wish. Press `Proceed to checkout`
- Check it out ;)
- Check email log after order http://localhost:8000/wp/wp-admin/tools.php?page=wpml_plugin_log. Need WP Mail Logging plugin to be activated.
