<?php
/**
 * BP-Activity Privacy loader
 *
 * @package BP-Activity-Privacy
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( file_exists( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/languages/' . get_locale() . '.mo' ) )
	load_textdomain( 'bp-activity-privacy', BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/languages/' . get_locale() . '.mo' );
	

/**
 * BP_Activity_Privacy Class
 */
class BP_Activity_Privacy {

	var $profile_activity_levels = array();
	var $groups_activity_levels = array();

	var $profile_activity_visibility_levels = array();
	var $groups_activity_visibility_levels = array();

	function __construct() {
		global $bp;

		$activity_levels = array('public', 'loggedin', 'friends', 'adminsonly', 'onlyme');
		$groups_activity_levels = array('public', 'loggedin', 'friends', 'groupfriends', 'grouponly', 'groupmoderators', 'groupadmins', 'adminsonly', 'onlyme');
		
		// Register the visibility levels
		$this->profile_activity_visibility_levels  = array(
	        'public' => array(
	            'id'      => 'public',
	            'label'   => __( 'Anyone', 'bp-activity-privacy' ),
	            'default' => true,
	        ),
	        'loggedin' => array(
	            'id'      => 'loggedin',
	            'label'   => __( 'Logged In Users', 'bp-activity-privacy' ),
	            'default' => false,
	        )
	    );

	    if ( bp_is_active( 'friends' ) ) {
	        $this->profile_activity_visibility_levels['friends'] = array(
	            'id'      => 'friends',
	            'label'   => __( 'My Friends', 'bp-activity-privacy' ),
	            'default' => false,
	        );
	    }

	    $this->profile_activity_visibility_levels['adminsonly'] = array(
	        'id'      => 'adminsonly',
	        'label'   => __( 'Admins Only', 'bp-activity-privacy' ),
	        'default' => false,
	    );

	    $this->profile_activity_visibility_levels['onlyme'] = array(
	        'id'      => 'onlyme',
	        'label'   => __( 'Only me', 'bp-activity-privacy' ),
	        'default' => false,
	    );

	    $this->groups_activity_visibility_levels = array(
	        'public' => array(
	            'id'      => 'public',
	            'label'   => __( 'Anyone', 'bp-activity-privacy' ),
	            'default' => true,
	        ),
	        'loggedin' => array(
	            'id'      => 'loggedin',
	            'label'   => __( 'Logged In Users', 'bp-activity-privacy' ),
	            'default' => false,
	        )
	    );

	    if ( bp_is_active( 'friends' ) ) {
	        $this->groups_activity_visibility_levels['friends'] = array(
	            'id'      => 'friends',
	            'label'   => __( 'My Friends', 'bp-activity-privacy' ),
	            'default' => false,
	        );

	        $this->groups_activity_visibility_levels['groupfriends'] = array(
	            'id'      => 'groupfriends',
	            'label'   => __( 'My Friends in Group', 'bp-activity-privacy' ),
	            'default' => false,
	        );
	    }

	    if ( bp_is_active( 'groups' ) ) {
	        $this->groups_activity_visibility_levels['grouponly'] = array(
	            'id'      => 'grouponly',
	            'label'   => __( 'Group Members', 'bp-activity-privacy' ),
	            'default' => false,
	        );

	        $this->groups_activity_visibility_levels['groupmoderators'] = array(
	            'id'      => 'groupmoderators',
	            'label'   => __( 'Group Moderators', 'bp-activity-privacy' ),
	            'default' => false,
	        );

	        $this->groups_activity_visibility_levels['groupadmins'] = array(
	            'id'      => 'groupadmins',
	            'label'   => __( 'Group Admins', 'bp-activity-privacy' ),
	            'default' => false,
	        );
		}	  

		$this->groups_activity_visibility_levels['adminsonly'] = array(
	        'id'      => 'adminsonly',
	        'label'   => __( 'Admins Only', 'bp-activity-privacy' ),
	        'default' => false,
	    );

	    $this->groups_activity_visibility_levels['onlyme'] = array(
	        'id'      => 'onlyme',
	        'label'   => __( 'Only me', 'bp-activity-privacy' ),
	        'default' => false,
	    );      

		 $this->includes();
	}

	function includes() {
		// Files to include
		include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-actions.php' );
		include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-filters.php' );
		include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-template.php' );
		include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-functions.php' );
		include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-cssjs.php' );
		include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-ajax.php' );
	
		// As an follow of how you might do it manually, let's include the functions used
		// on the WordPress Dashboard conditionally:	
		if ( is_admin() || is_network_admin() ) {
			include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-admin.php' );
		}
		
	}
}

function bp_activity_load_core() {
	global $bp, $bp_activity_privacy;

	$bp_activity_privacy = new BP_Activity_Privacy;
	do_action('bp_activity_load_core');
}
add_action( 'bp_loaded', 'bp_activity_load_core', 5 );