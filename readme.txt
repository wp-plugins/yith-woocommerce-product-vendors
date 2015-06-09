=== YITH WooCommerce Multi Vendor ===

Contributors: yithemes
Tags: product vendors, vendors, vendor, multi store, multi vendor, multi seller, woocommerce product vendors, woocommerce multi vendor, commission rate, seller, shops, vendor shop, vendor system, woo vendors, wc vendors, e-commerce, multivendor, multivendors, wc multivendor, yit, yith, yithemes
Requires at least: 4.0
Tested up to: 4.2.2
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

YITH WooCommerce Multi Vendor is a plugin explicitly developed to switch your website into a platform hosting more than one shop.

== Description ==

Are you trying to make your website a place where you can host many vendor pages and earn commissions from their sales? YITH WooCommerce Multi Vendor
is the plugin explicitly developed to switch your website into a multi-vendor platform that will let you earn from commissions without you to lift a finger.
Moreover a multi-vendor shop brings more traffic to your site and allows you to get a better engine search positioning.
A double, huge benefit from a single plugin. And all free.

**Section “Vendors”:**

* Vendor page creation with following information: name, PayPal email address, Owner, Store description, Slug
* Global Commission Rate
* Superadmin can enable or disable sales for each vendor

**Show owner can:**

* Manage vendor’s media gallery
* Manage vendor’s products
* Manage linked products: grouped, upsells and cross sells
* Edit vendor information: name, description, PayPal email address, slug

**Section “Commissions”:**

* Superadmin makes payments on the basis of his/her own policy, agreed by vendors when starting cooperation sale and commission reports

**Section “Shop”:**

* Tab “Vendor” with information about the supplier (if any) and link to vendor’s page in single product page
* Vendor’s name next to product name in shop page
* Vendor’s name next to product name in single product page
* Widget for displaying a list of all vendors

Please, read the the **[official plugin documentation](http://yithemes.com/docs-plugins/yith-woocommerce-multi-vendor)** to know all plugin features.

== Screenshots ==

1. Admin: add a new vendor shop
2. Admin: vendor's product list
3. Admin: Set default commission percentage
4. Admin: Commision details
5. Admin: Vendor's commission report
6. Admin: Widget option details
7. Shop: Shop page with widget showing vendor list
8. Shop: Vendor's shop page
9. Shop: Show Vendor for this product


== Frequently Asked Questions ==

= Can i customize plugin templates ? =

Yes, you can, plugin templates can be overwritten from your theme.
You just have to create the folder *woocommerce/product-vendors* and add in it the templates that you want to customize.

== Installation ==

1. Unzip the downloaded zip file.
2. Upload the plugin folder into the `wp-content/plugins/` directory of your WordPress site.
3. Activate `YITH WooCommerce Multi Vendor` from Plugins page

== Changelog ==

= 1.4.0 =

* Added: Support to WooCommerce 2.3.9
* Updated: Plugin default language file
* Fixed: Store header image on Firefox and Safari
* Fixed: Wrong commission link in order page

= 1.3.0 =

* Updated: Plugin default language file
* Fixed: Changed "Product Vendors" label  to "Vendor" in product list table
* Fixed: Unable to rewrite frontend css on child theme
* Fixed: Widget Vendor list: option "Hide this widget on vendor page" doesn't work
* Removed: Old sidebar template
* Removed: Old default.po file

= 1.2.0 =

* Tweak: Admin options management
* Updated: default.po file
* Fixed: 404 error on wp_enqueue_style
* Fixed: Options for Vendors list widget doesn't work

= 1.1.1 =

* Fixed: Media link disappear

= 1.1.0 =

* Added: Support to WordPress 4.2
* Tweak: safe url on add_query_args() and remove_query_args()
* Fixed: wc_get_template was called incorrectly in add product
* Fixed: email template doesn't exists

= 1.0.2 =

* Added: Support to WooCommerce 2.3.8
* Added: yith_wc_product_vendors_details_menu_items filter
* Tweak: Vendors management
* Tweak: Commissions management
* Tweak: Template management
* Updated: Database version
* Fixed: Issue on edit/create vendor
* Removed: Old global template for vendor name
* Removed: YITH Vendors Backend class

= 1.0.1 =

* Fixed: Wrong commission rate on vendor admin page

= 1.0.0 =

* Initial release

== Upgrade Notice ==

= 1.4.0 =

* Added coupon and review management.

= 1.3.0 =

* Fixed minor bugs

= 1.2.0 =

* New admin options management

= 1.1.1 =

* Restore the media link gallery on wp dashboard.

= 1.1.0 =

* Fixed the WordPress vulnerability on add_query_arg() and remove_query_arg() functions

= 1.0.0 =

* Initial release
