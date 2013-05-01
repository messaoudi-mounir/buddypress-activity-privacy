<?php
/**
 * BP Activity Privacy Filters
 *
 * @package BP-Activity-Privacy
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * bp_visibility_activity_filter
 * @param  [type] $a          [description]
 * @param  [type] $activities [description]
 * @return [type]             [description]
 */
function bp_visibility_activity_filter( $a, $activities ) {
    global $bp;

    $is_super_admin = is_super_admin();
    $bp_displayed_user_id = bp_displayed_user_id();
    $bp_loggedin_user_id = bp_loggedin_user_id();
    
    foreach ( $activities->activities as $key => $activity ) {
        if( $bp_loggedin_user_id == $activity->user_id  ) 
            continue;
        $visibility = bp_activity_get_meta( $activity->id, 'activity-privacy' );
        $remove_from_stream = false;

        switch ($visibility) {
            //Logged in users
            case 'loggedin' :
                if( !$bp_loggedin_user_id )
                    $remove_from_stream = true;
                break;

            //My friends    
            case 'friends' :
                $is_friend = friends_check_friendship( $bp_loggedin_user_id, $activity->user_id );
                if( !$is_friend )
                    $remove_from_stream = true;
                break;    

            //My friends in the group    
            case 'groupfriends' :
                $is_friend = friends_check_friendship( $bp_loggedin_user_id, $activity->user_id );
                $group_is_user_member = groups_is_user_member( $bp_loggedin_user_id, $activity->item_id );

                if( !$is_friend || !$group_is_user_member)
                    $remove_from_stream = true;
                break; 

            //Only group members    
            case 'grouponly' :
                $group_is_user_member = groups_is_user_member( $bp_loggedin_user_id, $activity->item_id );
                if( !$group_is_user_member )
                    $remove_from_stream = true;
                break;  

            //Only group moderators    
            case 'groupmoderators' :
                $group_is_user_mod = groups_is_user_mod( $bp_loggedin_user_id, $activity->item_id );
                if( !$group_is_user_mod )
                    $remove_from_stream = true;
                break;  

            //Only group admins    
            case 'groupadmins' :
                $group_is_user_admin = groups_is_user_admin( $bp_loggedin_user_id, $activity->item_id );
                if( !$group_is_user_admin )
                    $remove_from_stream = true;
                break;  

            //Only Admins    
            case 'adminsonly' :
                if( !$is_super_admin )
                    $remove_from_stream = true;
                break;   

            //Only Me    
            case 'onlyme' :
                if( $bp_loggedin_user_id != $activity->user_id  )
                    $remove_from_stream = true;
                break;             

            default:
                //public 
                break;
        }

        $remove_from_stream = apply_filters( 'bp_more_visibility_activity_filter', $remove_from_stream, $visibility, $activity);

        if ( $remove_from_stream ) {
            $activities->activity_count = $activities->activity_count - 1;
            unset( $activities->activities[$key] );
        }
    }

    $activities_new = array_values( $activities->activities );
    $activities->activities = $activities_new;
    
    return $activities;
}
add_action( 'bp_has_activities', 'bp_visibility_activity_filter', 10, 2 );