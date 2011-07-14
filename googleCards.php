<?php
/*
Plugin Name: googleCards
Plugin URI: http://plusdevs.com/google-wordpress-plugin/
Description: Adds google+ contact card widget to your blog
Version: 0.4.3
Author: Mabujo
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
define( 'GOOGLECARD_CURRENT_VERSION', '0.4.3' );
define( 'GOOGLECARD_DEBUG', false);


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

function googleCards($plus_id, $credit=1)
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

	if (isset($data) && !empty($data['name']) && !empty($data['img']))
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
				<p>In <?php if (isset($data['count'])) { echo $data['count']; } else { echo 0; } ?> people's circles</p>
			</div>
			<?php 
				if (isset($credit) && $credit > 0) 
				{
			?>
			<div id="plusCardCredit">
				<p>Google+ card by <a href="http://plusdevs.com">plusdevs</a></p>
			</div>
			<span id="plusCardShowHide" onclick="togglePlusCredit()">+i</span>
			<?php
				}
			?>
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


class GoogleCardsWidget extends WP_Widget {
	/** constructor */
	function GoogleCardsWidget() {
		parent::WP_Widget(false, $name = 'GoogleCard');
		$css = '/wp-content/plugins/googlecards/css/googleCards.css';
		wp_enqueue_style('googleCards', $css);
		$js = '/wp-content/plugins/googlecards/js/googleCards.min.js';
		wp_enqueue_script('googleCards', $js);
	}

	/** @see WP_Widget::widget */
	function widget($args, $instance) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		?>
			<?php echo $before_widget; ?>
					<?php if ( $title )
							echo $before_title . $title . $after_title; ?>
				<?php googleCards($instance['plus_id'], $instance['credit']); ?>
			<?php echo $after_widget; ?>
		<?php
	}

	/** @see WP_Widget::update */
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['plus_id'] = strip_tags($new_instance['plus_id']);
		$instance['credit'] = ( isset( $new_instance['credit'] ) ? 1 : 0 );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form($instance) {
		$title = 'Follow me on Google+';
		$plus_id = '';
		$credit = true;
		
		if ($instance) {
			$title = esc_attr($instance['title']);
			$plus_id = esc_attr($instance['plus_id']);
			$credit = isset($instance['credit']) ? $instance['credit'] : true;
		}
		else
		{
			$defaults = array('title' => 'Follow me on Google+', 'plus_id' => '', 'credit' => 'true');
			$instance = wp_parse_args( (array) $instance, $defaults );
		}

		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('plus_id'); ?>"><?php _e('Google Plus ID:'); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id('plus_id'); ?>" name="<?php echo $this->get_field_name('plus_id'); ?>" type="text" value="<?php echo $plus_id; ?>" />
		</p>
		<p>
			<?php (isset($instance['credit']) && $instance['credit'] == true) ? $color = 'green' : $color = 'red'; ?>

			<input class="checkbox" type="checkbox" <?php checked( (bool) $instance['credit'], true ); ?> id="<?php echo $this->get_field_id( 'credit' ); ?>" name="<?php echo $this->get_field_name( 'credit' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'credit' ); ?>"><?php _e('Show developer credit '); ?><abbr style="border-bottom: 1px dotted black; color:<?php echo $color;?>; font-weight:bold;" title="Whether to show the 'Google+ card by plusdevs' message when a user clicks on '+i'. You don't have to leave this enabled but it is the best way for other people to find out about this plugin (which we've worked very hard on!) and we promise to love you forever if you do. The message is inobtrusive, your circles are always shown.">?</abbr></label>
		</p>
		<?php 
	}

} // class GoogleCardsWidget

// register GoogleCardsWidget widget
add_action('widgets_init', create_function('', 'return register_widget("GoogleCardsWidget");'));

?>