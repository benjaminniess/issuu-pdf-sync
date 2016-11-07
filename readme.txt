=== Issuu PDF Sync ===
Contributors: benjaminniess, momo360modena, pronamic, remcotolsma
Donate link: http://beapi.fr/donate
Tags: Issuu, flipbook, PDF, upload, synchronisation, flash, flip, book
Requires at least: 3.5
Tested up to: 4.7
Stable tag: 3.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Allows to create PDF Flipbooks with the http://issuu.com service. You just need to get a free key and all your PDF will synchronised on the site.
Then you'll be abble to insert flipbooks inside your posts without having to quit the WordPress admin pannel.


== Installation ==

1.	Upload and activate the plugin
2.	Go to Issuu.com and get an API Key http://issuu.com/user/settings?services=true#services
3.	Go back to your WordPress admin and go to 'Settings > Issuu PDF Sync'
4.	Enter your Issuu API Key and Secret Key
5.	Now, when you will upload a PDF in your library (or directly in your post), it will be sent to Issuu
6.	If you want to add a flipbook into a post, clic on the Issuu button in your tinyMCE editor and select your PDF file.
7.	IMPORTANT : If you want to use the custom layouts, you first need to copy the crossdomain.xml file located in the root folder of the plugin to the root folder of your website.
8.	For more info, go to the bottom of the 'Settings > Issuu PDF Sync' page 


== Frequently Asked Questions ==

= How to get an Issuu API Key ? =

You need to go to http://issuu.com and to create an account. Then go to the http://issuu.com/user/settings?services=true#services page and create a new application

= I can't see my PDF files in the dropdown list =

You may have configured the plugin after having uploaded some PDF. In this, case, you'll have to edit each of them and click to the sync link. We don't have a resync all function yet
You may have used the media button right now, in this post. Save the post, and you'll see the PDF after refreshing
You may have not used the official media library. It will not work if you have uploaded PDF's with your FTP software for example
You may have a communication problem with Issuu, especially if you use a htaccess file to deny the access to your site

= How to use the shortocde ? =

Click to the media button, choose a PDF document and click on the Issuu PDF button to insert the basic shortcode

If you want to add params for a specific PDF, you can follow these examples:

<code>[pdf issuu_pdf_id="id_of_your_PDF" width="500" height="300"]</code>

In this example, we want to specify a width and a height only for this PDF
<code>[pdf issuu_pdf_id="id_of_your_PDF" layout="browsing" autoFlip="true" autoFlipTime="4000"]</code>

In this other example, we want to specify the browsing layout (one page presentation) and we want the PDF pages to autoflip each 4 seconds

You will see all available params inside the plugin option pannel

= I choose another skin but it doesn't work. why ?

As mentioned in the installation steps, you first need to copy the crossdomain.xml file located in the root folder of the plugin to the root folder of your website.

= How to customize buttons labels ? =

If you want to make some customisation in the Issuu player, you need to copy the 'issuu-skins' folder into your theme. Then, you'll be able to work on the skins.fla file or on the layout.xml file within your theme.

= How can I add a custom po/mo translation file =

Create your po translation file from the ips.pot and keep the previx (eg. ips-us_US.po)
Paste the po and mo file into your wp-content/languages/plugins/ folder

= What is the difference between the old and the new Issuu API? =

The new Issuu API has less customization params than the old one but it works on mobiles and tablets. The old API let you customize more options but works on desktop only.

= The PDF are not showing on mobile/tablet =

You need to enable the the embed API in the plugin's settings page

== Screenshots ==

1.	The config page
2.	The shortcode
3.	The Issuu Flipbook in a post
4.	The media edit view
5.	The Issuu button
6.	The shortcode generator


== Changelog ==

= 3.1.1 =
* Minor coding standards review
* Added some more security escaping checks
* Tested up to 4.7
* Added color picker for the old API parameters

= 3.1 =
* Fixed sync error when the PDF file name is to long
* Fixed conditional logic on template loading
* Added more detailed error messages on the manual PDF sync button
* Removed < WP 3.5 code
* Removed useless queries to Issuu
* Removed useless extra fields in the media edition screen and add them as static data information

= 3.0 =
* Added new Issuu embed code API. The old one is still available and will stay used by default if you update this plugin.
* Code refactoring.
* WordPress Core coding standards respected

= 2.2.8 =
*   Added the ability to load a mo (translation) file from wp-content/languages/plugins/ folder

= 2.2.7 =
*	Added some help inside the popin when no PDF synced

= 2.2.6 =
*	Added ES and RS translations thanks to Andrew Kurtis and Borisa Djuraskovic
*	Change is_admin condition to avoid errors when faking admin environment

= 2.2.5 =
*	Added support for percent width and height values in the shortcode.
*	Added POT file for translators.

= 2.2.4 =
*	Skipping a few version because version 2.2.2 and 2.2.3 were accidentally tagged
*	Added Russian translations by Konstantin Ryabenko

= 2.0.8 =
*	Fix bug in Firefox when default layout is chosen

= 2.0.7 =
*	?

= 2.0.6 =
*	Improved support for WordPress 3.5.1
*	Added Pronamic as contributor

= 2.0.5 =
*	Fix bug in pdf insertion
*	Fix notices in admin

= 2.0.4 =
*	Add new feature to choose custom skins

= 2.0.3 =
*	Add hook after shortcode display ( action : after-ips-shortcode)
*	Fix admin notice
	
= 2.0.2 =
*	Security update thanks to http://secu.boiteaweb.fr/
*	Remove useless file
	
= 2.0.1 =
*	Fix minor bug
	
= 2.0 =
*	Add a new popin feature in the TinyMCE editor. Allow to generate a shortcode with all the Issuu options.
*	Add the wmode transparent to the swf embed code
	
= 1.0.2 =
*	Apply a new filter
*	Clean useless js
	
= 1.0.1 =
*	Fix bug on shortcode
	
= 1.0 =
*	First release


== Upgrade Notice ==

= 2.2.4 =
*	This version contains the Russian translation files.

= 2.0.8 =
*	This version fixes a Firefox related bug. 


== Developers ==

*        php ~/wp/svn/i18n-tools/makepot.php wp-plugin ~/wp/git/issuu-pdf-sync ~/wp/git/issuu-pdf-sync/languages/ips.pot

