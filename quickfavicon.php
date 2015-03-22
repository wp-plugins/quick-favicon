<?php
/*
Plugin Name: Quick Favicon
Description: Easily upload and set a Favicon (browser icon, shortcut icon) for your WordPress site. It is possible to set a different icon for both the front-end and dashboard areas.
Version: 0.22.2
Author: Robert Cummings
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

/**
 * Add actions
 */
add_action('wp_head','quickfavicon_frontend_output');
add_action('wp_head','quickfavicon_appletouch_output');
add_action('admin_head','quickfavicon_backend_output');
add_action('admin_menu','quickfavicon_create_menu');

/**
 * If plugin settings page is being displayed, enqueue admin scripts
 */

if (isset($_GET['page']) && $_GET['page'] == 'quick-favicon/quickfavicon.php') {
	add_action('admin_print_scripts','quickfavicon_admin_enqueue');
}

function quickfavicon_admin_enqueue() {
	wp_enqueue_script('quick-favicon', plugins_url('js/quickfavicon.min.js', __FILE__ ), array('jquery'), '0.22', true);
	wp_enqueue_script('quick-favicon-bootstrap-js', plugins_url('js/bootstrap.min.js', __FILE__ ), array('jquery'), '3.3.4', true);
	wp_enqueue_style('quick-favicon-bootstrap-css', plugins_url('css/bootstrap.min.css', __FILE__ ), array(), '3.3.4');
	wp_enqueue_style('quick-favicon-css', plugins_url('css/quickfavicon.min.css', __FILE__ ), array('quick-favicon-bootstrap-css'), '0.22');
}

/**
 * Output for the front-end favicon
 *
 * @return void
 */
function quickfavicon_frontend_output() {
	$url = wp_get_attachment_url(get_option('quickfavicon_frontend_icon_id'));
	if ($url != '')
		echo '<link rel="icon" href="'.$url.'" /><link rel="shortcut icon" href="'.$url.'" />';
}

/**
 * Output for the back-end favicon
 *
 * @return void
 */
function quickfavicon_backend_output() {
	$url = wp_get_attachment_url(get_option('quickfavicon_backend_icon_id'));
	if ($url != '')
		echo '<link rel="icon" href="'.$url.'" /><link rel="shortcut icon" href="'.$url.'" />';
}

/**
 * Output for the appletouch icon
 *
 * @return void
 */
function quickfavicon_appletouch_output() {
	$url = wp_get_attachment_url(get_option('quickfavicon_appletouch_icon_id'));
	if ($url != '')
		echo '<link rel="apple-touch-icon" sizes="180x180" href="'.$url.'" />';
}

/**
 * Add a dashboard page and menu item and initialize settings registration
 *
 * @return void
 */
function quickfavicon_create_menu() {
	add_menu_page('Easy Favicon', 'Favicon', 'administrator', __FILE__, 'quickfavicon_settings_page', 'dashicons-info');
	add_action('admin_init', 'quickfavicon_settings');
}

/**
 * Register the settings
 *
 * @return void
 */
function quickfavicon_settings() {
	register_setting('quickfavicon-settings-group', 'quickfavicon_frontend_icon_id');
	register_setting('quickfavicon-settings-group', 'quickfavicon_backend_icon_id');
	register_setting('quickfavicon-settings-group', 'quickfavicon_appletouch_icon_id');
}

/**
 * Build the settings page
 *
 * @return void
 */
function quickfavicon_settings_page() {
	wp_enqueue_media();
	$plugin_data = get_plugin_data( __FILE__ );
	$plugin_version = $plugin_data['Version'];
?>
<div class="wrap">
<h2>Favicon Settings <span class="pull-right text-muted"><small>Easy Favicon (<?php echo $plugin_version; ?>) by Robert Cummings</small></span></h2>

<form method="post" action="options.php">
  <?php settings_fields( 'quickfavicon-settings-group' ); ?>
  <?php do_settings_sections( 'quickfavicon-settings-group' ); ?>

	<div role="tabpanel">

	  <!-- Nav tabs -->
	  <ul class="nav nav-tabs" role="tablist">
	    <li role="presentation" class="active"><a href="#frontend" aria-controls="frontend" role="tab" data-toggle="tab">Front-End</a></li>
	    <li role="presentation"><a href="#backend" aria-controls="backend" role="tab" data-toggle="tab">Back-End</a></li>
	    <li role="presentation"><a href="#appletouch" aria-controls="appletouch" role="tab" data-toggle="tab">AppleTouch</a></li>
	  </ul>

	  <!-- Tab panes -->
	  <div class="tab-content favicon-tabs">
	    <div role="tabpanel" class="tab-pane active" id="frontend">
				<h3>Front-End Favicon</h3>
				<div class="alert alert-info" role="alert"><span class="glyphicon glyphicon-info-sign"></span> This is the icon that appears when you view the site's front-end (what your visitors see). Favicons may be either <mark>.png</mark> or <mark>.ico</mark> format and must be <mark>exactly 16 x 16 pixels</mark> to show up correctly.</div>
				<div id="frontend_image" class="icon-image" style="margin-bottom:10px;"><?php if (get_option('quickfavicon_frontend_icon_id') != '') echo '<img src="'.wp_get_attachment_url(get_option('quickfavicon_frontend_icon_id')).'" height="16" width="16" />' ; ?></div>
				<input type="hidden" id="quickfavicon_frontend_uploaded_image" class="hidden-id-field" name="quickfavicon_frontend_icon_id" value="<?php if (get_option('quickfavicon_frontend_icon_id') != '') echo get_option('quickfavicon_frontend_icon_id'); ?>" />
				<button type="button" id="quickfavicon-frontend-upload" class="icon-upload btn btn-default"><?php echo (get_option('quickfavicon_frontend_icon_id') == '' ? 'Add Icon':'Replace Icon'); ?></button>
				<button type="button" id="quickfavicon-frontend-remove" class="btn btn-default">Remove Icon</button>
			</div>
	    <div role="tabpanel" class="tab-pane" id="backend">
				<h3>Back-End Favicon</h3>
				<div class="alert alert-info" role="alert"><span class="glyphicon glyphicon-info-sign"></span> This is the icon that appears when you view the site's dashboard. Favicons may be either <mark>.png</mark> or <mark>.ico</mark> format and must be <mark>exactly 16 x 16 pixels</mark> to show up correctly.</div>
				<div id="backend_image" class="icon-image" style="margin-bottom:10px;"><?php if (get_option('quickfavicon_backend_icon_id') != '') echo '<img src="'.wp_get_attachment_url(get_option('quickfavicon_backend_icon_id')).'" height="16" width="16" />' ; ?></div>
				<input type="hidden" id="quickfavicon_backend_uploaded_image" class="hidden-id-field" name="quickfavicon_backend_icon_id" value="<?php if (get_option('quickfavicon_backend_icon_id') != '') echo get_option('quickfavicon_backend_icon_id'); ?>" />
				<button type="button" id="quickfavicon-backend-upload" class="icon-upload btn btn-default"><?php echo (get_option('quickfavicon_backend_icon_id') == '' ? 'Add Icon':'Replace Icon'); ?></button>
				<button type="button" id="quickfavicon-backend-remove" class="btn btn-default">Remove Icon</button>
			</div>
	    <div role="tabpanel" class="tab-pane" id="appletouch">
				<h3>AppleTouch Icon</h3>
				<div class="alert alert-info" role="alert"><span class="glyphicon glyphicon-info-sign"></span> AppleTouch Icons are seen when a user adds a shortcut to your site on their Home screen. AppleTouch Icons should be <mark>.png</mark> format and be <mark>180 x 180 pixels</mark> to show up optimally.</div>
				<div id="appletouch_image" class="icon-image" style="margin-bottom:10px;"><?php if (get_option('quickfavicon_appletouch_icon_id') != '') echo '<img src="'.wp_get_attachment_url(get_option('quickfavicon_appletouch_icon_id')).'" height="16" width="16" />' ; ?></div>
				<input type="hidden" id="quickfavicon_appletouch_uploaded_image" class="hidden-id-field" name="quickfavicon_appletouch_icon_id" value="<?php if (get_option('quickfavicon_appletouch_icon_id') != '') echo get_option('quickfavicon_appletouch_icon_id'); ?>" />
				<button type="button" id="quickfavicon-appletouch-upload" class="icon-upload btn btn-default"><?php echo (get_option('quickfavicon_appletouch_icon_id') == '' ? 'Add Icon':'Replace Icon'); ?></button>
				<button type="button" id="quickfavicon-appletouch-remove" class="btn btn-default">Remove Icon</button>
			</div>
	  </div>

	</div>

	<?php submit_button(); ?>

</form>
</div>
<?php
}
?>
