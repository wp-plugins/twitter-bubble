<?php
/*
Plugin Name: Twitter Bubble
Plugin URI: http://mfd-consult.dk/twitter-bubble/
Description: A sidebar widget to display Twitter updates in a nice talk bubble using the Javascript <a href="http://twitter.com/badges/which_badge">Twitter 'badge'</a>.
Version: 1.0
Author: Morten Høybye Frederiksen
Author URI: http://www.wasab.dk/morten/
License: GPL
*/

function twitter_bubble_init() {

	if ( !function_exists('register_sidebar_widget') )
		return;

	function twitter_bubble($args) {
		// "$args is an array of strings that help widgets to conform to
		// the active theme: before_widget, before_title, after_widget,
		// and after_title are the array keys." - These are set up by the theme
		extract($args);

		// These are our own options
		$options = get_option('twitter_bubble');
		$account = $options['account'];
		$prefix = $options['prefix'];
		
		// Smart prefix linking.
		$prefix = str_replace($options['account'], 
		    '<a href="' . esc_attr('http://twitter.com/' . $options['account']) . '">' . $options['account'] . '</a>', 
		    $prefix);

		// Output
		echo $before_widget;
		echo '
<div id="twitter_bubble_widget">
	<span id="twitter_bubble_prefix">' . $prefix . '</span>
	<div id="twitter_bubble_container">
		<ul id="twitter_update_list">
			<li><span id="load">Loading...</span></li>
		</ul>
	</div>
</div>';
		echo $after_widget;
	}

	// Settings form
	function twitter_bubble_control() {
		// Get options
		$options = get_option('twitter_bubble');
		// options exist? if not set defaults
		if ( !is_array($options) )
			$options = array('account'=>'mortenf', 'prefix'=>'mortenf tweets: ');

		// form posted?
		if ( $_POST['twitter_bubble-submit'] ) {
			// Remember to sanitize and format use input appropriately.
			$options['account'] = strip_tags(stripslashes($_POST['twitter_bubble-account']));
			$options['prefix'] = strip_tags(stripslashes($_POST['twitter_bubble-prefix']));
			update_option('twitter_bubble', $options);
		}

		// Get options for form fields to show
		$account = htmlspecialchars($options['account'], ENT_QUOTES);
		$prefix = htmlspecialchars($options['prefix'], ENT_QUOTES);

		// The form fields
		echo '<p style="text-align:right;">
				<label for="twitter_bubble-account">' . __('Account:') . '
				<input style="width: 200px;" id="twitter_bubble-account" name="twitter_bubble-account" type="text" value="'.$account.'" />
				</label></p>';
		echo '<p style="text-align:right;">
				<label for="twitter_bubble-prefix">' . __('Prefix:') . '
				<input style="width: 200px;" id="twitter_bubble-prefix" name="twitter_bubble-prefix" type="text" value="'.$prefix.'" />
				</label></p>';
		echo '<input type="hidden" id="twitter_bubble-submit" name="twitter_bubble-submit" value="1" />';
	}

	// Plugin URL
	function twitter_bubble_url() {
		$path = substr(__FILE__, 1);
		while (strpos($path, '/') && !file_exists(ABSPATH . PLUGINDIR . '/' . $path))
			$path = preg_replace('|^[^/]+/+|', '', $path);
		return get_option('siteurl') . '/' . PLUGINDIR . '/' . dirname($path);
	}

	// CSS in header
	function twitter_bubble_head() {
		if (!is_active_widget('twitter_bubble'))
		  return;
		// Output CSS
		$url = twitter_bubble_url();
		echo '<style type="text/css">
.twitter_bubble { list-style: none; padding-top: 10px !important; }
#twitter_bubble_widget { height: 147px; width: 100%; background: url(' . $url . '/twitter_bg_left.png) top left no-repeat; position: relative; }
#twitter_bubble_prefix { position: absolute; top: -8px; left: 0px; font-size: 80%; }
#twitter_bubble_container { height: 147px; width: 100%; background: url(' . $url . '/twitter_bg_right.png) top right no-repeat; display: block; position: absolute; right: 0; }
#twitter_bubble_container ul { height: 147px; background: url(' . $url . '/twitter_bg_center.png) top left repeat-x; margin: 0 44px 0 130px; }
#twitter_bubble_container ul li { height: 72px; background-color: white; border: 2px solid #888888; display: block; position: relative; top: 23px; margin-left: 10px; padding: 2px 8px 4px; font-size: 125%; }
#twitter_bubble_container ul li a { display: block; position: absolute; top: 80px; font-size: 50% !important; }
#twitter_bubble_container ul li span a { display: inline; position: relative; top: 0; font-size: 100% !important; }
#twitter_bubble_container ul li #load { height: 72px; background: url(' . $url . '/loader.gif) no-repeat 50% 50%; display: block; color: white; }
</style>';
	}

	// JS in footer
	function twitter_bubble_footer() {
		if (!is_active_widget('twitter_bubble'))
		  return;
		// Get options
		$options = get_option('twitter_bubble');
		$account = $options['account'];
		// Output JS
		$url = twitter_bubble_url();
		echo '<script type="text/javascript" src="' . $url . '/twitter-bubble.js"> </script>';
		echo '<script type="text/javascript" src="http://twitter.com/statuses/user_timeline/'.$account.'.json?callback=twitterCallback2&amp;count=1"> </script>';
	}

	// Register widget for use
	register_sidebar_widget(array('Twitter Bubble', 'widgets'), 'twitter_bubble');

	// Register settings for use, 300x200 pixel form
	register_widget_control(array('Twitter Bubble', 'widgets'), 'twitter_bubble_control', 300, 200);

	// Register header call for CSS
	add_action('wp_head', 'twitter_bubble_head');

	// Register footer call for JS
	add_action('wp_footer', 'twitter_bubble_footer');
}

// Run code and init
add_action('widgets_init', 'twitter_bubble_init');

// EOF