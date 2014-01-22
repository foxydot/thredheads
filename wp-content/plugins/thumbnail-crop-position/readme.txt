=== Thumbnail Crop Position ===
Contributors: javitxu123, GregLone
Donate link: http://poselab.com/
Tags: thumbnail, crop, position, upload, media, library, gallery, image, size
Requires at least: 3.5
Tested up to: 3.5
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Select the crop position of your thumbnails.

== Description ==

Select the crop position of your thumbnails. Wordpress crops thumbnails of images through the center, which does not always give us the desired results. This plugin allows you to select the crop position of images from Wordpress uploader.


== Installation ==

1. Install Thumbnail Crop Position either via the WordPress.org plugin directory, or by uploading the files to your server.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. That's it. You're ready to go! You will find Thumbnail Crop Position panel in Upload files tab of Insert Media panel and in Upload Media page of Media.


== Screenshots ==

1. Thumbnail Crop Position in Insert Media panel.
2. Thumbnail Crop Position in Upload Media page.


== Changelog ==

= 1.3 =
This update were made by Grégory Viguier ([ScreenfeedFr](http://profiles.wordpress.org/GregLone/)). Thanks Grégory.

* The PHP class is now in a separated file, so it is included only in the administration pages (and removed the create_function(), bad for the php cache).
* Globally, use a more "WordPress way" for some parts of the code.
* SECURITY: better sanitization of the option, check user capability, and add a nonce for the ajax call.
* Minify css, js and images (js and css dev. versions still presents).
* HTML: remove useless divs, adjust some classes.
* CSS: use existing WordPress styles for the buttons, simpler but same look.
* JS: no more inline scripts (onclick), change button active state only on valid response, add a loading state.
* Translation: use native translations (left, right, center) and add French translation.

= 1.2.1 =
* Fixed a bug that showed a text in the footer of posts.

= 1.2 =
* Corrected load of plugin.
* CSS improvement.

= 1.1 =
* Initial Release.