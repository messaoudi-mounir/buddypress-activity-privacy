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

	if( bp_ap_is_use_custom_styled_selectbox() ) {
		wp_enqueue_script( 'jq-customselect-js', plugins_url( 'js/jquery.customSelect.js' ,  __FILE__ ), array(), false, true );
	}

	wp_enqueue_script( 'bp-activity-privacy-js', plugins_url( 'js/bp-activity-privacy.js' ,  __FILE__ ), array(), false, true );
	
	$visibility_levels = array(
		'custom_selectbox' => bp_ap_is_use_custom_styled_selectbox(),
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
   	if( bp_ap_is_use_fontawsome() ) {
    	wp_enqueue_style( 'bp-activity-privacy-font-awesome-css', plugins_url( 'css/font-awesome/css/font-awesome.min.css' ,  __FILE__ )); 
    	wp_enqueue_style( 'bp-activity-privacy-css', plugins_url( 'css/bp-activity-privacy.css' ,  __FILE__ )); 
	}

	if( !bp_ap_show_privacy_levels_label() ){
	   	$hide_privacy_label_css = ".customSelectInner { display: none !important; }";
        wp_add_inline_style( 'bp-activity-privacy-css', $hide_privacy_label_css );
	}

}
add_action( 'bp_actions', 'bp_activity_privacy_add_css', 1 );