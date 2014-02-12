*** This readme must accompany the Plugin at all times and not to be altered or changed in any way. ***

=== WP e-Commerce - Manual Ordering ===

Contributors: Visser Labs
Tags: e-commerce, wp-e-commerce, shop, cart, manual ordering
Requires at least: 2.9.2
Tested up to: 3.6
Stable tag: 1.4.2

== Description ==

Manually process orders on behalf of the customer within WP e-Commerce.

For more information visit: http://www.visser.com.au/wp-ecommerce/

== Installation ==

1. Upload the folder 'wp-e-commerce-manual-ordering' to the '/wp-content/plugins/' directory

2. Activate 'Manual Ordering for WP e-Commerce' through the 'Plugins' menu in WordPress

== Usage ==

====== In WP e-Commerce 3.7 ======

1. Open Store > Add Order

====== In WP e-Commerce 3.8 ======

1. Open Products > Add Order

======

2. Have fun!

== Support ==

If you have any problems, questions or suggestions please join the members discussion on my WP e-Commerce dedicated forum.

http://www.visser.com.au/wp-ecommerce/forums/

== Changelog ==

= 1.4.2 =
* Fixed: Payment Methods list under WP e-Commerce 3.8.12

= 1.4.1 =
* Fixed: Duplicate Select Product
* Changed: Hide duplicate Products from Select Product

= 1.4 =
* Fixed: Sale Status list not working
* Fixed: Styling differences between Edit Orders Plugin
* Fixed: Saving total amounts as double

= 1.3.9 =
* Added: Added debug support to Manual Ordering
* Changed: Moved styles to CSS

= 1.3.8 =
* Fixed: Help support for older WordPress 3.3

= 1.3.7 =
* Added: New Order to New Admin bar dropdown
* Fixed: Capability not loading on activation
* Added: Pre-population of all Checkout details based on the selected user
* Changed: E-mail field is now longer
* Added: jQuery validation for textarea fields
* Fixed: Default type (no Type) now shows
* Fixed: Disable dropdown for out of stock Products
* Added: SKU/Product Name search to Products list
* Added: SKU/Product Name search supports partial matching
* Fixed: Price override on Products
* Fixed: Text styling re-applied

= 1.3.6 =
* Added: Pre-populating billing and shipping details based on the selected user
* Fixed: Loading class conflict
* Added: Assigned to User dropdown (for reporting)
* Added: Conceptual help support to Add Order
* Fixed: Session ID not displaying
* Added: Edit Orders integation
* Added: Discount type support
* Added: Non-admin capability support: add_sales

= 1.3.5 =
* Fixed: Deprecated function notice
* Fixed: Various Undefined Property notices
* Fixed: Shipping & Billing Country is now saved correctly
* Fixed: Payment method is now saved correctly
* Added: Shipping address now has a "Same as Billing Address" option
* Added: Currency to price
* Added: Javascript product entries
* Added: Javascript form validation
* Added: Variation support

= 1.3.4 =
* Fixed: Support for WP e-Commerce 3.8.8
* Added: Order Notes
* Added: Validation remembers Product quantities
* Fixed: Address and Delivery Address types appear as text boxes
* Fixed: Total overrides Sale with new WP e-Commerce 3.8.8
* Fixed: Downloads not working with WP e-Commerce 3.8.8

= 1.3.3 =
* Added: Validation doesn't clear form fields
* Added: Override Sale total
* Added: Apply discount codes
* Added: Payment Type
* Added: Default setting for Payment Type
* Added: Layout setting for Payment Type
* Added: Default setting for Sale Status
* Added: Layout setting for Sale Status
* Added: Settings page for Manual Ordering
* Added: Uninstall script
* Added: Setting to control display of Session ID after each Sale
* Added: Hook to the confirmation prompt for external Plugins

= 1.3.2 =
* Added: Add Order button to User Profile
* Added: Add Order button to Users page

= 1.3.1 =
* Fixed: Database error on User name details

= 1.3 =
* Fixed: WP e-Commerce Plugins widget markup
* Fixed: Sale price bug not being saved against Sales
* Changed: Moved inline stlying into stylesheet
* Added: Using Checkout form fields
* Added: Validation for mandatory Checkout fields

= 1.2.9 =
* Fixed: Styling issue within Plugins Dashboard widget
* Added: Alt. switch to wpsc_get_action()
* Fixed: Overhauled Offline Credit Card integration
* Fixed: Updated 'plugin_version'
* Added: Using dynamic Sale status in WP e-Commerce 3.8

= 1.2.8 =
* Fixed: Issue introduced with wpsc_get_action()

= 1.2.7 =
* Added: Decrement stock based on cart quantities
* Changed: Interface to match WordPress WordPress Administration
* Added: Automated Plugin Updates via Dashboard > Updates
* Fixed: Subscription issue with Members Access
* Added: File download support for Products
* Added: Integrated Version Monitor into Plugin

= 1.2.6 =
* Added: Support for Store Menu for WP e-Commerce

= 1.2.5 =
* Added: Support for Members Access Plugin from GetShopped.org
* Added: Manual Ordering merchant file as payment option

= 1.2.4 =
* Fixed: Issue affecting Plugin update notification

= 1.2.3 =
* Added: Urgent support for WP e-Commerce 3.8+

= 1.2.2 =
* Fixed: Issue that displayed deleted Products in WP e-Commerce 3.7

= 1.2.1 =
* Added: Integration for Offline Credit Card Processing
* Changed: Formatted the Add Sale page better

= 1.2 =
* Added: Support for assigning Sales to existing Customers (Users)

= 1.1 =
* Changed: Migrated custom Page Template solution to a WordPress Plugin

= 1.0 =
* Added: First working release of the Plugin

== Disclaimer ==

This Plugin does not claim to be a PCI-compliant solution. It is not responsible for any harm or wrong doing this Plugin may cause. Users are fully responsible for their own use. This Plugin is to be used WITHOUT warranty.