<?php
/**
 * BP Activity Privacy Integrations with others plugins 
 *  
 * @package BP-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Integration with Buddypress Followers
if( function_exists('bp_follow_is_following') ) {

	add_filter('bp_more_visibility_activity_filter', 'bp_follow_visibility_activity', 10, 3);
	function bp_follow_visibility_activity($remove_from_stream, $visibility, $activity){
		$bp_loggedin_user_id = bp_loggedin_user_id();

		switch ($visibility) {
			case 'followers':
				$args = array(
					'leader_id'   => $activity->user_id,
					'follower_id' => $bp_loggedin_user_id
				);
				$is_following = bp_follow_is_following($args);

				if( !$is_following ) 
					$remove_from_stream = true;
				break;
			case 'groupfollowers' :
				$args = array(
					'leader_id'   => $activity->user_id,
					'follower_id' => $bp_loggedin_user_id
				);
				$is_following = bp_follow_is_following($args);

				$group_is_user_member = groups_is_user_member( $bp_loggedin_user_id, $activity->item_id );

	            if( !$is_following || !$group_is_user_member)
	                $remove_from_stream = true;

				# code...
			default:
				# code...
				break;
		}		



		return $remove_from_stream;
	}


	add_filter('bp_profile_activity_privacy_levels_filter', 'bp_get_profile__follow_activity_privacy_levels', 10, 1);
	function bp_get_profile__follow_activity_privacy_levels($profile_activity_privacy_levels){
		$profile_activity_privacy_levels [] = 'followers';

		return $profile_activity_privacy_levels;
	}

	add_filter('bp_groups_activity_privacy_levels_filter', 'bp_get_profile__follow_groups_privacy_levels', 10, 1);
	function bp_get_profile__follow_groups_privacy_levels($groups_activity_privacy_levels){
		$groups_activity_privacy_levels [] = 'followers';
		//followers in the group
		$groups_activity_privacy_levels [] = 'groupfollowers';

		return $groups_activity_privacy_levels;
	}

	add_filter('bp_profile_activity_visibility_levels_filter', 'bp_get_profile_follow_activity_visibility_levels', 10, 1);
	function bp_get_profile_follow_activity_visibility_levels($profile_activity_visibility_levels){
		$profile_activity_visibility_levels ['follow'] = array(
		        'id'      => 'followers',
		        'label'   => __( 'My Followers', 'bp-activity-privacy' ),
		        'default' => false,
		        'position' => 30
		);

		return $profile_activity_visibility_levels;
	}

	add_filter('bp_groups_activity_visibility_levels_filter', 'bp_get_groups_follow_activity_visibility_levels', 10, 1);
	function bp_get_groups_follow_activity_visibility_levels($groups_activity_visibility_levels){
		$groups_activity_visibility_levels ['followers'] = array(
		        'id'      => 'followers',
		        'label'   => __( 'My Followers', 'bp-activity-privacy' ),
		        'default' => false,
		        'position' => 35
		);
		$groups_activity_visibility_levels ['groupfollowers'] = array(
		        'id'      => 'groupfollowers',
		        'label'   => __( 'My Followers in Group', 'bp-activity-privacy' ),
		        'default' => false,
		        'position' => 45
		);

		return $groups_activity_visibility_levels;
	}
}

// Fix/Integration with Buddypress Activity Plus
if( function_exists('bpfb_plugin_init') ) {

	add_action( 'wp_footer', 'bp_activity_privacy_fix_bp_activity_plus' );
	function bp_activity_privacy_fix_bp_activity_plus() {
	?>
	<script type="text/javascript">

	if ( typeof jq == "undefined" )
		var jq = jQuery;

	jq(document).ready( function() {

		form = jq("#whats-new-form");
		text = form.find('textarea[name="whats-new"]');

		//remove event handler previously attached to #bpfb_submit
		jq("#bpfb_submit").die( "click" );

		jq(document).delegate("#bpfb_submit", 'click', function (event) {

			event.preventDefault();
			var params = _bpfbActiveHandler.get();
			var group_id = jq('#whats-new-post-in').length ?jq('#whats-new-post-in').val() : 0;
			
			jq.post(ajaxurl, {
				"action": "bpfb_update_activity_contents", 
				"data": params, 
				// add visibility level to the ajax post
				"visibility" : jq("select#activity-privacy").val(),
				"content": text.val(), 
				"group_id": group_id
			}, function (data) {
				_bpfbActiveHandler.destroy();
				text.val('');
				jq('#activity-stream').prepend(data.activity);
				/**
				 * Handle image scaling in previews.
				 */
				jq(".bpfb_final_link img").each(function () {
					jq(this).width(jq(this).parents('div').width());
				});

				//reset the privacy selection
				jq("select#activity-privacy option[selected]").prop('selected', true).trigger('change');
				<?php if( bp_ap_is_use_custom_styled_selectbox() ) { ?>
				jq('select.bp-ap-selectbox').customStyle('2');
				<?php } ?>
			});
		});
	});

	</script>
	<?php 
	}
	
}

// Integration with rTmedia
if( function_exists('rtmedia_autoloader') ) { 

	/**
	 * Check the privacy for each medias and remove 
	 * the not authorized medias from media array
	 */
	function bp_ap_rtmedia(){
		global $rtmedia_query;
		have_rtmedia ();

	    $is_super_admin = is_super_admin();
	    $bp_displayed_user_id = bp_displayed_user_id();
	    $bp_loggedin_user_id = bp_loggedin_user_id();

	    if ( ( !empty($bp_displayed_user_id)  && $bp_displayed_user_id == $bp_loggedin_user_id ) ||  $is_super_admin )
	    	return;

	    $count_removed_media = 0;
	    if (!empty($rtmedia_query->media)) {
			foreach ($rtmedia_query->media as $key => $media) {

		        $activities = bp_activity_get_specific( array( 'activity_ids' => $media->activity_id ) );
		        $activity = $activities["activities"][0];
		        
		        $remove_from_stream = bp_visibility_is_activity_invisible( $activity, $bp_loggedin_user_id, $is_super_admin, $bp_displayed_user_id );
		        if ($remove_from_stream) {
		        	unset($rtmedia_query->media[$key]);
		            $count_removed_media++;
		        }   
			}
			//rearrange array keys
			$rtmedia_query->media = array_values($rtmedia_query->media);

			$rtmedia_query->media_count = $rtmedia_query->media_count - $count_removed_media;
	    }
	}
	add_action('rtmedia_before_media', 'bp_ap_rtmedia');
	add_action('rtmedia_after_media_gallery_title', 'bp_ap_rtmedia');

	/**
	 * Update media count user meta each time a user 
	 * visit the profile page.
	 */
	function bp_ap_rtmedia_update_member_medias_count(){
		global $bp, $wpdb;

	    $is_super_admin = is_super_admin();
	    $bp_displayed_user_id = bp_displayed_user_id();
	    $bp_loggedin_user_id = bp_loggedin_user_id();
	    //if ($bp_displayed_user_id == $bp_loggedin_user_id)
	    //	return;

	    global $rtmedia;

	    $media_model = new RTMediaModel();


	    $allowed_media_types = array();
		foreach ( $rtmedia->allowed_types as $value ) {
			$allowed_media_types[ ] = $value['name'];
		}
		$allowed_media_types = implode("','", $allowed_media_types);
		$allowed_media_types = "'".$allowed_media_types."'";


	    $table_name = $media_model->table_name;

	    $r = $wpdb->get_results( $wpdb->prepare( "SELECT activity_id, media_type 
	    	                       from {$table_name} 
	    	                      where media_type IN ({$allowed_media_types}) 
	    	                        and context = 'profile'
	    	                        and media_author = %d   
	        	                    and blog_id = %d", $bp_displayed_user_id, get_current_blog_id() ) );


	    $removed_media_count = array();
	    foreach ( $r as $my_r ) {
	   		$activities = bp_activity_get_specific( array( 'activity_ids' => $my_r->activity_id ) );
	        $activity = $activities["activities"][0];

	    	$remove_from_stream = bp_visibility_is_activity_invisible( $activity, $bp_loggedin_user_id, $is_super_admin, $bp_displayed_user_id );
	        
	        if ($remove_from_stream) {
	        	if(!isset($removed_media_count[$my_r->media_type]))
	        		$removed_media_count[$my_r->media_type] = 0;

	        	$removed_media_count[$my_r->media_type]++;
	        }
	  
	    }

		$rtMediaNav = new RTMediaNav();
	    $rtMediaNav->refresh_counts( $bp_displayed_user_id, array( "context" => 'profile', 'media_author' => $bp_displayed_user_id )  );
		$media_count = $rtMediaNav->get_counts( $bp_displayed_user_id );

		$count = array();
		$count[0] = new stdClass();
		if ( !empty($media_count) ){
			// merga and sum all media by privacy
			foreach ($media_count as $media) {
				foreach ($media as $key => $value) {
					if ( !isset($count[0]->{$key}) ) 
						$count[0]->{$key} = 0;

					$count[0]->{$key} += $value;
				}
			}
			$media_count = $count;
		}

		$total = null;
		if ( !empty($removed_media_count) && !empty($media_count) ) {
			$total = 0;
			foreach ($removed_media_count as $key => $value) {
				$media_count[0]->{$key} -= $value;
				$total += $media_count[0]->{$key};
			}
		}

		if ( isset($total) ) {
			$slug = apply_filters('rtmedia_media_tab_slug', RTMEDIA_MEDIA_SLUG );

			foreach ($bp->bp_nav as $key => $value) {
				if ($value['slug'] == $slug) {
					$bp->bp_nav[$key]['name'] = RTMEDIA_MEDIA_LABEL . '<span>' . $total . '</span>';
					break;
				}
			}
		}

		// update metadata
		update_user_meta ( $bp_displayed_user_id, 'rtmedia_counts_' . get_current_blog_id(), $media_count );
	}
	add_action('bp_after_member_header', 'bp_ap_rtmedia_update_member_medias_count');


	/**
	 * clear the media count user meta to force recomputing 
	 * it after each visit of profile page
	 */
	function bp_ap_rtmedia_reset_member_medias_count(){
		global $bp;
		$bp_displayed_user_id = bp_displayed_user_id();
		update_user_meta ( $bp_displayed_user_id, 'rtmedia_counts_' . get_current_blog_id(), null );
	}
	add_action('bp_after_member_body', 'bp_ap_rtmedia_reset_member_medias_count');
	//add_action('rtmedia_after_media_gallery', 'bp_activity_privacy_rtmedia_reset_count');


	/**
	 * Update media count user meta each time a user 
	 * visit the profile page.
	 */
	function bp_ap_rtmedia_update_group_medias_count(){
		global $bp, $wpdb;

	    $is_super_admin = is_super_admin();
	    $bp_group_id = bp_get_group_id();
	    $bp_loggedin_user_id = bp_loggedin_user_id();
	    //if ($bp_displayed_user_id == $bp_loggedin_user_id)
	    //	return;
	   
	    global $rtmedia, $rtmedia_query;

	    $allowed_media_types = array();
		foreach ( $rtmedia->allowed_types as $value ) {
			$allowed_media_types[ ] = $value['name'];
		}
		$allowed_media_types = implode("','", $allowed_media_types);
		$allowed_media_types = "'".$allowed_media_types."'";

	    $table_name = $rtmedia_query->model->table_name;

	    $r = $wpdb->get_results( $wpdb->prepare( "SELECT activity_id, media_type 
	    	                       from {$table_name} 
	    	                      where media_type IN ({$allowed_media_types}) 
	    	                        and context = 'group' 
	    	                        and context_id = %d  
	    	                        and blog_id = %d", $bp_group_id, get_current_blog_id() ) );

	    $removed_media_count = array();
	    foreach ( $r as $my_r ) {
	   		$activities = bp_activity_get_specific( array( 'activity_ids' => $my_r->activity_id ) );
	        $activity = $activities["activities"][0];

	    	$remove_from_stream = bp_visibility_is_activity_invisible( $activity, $bp_loggedin_user_id, $is_super_admin, $bp_displayed_user_id );
	        
	        if ($remove_from_stream) {
	        	$removed_media_count[$my_r->media_type]++;
	        }

	    }

		$rtMediaNav = new RTMediaNav();
	    $rtMediaNav->refresh_counts( $bp_group_id, array( "context" => 'group', 'context_id' => $bp_group_id ) );
		$media_count = $rtMediaNav->get_counts( $bp_group_id, array( "context" => 'group', 'context_id' => $bp_group_id ) );

		$count = array();
		$count[0] = new stdClass();
		if ( !empty($media_count) ){
			// merga and sum all media by privacy
			foreach ($media_count as $media) {
				foreach ($media as $key => $value) {
					if ( !isset($count[0]->{$key}) ) 
						$count[0]->{$key} = 0;

					$count[0]->{$key} += $value;
				}
			}
			$media_count = $count;
		}

		$total = 0;
		if ( !empty($removed_media_count) && !empty($media_count) ) {
			foreach ($removed_media_count as $key => $value) {
				$media_count[0]->{$key} -= $value;
				$total += $media_count[0]->{$key};
			}

			$slug = apply_filters('rtmedia_media_tab_slug', RTMEDIA_MEDIA_SLUG );
			$nav = reset($bp->bp_options_nav);
			$kkey = key($bp->bp_options_nav);
			foreach ($nav as $key => $value) {
				if ($value['slug'] == $slug)  {
					$bp->bp_options_nav[$kkey][$key]['name'] = RTMEDIA_MEDIA_LABEL . '<span>' . $total . '</span>';
					break;
				}
			}

		}

		// update metadata
		groups_update_groupmeta ( $bp_group_id, 'rtmedia_counts_' . get_current_blog_id(), $media_count );

	}
	add_action('bp_after_group_header', 'bp_ap_rtmedia_update_group_medias_count');



	/**
	 * clear the media count user meta to force recomputing 
	 * it after each visit of profile page
	 */
	function bp_ap_rtmedia_reset_group_medias_count(){
		global $bp, $groups_template;

		$bp_group_id = bp_get_group_id();
		if ( !isset($bp_group_id) ) {
			$bp_group_id = $groups_template->group->group_id;
		}
		//  bp_get_group_id() return null in media tab ( $groups_template->group is  stdClass on bp_after_group_body)
		
		groups_update_groupmeta ( $bp_group_id, 'rtmedia_counts_' . get_current_blog_id(), null );

	}
	add_action('bp_after_group_body', 'bp_ap_rtmedia_reset_group_medias_count');



	function bp_ap_rtmedia_add_edit_fields(){
		global $bp, $rtmedia_query, $rtmedia_media;

		if ( isset($rtmedia_media) ) {
			$activity_id = $rtmedia_media->activity_id;
			/*
			if ( isset( $rtmedia_query->query[ 'context' ] ) && $rtmedia_query->query[ 'context' ] == 'group' ){
				//if context is group i.e editing a group media, dont show the privacy dropdown
				// group media
			} else {
				// profile media
			}
			*/

	        $visibility = bp_activity_get_meta( $activity_id, 'activity-privacy' );

	        global $bp_activity_privacy;

	        if ($rtmedia_media->context == 'group')
	        	$group_id = $rtmedia_media->context_id;
	        else 
	        	$group_id = null;

	        //if is not a group activity or a new blog post
	        if( !isset( $group_id ) )
	            $visibility_levels = bp_get_profile_activity_visibility_levels();   
	        else
	            $visibility_levels = bp_get_groups_activity_visibility_levels();
	        
	        //sort visibility_levels by position 
	        uasort ($visibility_levels, 'bp_activity_privacy_cmp_position');

	        $html = '<select class="bp-ap-media-selectbox" name="visibility" >';
	        foreach ($visibility_levels as $visibility_level) {
	            if( $visibility_level["disabled"] )
	                continue;
	            $html .= '<option class="" ' . ( $visibility_level['id'] == $visibility ? " selected='selected'" : '' ) . ' value="' . $visibility_level["id"] . '">' . $visibility_level["label"] . '</option>';
	        }
	        $html .= '</select>';

	        $html = apply_filters( 'bp_get_update_activitiy_visibility_selectbox', $html );

			echo "<div class=''><label for='privacy'>" . __( 'Privacy : ', 'rtmedia' ) . "</label>   " . $html . "  </div>";

		}
	}
	add_action('rtmedia_add_edit_fields', 'bp_ap_rtmedia_add_edit_fields');

	/** 
	 * Update the privacy value of the activity related to the media
	 */
	function bp_ap_rtmedia_after_update_media( $media_id ){
	  if ( isset($media_id) ){
	  	global $bp, $wpdb, $rtmedia, $rtmedia_query;

	  	// get the activity id related to the media
	    $table_name = $rtmedia_query->model->table_name;
	    $activity_id = $wpdb->get_var( $wpdb->prepare( "SELECT activity_id 
	    	                       from {$table_name} 
	    	                      where id = %d", $media_id ) );

	    $visibility = esc_attr($_POST['visibility']);
	    bp_activity_update_meta( $activity_id, 'activity-privacy', $visibility );

	  }	

	}
	add_action('rtmedia_after_update_media', 'bp_ap_rtmedia_after_update_media');
}