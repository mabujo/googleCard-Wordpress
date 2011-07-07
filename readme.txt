=== Plugin Name ===
Contributors: mabujo
Donate link: http://plusdevs.com/donate
Tags: google+, widget, sidebar, google, social
Requires at least: 3
Tested up to: 3.2
Stable tag: trunk,

A simple plugin that adds a google+ widget for linking to your google+ profile and showing your number of followers

== Description ==

Adds a widget to your blog that will display a link to your google+ profile so people can add you to a circle (follow you). It also displays your name, profile picture and the number of people who have you in circles.


The plugin uses caching to store your google+ profile data to eliminate checking google+ on every page load. 
For the caching to work, your web-server needs to be able to write to wp-content. (a lot of plugins require this so it should be fine).
If the plugin cannot cache it will still work, but I advise you to make sure caching is working. If caching is working you should see a file called plus_card.txt in /wp-content/cache/.


== Installation ==

1. Download googleCards.zip and unzip
2. Upload the unzipped googleCards folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the 'Widgets' menu in Wordpress and add the widget to your sidebar.
5. Choose a title for the widget and input your google+ id. (You can find your google+ id by going to your profile, it is the 21 digit number e.g. plus.google.com/YOUR_ID_IS_HERE).


== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)

== Changelog ==

= 0.1 =
* Initial release