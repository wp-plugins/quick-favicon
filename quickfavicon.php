<?php
/*
Plugin Name: Quick Favicon
Description: Quick Favicon makes it easy to set up icons for your WordPress site. Favicons! iOS Icons! Android Icons! Windows 8.x Tiles! And more! If you have any issues at all, please <a href="http://pluginspired.com/support/">create a support ticket</a>.
Version: 0.22.8
Author: PlugInspired
Author URI: http://pluginspired.com/
License: GPL2

Copyright 2015  Robert Cummings  (email : robertcummings@live.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Include WordPress image scripts so we can use wp_generate_attachment_metadata() */
require_once( ABSPATH . 'wp-admin/includes/image.php' );

/**
 * Add a dashboard page and menu item and initialize settings registration
 *
 * @return void
 */
function quickfavicon_create_menu() {
	add_theme_page( 'Icons', 'Icons', 'administrator', 'icon_settings', 'quickfavicon_settings_page' );
	add_action( 'admin_init', 'quickfavicon_settings' );
}
add_action( 'admin_menu', 'quickfavicon_create_menu' );

function quickfavicon_add_action_links ( $actions, $plugin_file ) {
	static $plugin;
	if (!isset($plugin))
		$plugin = plugin_basename(__FILE__);
	if ($plugin == $plugin_file) {
		$donate_action = array('donate' => '<a href="//pluginspired.com/donate/" title="You can donate using your credit card through PayPal without having an account. We also have a Bitcoin wallet." target="_blank">Make a Donation</a>');
		$rate_action = array('rate' => '<a href="https://wordpress.org/support/view/plugin-reviews/quick-favicon?filter=5#postform" target="_blank" title="Leave Quick Favicon a 5-Star rating on WordPress.org!" data-rated="Thanks :)">★★★★★</a>');
		$settings_action = array('settings' => '<a href="' . admin_url( 'themes.php?page=icon_settings' ) . '" title="Configure your site\'s Icons. You can also find this link under the Appearance menu. It\'s labeled \'Icons\'.">Icon Settings</a>');
		$actions = array_merge($donate_action, $actions);
		$actions = array_merge($rate_action, $actions);
		$actions = array_merge($settings_action, $actions);
	}
	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'quickfavicon_add_action_links', 10, 5 );

/**
 * Output for all front-end icons
 *
 * @return void
 */
function quickfavicon_frontend_output() {
	quickfavicon_favicon_frontend_output();
	quickfavicon_ios_output();
	quickfavicon_android_output();
	quickfavicon_windows_output();
}
add_action( 'wp_head', 'quickfavicon_frontend_output' );

/**
 * Output for all back-end icons
 *
 * @return void
 */
function quickfavicon_backend_output() {
	quickfavicon_favicon_backend_output();
}
add_action( 'admin_head', 'quickfavicon_backend_output' );

// Add filters
add_filter( 'update_option_quickfavicon_frontend_icon_id', 			'quickfavicon_make_frontend_icons', 			10, 2); // Make new frontend favicons when the source icon gets replaced
add_filter( 'update_option_quickfavicon_backend_icon_id', 			'quickfavicon_make_backend_icons', 				10, 2); // Make new backend favicons when the source icon gets replaced
add_filter( 'update_option_quickfavicon_ios_icon_id', 					'quickfavicon_make_ios_icons', 						10, 2); // Make new ios icons when the source icon gets replaced
add_filter( 'update_option_quickfavicon_ios_icon_bg', 					'quickfavicon_make_ios_icons', 						10, 2); // Make new ios icons when the background color gets changed
add_filter( 'update_option_quickfavicon_android_icon_id', 			'quickfavicon_make_android_icons', 				10, 2); // Make new android icons when the source icon gets replaced
add_filter( 'update_option_quickfavicon_android_icon_bg', 			'quickfavicon_make_android_icons', 				10, 2); // Make new android icons when the background color gets changed
add_filter( 'update_option_quickfavicon_android_icon_app_name',	'quickfavicon_android_update_manifest', 	10, 2); // Make new android manifest when the app_name gets changed
add_filter( 'update_option_quickfavicon_windows_icon_id', 			'quickfavicon_make_windows_icons', 				10, 2); // Make new windows icons when the source icon gets replaced
add_filter( 'update_option_quickfavicon_windows_tile_color', 		'quickfavicon_windows_update_manifest', 	10, 2); // Make new windows manifest when the source icon gets replaced
add_filter( 'update_option_quickfavicon_windows_icon_style', 		'quickfavicon_make_windows_icons', 				10, 2); // Make new windows icons when the icon style gets changed

/**
 * Register the settings
 *
 * @return void
 */
function quickfavicon_settings() {
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_last_tab_used' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_frontend_icon_id' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_backend_icon_id' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_ios_icon_id' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_ios_icon_bg' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_ios_icon_bg_radio' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_android_icon_id' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_android_icon_bg' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_android_icon_bg_radio' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_android_icon_theme_color' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_android_icon_app_name' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_windows_icon_id' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_windows_icon_style' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_windows_tile_color' );
	register_setting( 'quickfavicon-settings-group', 			'quickfavicon_windows_tile_color_radio' );
	register_setting( 'quickfavicon-output-group',				'quickfavicon_android_icon_manifest' );
	register_setting( 'quickfavicon-output-group', 				'quickfavicon_windows_icon_manifest' );
	register_setting( 'quickfavicon-browser-icons-group', 'quickfavicon_frontend_icon_96x96_id' );
	register_setting( 'quickfavicon-browser-icons-group', 'quickfavicon_frontend_icon_32x32_id' );
	register_setting( 'quickfavicon-browser-icons-group', 'quickfavicon_frontend_icon_16x16_id' );
	register_setting( 'quickfavicon-browser-icons-group', 'quickfavicon_backend_icon_96x96_id' );
	register_setting( 'quickfavicon-browser-icons-group', 'quickfavicon_backend_icon_32x32_id' );
	register_setting( 'quickfavicon-browser-icons-group', 'quickfavicon_backend_icon_16x16_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_180x180_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_152x152_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_144x144_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_120x120_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_114x114_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_76x76_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_72x72_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_60x60_id' );
	register_setting( 'quickfavicon-ios-icons-group', 		'quickfavicon_ios_icon_57x57_id' );
	register_setting( 'quickfavicon-android-icons-group', 'quickfavicon_android_icon_192x192_id' );
	register_setting( 'quickfavicon-android-icons-group', 'quickfavicon_android_icon_144x144_id' );
	register_setting( 'quickfavicon-android-icons-group', 'quickfavicon_android_icon_96x96_id' );
	register_setting( 'quickfavicon-android-icons-group', 'quickfavicon_android_icon_72x72_id' );
	register_setting( 'quickfavicon-android-icons-group', 'quickfavicon_android_icon_48x48_id' );
	register_setting( 'quickfavicon-android-icons-group', 'quickfavicon_android_icon_36x36_id' );
	register_setting( 'quickfavicon-windows-icons-group', 'quickfavicon_windows_icon_310x150_id' );
	register_setting( 'quickfavicon-windows-icons-group', 'quickfavicon_windows_icon_310x310_id' );
	register_setting( 'quickfavicon-windows-icons-group', 'quickfavicon_windows_icon_150x150_id' );
	register_setting( 'quickfavicon-windows-icons-group', 'quickfavicon_windows_icon_144x144_id' );
	register_setting( 'quickfavicon-windows-icons-group', 'quickfavicon_windows_icon_70x70_id' );
}

/**
 * Setting page Scripts and Styles
 */
function quickfavicon_admin_enqueue() {
	wp_enqueue_script( 	'quick-favicon-bootstrap-js', 							plugins_url( 'js/bootstrap.min.js', __FILE__ ), 							array( 'jquery' ), 																																					'3.3.4', true);
	wp_enqueue_script( 	'quick-favicon-bootstrap-colorpicker-js', 	plugins_url( 'js/bootstrap-colorpicker.min.js', __FILE__ ), 	array( 'jquery', 'quick-favicon-bootstrap-js' ), 																						'2.1', true);
	wp_enqueue_script( 	'quick-favicon', 														plugins_url( 'js/quickfavicon.min.js', __FILE__ ), 						array( 'jquery', 'quick-favicon-bootstrap-js', 'quick-favicon-bootstrap-colorpicker-js' ), 	'0.22.4', true);
	wp_enqueue_style( 	'quick-favicon-bootstrap-css', 							plugins_url( 'css/bootstrap.min.css', __FILE__ ), 						array(), 																																										'3.3.4' );
	wp_enqueue_style( 	'quick-favicon-bootstrap-colorpicker-css', 	plugins_url( 'css/bootstrap-colorpicker.min.css', __FILE__ ), array( 'quick-favicon-bootstrap-css' ), 																										'2.1' );
	wp_enqueue_style( 	'quick-favicon-css', 												plugins_url( 'css/quickfavicon.min.css', __FILE__ ), 					array( 'quick-favicon-bootstrap-css' ), 																										'0.22.4' );

	// Set up localization for options so we can use them in our JS and jQuery
	wp_localize_script(
		'quick-favicon',
		'quick_favicon',
		array(
			'quickfavicon_ios_icon_bg' 				=> quickfavicon_get_the_ios_icon_bg(),
			'quickfavicon_android_icon_bg' 		=> quickfavicon_get_the_android_icon_bg(),
			'quickfavicon_windows_icon_style' => quickfavicon_get_the_windows_icon_style(),
			'quickfavicon_windows_tile_color' => quickfavicon_get_the_windows_tile_color()
		)
	);
}
// If plugin setting page is being displayed, enqueue scripts and styles
if (isset($_GET['page']) && $_GET['page'] == 'icon_settings' ) {
add_action( 'admin_print_scripts', 'quickfavicon_admin_enqueue' );
}

/**
 * Return ios chosen background color
 *
 * @return string
 */
function quickfavicon_get_the_ios_icon_bg() {
	return get_option( 'quickfavicon_ios_icon_bg' );
}

/**
 * Return android chosen background color
 *
 * @return string
 */
function quickfavicon_get_the_android_icon_bg() {
	return get_option( 'quickfavicon_android_icon_bg' );
}

/**
 * Return windows chosen tile color
 *
 * @return string
 */
function quickfavicon_get_the_windows_tile_color() {
	return get_option( 'quickfavicon_windows_tile_color' );
}

/**
 * Return windows chosen icon style
 *
 * @return string
 */
function quickfavicon_get_the_windows_icon_style() {
	return get_option( 'quickfavicon_windows_icon_style' );
}

/**
 * Delete frontend favicons
 *
 * @return void
 */
function quickfavicon_delete_frontend_icons() {
	wp_delete_attachment( get_option( 'quickfavicon_frontend_icon_96x96_id' ), true);
	update_option( 'quickfavicon_frontend_icon_96x96_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_frontend_icon_32x32_id' ), true);
	update_option( 'quickfavicon_frontend_icon_32x32_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_frontend_icon_16x16_id' ), true);
	update_option( 'quickfavicon_frontend_icon_16x16_id', '0' );
}

/**
 * Delete backend favicons
 *
 * @return void
 */
function quickfavicon_delete_backend_icons() {
	wp_delete_attachment( get_option( 'quickfavicon_backend_icon_96x96_id' ), true);
	update_option( 'quickfavicon_backend_icon_96x96_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_backend_icon_32x32_id' ), true);
	update_option( 'quickfavicon_backend_icon_32x32_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_backend_icon_16x16_id' ), true);
	update_option( 'quickfavicon_backend_icon_16x16_id', '0' );
}

/**
 * Delete ios favicons
 *
 * @return void
 */
function quickfavicon_delete_ios_icons() {
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_180x180_id' ), true);
	update_option( 'quickfavicon_ios_icon_180x180_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_152x152_id' ), true);
	update_option( 'quickfavicon_ios_icon_152x152_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_144x144_id' ), true);
	update_option( 'quickfavicon_ios_icon_144x144_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_120x120_id' ), true);
	update_option( 'quickfavicon_ios_icon_120x120_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_114x114_id' ), true);
	update_option( 'quickfavicon_ios_icon_114x114_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_76x76_id' ), true);
	update_option( 'quickfavicon_ios_icon_76x76_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_72x72_id' ), true);
	update_option( 'quickfavicon_ios_icon_72x72_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_60x60_id' ), true);
	update_option( 'quickfavicon_ios_icon_60x60_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_ios_icon_57x57_id' ), true);
	update_option( 'quickfavicon_ios_icon_57x57_id', '0' );
}

/**
 * Delete android favicons
 *
 * @return void
 */
function quickfavicon_delete_android_icons() {
	wp_delete_attachment( get_option( 'quickfavicon_android_icon_192x192_id' ), true);
	update_option( 'quickfavicon_android_icon_192x192_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_android_icon_144x144_id' ), true);
	update_option( 'quickfavicon_android_icon_144x144_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_android_icon_96x96_id' ), true);
	update_option( 'quickfavicon_android_icon_96x96_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_android_icon_72x72_id' ), true);
	update_option( 'quickfavicon_android_icon_72x72_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_android_icon_48x48_id' ), true);
	update_option( 'quickfavicon_android_icon_48x48_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_android_icon_36x36_id' ), true);
	update_option( 'quickfavicon_android_icon_36x36_id', '0' );

	quickfavicon_android_update_manifest();
}

/**
 * Delete windows favicons
 *
 * @return void
 */
function quickfavicon_delete_windows_icons() {
	wp_delete_attachment( get_option( 'quickfavicon_windows_icon_310x310_id' ), true);
	update_option( 'quickfavicon_windows_icon_310x310_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_windows_icon_310x150_id' ), true);
	update_option( 'quickfavicon_windows_icon_310x150_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_windows_icon_150x150_id' ), true);
	update_option( 'quickfavicon_windows_icon_150x150_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_windows_icon_144x144_id' ), true);
	update_option( 'quickfavicon_windows_icon_144x144_id', '0' );
	wp_delete_attachment( get_option( 'quickfavicon_windows_icon_70x70_id' ), true);
	update_option( 'quickfavicon_windows_icon_70x70_id', '0' );

	quickfavicon_windows_update_manifest();
}

/**
 * Convert hex color string to RGB values
 *
 * @return string|array
 */
function quickfavicon_hex_to_rgb($hexStr, $returnAsString = false, $seperator = ', ' ) {
  $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
  $rgbArray = array();
  if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
    $colorVal = hexdec($hexStr);
    $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
    $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
    $rgbArray['blue'] = 0xFF & $colorVal;
  }
	elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
    $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2) );
    $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2) );
    $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2) );
  }
	else {
    return false; //Invalid hex color code
  }
  return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
}

/**
 * Image manipulation class
 */
class quickfavicon_image {
	var $image;
	var $image_type;

	/**
	 * Load image file
	 *
	 * @return void
	 */
	function load($filename) {
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];

		if( $this->image_type == IMAGETYPE_JPEG ) {
			$this->image = imagecreatefromjpeg($filename);
		}
		elseif( $this->image_type == IMAGETYPE_GIF ) {
			$this->image = imagecreatefromgif($filename);
		}
		elseif( $this->image_type == IMAGETYPE_PNG ) {
			$this->image = imagecreatefrompng($filename);
		}
	}

	/**
	 * Save image file
	 *
	 * @return void
	 */
	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename,$compression);
		}
		elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image,$filename);
		}
		elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image,$filename);
		}
		if( $permissions != null) {
			chmod($filename,$permissions);
		}
	}

	/**
	 * Output image file
	 *
	 * @return void
	 */
	function output($image_type=IMAGETYPE_JPEG) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image);
		}
		elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image);
		}
		elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image);
		}
	}

	/**
	 * Get image width
	 *
	 * @return void
	 */
	function getWidth() {
		return imagesx($this->image);
	}

	/**
	 * Get image height
	 *
	 * @return void
	 */
	function getHeight() {
		return imagesy($this->image);
	}

	/**
	 * Resize image
	 *
	 * @return void
	 */
  function resize($width,$height,$rgb,$transparency) {
  	$new_image = imagecreatetruecolor($width, $height);
  	if( $this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG ) {
  		$current_transparent = imagecolortransparent($this->image);
  		if($current_transparent != -1) {
  			$transparent_color = imagecolorsforindex($this->image, $current_transparent);
  			$current_transparent = imagecolorallocate($new_image, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
  			imagefill($new_image, 0, 0, $current_transparent);
  			imagecolortransparent($new_image, $current_transparent);
  		} elseif( $this->image_type == IMAGETYPE_PNG) {
  			imagealphablending($new_image, true);
  			$color = imagecolorallocatealpha($new_image, $rgb['red'], $rgb['green'], $rgb['blue'], ($transparency)?127:0);
  			imagefill($new_image, 0, 0, $color);
  			imagesavealpha($new_image, true);
  		}
  	}
  	imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight() );
  	$this->image = $new_image;
  }

	/**
	 * Mask image
	 *
	 * @return void
	 */
	function mask($width, $height) {
  	$new_image = imagecreatetruecolor($width, $height);
		$current_transparent = imagecolortransparent($this->image);
		$transparent_color = imagecolorsforindex($this->image, $current_transparent);
		imagefill($new_image, 0, 0, $current_transparent);

		imagealphablending($new_image, true);

    // Work through pixels
    for($y=0;$y<$height;$y++) {
        for($x=0;$x<$width;$x++) {
					// Apply new color + Alpha
					$rgb = imagecolorsforindex($new_image, imagecolorat($this->image, $x, $y));

					$pixelColor = imagecolorallocatealpha($new_image, 255, 255, 255, $rgb['alpha']);
					imagesetpixel ($new_image, $x, $y, $pixelColor);
        }
    }
		imagesavealpha($new_image, true);
  	$this->image = $new_image;
	}

	/**
	 * Resize image canvas
	 *
	 * @return void
	 */
	function sizecanvas($width, $height, $voffset, $hoffset) {
		$new_image = imagecreatetruecolor($width, $height);
		$current_transparent = imagecolortransparent($this->image);
		$transparent_color = imagecolorsforindex($this->image, $current_transparent);
		imagesavealpha($new_image, true);
		imagealphablending($new_image, true);
		imagefill($new_image, 0, 0, $current_transparent);
  	imagecopyresampled($new_image, $this->image, $hoffset, $voffset, 0, 0, $width, $height, $width, $height );
		imagefill($new_image, $width-1, $height-1, $current_transparent);
  	$this->image = $new_image;
	}

}

/**
 * Create frontend favicons
 *
 * @return void
 */
function quickfavicon_make_frontend_icons() {

	// Get the upload directory
	$upload_dir = wp_upload_dir();

	// Get the icon attachment ID
	$icon_id = get_option( 'quickfavicon_frontend_icon_id' );

	// Delete old apple touch icons
	quickfavicon_delete_frontend_icons();

	if ($icon_id != '' ) {

		// Get the apple touch icon attachment
		$icon_image = get_attached_file($icon_id, false);

		$transparency = true;
		$icon_bg_rgb_array = array('red' => 0, 'green' => 0, 'blue' => 0);

		// 96 x 96
		$filename = $upload_dir['path'].'/favicon-96x96.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(96,96,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'favicon-96x96', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_frontend_icon_96x96_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 32 x 32
		$filename = $upload_dir['path'].'/favicon-32x32.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(32,32,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'favicon-32x32', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_frontend_icon_32x32_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 16 x 16
		$filename = $upload_dir['path'].'/favicon-16x16.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(16,16,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'favicon-16x16', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_frontend_icon_16x16_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );
	}
}

/**
 * Create backend favicons
 *
 * @return void
 */
function quickfavicon_make_backend_icons() {

	// Get the upload directory
	$upload_dir = wp_upload_dir();

	// Get the icon attachment ID
	$icon_id = get_option( 'quickfavicon_backend_icon_id' );

	// Delete old apple touch icons
	quickfavicon_delete_backend_icons();

	if ($icon_id != '' ) {

		// Get the apple touch icon attachment
		$icon_image = get_attached_file($icon_id, false);

		$transparency = true;
		$icon_bg_rgb_array = array('red' => 0, 'green' => 0, 'blue' => 0);

		// 96 x 96
		$filename = $upload_dir['path'].'/dashboard-favicon-96x96.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(96,96,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'dashboard-favicon-96x96', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_backend_icon_96x96_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 32 x 32
		$filename = $upload_dir['path'].'/dashboard-favicon-32x32.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(32,32,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'dashboard-favicon-32x32', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_backend_icon_32x32_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 16 x 16
		$filename = $upload_dir['path'].'/dashboard-favicon-16x16.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(16,16,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'dashboard-favicon-16x16', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_backend_icon_16x16_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );
	}
}

/**
 * Create ios favicons
 *
 * @return void
 */
function quickfavicon_make_ios_icons() {

	// Get the upload directory
	$upload_dir = wp_upload_dir();

	// Get the icon attachment ID
	$icon_id = get_option( 'quickfavicon_ios_icon_id' );

	// Delete old apple touch icons
	quickfavicon_delete_ios_icons();

	if ($icon_id != '' ) {

		// Get the apple touch icon attachment
		$icon_image = get_attached_file($icon_id, false);

		$transparency = false;

		$icon_bg_color = get_option( 'quickfavicon_ios_icon_bg' );

		$icon_bg_rgb_array = quickfavicon_hex_to_rgb(str_replace("#", "", $icon_bg_color) );

		// 180 x 180
		$filename = $upload_dir['path'].'/apple-touch-icon-180x180.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(180,180,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-180x180', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_180x180_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 152 x 152
		$filename = $upload_dir['path'].'/apple-touch-icon-152x152.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(152,152,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-152x152', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_152x152_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 144 x 144
		$filename = $upload_dir['path'].'/apple-touch-icon-144x144.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(144,144,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-144x144', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_144x144_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 120 x 120
		$filename = $upload_dir['path'].'/apple-touch-icon-120x120.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(120,120,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-120x120', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_120x120_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 114 x 114
		$filename = $upload_dir['path'].'/apple-touch-icon-114x114.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(114,114,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-114x114', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_114x114_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 76 x 76
		$filename = $upload_dir['path'].'/apple-touch-icon-76x76.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(76,76,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-76x76', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_76x76_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 72 x 72
		$filename = $upload_dir['path'].'/apple-touch-icon-72x72.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(72,72,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-72x72', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_72x72_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 60 x 60
		$filename = $upload_dir['path'].'/apple-touch-icon-60x60.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(60,60,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-60x60', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_60x60_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 57 x 57
		$filename = $upload_dir['path'].'/apple-touch-icon-57x57.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(57,57,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'apple-touch-icon-57x57', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_ios_icon_57x57_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );
	}
}

/**
 * Create android favicons
 *
 * @return void
 */
function quickfavicon_make_android_icons() {

	// Get the upload directory
	$upload_dir = wp_upload_dir();

	// Get the icon attachment ID
	$icon_id = get_option( 'quickfavicon_android_icon_id' );

	// Delete old apple touch icons
	quickfavicon_delete_android_icons();

	if ($icon_id != '' ) {

		// Get the apple touch icon attachment
		$icon_image = get_attached_file($icon_id, false);

		$transparency = false;
		$icon_bg_rgb_array = array('red' => 0, 'green' => 0, 'blue' => 0);

		$icon_bg_color = get_option( 'quickfavicon_android_icon_bg' );

		if ($icon_bg_color != '' ) :
			$icon_bg_rgb_array = quickfavicon_hex_to_rgb(str_replace("#", "", $icon_bg_color) );
		else :
			$transparency = true;
		endif;

		// 192 x 192
		$filename = $upload_dir['path'].'/android-chrome-192x192.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(192,192,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'android-chrome-192x192', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_android_icon_192x192_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 144 x 144
		$filename = $upload_dir['path'].'/android-chrome-144x144.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(144,144,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'android-chrome-144x144', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_android_icon_144x144_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 96 x 96
		$filename = $upload_dir['path'].'/android-chrome-96x96.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(96,96,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'android-chrome-96x96', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_android_icon_96x96_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 72 x 72
		$filename = $upload_dir['path'].'/android-chrome-72x72.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(72,72,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'android-chrome-72x72', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_android_icon_72x72_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 48 x 48
		$filename = $upload_dir['path'].'/android-chrome-48x48.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(48,48,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'android-chrome-48x48', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_android_icon_48x48_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 36 x 36
		$filename = $upload_dir['path'].'/android-chrome-36x36.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(36,36,$icon_bg_rgb_array,$transparency);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'android-chrome-36x36', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_android_icon_36x36_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		quickfavicon_android_update_manifest();
	}
}

/**
 * Create windows favicons
 *
 * @return void
 */
function quickfavicon_make_windows_icons() {

	// Get the upload directory
	$upload_dir = wp_upload_dir();

	// Get the icon attachment ID
	$icon_id = get_option( 'quickfavicon_windows_icon_id' );

	// Delete old apple touch icons
	quickfavicon_delete_windows_icons();

	if ($icon_id != '' ) {

		// Get the apple touch icon attachment
		$icon_image = get_attached_file($icon_id, false);

		$transparency = true;
		$icon_bg_rgb_array = array('red' => 0, 'green' => 0, 'blue' => 0);

		// 310 x 310
		$filename = $upload_dir['path'].'/mstile-310x310.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(260,260,$icon_bg_rgb_array,$transparency);
		if (get_option('quickfavicon_windows_icon_style') == 'white') {
		$image->mask(260, 260);
		}
		$image->sizecanvas(558, 558, 130, 149);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'mstile-310x310', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_windows_icon_310x310_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 310 x 150
		$filename = $upload_dir['path'].'/mstile-310x150.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(125,125,$icon_bg_rgb_array,$transparency);
		if (get_option('quickfavicon_windows_icon_style') == 'white') {
		$image->mask(125, 125);
		}
		$image->sizecanvas(558, 270, 55, 216);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'mstile-310x150', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_windows_icon_310x150_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 150 x 150
		$filename = $upload_dir['path'].'/mstile-150x150.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(130,130,$icon_bg_rgb_array,$transparency);
		if (get_option('quickfavicon_windows_icon_style') == 'white') {
		$image->mask(130, 130);
		}
		$image->sizecanvas(270, 270, 55, 70);
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'mstile-150x150', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_windows_icon_150x150_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 144 x 144
		$filename = $upload_dir['path'].'/mstile-144x144.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(144,144,$icon_bg_rgb_array,$transparency);
		if (get_option('quickfavicon_windows_icon_style') == 'white') {
		$image->mask(144, 144);
		}
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'mstile-144x144', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_windows_icon_144x144_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		// 70 x 70
		$filename = $upload_dir['path'].'/mstile-70x70.png';
		$image = new quickfavicon_image();
		$image->load($icon_image);
		$image->resize(128,128,$icon_bg_rgb_array,$transparency);
		if (get_option('quickfavicon_windows_icon_style') == 'white') {
		$image->mask(128, 128);
		}
		$image->save($filename, IMAGETYPE_PNG);
		$filetype = wp_check_filetype( basename( $filename ), null );
		$attachment_id = wp_insert_attachment( array( 'guid' => $upload_dir['url'] . '/' . basename( $filename ), 'post_title' => 'mstile-70x70', 'post_content' => '', 'post_status' => 'inherit', 'post_mime_type' => $filetype['type']), $filename );
		update_option( 'quickfavicon_windows_icon_70x70_id', $attachment_id);
		// Generate the metadata and thumbnail for the attachment, and update the database record.
		$attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
		wp_update_attachment_metadata( $attachment_id, $attachment_data );

		quickfavicon_windows_update_manifest();
	}
}

/**
 * Output for the frontend favicon
 *
 * @return void
 */
function quickfavicon_favicon_frontend_output() {
	if (get_option( 'quickfavicon_frontend_icon_id' ) != '') {
		// Standard favicon, displayed on tab in modern browsers
		$url = wp_get_attachment_url( get_option( 'quickfavicon_frontend_icon_16x16_id' ) );
		echo '<link rel="icon" type="image/png" sizes="16x16" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For Safari on Mac OS
		$url = wp_get_attachment_url( get_option( 'quickfavicon_frontend_icon_32x32_id' ) );
		echo '<link rel="icon" type="image/png" sizes="32x32" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For Google TV
		$url = wp_get_attachment_url( get_option( 'quickfavicon_frontend_icon_96x96_id' ) );
		echo '<link rel="icon" type="image/png" sizes="96x96" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";
	}
}

/**
 * Output for the backend favicon
 *
 * @return void
 */
function quickfavicon_favicon_backend_output() {
	if (get_option( 'quickfavicon_backend_icon_id' ) != '') {
		// Standard favicon, displayed on tab in modern browsers
		$url = wp_get_attachment_url( get_option( 'quickfavicon_backend_icon_16x16_id' ) );
		echo '<link rel="icon" type="image/png" sizes="16x16" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For Safari on Mac OS
		$url = wp_get_attachment_url( get_option( 'quickfavicon_backend_icon_32x32_id' ) );
		echo '<link rel="icon" type="image/png" sizes="32x32" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For Google TV
		$url = wp_get_attachment_url( get_option( 'quickfavicon_backend_icon_96x96_id' ) );
		echo '<link rel="icon" type="image/png" sizes="96x96" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";
	}
}

/**
 * Output for the iOS icons
 *
 * @return void
 */
function quickfavicon_ios_output() {
	if (get_option( 'quickfavicon_ios_icon_id' ) != '') {
		// For non-retina iPhone with iOS6 or prior
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_57x57_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="57x57" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For non-retina iPhone with iOS7
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_60x60_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="60x60" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For non-retina iPad with iOS6 or prior
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_72x72_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="72x72" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For non-retina iPad with iOS7
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_76x76_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="76x76" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For retina iPhone with iOS6 or prior
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_114x114_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="114x114" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For retina iPhone with iOS7
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_120x120_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="120x120" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For retina iPad with iOS6 or prior
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_144x144_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="144x144" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For retina iPad with iOS7
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_152x152_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="152x152" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// For iPhone 6 Plus with iOS8
		$url = wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_180x180_id' ) );
		echo '<link rel="apple-touch-icon" type="image/png" sizes="180x180" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";
	}
}

/**
 * Output for the Android icons
 *
 * @return void
 */
function quickfavicon_android_output() {
	if (get_option( 'quickfavicon_android_icon_id' ) != '') {
		// Echo the Android theme color
		echo '<meta name="theme-color" content="'.get_option( 'quickfavicon_android_icon_theme_color' ).'">'."\n";

		// Echo the 192x192 Android icon
		$url = wp_get_attachment_url( get_option( 'quickfavicon_android_icon_192x192_id' ) );
		echo '<link rel="icon" type="image/png" sizes="192x192" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";

		// Echo the Android manifest
		$url = get_option( 'quickfavicon_android_icon_manifest' );
		echo '<link rel="manifest" href="'.str_replace(get_bloginfo('url'),'',$url).'" />'."\n";
	}
}

/**
 * Output for the Windows icons
 *
 * @return void
 */
function quickfavicon_windows_output() {
	if (get_option( 'quickfavicon_windows_icon_id' ) != '') {
		// Define the uploads directory
		$upload_dir = wp_upload_dir();

		// Echo the Windows tile color and print Windows 8.0 / IE10 compatible tile color meta
		echo '<meta name="msapplication-TileColor" content="'. get_option( 'quickfavicon_windows_tile_color' ). '">'."\n";

		// Echo the Windows 8.0 / IE10 compatible tile image
		$url = wp_get_attachment_url( get_option( 'quickfavicon_windows_icon_144x144_id' ) );
		echo '<meta name="msapplication-TileImage" content="'. str_replace(get_bloginfo('url'),'',$url). '">'."\n";

		// Echo the Windows 8.1 / IE11 compatible manifest
		$url = get_option( 'quickfavicon_windows_icon_manifest' );
		echo '<meta name="msapplication-config" content="'.str_replace(get_bloginfo('url'),'',$url).'">'."\n";
	}
}

/**
 * Manage the android icon manifest
 *
 * @return void
 */
function quickfavicon_android_update_manifest() {
	// Define the uploads directory
	$upload_dir = wp_upload_dir();

	// If a manifest file already exists in the current uploads directory, delete it
	if(file_exists($upload_dir['path']."/manifest.json"))
		unlink($upload_dir['path']."/manifest.json");

	// If Android icon has been chosen
	if (get_option( 'quickfavicon_android_icon_id' ) != '') {

		if (get_option('quickfavicon_android_icon_app_name') != '')
			$android_app_name = get_option( 'quickfavicon_android_icon_app_name' );
		else
			$android_app_name = get_bloginfo( 'name' );

		// Update the Android manifest url option
		update_option('quickfavicon_android_icon_manifest', $upload_dir['url']."/manifest.json" );

		// Create a new Android manifest file
		$handle = fopen($upload_dir['path']."/manifest.json", "x");

		// If error, return
		if ($handle === false) {
			// error reading or opening file
			return true;
		}
		// If successful, set up file contents
		else {
			$content = '
			{
		  "name": "'.$android_app_name.'",
		  "icons": [
		    {
		      "src": "'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/android-chrome-36x36.png",
		      "sizes": "36x36",
		      "type": "image/png",
		      "density": "0.75"
		    },
		    {
		      "src": "'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/android-chrome-48x48.png",
		      "sizes": "48x48",
		      "type": "image/png",
		      "density": "1.0"
		    },
		    {
		      "src": "'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/android-chrome-72x72.png",
		      "sizes": "72x72",
		      "type": "image/png",
		      "density": "1.5"
		    },
		    {
		      "src": "'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/android-chrome-96x96.png",
		      "sizes": "96x96",
		      "type": "image/png",
		      "density": "2.0"
		    },
		    {
		      "src": "'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/android-chrome-144x144.png",
		      "sizes": "144x144",
		      "type": "image/png",
		      "density": "3.0"
		    },
		    {
		      "src": "'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/android-chrome-192x192.png",
		      "sizes": "192x192",
		      "type": "image/png",
		      "density": "4.0"
		    }
		  ]
			}';
		}
		// Write to the file
		if (fwrite($handle, $content) === FALSE) {
	     return true;
	   }
		// Close the file stream
	  fclose($handle);
	  return false;
	}
}

/**
 * Manage the windows icon manifest
 *
 * @return void
 */
function quickfavicon_windows_update_manifest() {
	// Define the uploads directory
	$upload_dir = wp_upload_dir();

	// If a manifest file already exists in the current uploads directory, delete it
	if(file_exists($upload_dir['path']."/browserconfig.xml"))
		unlink($upload_dir['path']."/browserconfig.xml");

	// If Windows icon has been chosen
	if (get_option( 'quickfavicon_windows_icon_id' ) != '') {

		// Update the Android manifest url option
		update_option('quickfavicon_windows_icon_manifest', $upload_dir['url']."/browserconfig.xml" );

		// Create a new Android manifest file
		$handle = fopen($upload_dir['path']."/browserconfig.xml", "x");

		// If error, return
		if ($handle === false) {
			// error reading or opening file
			return true;
		}
		// If successful, set up file contents
		else {
			$content = '<?xml version="1.0" encoding="utf-8"?>';
			$content .= '<browserconfig>';
			$content .= '<msapplication>';
			$content .= '<tile>';
			$content .= '<square70x70logo src="'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/mstile-70x70.png"/>';
			$content .= '<square150x150logo src="'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/mstile-150x150.png"/>';
			$content .= '<wide310x150logo src="'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/mstile-310x310.png"/>';
			$content .= '<square310x310logo src="'.str_replace(get_bloginfo('url'),'',$upload_dir['url']).'/mstile-310x150.png"/>';
			$content .= '<TileColor>'.get_option( 'quickfavicon_windows_tile_color' ).'</TileColor>';
			$content .= '</tile>';
			$content .= '</msapplication>';
			$content .= '</browserconfig>';
		}
		// Write to the file
		if (fwrite($handle, $content) === FALSE) {
	     return true;
	   }
		// Close the file stream
	  fclose($handle);
	  return false;
	}
}

/**
 * Build the settings page structure
 *
 * @return void
 */
function quickfavicon_settings_page() {

	// Load media uploader dependencies
	wp_enqueue_media();

	// Set up plugin data for use
	$plugin_data = get_plugin_data( __FILE__ );
?>
<div id="quickfavicon_settings_page" class="wrap">

	<h2><?php _e( 'Icon Settings', 'quickfavicon' ); ?> <span class="pull-right text-muted"><small><strong>Quick Favicon</strong> (<?php echo $plugin_data['Version']; ?>) <?php _e( 'by', 'quickfavicon' ); ?> <strong><?php echo $plugin_data['Author']; ?></strong></small></span></h2>
	<form id="quickfavicon_settings_form" method="post" action="options.php">
		<?php settings_fields( 'quickfavicon-settings-group' ); ?>
		<?php do_settings_sections( 'quickfavicon-settings-group' ); ?>

		<?php quickfavicon_top_notice(); ?>

		<?php
		$active_tab = get_option( 'quickfavicon_last_tab_used' );
		if ($active_tab == '' || !isset($_GET['settings-updated']) ) :
			$active_tab = 'quickfavicon_browser_tab';
		elseif ($active_tab != '' && $_GET['settings-updated'] =='true' ) :
			$active_tab = $active_tab;
			quickfavicon_updated_notice();
		endif;
		?>

		<div role="tabpanel">

		  <!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
		    <li role="presentation" <?php if ($active_tab == 'quickfavicon_browser_tab' ) : echo ' class="active"'; endif; ?>><a href="#quickfavicon_browser_tab" data-tab-id="quickfavicon_browser_tab" aria-controls="quickfavicon_browser_tab" role="tab" data-toggle="tab" class="setting-tab-pane"><small><?php _e( 'Browser', 'quickfavicon' ); ?></small></a></li>
		    <li role="presentation" <?php if ($active_tab == 'quickfavicon_ios_tab' ) : echo ' class="active"'; endif; ?>><a href="#quickfavicon_ios_tab" data-tab-id="quickfavicon_ios_tab" aria-controls="quickfavicon_ios_tab" role="tab" data-toggle="tab" class="setting-tab-pane"><small><?php _e( 'iOS', 'quickfavicon' ); ?></small></a></li>
		    <li role="presentation" <?php if ($active_tab == 'quickfavicon_android_tab' ) : echo ' class="active"'; endif; ?>><a href="#quickfavicon_android_tab" data-tab-id="quickfavicon_android_tab" aria-controls="quickfavicon_android_tab" role="tab" data-toggle="tab" class="setting-tab-pane"><small><?php _e( 'Android', 'quickfavicon' ); ?></small></a></li>
		    <li role="presentation" <?php if ($active_tab == 'quickfavicon_windows_tab' ) : echo ' class="active"'; endif; ?>><a href="#quickfavicon_windows_tab" data-tab-id="quickfavicon_windows_tab" aria-controls="quickfavicon_windows_tab" role="tab" data-toggle="tab" class="setting-tab-pane"><small><?php _e( 'Windows 8', 'quickfavicon' ); ?></small></a></li>
		  </ul>

		  <!-- Tab panes -->
		  <div class="tab-content">
		    <div role="tabpanel" class="tab-pane<?php if ($active_tab == 'quickfavicon_browser_tab' ) echo ' active'; ?>" id="quickfavicon_browser_tab">
						<?php quickfavicon_build_panel_browser(); ?>
				</div>
		    <div role="tabpanel" class="tab-pane<?php if ($active_tab == 'quickfavicon_ios_tab' ) echo ' active'; ?>" id="quickfavicon_ios_tab">
						<?php quickfavicon_build_panel_ios(); ?>
				</div>
		    <div role="tabpanel" class="tab-pane<?php if ($active_tab == 'quickfavicon_android_tab' ) echo ' active'; ?>" id="quickfavicon_android_tab">
						<?php quickfavicon_build_panel_android(); ?>
				</div>
		    <div role="tabpanel" class="tab-pane<?php if ($active_tab == 'quickfavicon_windows_tab' ) echo ' active'; ?>" id="quickfavicon_windows_tab">
						<?php quickfavicon_build_panel_windows(); ?>
				</div>
		  </div>

		</div>

		<input type="hidden" name="quickfavicon_last_tab_used" id ="quickfavicon_last_tab_used" value="quickfavicon_browser_tab">

	</form>

</div>
<?php
}

/**
 * Build the Broswer icon panel
 *
 * @return void
 */
function quickfavicon_build_panel_browser() {
?>
<div class="panel panel-default">
	<div class="panel-body">
		<h2><?php _e( 'Browser', 'quickfavicon' ); echo ( ( get_option( 'quickfavicon_frontend_icon_id' ) != '' && get_option( 'quickfavicon_backend_icon_id' ) != '' ) ? ' <span class="text-success glyphicon glyphicon-ok-sign"></span>':'' ); ?></h2>
		<small><?php _e( 'This is the traditional Favicon seen beside your page title in modern browsers.', 'quickfavicon' ); ?></small>
		<div><small><a data-toggle="collapse" href="#collapseBrowser" aria-expanded="false" aria-controls="collapseBrowser"><b>Read more</b> <span class="glyphicon glyphicon-collapse-down"></span></a></small></div>
		<div class="collapse" id="collapseBrowser">
			<small><?php _e( 'The image you use here should be at least <mark><span class="text-info"><b>96 x 96 pixels</b></span></mark> for optimal results. If the image is not square, it will be made square. Any transparent areas will remain transparent.', 'quickfavicon' ); ?></small>
		</div>
		<p>&nbsp;</p>
		<div class="row">
			<div class="col-sm-6">

				<div class="panel panel-default options-section">
					<div class="panel-heading"><?php _e( 'Front-End', 'quickfavicon' ); ?></div>
					<div class="panel-body">
						<p><small><?php _e( 'This is what your visitors see.', 'quickfavicon' ); ?></small></p>
						<div>

							<div class="row">

								<div class="col-sm-4">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_frontend_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner">
											<img src="<?php if ( get_option( 'quickfavicon_frontend_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_frontend_icon_id' ) ); ?>" height="96" width="96" />
										</div>
										<div><small><b><?php _e( '96 x 96 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Google TV' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-4">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_frontend_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner">
											<img src="<?php if ( get_option( 'quickfavicon_frontend_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_frontend_icon_id' ) ); ?>" height="32" width="32" />
										</div>
										<div><small><b><?php _e( '32 x 32 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Safari on Mac OS' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-4">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_frontend_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner">
											<img src="<?php if ( get_option( 'quickfavicon_frontend_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_frontend_icon_id' ) ); ?>" height="16" width="16" />
										</div>
										<div><small><b><?php _e( '16 x 16 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Standard Favicon for tabs' ); ?></small></div>
									</div>
								</div>

							</div>

							<input type="hidden" class="hidden-id-field" name="quickfavicon_frontend_icon_id" value="<?php if ( get_option( 'quickfavicon_frontend_icon_id' ) != '' ) echo get_option( 'quickfavicon_frontend_icon_id' ); ?>" />
							<button type="button" class="icon-upload btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_frontend_icon_id' ) != '' ? ' hidden':'' ); ?>"><?php _e( 'Select Image', 'quickfavicon' ); ?></button>
							<button type="button" class="icon-remove btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_frontend_icon_id' ) == '' ? ' hidden':'' ); ?>"><?php _e( 'Remove', 'quickfavicon' ); ?></button>
						</div>

						<?php submit_button(); ?>
					</div>
				</div>

			</div>
			<div class="col-sm-6">

				<div class="panel panel-default options-section">
					<div class="panel-heading"><?php _e( 'Back-End', 'quickfavicon' ); ?></div>
					<div class="panel-body">
						<p><small><?php _e( 'This is what your logged-in users see.', 'quickfavicon' ); ?></small></p>
						<div>

							<div class="row">

								<div class="col-sm-4">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_backend_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner">
											<img src="<?php if ( get_option( 'quickfavicon_backend_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_backend_icon_id' ) ); ?>" height="96" width="96" />
										</div>
										<div><small><b><?php _e( '96 x 96 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Google TV' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-4">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_backend_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner">
											<img src="<?php if ( get_option( 'quickfavicon_backend_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_backend_icon_id' ) ); ?>" height="32" width="32" />
										</div>
										<div><small><b><?php _e( '32 x 32 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Safari on Mac OS' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-4">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_backend_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner">
											<img src="<?php if ( get_option( 'quickfavicon_backend_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_backend_icon_id' ) ); ?>" height="16" width="16" />
										</div>
										<div><small><b><?php _e( '16 x 16 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Standard Favicon for tabs' ); ?></small></div>
									</div>
								</div>

							</div>

							<input type="hidden" class="hidden-id-field" name="quickfavicon_backend_icon_id" value="<?php if ( get_option( 'quickfavicon_backend_icon_id' ) != '' ) echo get_option( 'quickfavicon_backend_icon_id' ); ?>" />
							<button type="button" class="icon-upload btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_backend_icon_id' ) != '' ? ' hidden':'' ); ?>"><?php _e( 'Select Image', 'quickfavicon' ); ?></button>
							<button type="button" class="icon-remove btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_backend_icon_id' ) == '' ? ' hidden':'' ); ?>"><?php _e( 'Remove', 'quickfavicon' ); ?></button>
						</div>

						<?php submit_button(); ?>
					</div>
				</div>

			</div>
		</div>

	</div>
	</div>
<?php
}

/**
 * Build the iOS icon panel
 *
 * @return void
 */
function quickfavicon_build_panel_ios() {
?>
<div class="panel panel-default options-section">
	<div class="panel-body">

		<h2><?php _e( 'iOS', 'quickfavicon' ); echo ( get_option( 'quickfavicon_ios_icon_id' ) != '' ? ' <span class="text-success glyphicon glyphicon-ok-sign"></span>':'' ); ?></h2>
		<small><?php _e( 'iOS users can pin your site to their home screen for quick access to their favorite bookmarks.', 'quickfavicon' ); ?></small>
		<div><small><a data-toggle="collapse" href="#collapseiOS" aria-expanded="false" aria-controls="collapseiOS"><b>Read more</b> <span class="glyphicon glyphicon-collapse-down"></span></a></small></div>
		<div class="collapse" id="collapseiOS">
			<small><?php _e( 'The image you use here should be at least <mark><span class="text-info"><b>180 x 180 pixels</b></span></mark> for optimal results. If the image is not square, it will be made square. Any transparent areas will remain transparent.', 'quickfavicon' ); ?></small>
		</div>
		<p>&nbsp;</p>

		<div class="row">

			<div class="col-sm-6">
				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'Icon Options', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div class="row">

							<div class="col-lg-6">
								<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
									<div class="icon-image-inner">
										<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="180" width="180" />
									</div>
									<div><small><b><?php _e( 'Original Image', 'quickfavicon' ); ?></b></small></div>
								</div>
								<p>
									<input type="hidden" class="hidden-id-field" name="quickfavicon_ios_icon_id" value="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo get_option( 'quickfavicon_ios_icon_id' ); ?>" />
									<button type="button" class="icon-upload btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) != '' ? ' hidden':'' ); ?>"><?php _e( 'Select Image', 'quickfavicon' ); ?></button>
									<button type="button" class="icon-remove btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>"><?php _e( 'Remove', 'quickfavicon' ); ?></button>
								</p>
							</div>

							<div class="col-lg-6">
								<div class="ios_sizes">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="180" width="180" />
										</div>
										<div><small><b><?php _e( 'Preview', 'quickfavicon' ); ?></b></small></div>
									</div>
								</div>
							</div>

						</div><!-- /.row -->

						<div class="row">

							<div class="col-sm-12">

								<p><b><?php _e( 'Background Color', 'quickfavicon' ); ?></b></p>
								<div class="radio">
									<label>
										<input type="radio" name="quickfavicon_ios_icon_bg_radio" value="white"<?php echo ( ( get_option( 'quickfavicon_ios_icon_bg_radio' ) == 'white' || get_option( 'quickfavicon_ios_icon_bg_radio' ) == '' ) ? ' checked':'' ); ?>>
										<small><?php _e( 'Fill the transparent areas with white' ); ?></small>
									</label>
								</div>

								<div class="radio">
									<label>
										<input type="radio" name="quickfavicon_ios_icon_bg_radio" value="black"<?php echo ( get_option( 'quickfavicon_ios_icon_bg_radio' ) == 'black' ? ' checked':'' ); ?>>
										<small><?php _e( 'Fill the transparent areas with black' ); ?></small>
									</label>
								</div>

								<div class="radio">
									<label>
										<input type="radio" name="quickfavicon_ios_icon_bg_radio" value="custom"<?php echo ( get_option( 'quickfavicon_ios_icon_bg_radio' ) == 'custom' ? ' checked':'' ); ?>>
										<small><?php _e( 'Use a custom solid background color' ); ?></small>
									</label>
								</div>

								<div class="input-group colorpicker">
									<input type="text" id="quickfavicon_ios_icon_bg" name="quickfavicon_ios_icon_bg_visible" class="form-control" value="<?php echo get_option( 'quickfavicon_ios_icon_bg' ); ?>" <?php echo ( get_option( 'quickfavicon_ios_icon_bg_radio' ) != 'custom' ? 'disabled':'' ); ?>/>
									<input type="hidden" name="quickfavicon_ios_icon_bg" value="<?php echo get_option( 'quickfavicon_ios_icon_bg' ); ?>"/>
									<span class="input-group-addon"><i></i></span>
								</div>

								<?php submit_button(); ?>
							</div><!-- /.col-sm-12 -->

						</div><!-- /.row -->

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-6 -->

			<div class="col-sm-6">

				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'On-Screen Preview', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div class="preview-ios">

							<div class="row">
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">&nbsp;</div>
							</div><!-- /.row -->

							<div class="row">
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">
									<div class="ios_sizes">
										<div class="icon-image homescreen-icon <?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
											<div class="icon-image-inner ios_icon_image_preview">
												<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="55" width="55" />
											</div>
											<div><small><b><?php echo ( get_option( 'quickfavicon_ios_icon_app_name' ) == '' )? 'My App':get_option( 'quickfavicon_ios_icon_app_name' ); ?></b></small></div>
										</div>
									</div>
								</div>
								<div class="col-xs-4">&nbsp;</div>
							</div><!-- /.row -->

							<div class="row">
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">&nbsp;</div>
							</div><!-- /.row -->

						</div><!-- /.preview_ios -->

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-6 -->

		</div><!-- /.row -->

		<div class="row">

			<div class="col-sm-12">

				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'Icon Sizes', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div id="ios_sizes" class="ios_sizes">

							<div class="row">

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="180" width="180" />
										</div>
										<div><small><b><?php _e( '180 x 180 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'iPhone 6 Plus with iOS8', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="152" width="152" />
										</div>
										<div><small><b><?php _e( '152 x 152 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Retina iPad with iOS7', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="144" width="144" />
										</div>
										<div><small><b><?php _e( '144 x 144 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Retina iPad with iOS6 or prior', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="120" width="120" />
										</div>
										<div><small><b><?php _e( '120 x 120 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Retina iPhone with iOS7', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

							</div><!-- /.row -->
							<div class="row">

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="114" width="114" />
										</div>
										<div><small><b><?php _e( '114 x 114 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Retina iPhone with iOS6 or prior', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="76" width="76" />
										</div>
										<div><small><b><?php _e( '76 x 76 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Non-retina iPad with iOS7', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="72" width="72" />
										</div>
										<div><small><b><?php _e( '72 x 72 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Non-retina iPad with iOS6 or prior', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="60" width="60" />
										</div>
										<div><small><b><?php _e( '60 x 60 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Non-retina iPhone with iOS7', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

							</div><!-- /.row -->
							<div class="row">

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_ios_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner ios_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_ios_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_ios_icon_id' ) ); ?>" height="57" width="57" />
										</div>
										<div><small><b><?php _e( '57 x 57 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Non-retina iPhone with iOS6 or prior', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

							</div><!-- /.row -->

						</div><!-- /#ios_sizes -->

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-12 -->

		</div><!-- /.row -->

	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<?php
}

/**
 * Build the Android icon panel
 *
 * @return void
 */
function quickfavicon_build_panel_android() {
?>
<div class="panel panel-default options-section">
	<div class="panel-body">

		<h2><?php _e( 'Android', 'quickfavicon' ); echo ( get_option( 'quickfavicon_android_icon_id' ) != '' ? ' <span class="text-success glyphicon glyphicon-ok-sign"></span>':'' ); ?></h2>
		<small><?php _e( 'On Android devices, Chrome users can pin your site to their home screen just like with iOS.', 'quickfavicon' ); ?></small>
		<div><small><a data-toggle="collapse" href="#collapseAndroid" aria-expanded="false" aria-controls="collapseAndroid"><b>Read more</b> <span class="glyphicon glyphicon-collapse-down"></span></a></small></div>
		<div class="collapse" id="collapseAndroid">
			<small><?php _e( 'The image you use here should be at least <mark><span class="text-info"><b>192 x 192 pixels</b></span></mark> for optimal results. If the image is not square, it will be made square. Any transparent areas will remain transparent.' ); ?></small>
		</div>
		<p>&nbsp;</p>

		<div class="row">

			<div class="col-sm-6">
				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'Icon Options', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div class="row">

							<div class="col-lg-6">
								<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
									<div class="icon-image-inner">
										<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="192" width="192" />
									</div>
									<div><small><b><?php _e( 'Original Image', 'quickfavicon' ); ?></b></small></div>
								</div>
								<p>
									<input type="hidden" class="hidden-id-field" name="quickfavicon_android_icon_id" value="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo get_option( 'quickfavicon_android_icon_id' ); ?>" />
									<button type="button" class="icon-upload btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_android_icon_id' ) != '' ? ' hidden':'' ); ?>"><?php _e( 'Select Image', 'quickfavicon' ); ?></button>
									<button type="button" class="icon-remove btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>"><?php _e( 'Remove', 'quickfavicon' ); ?></button>
								</p>
							</div>

							<div class="col-lg-6">
								<div class="android_sizes">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner android_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="192" width="192" />
										</div>
										<div><small><b><?php _e( 'Preview', 'quickfavicon' ); ?></b></small></div>
									</div>
								</div>
							</div>

						</div><!-- /.row -->

						<div class="row">

							<div class="col-sm-12">

								<p><b><?php _e( 'Background Color', 'quickfavicon' ); ?></b></p>

								<div class="radio">
									<label>
										<input type="radio" name="quickfavicon_android_icon_bg_radio" value="white"<?php echo ( ( get_option( 'quickfavicon_android_icon_bg_radio' ) == 'white' || get_option( 'quickfavicon_android_icon_bg_radio' ) == '' ) ? ' checked':'' ); ?>>
										<small><?php _e( 'Fill the transparent areas with white' ); ?></small>
									</label>
								</div>

								<div class="radio">
									<label>
										<input type="radio" name="quickfavicon_android_icon_bg_radio" value="black"<?php echo ( get_option( 'quickfavicon_android_icon_bg_radio' ) == 'black' ? ' checked':'' ); ?>>
										<small><?php _e( 'Fill the transparent areas with black' ); ?></small>
									</label>
								</div>

								<div class="radio">
									<label>
										<input type="radio" name="quickfavicon_android_icon_bg_radio" value="transparent"<?php echo ( get_option( 'quickfavicon_android_icon_bg_radio' ) == 'transparent' ? ' checked':'' ); ?>>
										<small><?php _e( 'Leave the transparent areas as is' ); ?></small>
									</label>
								</div>

								<div class="radio">
									<label>
										<input type="radio" name="quickfavicon_android_icon_bg_radio" value="custom"<?php echo ( get_option( 'quickfavicon_android_icon_bg_radio' ) == 'custom' ? ' checked':'' ); ?>>
										<small><?php _e( 'Use a custom solid background color' ); ?></small>
									</label>
								</div>

								<div class="input-group colorpicker">
									<input type="text" id="quickfavicon_android_icon_bg" name="quickfavicon_android_icon_bg_visible" class="form-control" value="<?php echo get_option( 'quickfavicon_android_icon_bg' ); ?>" <?php echo ( get_option( 'quickfavicon_android_icon_bg_radio' ) != 'custom' ? 'disabled':'' ); ?>/>
									<input type="hidden" name="quickfavicon_android_icon_bg" value="<?php echo get_option( 'quickfavicon_android_icon_bg' ); ?>"/>
									<span class="input-group-addon"><i></i></span>
								</div>

								<div>&nbsp;</div>

								<p><b><?php _e( 'Theme Color' ); ?></b></p>

								<div class="form-group">
									<div class="input-group colorpicker">
										<input type="text" id="quickfavicon_android_icon_theme_color" name="quickfavicon_android_icon_theme_color" class="form-control" value="<?php echo get_option( 'quickfavicon_android_icon_theme_color' ); ?>" />
										<span class="input-group-addon"><i></i></span>
									</div>
									<span class="help-block"><small><?php _e( 'Starting with Android Lollipop, you may customize the color of the task bar.' ); ?></small></span>
								</div>

								<div>&nbsp;</div>

								<p><b><?php _e( 'App Name' ); ?></b></p>

								<div class="form-group">
									<input type="text" name="quickfavicon_android_icon_app_name" value="<?php echo get_option( 'quickfavicon_android_icon_app_name' ); ?>" placeholder="My App" class="form-control" />
									<span class="help-block"><small><?php _e( 'This is the name that will appear under your icon when pinned to a home screen. If left blank, the WordPress Site Title will be used. Starting with Android Chrome M39 and its manifest, app name is required.' ); ?></small></span>
								</div>

								<?php submit_button(); ?>
							</div>

						</div>

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-6 -->

			<div class="col-sm-6">

				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'On-Screen Preview', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div class="preview-android">

							<div class="row">
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">&nbsp;</div>
							</div>

							<div class="row">
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">
									<div class="android_sizes">
										<div class="icon-image homescreen-icon <?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
											<div class="icon-image-inner android_icon_image_preview">
												<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="48" width="48" />
											</div>
											<div><small><b><?php echo ( get_option( 'quickfavicon_android_icon_app_name' ) == '' )? 'My App':get_option( 'quickfavicon_android_icon_app_name' ); ?></b></small></div>
										</div>
									</div>
								</div>
								<div class="col-xs-4">&nbsp;</div>
							</div>

							<div class="row">
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">&nbsp;</div>
								<div class="col-xs-4">&nbsp;</div>
							</div>

						</div>

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-6 -->

		</div><!-- /.row -->

		<div class="row">

			<div class="col-sm-12">

				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'Icon Sizes', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div id="android_sizes" class="android_sizes">

							<div class="row">

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner android_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="192" width="192" />
										</div>
										<div><small><b><?php _e( '192 x 192 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Chrome M39+ with 4.0 screen density' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner android_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="144" width="144" />
										</div>
										<div><small><b><?php _e( '144 x 144 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Chrome M39+ with 3.0 screen density' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner android_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="96" width="96" />
										</div>
										<div><small><b><?php _e( '96 x 96 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Chrome M39+ with 2.0 screen density' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner android_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="72" width="72" />
										</div>
										<div><small><b><?php _e( '72 x 72 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Chrome M39+ with 1.5 screen density' ); ?></small></div>
									</div>
								</div>

							</div>
							<div class="row">

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner android_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="48" width="48" />
										</div>
										<div><small><b><?php _e( '48 x 48 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Chrome M39+ with 1.0 screen density' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-3">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_android_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner android_icon_image_preview">
											<img src="<?php if ( get_option( 'quickfavicon_android_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_android_icon_id' ) ); ?>" height="36" width="36" />
										</div>
										<div><small><b><?php _e( '36 x 36 pixels' ); ?></b></small></div>
										<div><small><?php _e( 'Chrome M39+ with 0.75 screen density' ); ?></small></div>
									</div>
								</div>

							</div><!-- /.row -->

						</div><!-- /#android_sizes -->

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-12 -->

		</div><!-- /.row -->

	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<?php
}

/**
 * Build the Windows icon panel
 *
 * @return void
 */
function quickfavicon_build_panel_windows() {
?>
<div id="windows-panel" class="panel panel-default options-section windows-panel">
	<div class="panel-body">

		<h2><?php _e( 'Windows 8 Tile', 'quickfavicon' ); echo ( get_option( 'quickfavicon_windows_icon_id' ) != '' ? ' <span class="text-success glyphicon glyphicon-ok-sign"></span>':'' ); ?></h2>
		<small><?php _e( 'On Windows 8, your site appears as a tile just like a native application.', 'quickfavicon' ); ?></small>
		<div><small><a data-toggle="collapse" href="#collapseWindows" aria-expanded="false" aria-controls="collapseWindows"><b>Read more</b> <span class="glyphicon glyphicon-collapse-down"></span></a></small></div>
		<div class="collapse" id="collapseWindows">
			<small><?php _e( 'The image you use here should be at least <mark><span class="text-info"><b>310 x 310 pixels</b></span></mark> for optimal results. If the image is not square, it will be made square. Any transparent areas will remain transparent.', 'quickfavicon' ); ?></small>
		</div>
		<p>&nbsp;</p>

		<div class="row">

			<div class="col-sm-6">
				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'Icon Options', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div class="row">

							<div class="col-lg-12">
								<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) == '' ? ' hidden':'' ); ?>">
									<div class="icon-image-inner">
										<img src="<?php if ( get_option( 'quickfavicon_windows_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_windows_icon_id' ) ); ?>" height="310" width="310" />
										<?php $image = wp_get_attachment_metadata(get_option('quickfavicon_windows_icon_id')); ?>
										<img id="quickfavicon_white_mask_source" src="<?php if ( get_option( 'quickfavicon_windows_icon_id' ) != '' ) echo wp_get_attachment_url( get_option( 'quickfavicon_windows_icon_id' ) ); ?>" width="<?php echo $image['width']; ?>" height="<?php echo $image['height']; ?>" />
									</div>
									<div><small><b><?php _e( 'Original Image', 'quickfavicon' ); ?></b></small></div>
								</div>
								<p>
									<input type="hidden" class="hidden-id-field" name="quickfavicon_windows_icon_id" value="<?php if ( get_option( 'quickfavicon_windows_icon_id' ) != '' ) echo get_option( 'quickfavicon_windows_icon_id' ); ?>" />
									<button type="button" class="icon-upload btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) != '' ? ' hidden':'' ); ?>"><?php _e( 'Select Image', 'quickfavicon' ); ?></button>
									<button type="button" class="icon-remove btn btn-sm btn-default<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) == '' ? ' hidden':'' ); ?>"><?php _e( 'Remove', 'quickfavicon' ); ?></button>
								</p>
							</div>

						</div><!-- /.row -->

						<div class="row">

							<div class="col-sm-12">

								<p><b><?php _e( 'Tile Color', 'quickfavicon' ); ?></b></p>
								<div class="input-group colorpicker">
									<input type="text" id="quickfavicon_windows_tile_color" name="quickfavicon_windows_tile_color" value="<?php echo ( get_option( 'quickfavicon_windows_tile_color' ) != '' )? get_option( 'quickfavicon_windows_tile_color' ):'#da532c'; ?>" class="form-control" />
									<span class="input-group-addon"><i></i></span>
								</div>
								<span class="help-block"><small><?php _e( 'You may also choose from one of the standard tile colors below.' ); ?></small></span>
								<div class="tile-colors">
									<span class="label label-dark-orange"><a class="windows_tile_label" data-tile-color="#da532c"><?php _e( 'Dark Orange', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
									<span class="label label-yellow"><a class="windows_tile_label" data-tile-color="#ffc40d"><?php _e( 'Yellow', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
									<span class="label label-green"><a class="windows_tile_label" data-tile-color="#00a300"><?php _e( 'Green', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
									<span class="label label-teal"><a class="windows_tile_label" data-tile-color="#00aba9"><?php _e( 'Teal', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
									<span class="label label-blue"><a class="windows_tile_label" data-tile-color="#2d89ef"><?php _e( 'Blue', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
									<span class="label label-dark-blue"><a class="windows_tile_label" data-tile-color="#2b5797"><?php _e( 'Dark Blue', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
									<span class="label label-light-purple"><a class="windows_tile_label" data-tile-color="#9f00a7"><?php _e( 'Light Purple', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
									<span class="label label-dark-purple"><a class="windows_tile_label" data-tile-color="#603cba"><?php _e( 'Dark Purple', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
									<span class="label label-dark-red"><a class="windows_tile_label" data-tile-color="#b91d47"><?php _e( 'Dark Red', 'quickfavicon' ); ?> <span class="hidden selected-notice">(<?php _e( 'Selected', 'quickfavicon' ); ?>)</span></a></span>
								</div>
								<div>&nbsp;</div>
								<p><b><?php _e( 'Icon Style', 'quickfavicon' ); ?></b></p>
								<div class="radio">
									<label>
										<input type="radio" id="quickfavicon_windows_icon_style" name="quickfavicon_windows_icon_style" value="white"<?php echo ( ( get_option( 'quickfavicon_windows_icon_style' ) == 'white' || get_option( 'quickfavicon_windows_icon_style_radio' ) == '' ) ? ' checked':'' ); ?>>
										<small><?php _e( 'Use a white silhouette of the icon', 'quickfavicon' ); ?></small>
									</label>
								</div>
								<div class="radio">
									<label>
										<input type="radio" name="quickfavicon_windows_icon_style" value="asis"<?php echo ( ( get_option( 'quickfavicon_windows_icon_style' ) == 'asis' ) ? ' checked':'' ); ?>>
										<small><?php _e( 'Use the icon as is', 'quickfavicon' ); ?></small>
									</label>
								</div>

								<?php submit_button(); ?>
							</div><!-- /.col-sm-12 -->

						</div><!-- /.row -->

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-6 -->

			<div class="col-sm-6">

				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'On-Screen Preview', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div class="preview-windows">
							<div class="windows-icon">

								<div class="icon-image windows_icon_cell<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) == '' ? ' hidden':'' ); ?>" style="height: 100px;width: 100px;">
									<div class="icon-image-inner windows_icon_image_preview" style="padding-left: 25px;padding-top: 20px;padding-bottom: 26px;">
										<canvas id="win_tile" height="50" width="50" class="win-canvas"></canvas>
									</div>
								</div>

							</div>
						</div>

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-6 -->

		</div><!-- /.row -->

		<div class="row">

			<div class="col-sm-12">

				<div class="panel panel-default">
					<div class="panel-heading"><?php _e( 'Icon Sizes', 'quickfavicon' ); ?></div>
					<div class="panel-body">

						<div id="windows_sizes" class="windows_sizes">

							<div class="row">

								<div class="col-sm-12 col-lg-6">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner windows_icon_image_preview" style="width: 310px;height: 310px;padding-top: 50px;">
											<canvas id="win_310x310" height="180" width="180" style="width: 180px;height: 180px;margin: 0 auto;" class="win-canvas"></canvas>
										</div>
										<div><small><b><?php _e( '310 x 310 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Windows 8 / IE11', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-6">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner windows_icon_image_preview" style="width: 310px;height: 150px;padding-top: 25px;">
											<canvas id="win_310x150" height="75" width="75" style="width: 75px;height: 75px;margin: 0 auto;" class="win-canvas"></canvas>
										</div>
										<div><small><b><?php _e( '310 x 150 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Windows 8 / IE11', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

							</div><!-- /.row -->
							<div class="row">

								<div class="col-sm-12 col-lg-6">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner windows_icon_image_preview" style="width: 150px;height: 150px;padding-top: 25px;">
											<canvas id="win_150x150" height="80" width="80" style="width: 80px;height: 80px;margin: 0 auto;" class="win-canvas"></canvas>
										</div>
										<div><small><b><?php _e( '150 x 150 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Windows 8 / IE11', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

								<div class="col-sm-12 col-lg-6">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner windows_icon_image_preview">
											<canvas id="win_144x144" height="144" width="144" style="width: 144px;height: 144px;" class="win-canvas"></canvas>
										</div>
										<div><small><b><?php _e( '144 x 144 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Windows 8 / IE10', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

							</div><!-- /.row -->
							<div class="row">

								<div class="col-sm-12 col-lg-6">
									<div class="icon-image icon-image-bordered<?php echo ( get_option( 'quickfavicon_windows_icon_id' ) == '' ? ' hidden':'' ); ?>">
										<div class="icon-image-inner windows_icon_image_preview">
											<canvas id="win_70x70" height="70" width="70" style="width: 70px;height: 70px;" class="win-canvas"></canvas>
										</div>
										<div><small><b><?php _e( '70 x 70 pixels', 'quickfavicon' ); ?></b></small></div>
										<div><small><?php _e( 'Windows 8 / IE11', 'quickfavicon' ); ?></small></div>
									</div>
								</div>

							</div><!-- /.row -->

						</div><!-- /#windows_sizes -->

					</div><!-- /.panel-body -->
				</div><!-- /.panel -->

			</div><!-- /.col-sm-12 -->

		</div><!-- /.row -->

	</div><!-- /.panel-body -->
</div><!-- /.panel -->
<canvas id="hidden_canvas_1" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_2" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_3" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_4" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_5" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_6" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_7" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_8" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_9" class="hidden-canvas"></canvas>
<canvas id="hidden_canvas_10" class="hidden-canvas"></canvas>
<?php
}

/**
 * Build the info notice for the setting page
 *
 * @return void
 */
function quickfavicon_top_notice() {
?>
<div class="updated">
	<p>
		<?php _e( 'If you like <strong>Quick Favicon</strong> please leave it a <a href="https://wordpress.org/support/view/plugin-reviews/quick-favicon?filter=5#postform" target="_blank" title="Leave Quick Favicon a 5-Star rating on WordPress.org" data-rated="Thanks :)">★★★★★</a> rating and/or consider <a href="//pluginspired.com/donate/" title="Donate with PayPal or Bitcoin and help make Quick Favicon better" target="_blank"><b>making a donation</b></a>. Even the smallest donations are appreciated!', 'quickfavicon' ); ?>
	</p>
</div>
<?php
}

/**
 * Build the success notice for the setting page
 *
 * @return void
 */
function quickfavicon_updated_notice() {
?>
<div class="updated" role="alert"><span class="glyphicon glyphicon-ok-sign"></span> <?php _e( 'Your settings have been saved.', 'quickfavicon' ); ?></div>
<?php
}
