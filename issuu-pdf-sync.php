<?php
/*
Plugin Name: Issuu PDF Sync
Plugin URI: http://beapi.fr
Description: Allows to create PDF Flipbooks with the http://issuu.com service.
Version: 3.1.1
Author: Benjamin Niess
Author URI: http://beapi.fr
Text Domain: ips
Domain Path: /languages/

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'IPS_VERSION', '3.1.1' );
define( 'IPS_URL', plugins_url( '', __FILE__ ) );
define( 'IPS_DIR', dirname( __FILE__ ) );

require_once( IPS_DIR . '/classes/main.php');
require_once( IPS_DIR . '/classes/issuu-api.php');
require_once( IPS_DIR . '/classes/shortcodes.php');

if ( is_admin() ) {
	require( IPS_DIR . '/classes/admin/main.php');
}

// Activate Issuu PDF Sync
register_activation_hook( __FILE__, array( 'IPS_Main', 'install' ) );

// Init Issuu PDF Sync
function ips_init() {
	global $ips, $ips_options;

	// Load up the localization file if we're using WordPress in a different language
	// Important: If you want to add you own translation file without having to hack this plugin, put you mo file in wp-content/languages/plugins/ips-xx_XX.mo
	if ( ! load_textdomain( 'ips', trailingslashit( WP_LANG_DIR ) . 'plugins/ips-' . get_locale() . '.mo' ) ) {
		load_plugin_textdomain( 'ips', false, basename( rtrim( dirname( __FILE__ ), '/' ) ) . '/languages' );
	}

	$ips_options = get_option( 'ips_options' );

	new IPS_Main();
	new IPS_Shortcodes();

	// Admin
	if ( class_exists( 'IPS_Admin_Main' ) ) {
		$ips['admin'] = new IPS_Admin_Main();
	}
}

add_action( 'plugins_loaded', 'ips_init' );
