<?php
/**
 * BP Activity Privacy Css and js enqueue  
 *
 * @package BP-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Load js files
 */
function bp_activity_privacy_add_js() {
	global $bp;
	// load the script after handles : bp-legacy-js || bp-parent-js || bp-child-js || bp-js || dtheme-ajax-js :( ???
	//wp_enqueue_script( 'bp-activity-privacy-js', plugins_url( 'js/general.js' ,  __FILE__ ), array('jquery','dtheme-ajax-js') );

	//wp_enqueue_script( 'jquery-jui-dropdown-js', plugins_url( 'js/jdropdown.js' ,  __FILE__ ), array('jquery'), false, true );

	//load the script at the footer
	//wp_enqueue_script( 'bp-activity-privacy-js', plugins_url( 'js/bp-activity-privacy.js' ,  __FILE__ ), array('jquery','jquery-jui-dropdown-js'), false, true );

	//wp_enqueue_script( 'bp-activity-privacy-js', plugins_url( 'js/bp-activity-privacy.js' ,  __FILE__ ), array('jquery'), false, true );
	// remove jquery from deps , it's should be loaded by default or by theme from CDN
	wp_enqueue_script( 'bp-activity-privacy-js', plugins_url( 'js/bp-activity-privacy.js' ,  __FILE__ ), array(), false, true );
	
	$visibility_levels = array(
	    'profil' => bp_get_profile_activity_visibility(),
	    'groups' => bp_get_groups_activity_visibility()
    );

	wp_localize_script( 'bp-activity-privacy-js', 'visibility_levels', $visibility_levels );

}
add_action( 'wp_enqueue_scripts', 'bp_activity_privacy_add_js', 1 );

/**
 * Load css files
 */
function bp_activity_privacy_add_css() {
	// global $wp_styles;

	// $srcs = array_map('basename', (array) wp_list_pluck($wp_styles->registered, 'src') );
	// if ( !in_array('font-awesome.css', $srcs) && !in_array('font-awesome.min.css', $srcs)  ) {
    wp_enqueue_style( 'bp-font-awesome-css', plugins_url( 'css/font-awesome/css/font-awesome.min.css' ,  __FILE__ )); 
	// }
    wp_enqueue_style( 'bp-activity-privacy-css', plugins_url( 'css/bp-activity-privacy.css' ,  __FILE__ )); 
}
add_action( 'bp_actions', 'bp_activity_privacy_add_css', 1 );