<?php
/*
Plugin Name: BuddyPress Activity Privacy
Plugin URI: 
Description: BP Activity Privacy add the ability for members to choose who can read his activity before it posted !
Version: 1.0.4.3
Requires at least:  WP 3.4, BuddyPress 1.5
Tested up to: BuddyPress 1.5, 1.7, 1.7.1
License: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: Meg@Info
Author URI: http://profiles.wordpress.org/megainfo 
Network: true
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*************************************************************************************************************
 --- BuddyPress Activity Privacy 1.0.4.3 ---
 *************************************************************************************************************/

// Define a constant that can be checked to see if the component is installed or not.
define( 'BP_ACTIVITY_PRIVACY_IS_INSTALLED', 1 );

// Define a constant that will hold the current version number of the component
// This can be useful if you need to run update scripts or do compatibility checks in the future
define( 'BP_ACTIVITY_PRIVACY_VERSION', '1.0.4.3' );

// Define a constant that we can use to construct file paths throughout the component
define( 'BP_ACTIVITY_PRIVACY_PLUGIN_DIR', dirname( __FILE__ ) );

//define ( 'BP_ACTIVITY_PRIVACY_DB_VERSION', '1.0' );

/* Only load the component if BuddyPress is loaded and initialized. */
function bp_activity_privacy_init() {
	// Because our loader file uses BP_Component, it requires BP 1.5 or greater.
	//if ( version_compare( BP_VERSION, '1.3', '>' ) )
	require( dirname( __FILE__ ) . '/includes/bp-activity-privacy-loader.php' );
}
add_action( 'bp_include', 'bp_activity_privacy_init' );

/* Put setup procedures to be run when the plugin is activated in the following function */
function bp_activity_privacy_activate() {
	global $bp;

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active( 'buddypress/bp-loader.php' ) ) {
		//deactivate_plugins( basename( __FILE__ ) ); // Deactivate this plugin
		die( _e( 'You cannot enable BuddyPress Activity Privacy because <strong>BuddyPress</strong> is not active. Please install and activate BuddyPress before trying to activate Buddypress Activity Privacy again.' , 'bp-activity-privacy' ) );
	}	
}
register_activation_hook( __FILE__, 'bp_activity_privacy_activate' );

/* On deacativation, clean up anything your component has added. */
function bp_activity_privacy_deactivate() {
	/* You might want to delete any options or tables that your component created. */
}
register_deactivation_hook( __FILE__, 'bp_activity_privacy_deactivate' );