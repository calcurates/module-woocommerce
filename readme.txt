=== Calcurates for WooCommerce ===
Contributors: calcurates
Tags: woocommerce, shipping rates, shipping methods, shipping carriers, shipping zones, shipping rules, delivery dates, international shipping, table rates, free shipping, in-store pickup, dimensional shipping, multi-origin shipping, dropshipping
Requires at least: 5.2
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.6.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

An ultimate multi-carrier shipping plugin for e-commerce that helps manage and display the right shipping methods and rates at checkout

== Description ==

# Overview

Calcurates is a cloud-based entire shipping solution that calculates and shows the right shipping methods and rates to customers at checkout based on multiple conditions. It helps e-commerce to seamlessly integrate with major shipping carriers (DHL, UPS, FedEx, USPS, Purolator, Canada Post and more) in order to display real-time rates at checkout.

Calcurates helps you to reduce the gap between true shipping costs you bear and shipping rates your customers pay at the checkout. Bring your shipping to a new level and make it cost-effective and sales-stimulating.

# Features

- Shipping Zones
- Custom Shipping Options (Flat Rate, Free Shipping)
- Carrier Shipping Options
- Table Rates
- Multi-Origin Shipping
- Shipping Rules and Restrictions
- International Shipping and Landed Costs
- Estimated Delivery Dates
- In-Store Pickup
- Volumetric Weight
- Smart Packaging
- Rate Shopping

# Developed By Experts

Calcurates is developed and supported by Amasty. After 10 years of success in developing and supporting e-commerce apps and extensions, the Amasty team is happy to introduce a multi-platform shipping SaaS, which is the perfect solution for shipping profitability.

# Additional Benefits

- Free consultations on e-commerce shipping
- Qualified and careful support
- Migration and configuration services

== Installation ==

1. Upload the plugin folder to the '/wp-content/plugins' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to WordPress settings -> General -> set your Timezone. ATTENTION! Do not use any UTC option!
4. Sign up to [Calcurates](https://my.calcurates.com/) and go to "Websites". Add your WooCommerce website and copy Calcurates API Key from the step 3.
5. Go to Woocommerce -> Settings -> Shipping -> Calcurates Shipping Method. Paste Calcurates API Key in the corresponding field.
6. Copy Plugin Api Key. Go to Calcurates account -> Website Settings and paste it in the corresponding field on the step 4.
7. Click "Sync". After successful synchronization press "Save".
8. Go to Woocommerce -> Settings -> Shipping -> Shipping zones. Add Calcurates Shipping Method to your zones. We recommend allowing Calcurates shipping method to Worldwide zone and control shipping by zones in Calcurates using Shipping Areas functionality.
9. Configure cost-effective shipping with Calcurates and get more happy customers.

== Screenshots ==

1. Right shipping methods and accurate rates for WooCommerce
2. Advanced Shipping Areas by postcodes for WooCommerce
3. Shipping Rules and Restrictions for WooCommerce
4. Dimensional Shipping for WooCommerce
5. Accurate shipping and delivery dates for WooCommerce checkout

== Changelog ==

= 1.6.4 =
- Fix: Fixed PHP Warning: Undefined array key "post_data"

= 1.6.3 =
- Add: New variables for shipping method's explanatory text are now supported: min transit days qty - {min_transit_days}, max transit days qty - {max_transit_days}
- Add: Link to "Settings" has been added in the list of plugins

= 1.6.2 =
- Fix: compatibility with Revolut payment plugin
- Fix: delivery dates picker

= 1.6.1 =
- Add: Duties & taxes: the {tax_amount} variable has been added to Explanatory Text for Carrier and Table Rates Shipping Options

= 1.6.0 =
- Add: "Delivery Date and Time Slot picker" feature for WooCommerce has been implemented
- Fix: Big fix for getting a "Company Name" parameter from the checkout

= 1.5.10 =
- Fix: Error message visibility bug for the carrier shipping option has been fixed

= 1.5.9 =
- Fix: Error message visibility issue for the carrier shipping option has been fixed

= 1.5.8 =
- Fix: Compatibility issues with WPML have been fixed

= 1.5.7 =
- Fix: PHP Warning in rate request

= 1.5.6 =
- Add: Display name for Merged Shipping Option has been added
- Fix: Minor fixes and improvements

= 1.5.5 =
- Fix: Minor fixes and improvements

= 1.5.4 =
- Add: Shipping Segments - filtering by "Cost" has been added for "Custom Group" conditions
- Add: Sort order for Shipping Methods and Carrier Services at the checkout has been implemented

= 1.5.3 =
- Fix: support Shipping class attribute
- Enhancement: better shipping rates sorting

= 1.5.2 =
- Fix: make order emails

= 1.5.1 =
- Fix: SKU didn't sync

= 1.5.0 =
- Add: Support "SKU" as a product attribute has been added
- Enhancement: minimal version of php is 7.2.5

= 1.4.1 =
- Fix: Displaying shipping rates at the cart level (issue with the address update)

= 1.4.0 =
- Add: Multi-Origin improvement - multiple origins per single product (instead of just one) are now available

= 1.3.1 =
- Fix: Error with delivery dates
- Fix: Don't show empty rates
- Fix: Correct disabled rates

= 1.3.0 =
- Add: "Packaging Rules" functionality has been added
- Add: "Merged Shipping Option" has been added
- Add: "Fixed Per Package" calculation for Table Rates has been added
- Add: "Fixed Per Package" calculation for Delivery Dates has been added
- Add: "Fixed Per Package" calculation for Shipping Rues has been added
- Add: Shipping estimates for cart are now available
- Enhancement: Frontend improvements for displaying info, error message and delivery dates
- Enhancement: Delivery Dates and Info Messages display settings have been added to the plugin config

= 1.2.0 =
- Fix: Remove the psr/log dependency as woocommerce has found malware in it

= 1.1.0 =
- Fix: Rename the plugin directory from `wc-calcurates` to `calcurates-for-woocommerce`
- Fix: Improve shipping methods styles and structure

= 1.0.0 =
- First release
