<?php
/**
 * Buddypress Activity Privacy actions
 *
 * @package BP-Activity-Privacy
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Add visibility level to user activity meta
 * @param  [type] $content     [description]
 * @param  [type] $user_id     [description]
 * @param  [type] $activity_id [description]
 * @return [type]              [description]
 */
function bp_add_visibility_to_activity( $content, $user_id, $activity_id ) {
    $visibility = 'public';
    
    /*
    if ( !empty( $_POST['cookie'] ) )
        $_BP_COOKIE = wp_parse_args( str_replace( '; ', '&', urldecode( $_POST['cookie'] ) ) );
    else
        $_BP_COOKIE = &$_COOKIE;
    
    $visibility = $_BP_COOKIE['bp-visibility'];
    */

    $levels = bp_get_profile_activity_privacy_levels();
    $levels += bp_get_groups_activity_privacy_levels();

    if( isset( $_POST['visibility'] ) || in_array( $_POST['visibility'], $levels ) )
        $visibility = $_POST['visibility'];
    
    bp_activity_update_meta( $activity_id, 'activity-privacy', $visibility );
}
add_action( 'bp_activity_posted_update', 'bp_add_visibility_to_activity', 10, 3 );

/**
 * Add visibility level to group activity meta
 * @param  [type] $content     [description]
 * @param  [type] $user_id     [description]
 * @param  [type] $group_id    [description]
 * @param  [type] $activity_id [description]
 * @return [type]              [description]
 */
function bp_add_visibility_to_group_activity( $content, $user_id, $group_id, $activity_id ) {
    $visibility = 'public';

    $levels = bp_get_groups_activity_privacy_levels();

    if( isset( $_POST['visibility'] ) || in_array( $_POST['visibility'], $levels ) )
        $visibility = $_POST['visibility'];
    
    bp_activity_update_meta( $activity_id, 'activity-privacy', $visibility );
}
add_action( 'bp_groups_posted_update', 'bp_add_visibility_to_group_activity', 10, 4 );

/**
 * Return Html Select box for activity privacy UI
 * @return [type] [description]
 */
function bp_add_activitiy_visibility_selectbox() {
	echo '<span name="activity-visibility" id="activity-visibility">';
	_e( 'Privacy: ', 'bp-activity-privacy' );
	if ( bp_is_group_home() )
		bp_groups_activity_visibility();
	else 
		bp_profile_activity_visibility();
	echo '</span>';
}
add_action('bp_activity_post_form_options','bp_add_activitiy_visibility_selectbox');