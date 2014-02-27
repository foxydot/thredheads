=== Dynamic Featured Image ===
Contributors: ankitpokhrel, cfoellmann
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J9FVY3ESPPD58
Tags: dynamic featured image, featured image, post thumbnail, dynamic post thumbnail, multiple featured image, multiple post thumbnail
Requires at least: 3.5
Tested up to: 3.8.1
Stable tag: 3.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Dynamically adds multiple featured image (post thumbnail) functionality to posts, pages and custom post types.

== Description ==
Dynamically adds multiple featured image or multiple post thumbnail functionality to your page, posts and custom post types. This plugin provides you an interface to add any number of featured image as you want without writing a single code. These dynamic featured images can then be collected by the various theme functions.

**Overview**  
Dynamic Featured Image enables the option to have MULTIPLE featured images within a post or page. 
This is especially helpful when you use other plugins, post thumbnails or sliders that use featured images. 
Why limit yourself to only one featured image if you can do some awesome stuffs with multiple featured image? 
DFI allows you to add different number of featured images to each post and page that can be collected by the various theme functions.

**How it works?**  
1. After successfull plugin activation go to `add` or `edit` page of posts or pages and you will notice a box for second featured image.  
2. Click `Set featured image`, select required image from "Dynamic Featured Image - Media Selector" popup and click `Set Featured Image`.  
3. Click on `Add New` to add new featured image or use `Remove` link to remove the featured image box.  
4. You can then get the images by calling the function  `$dynamic_featured_image->get_featured_images([$postId (optional)])` in your theme. ([Click here for details](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki "Documentation for current version"))  
5. The data will be returned in the following format.
`
array
  0 => 
    array
      'thumb' => string 'http://your_site/upload_path/yourSelectedImage.jpg' (length=50)
      'full' => string 'http://your_site/upload_path/yourSelectedImage_fullSize.jpg' (length=69)
	    'attachment_id' => string '197' (length=3)
  1 => 
    array
      'thumb' => string 'http://your_site/upload_path/yourSelectedImage.jpg' (length=50)
      'full' => string 'http://your_site/upload_path/yourSelectedImage_fullSize.jpg' (length=69)
	    'attachment_id' => string '198' (length=3)
  2 => ...
`

**Resources**  
1. [Detail Documentation](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki "Documentation for current ver.").  
2. [DFI Blog](http://ankitpokhrel.com.np/blog/category/dynamic-featured-image/ "DFI Blog").

**MultiSite Info**  
You can use `Network Activate` to activate plugin for all sites on a single install. It is only available on the Network admin site not anywhere else. 
Simple `Activate` activates for the site you are currently on. These will be permitted to be activated or deactivated on ANY blog.

While deleting the plugin from the `Network` be sure that the plugin is deactive in all installation of your WordPress network.

**Remote Image URL Info**  
You can add the image using the remote image url but various helper functions provided may/may not work for the image from remote url.
The attachment id for the remote image will always be `null`.

**Contribute**  
If you'd like to check out the code and contribute, join us on [Github](https://github.com/ankitpokhrel/Dynamic-Featured-Image "View this plugin in github"). 
Pull requests, issues, and plugin recommendations are more than welcome!

== Installation ==

1. Unzip and upload the `dynamic-featured-images` directory to the plugin directory (`/wp-content/plugins/`) or install it from `Plugins->Add New->Upload`.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. If you don't see new featured image box, click `Screen Options` in the upper right corner of your wordpress admin and make sure that the `Featured Image 2` box is selected.

== Frequently Asked Questions ==
= 1. The media uploader screen freezes and stays blank after clicking insert into post? =
The problem is usually due to the conflicts with other plugin or theme functions. You can use general debugging technique to find out the problem.

i. Switch to the default wordpress theme to rule out any theme-specific problems.  
ii. Try the plugin in a fresh new WordPress installation.  
iii. If it works, deactivate all plugins from your current wordpress installation to see if this resolves the problem. If this works, re-activate the plugins one by one until you find the problematic plugin(s).  
iv. [Resetting the plugins folder](http://www.google.com/url?q=http%3A%2F%2Fcodex.wordpress.org%2FFAQ_Troubleshooting%23How_to_deactivate_all_plugins_when_not_able_to_access_the_administrative_menus.3F&sa=D&sntz=1&usg=AFQjCNFaei9nyiMZe2yZQUBBA_MghJ-Wxw) by FTP or PhpMyAdmin. Sometimes, an apparently inactive plugin can still cause problems.

= 2. There is no additional image on the page when I save it or publish it? =
This happens when there is any problem in saving you post or page properly. For example, if you try to save or publish the post without the post title the featured images may not be saved properly.

= 3. Can i set the image from remote url? =
If you need to add images from the remote url you need to switch back to ver. 2.0.2 . There is no such feature in ver. 3.0.0 and above.

Note: If you are using remote url to use the feature image, the helper functions may not work properly. 
Alt, caption and title attribute for these images cannot be retrieved using helper functions. `NULL` is returned instead.

= 4. I am seeing a broken image icon when setting the second feature image? [ for ver. 2.0.2 and below ] =
Some plugins like `Regenerate Thumbnails` changes the default image format in media uploader from `File URL` to `Attachment Post URL`. 
Make sure you click on `File URL` under `Link URL` section before clicking `Insert Into Post`.

= 5. I cannot add or change secondary featured images after update? =
This usually happens because of cache. Clear all your cache and try again if you are having this problem. If you still have such problem you can get help through support forum.

= 6. Other problems or questions? =
Other problems? Don't forget to check the [blog](http://ankitpokhrel.com.np/blog/category/dynamic-featured-image/) and learn to create some exciting things using DFI.

Please use [support forum](http://wordpress.org/support/plugin/dynamic-featured-image) first if you have any question or queries about the project. 
If you don't receive any help in support forum then you can directly contact me at `ankitpokhrel@gmail.com`. Please atleast wait for 48hrs before sending another request.

Please feel free to report any bug found at https://github.com/ankitpokhrel/Dynamic-Featured-Image/ or `ankitpokhrel@gmail.com`.

== Screenshots == 
1. New featured image box.
2. Selecting image from media box.
3. Add new featured image box.

== Changelog ==
= 3.0.0 =
* Fully Object Oriented (Thanks to @cfoellmann).
* New WordPress Media Uploader.
* Uses dashicons instead of images.
* Functions to retrieve image descriptions and nth featured image.
* Well documented.

= 2.0.2 =
* Minor css fix (issue #18 in GitHub, Thanks to @cfoellmann)

= 2.0.1 =
* Change in design.

= 2.0.0 =
* Now with various helper functions.
* Helpers to retrieve alt, title and caption of each featured image.
* Added support for remote url.
* WordPress 3.7 compatible.
* Primarily focused on theme developers.

= 1.1.5 =
* Fixed PHP Notice issues in strict debugging mode (Issue #4 in GitHub, Thanks to @Micky Hulse).
* Added post id in media upload box.
* Enhanced MultiSite Support.

= 1.1.2 =
* Resolved media uploader conflicts.

= 1.1.1 =
* Fixed a bug on user access for edit operation.

= 1.1.0 =
* Major security update
* Now uses AJAX to create new featured box
 
= 1.0.3 =
* First stable version with minimum features released.
* Fixed bug for duplicate id.
* Updated dfiGetFeaturedImages function to accept post id.
* Fixed some minor issues.

== Upgrade Notice ==
= 3.0.0 =
This version has major changes which are not compatible with the previous version of the plugin. The plugin is now fully object oriented.

= 2.0.2 =
This version has some minor css fix. Issue #18 in GitHub.

= 2.0.1 =
This version has just some graphics change to make it more attractive. Please clear the cache after update.

= 2.0.0 =
This version has some major updates and is much more powerful than before. Read the documentation carefully before update.