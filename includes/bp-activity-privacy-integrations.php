<?php
/**
 * BP Activity Privacy Integrations with others plugins 
 *  
 * @package BP-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


// Integration of BP Activity Privacy with Buddypress Followers
if(function_exists('bp_follow_is_following')) {

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
		$profile_activity_privacy_levels [] = 'followers';
		//followers in the group
		$profile_activity_privacy_levels [] = 'groupfollowers';

		return $profile_activity_privacy_levels;
	}

	add_filter('bp_profile_activity_visibility_levels_filter', 'bp_get_profile_follow_activity_visibility_levels', 10, 1);
	function bp_get_profile_follow_activity_visibility_levels($profile_activity_visibility_levels){
		$profile_activity_visibility_levels ['follow'] = array(
		        'id'      => 'followers',
		        'label'   => __( 'Followers', 'bp-activity-privacy' ),
		        'default' => false,
		        'position' => 30
		);

		return $profile_activity_visibility_levels;
	}

	add_filter('bp_groups_activity_visibility_levels_filter', 'bp_get_groups_follow_activity_visibility_levels', 10, 1);
	function bp_get_groups_follow_activity_visibility_levels($groups_activity_visibility_levels){
		$groups_activity_visibility_levels ['followers'] = array(
		        'id'      => 'followers',
		        'label'   => __( 'Followers', 'bp-activity-privacy' ),
		        'default' => false,
		        'position' => 30
		);
		$groups_activity_visibility_levels ['groupfollowers'] = array(
		        'id'      => 'groupfollowers',
		        'label'   => __( 'My Followers in group', 'bp-activity-privacy' ),
		        'default' => false,
		        'position' => 31
		);

		return $groups_activity_visibility_levels;
	}
}

// Fix/Integration of BP Activity Privacy with Buddypress Activity Plus
add_action( 'wp_footer', 'bp_activity_privacy_fix_bp_activity_plus' );
function bp_activity_privacy_fix_bp_activity_plus() {
?>
<script type="text/javascript">
(function($){
$(function() {

	$form = $("#whats-new-form");
	$text = $form.find('textarea[name="whats-new"]');

	//remove event handler previously attached to #bpfb_submit
	$("#bpfb_submit").die( "click" );
	$(document).delegate("#bpfb_submit", 'click', function (event) {
		event.preventDefault();
		var params = _bpfbActiveHandler.get();
		var group_id = $('#whats-new-post-in').length ? $('#whats-new-post-in').val() : 0;
		
		$.post(ajaxurl, {
			"action": "bpfb_update_activity_contents", 
			"data": params, 
			// add visibility level to the ajax post
			"visibility" : $("select#activity-privacy").val(),
			"content": $text.val(), 
			"group_id": group_id
		}, function (data) {
			_bpfbActiveHandler.destroy();
			$text.val('');
			$('#activity-stream').prepend(data.activity);
			/**
			 * Handle image scaling in previews.
			 */
			$(".bpfb_final_link img").each(function () {
				$(this).width($(this).parents('div').width());
			});

			//reset the privacy selection
			$("select#activity-privacy option[selected]").prop('selected', true);
		});
	});
});
})(jQuery);
</script>
<?php 
}