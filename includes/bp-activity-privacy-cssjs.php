<?php
/**
 * BP Activity Privacy Css and js enqueue  
 *
 * @package BP-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * bp_activity_privacy_add_js
 * @return [type] [description]
 */
function bp_activity_privacy_add_js() {
	global $bp;
	// load the script after handles : bp-legacy-js || bp-parent-js || bp-child-js || bp-js || dtheme-ajax-js :( ???
	//wp_enqueue_script( 'bp-activity-privacy-js', plugins_url( 'js/general.js' ,  __FILE__ ), array('jquery','dtheme-ajax-js') );
	wp_enqueue_script('jquery');

	//wp_enqueue_script( 'jquery-jui-dropdown-js', plugins_url( 'js/jdropdown.js' ,  __FILE__ ), array('jquery'), false, true );

	//load the script at the footer :P
	//wp_enqueue_script( 'bp-activity-privacy-js', plugins_url( 'js/bp-activity-privacy.js' ,  __FILE__ ), array('jquery','jquery-jui-dropdown-js'), false, true );
	wp_enqueue_script( 'bp-activity-privacy-js', plugins_url( 'js/bp-activity-privacy.js' ,  __FILE__ ), array('jquery'), false, true );

	$visibility_levels = array(
	    'profil' => bp_get_profile_activity_visibility(),
	    'groups' => bp_get_groups_activity_visibility()
    );

	wp_localize_script( 'bp-activity-privacy-js', 'visibility_levels', $visibility_levels );

}
add_action( 'wp_enqueue_scripts', 'bp_activity_privacy_add_js', 1 );



/**
 * bp_activity_privacy_add_css
 * @return [type] [description]
 */
function bp_activity_privacy_add_css() {
	global $bp;
    wp_enqueue_style( 'bp-font-awesome-css', plugins_url( 'css/font-awesome/css/font-awesome.min.css' ,  __FILE__ )); 
    wp_enqueue_style( 'bp-activity-privacy-css', plugins_url( 'css/bp-activity-privacy.css' ,  __FILE__ )); 
}
add_action( 'bp_actions', 'bp_activity_privacy_add_css', 1 );




