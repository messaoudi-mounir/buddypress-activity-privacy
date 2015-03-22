<?php
/**
 * BP Activity Privacy Filters
 *
 * @package BP-Activity-Privacy
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


 /* Check if the loggedin member can view the activity
 * @param  [Activity] $activity          [description]
 * @param  [int] $bp_loggedin_user_id [description]
 * @param [boolean]$is_super_admin             [description]
 * @param [int]$bp_displayed_user_id             [description]
 * @return [boolean] 
 */
function bp_visibility_is_activity_invisible( $activity, $bp_loggedin_user_id, $is_super_admin ) {

    if( ($bp_loggedin_user_id == $activity->user_id) || 
        ($is_super_admin &&  bp_ap_is_admin_allowed_to_view_edit_privacy_levels()) 
    ) 
    return false;

    $visibility = bp_activity_get_meta( $activity->id, 'activity-privacy' );


   // echo "---activity_id-'"  .$activity->id . " -----" . $visibility;

    $remove_from_stream = false;

    switch ( $visibility ) {
        //Logged in users
        case 'loggedin' :
            if( !$bp_loggedin_user_id )
                $remove_from_stream = true;
            break;

        //My friends    
        case 'friends' :
            if ( bp_is_active( 'friends' ) ) {
                $is_friend = friends_check_friendship( $bp_loggedin_user_id, $activity->user_id );
                if( !$is_friend )
                    $remove_from_stream = true;
            }
            break;    

        //@Mentioned Only  
        case 'mentionedonly' :
            $usernames = bp_activity_find_mentions( $activity->content );
            $is_mentioned = array_key_exists( $bp_loggedin_user_id,  (array)$usernames );

            if( !$is_mentioned )
                $remove_from_stream = true;
            break;   

        //My friends in the group    
        case 'groupfriends' :
            if ( bp_is_active( 'friends' ) ) {
                $is_friend = friends_check_friendship( $bp_loggedin_user_id, $activity->user_id );
            } else 
                 $is_friend = true;

            if ( bp_is_active( 'groups' ) ) {     
                $group_is_user_member = groups_is_user_member( $bp_loggedin_user_id, $activity->item_id );
            } else 
                return true;

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
            if( $bp_loggedin_user_id != $activity->user_id )
                $remove_from_stream = true;
            break;             

        default:
            //public 
            break;
    }

    // mentioned members can always see the acitivity whatever the privacy level
    if ( $visibility != 'mentionedonly' && $bp_loggedin_user_id && $remove_from_stream ){
        $usernames = bp_activity_find_mentions( $activity->content );
        $is_mentioned = array_key_exists( $bp_loggedin_user_id,  (array)$usernames );
        if( $is_mentioned ) {
            $remove_from_stream = false;
        }
    }
 
    $remove_from_stream = apply_filters( 'bp_more_visibility_activity_filter', $remove_from_stream, $visibility, $activity);

    return $remove_from_stream;
}

/**
 * bp_visibility_activity_filter
 * @param  [type] $a          [description]
 * @param  [type] $activities [description]
 * @return [type]             [description]
 */
function bp_visibility_activity_filter( $has_activities, $activities ) {
    global $bp;
   
    $is_super_admin = is_super_admin();
    $bp_displayed_user_id = bp_displayed_user_id();
    $bp_loggedin_user_id = bp_loggedin_user_id();
    
    foreach ( $activities->activities as $key => $activity ) {

        /*
        if( $bp_loggedin_user_id == $activity->user_id  ) 
            continue;

        $visibility = bp_activity_get_meta( $activity->id, 'activity-privacy' );
        $remove_from_stream = false;
      

        switch ( $visibility ) {
            //Logged in users
            case 'loggedin' :
                if( !$bp_loggedin_user_id )
                    $remove_from_stream = true;
                break;

            //My friends    
            case 'friends' :
                if ( bp_is_active( 'friends' ) ) {
                    $is_friend = friends_check_friendship( $bp_loggedin_user_id, $activity->user_id );
                    if( !$is_friend )
                        $remove_from_stream = true;
                }
                break;    

            //@Mentioned Only  
            case 'mentionedonly' :
                $usernames = bp_activity_find_mentions( $activity->content );
                $is_mentioned = array_key_exists( $bp_loggedin_user_id,  (array)$usernames );

                if( !$is_mentioned )
                    $remove_from_stream = true;
                break;   

            //My friends in the group    
            case 'groupfriends' :
                if ( bp_is_active( 'friends' ) ) {
                    $is_friend = friends_check_friendship( $bp_loggedin_user_id, $activity->user_id );
                } else 
                     $is_friend = true;

                if ( bp_is_active( 'groups' ) ) {     
                    $group_is_user_member = groups_is_user_member( $bp_loggedin_user_id, $activity->item_id );
                } else 
                    return true;

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
                if( $bp_loggedin_user_id != $activity->user_id )
                    $remove_from_stream = true;
                break;             

            default:
                //public 
                break;
        }

        // mentioned members can always see the acitivity whatever the privacy level
        if ( $visibility != 'mentionedonly' && $bp_loggedin_user_id && $remove_from_stream ){
            $usernames = bp_activity_find_mentions( $activity->content );
            $is_mentioned = array_key_exists( $bp_loggedin_user_id,  (array)$usernames );
            if( $is_mentioned ) {
                $remove_from_stream = false;
            }
        }
     
        $remove_from_stream = apply_filters( 'bp_more_visibility_activity_filter', $remove_from_stream, $visibility, $activity);
        */

        $remove_from_stream = bp_visibility_is_activity_invisible( $activity, $bp_loggedin_user_id, $is_super_admin );
        
        if ( $remove_from_stream && isset( $activities->activity_count ) ) {
            $activities->activity_count = $activities->activity_count - 1;
            unset( $activities->activities[$key] );
        }
    }

    $activities_new = array_values( $activities->activities );
    $activities->activities = $activities_new;
    
    return $has_activities;
}
add_action( 'bp_has_activities', 'bp_visibility_activity_filter', 10, 2 );

//add_filter( 'bp_get_last_activity', 'bp_activity_privacy_last_activity', 10, 1);
function bp_activity_privacy_last_activity( $last_activity ){
    if( isset($last_activity) ) {
        $has_activities = false;
        $activities = new stdClass();
        $activities->activities = array();
        $activities->activities[] = $last_activity;
        bp_visibility_activity_filter($has_activities, $activities);

        if ( empty($activities) )
            $last_activity = null;
    }

    return $last_activity;
}

add_filter( 'bp_get_activity_latest_update', 'bp_activity_privacy_latest_update', 10, 1);
function bp_activity_privacy_latest_update( $latest_update ){
    $user_id = bp_displayed_user_id();

    if ( bp_is_user_inactive( $user_id ) )
        return $latest_update;

    if ( !$update = bp_get_user_meta( $user_id, 'bp_latest_update', true ) )
        return $latest_update;

    $activity_id = $update['id'];
    $activity = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );

    // single out the activity
    $activity_single = $activity["activities"][0];

    $has_activities = false;
    $activities = new stdClass();
    $activities->activities = array();
    $activities->activities[] = $activity_single;

    bp_visibility_activity_filter( $has_activities, $activities );

    if ( empty( $activities->activities ) )
        $latest_update = null;

    return $latest_update;
}

// prevent members to see last activity on members loop
add_filter('bp_get_member_latest_update', 'bp_activity_privacy_member_latest_update',10, 1);
function bp_activity_privacy_member_latest_update( $update_content ){
    $is_super_admin = is_super_admin();
    if( $is_super_admin && bp_ap_is_admin_allowed_to_view_edit_privacy_levels() )
        return $update_content;

    global $members_template;
    $latest_update = bp_get_user_meta( bp_get_member_user_id(), 'bp_latest_update' , true );
    if ( !empty( $latest_update ) ) {
        $activity_id = $latest_update['id'];
        $activities = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );

        // single out the activity
        $activity = $activities["activities"][0];

        /*
        $has_activities = false;
        $activities = new stdClass();
        $activities->activities = array();
        $activities->activities[] = $activity;

        bp_visibility_activity_filter( $has_activities, $activities );
        if ( empty( $activities->activities ) )
         return '';
        */

        $bp_displayed_user_id = bp_displayed_user_id();
        $bp_loggedin_user_id = bp_loggedin_user_id();
    
        $remove_from_stream = bp_visibility_is_activity_invisible( $activity, $bp_loggedin_user_id, $is_super_admin );
      
        if ($remove_from_stream) 
            return false;
    }

    return $update_content;
}

// prevent members to see last activity on member header page
add_filter('get_user_metadata', 'bp_activity_privacy_latest_user_update',10, 3);
function bp_activity_privacy_latest_user_update( $retval, $object_id, $meta_key ){
    if ($meta_key == 'bp_latest_update') {
        remove_filter('get_user_metadata', 'bp_activity_privacy_latest_user_update');
        
        $is_super_admin = is_super_admin();
        $bp_displayed_user_id = bp_displayed_user_id();
        $bp_loggedin_user_id = bp_loggedin_user_id();

        if($is_super_admin && bp_ap_is_admin_allowed_to_view_edit_privacy_levels())
            return $retval;

         $single = false;
         $retval = get_metadata('user', $object_id, $meta_key,  $single);
         if( isset($retval) && is_array($retval) && !empty( $retval) ) {
            $activity_id = $retval[0]['id'];

            $activities = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );
            $activity = $activities['activities'][0];
            $remove_from_stream = bp_visibility_is_activity_invisible( $activity, $bp_loggedin_user_id, $is_super_admin, $bp_displayed_user_id );
            if ($remove_from_stream) {
                return false;
            }   
         }
         return $retval;
        
    }
}

add_filter('bp_activity_allowed_tags', 'bp_activity_privacy_override_allowed_tags');
function bp_activity_privacy_override_allowed_tags($activity_allowedtags){
    $activity_allowedtags['i']['class'] = array();
    $activity_allowedtags['i']['title'] = array();
    $activity_allowedtags['select']          = array();
    $activity_allowedtags['select']['style'] = array();
    $activity_allowedtags['select']['class'] = array();
    $activity_allowedtags['select']['autocomplete'] = array();
    $activity_allowedtags['option']          = array();
    $activity_allowedtags['option']['value'] = array();
    $activity_allowedtags['option']['selected'] = array();
    $activity_allowedtags['option']['style'] = array();
    $activity_allowedtags['option']['class'] = array();
    $activity_allowedtags['div']          = array();
    $activity_allowedtags['div']['style'] = array();
    $activity_allowedtags['div']['class'] = array();
    $activity_allowedtags['small']          = array();
    $activity_allowedtags['small']['style'] = array();
    
    return $activity_allowedtags;
}


add_filter( 'heartbeat_received', 'bp_activity_heartbeat_last_recorded', 10, 2 );
add_filter( 'heartbeat_nopriv_received', 'bp_activity_heartbeat_last_recorded', 10, 2 );
