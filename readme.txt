=== OmniFund Payment Gateway for WooCommerce ===
Contributors: omnifunddev
Tags: credit card, payment request, omnifund, ach, pci, payment processing, card processing, merchant account
Requires at least: 3.0.1
Tested up to: 6.6.1
Requires PHP: 7.0 and higher
Stable tag: 1.1.4
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
The OmniFund payment gateway for WooCommerce allows you to accept credit card payments directly on your website storefront.
 
== Description ==
 
The OmniFund Payment Gateway for WooCommerce plugin was built by OmniFund, a service of GotoBilling Inc.

Supported Payment Types: ACH, Credit Cards, Debit Cards  

OmniFund is a "Payments as a Platform" company. With our Payments as a Platform solution, we simplify the payment process, creating a seamless experience for you and your customers. Dedicated US-based experts help support and protect your company from fraud, provide the tools for tokenization, and can transition your company to be 100% out of PCI scope.

Our cloud-based software solution gives all the benefits with less risk—streamlining procedures while protecting business data. We offer a scalable, customizable solution designed to meet the goals of your business and grow with you.

We take it one step further with expert, personalized consulting and support to fully and easily integrate our solution with your existing systems—giving you the resources to grow your business with peace of mind.

The plugin requires you to have an active OmniFund account and set up API credentials to process transactions securely.

This plugin communicates with OmniFund’s servers hosted at secure.gotobilling.com.

To be fully PCI compliant, you will need to install an SSL certificate on your site to ensure that all transactions are securely processed.

**Important** Be sure to install reCaptcha on the checkout page. Additionally, installation of WooCommerce Anti-Fraud plugin is recommended to prevent card attacks. Using a combination of both plugins will prevent fraudsters from running scripts against the page, which can cause costly fees to be incurred.

This version of the plugin has been tested up through WooCommerce 9.2.3.  For more information, please visit [our documentation] (https://gotobilling.atlassian.net/wiki/spaces/DOC/pages/505544712/WooCommerce+Plugin)

 
== Installation ==
 
Automatic installation

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don't need to leave your web browser. To do an automatic install of the plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "WooCommerce OmniFund Gateway" and click Search Plugins. Once you've found the plugin, you can install it by simply clicking "Install Now".

Manual installation

To manually install the plugin:
1. Visit WordPress.org, and search for "WooCommerce OmniFund Gateway".
1. Click "Download" to download the plugin.
1. Unzip the file you downloaded, and upload the content to the "/wp-content/plugins/" directory on your site.
1. Activate the plugin through the "Plugins" menu in WordPress
 
== Frequently Asked Questions ==
 
= Do I need any special accounts for this plugin? =
 
Yes, you will need to contact OmniFund to setup a payment account with them at the [OmniFund Website] (http://www.omnifund.com/ "OmniFund Website")
 
= Are special credentials required? =
 
Yes, you will need to have API credentials generated.  
1. Login to your OmniFund account.
1. Visit the "Profile" link in the upper right.
1. Generate API Credentials, and record those for use in the plugin.
 
== Screenshots ==
 
1. Plugin Configuration
1. CC Processing Fields
1. ACH Processing Fields
 
== Changelog ==

= 1.1.4 =
* Update for Wordpress compatiblity and WooCommerce compatibility. Documentation update - recommend use of WooCommerce Anti-Fraud plugin

= 1.1.3 =
* Refactoring for PHP version compatiblity, Wordpress compatiblity and WooCommerce compatibility
 
= 1.1.2 =
* Refactoring for further compatibility across PHP Versions.
 
= 1.1.1 =
* Bug fixes for language compatibility.
 
= 1.1 =
* Addition of reCaptcha as an optional element in the payment process.
 
= 1.0.1 =
* Compatibility Updates
 
= 1.0 =
* Initial Release
 
== Upgrade Notice ==

= 1.1 =
Upgrade to add reCaptcha support.

= 1.0.1 =
Upgrade to ensure your compatibility with the latest versions of Wordpress and WooCommerce

= 1.1.3 =
Upgrade to ensure compatibility with Wordpress 6.0 and WooCommerce 6.8
