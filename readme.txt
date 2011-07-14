=== Plugin Name ===
Contributors: mabujo
Donate link: http://plusdevs.com/donate
Tags: google+, widget, sidebar, google, social, google plus, +1, google +, google +1, stats
Requires at least: 3
Tested up to: 3.2
Stable tag: trunk,

A simple plugin that adds a google+ widget for linking to your google+ profile and showing your number of followers

== Description ==

googleCards is a google+ wordpress plugin.
It adds a widget to your blog that will display a link to your google+ profile so people can add you to a circle (follow you). It also displays your name, profile picture and the number of people who have you in circles.

The plugin uses caching to store your google plus profile data to eliminate checking google+ on every page load.
For the caching to work, your web-server needs to be able to write to wp-content. (a lot of plugins require this so it should be fine).
If the plugin cannot cache it will still work, but will store the data in the database instead. If caching is working you should see a file called plus_card.txt in /wp-content/cache/.
As of version 0.4, caching will also failback to using transients to store your scraped google plus data.


== Installation ==

1. Download googleCards.zip and unzip
2. Upload the unzipped googleCards folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the 'Widgets' menu in Wordpress and add the widget to your sidebar.
5. Choose a title for the widget and input your google+ id. (You can find your google+ id by going to your profile, it is the 21 digit number e.g. plus.google.com/YOUR_ID_IS_HERE).


== Screenshots ==

1. googleCards widget in the sidebar

== Changelog ==
= 0.4.3 =
* Added contribution from Joe Vaughan for using the Wordpress 3.0 widget API so widget can be used in multiple sidebars. Thanks Joe, chapeau. Also added the option to disable the developer credit if you're a really mean sort of person and removed the example Google+ ID.

= 0.4.2 =
* Forever alone - fix for bug when no one has the google+ account in a circle. 

= 0.4.1 =
* Test for safe_mode and open_basedir. Fixes curl_setopt() bug.

= 0.4 =
* Added file_get_contents as a backup for curl and use the transients API if we cant use a cache file. Tell curl not to verify https. Some minor css stuff.

= 0.3.1 =
* Some css fixes for people with big names and small sidebars.

= 0.3 =
* Fix for lowercase names in wordpress plugin directory

= 0.2 =
* Fixed some caching and css problems

= 0.1 =
* Initial release

== Upgrade Notice ==

= 0.4 =
googleCards no longer requires a cache file to work and should be much more reliable in fetching your data from google+. If you were having problems before please upgrade and try this new version.