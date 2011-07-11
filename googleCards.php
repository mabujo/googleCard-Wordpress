<?php
/*
Plugin Name: googleCards
Plugin URI: http://plusdevs.com/google-wordpress-plugin/
Description: Adds google+ contact card widget to your blog
Version: 0.4
Author: Mabujo, john@mabujo.com
Author URI: http://plusdevs.com
License: GPL3
*/

/*  
* 	Copyright (C) 2011  Mabujo
*	http://plusdevs.com
*	http://plusdevs.com/googlecard-googleplus-php-scraper/
*
*	This program is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation, either version 3 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License
*	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define( 'GOOGLECARD_PLUGIN_NAME', 'googleCards');
define( 'GOOGLECARD_PLUGIN_DIRECTORY', 'googlecards');
define( 'GOOGLECARD_CURRENT_VERSION', '0.4' );
define( 'GOOGLECARD_DEBUG', false);

function googleCards($plus_id)
{
	// include our scraper class
	include_once('googleCardClass.php');

	// initiate an instance of our scraper class
	$plus = new googleCard($plus_id);

	// if we can use file caching
	if (gc_caching())
	{
		$plus->cache_data = 1;
		$plus->cache_file = WP_CONTENT_DIR . "/cache/plus_cards.txt";
	}

	// do the scrape
	$data = $plus->googleCard();

	if (isset($data) && !empty($data['name']) && !empty($data['count']) && !empty($data['img']))
	{
		?>
		<div id="plus_card">
			<div id="plus_card_image">
				<a href="<?php echo $data['url']; ?>">
					<?php echo '<img src="' . $data['img'] . '" width="80" height="80" />'; ?>
				</a>
			</div>
			<div id="plus_card_name">
				<a href="<?php echo $data['url']; ?>"><?php echo $data['name'] ?></a>
			</div>
			<span id="plus_card_add">
				<a href="<?php echo $data['url']; ?>">Add to circles</a>
			</span>
			<div id="plusCardCount">
				<p>In <?php echo $data['count']; ?> people's circles</p>
			</div>
			<div id="plusCardCredit">
				<p>Google+ card by <a href="http://plusdevs.com">plusdevs</a></p>
			</div>
			<span id="plusCardShowHide" onclick="togglePlusCredit()">+i</span>
			<?php echo '<!--gcVersion = ' . GOOGLECARD_CURRENT_VERSION . ' -->'; ?>
		</div>
	<?php
	}
	// else show an error
	else
	{
		echo 'Couldn\'t get data from google+';
		echo '<!--gcVersion = ' . GOOGLECARD_CURRENT_VERSION . ' -->';
	}
}

// display the widget
function widget_googleCards($args) 
{
	extract($args);
	
	$options = get_option("widget_googleCards");
	if (!is_array( $options ))
	{
		$options = array(
			'title' => 'Follow me on Google+',
			'plus_id' => '106189723444098348646'
			);
	}

	echo $before_widget;
	echo $before_title;
	echo $options['title'];
	echo $after_title;
	googleCards($options['plus_id']);
	echo $after_widget;
}

// for widget options
function googleCards_control()
{
	$options = get_option("widget_googleCards");
	if (!is_array( $options ))
	{
		$options = array(
			'title' => 'Follow me on google+',
			'plus_id' => '106189723444098348646'
			);
	}

	if ($_POST['googleCards-Submit'])
	{
		$options['title'] = htmlspecialchars($_POST['googleCards-WidgetTitle']);
		$options['plus_id'] = htmlspecialchars($_POST['googleCards-plusId']);
		update_option("widget_googleCards", $options);
	}

	?>
	<p>
		<label for="googleCards-WidgetTitle">Title: </label>
		<br />
		<input type="text" id="googleCards-WidgetTitle" name="googleCards-WidgetTitle" value="<?php echo $options['title'];?>" />
		<br /><br />
		<label for="googleCards-plusId">Google+ id: </label>
		<br />
		<input type="text" id="googleCards-plusId" name="googleCards-plusId" value="<?php echo $options['plus_id'];?>" />
		<br />
		<input type="hidden" id="googleCards-Submit" name="googleCards-Submit" value="1" />
	</p>
	<?php
}

// test whether we can write to the cache directory or not
function gc_caching()
{
	if (!is_dir(WP_CONTENT_DIR . "/cache"))
	{
		mkdir (WP_CONTENT_DIR . "/cache", 0777, true);
	}
	if (is_dir(WP_CONTENT_DIR . "/cache") && is_writable(WP_CONTENT_DIR . "/cache"))
	{
		$cache = WP_CONTENT_DIR . "/cache/plus_cards.txt";
		return true;
	}
	else
	{
		return false;
	}
}

function googleCards_init()
{
	register_sidebar_widget(__('googleCards'), 'widget_googleCards');
	register_widget_control('googleCards', 'googleCards_control', '', '' );
	$css = '/wp-content/plugins/googlecards/css/googleCards.css';
	wp_enqueue_style('googleCards', $css);
	$js = '/wp-content/plugins/googlecards/js/googleCards.min.js';
	wp_enqueue_script('googleCards', $js);
}

add_action("plugins_loaded", "googleCards_init");
?>