<?php
/**
 * BP-Activity Privacy loader
 *
 * @package BP-Activity-Privacy
 */
 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
	
/**
 * BP_Activity_Privacy Class
 */
class BP_Activity_Privacy {
	
	var $profile_activity_privacy_levels = array();
	var $groups_activity_privacy_levels = array();

	var $profile_activity_visibility_levels = array();
	var $groups_activity_visibility_levels = array();

	function __construct() {
		global $bp;

		// Register the visibility levels
		$this->profile_activity_privacy_levels = array(
			'public', 'loggedin', 'adminsonly', 'onlyme'
		);

		$this->groups_activity_privacy_levels = array(
			'public', 'loggedin', 'adminsonly', 'onlyme'
		);

		if ( bp_is_active( 'friends' ) ) {
			$this->profile_activity_privacy_levels [] = 'friends';
			$this->groups_activity_privacy_levels [] = 'friends';
		}

		if ( bp_is_active( 'groups' ) ) {
			$this->groups_activity_privacy_levels [] = 'groupfriends';
			$this->groups_activity_privacy_levels [] = 'grouponly';
			$this->groups_activity_privacy_levels [] = 'groupmoderators';
			$this->groups_activity_privacy_levels [] = 'groupadmins';
		}

		//mentioned
		// https://buddypress.trac.wordpress.org/changeset/7193
		if ( function_exists('bp_activity_do_mentions') ) {
			if ( bp_activity_do_mentions() ) {
				$this->profile_activity_privacy_levels [] = 'mentionedonly';
				$this->groups_activity_privacy_levels [] = 'mentionedonly';			
			}	
		} else {
			//$this->profile_activity_privacy_levels [] = 'mentionedonly';
			//$this->groups_activity_privacy_levels [] = 'mentionedonly';		
		}

		// Register the visibility levels
		$this->profile_activity_visibility_levels  = array(
	        'public' => array(
	            'id'        => 'public',
	            'label'     => __( 'Anyone', 'bp-activity-privacy' ),
	            'default'   => true,
	            'position'  => 10,
	            'disabled'  => false
	        ),
	        'loggedin' => array(
	            'id'        => 'loggedin',
	            'label'     => __( 'Logged In Users', 'bp-activity-privacy' ),
	            'default'   => false,
	            'position'  => 20,
	            'disabled'  => false	            
	        )
	    );

	    if ( bp_is_active( 'friends' ) ) {
	        $this->profile_activity_visibility_levels['friends'] = array(
	            'id'        => 'friends',
	            'label'     => __( 'My Friends', 'bp-activity-privacy' ),
	            'default'   => false,
	            'position'  => 30,
	            'disabled'  => false	            
	        );
	    }

		//mentioned
		// https://buddypress.trac.wordpress.org/changeset/7193
		if ( function_exists('bp_activity_do_mentions') ) {	
			if ( bp_activity_do_mentions() ) {
		        $this->profile_activity_visibility_levels['mentionedonly'] = array(
		            'id'        => 'mentionedonly',
		            'label'     => __( 'Mentioned Only', 'bp-activity-privacy' ),
		            'default'   => false,
		            'position'  => 40,
		            'disabled'  => false	            
		        );			
			}
		}else {
			/*
	        $this->profile_activity_visibility_levels['mentionedonly'] = array(
	            'id'        => 'mentionedonly',
	            'label'     => __( 'Mentioned only', 'bp-activity-privacy' ),
	            'default'   => false,
	            'position'  => 40,
	            'disabled'  => false	            
	        );	*/			
		}
			
	    $this->profile_activity_visibility_levels['adminsonly'] = array(
	        'id'      => 'adminsonly',
	        'label'   => __( 'Admins Only', 'bp-activity-privacy' ),
	        'default' => false,
	        'position'  => 50,
	        'disabled'  => false	        
	    );

	    $this->profile_activity_visibility_levels['onlyme'] = array(
	        'id'        => 'onlyme',
	        'label'     => __( 'Only Me', 'bp-activity-privacy' ),
	        'default'   => false,
	        'position'  => 60,
	        'disabled'  => false	        
	    );

	    $this->groups_activity_visibility_levels = array(
	        'public' => array(
	            'id'        => 'public',
	            'label'     => __( 'Anyone', 'bp-activity-privacy' ),
	            'default'   => true,
	            'position'  => 10,
	            'disabled'  => false           
	        ),
	        'loggedin' => array(
	            'id'         => 'loggedin',
	            'label'      => __( 'Logged In Users', 'bp-activity-privacy' ),
	            'default'    => false,
	             'position'  => 20,
	            'disabled'   => false            
	        )
	    );

	    if ( bp_is_active( 'friends' ) ) {
	        $this->groups_activity_visibility_levels['friends'] = array(
	            'id'        => 'friends',
	            'label'     => __( 'My Friends', 'bp-activity-privacy' ),
	            'default'   => false,
	            'position'  => 30,
	            'disabled'  => false	            
	        );
	        if ( bp_is_active( 'groups' ) ) {
		        $this->groups_activity_visibility_levels['groupfriends'] = array(
		            'id'        => 'groupfriends',
		            'label'     => __( 'My Friends in Group', 'bp-activity-privacy' ),
		            'default'   => false,
		            'position'  => 40,
	            	'disabled'  => false	            
		        );
	   		}
	    }

		//mentioned
		// https://buddypress.trac.wordpress.org/changeset/7193
		if ( function_exists('bp_activity_do_mentions') ) {	
			if ( bp_activity_do_mentions() ) {
		        $this->groups_activity_visibility_levels['mentionedonly'] = array(
		            'id'        => 'mentionedonly',
		            'label'     => __( 'Mentioned Only', 'bp-activity-privacy' ),
		            'default'   => false,
		            'position'  => 50,
		            'disabled'  => false	            
		        );			
			}
		} else {
			/*
	        $this->groups_activity_visibility_levels['mentionedonly'] = array(
	            'id'        => 'mentionedonly',
	            'label'     => __( 'Mentioned only', 'bp-activity-privacy' ),
	            'default'   => false,
	            'position'  => 50,
	            'disabled'  => false	            
	        );	*/			
		}

	    if ( bp_is_active( 'groups' ) ) {
	        $this->groups_activity_visibility_levels['grouponly'] = array(
	            'id'        => 'grouponly',
	            'label'     => __( 'Group Members', 'bp-activity-privacy' ),
	            'default'   => false,
	            'position'  => 60,
	            'disabled'  => false	            
	        );

	        $this->groups_activity_visibility_levels['groupmoderators'] = array(
	            'id'        => 'groupmoderators',
	            'label'     => __( 'Group Moderators', 'bp-activity-privacy' ),
	            'default'   => false,
	            'position'  => 70,
	            'disabled'  => false            
	        );

	        $this->groups_activity_visibility_levels['groupadmins'] = array(
	            'id'        => 'groupadmins',
	            'label'     => __( 'Group Admins', 'bp-activity-privacy' ),
	            'default'   => false,
	            'position'  => 80,
	            'disabled'  => false	            
	        );
		}	  

		$this->groups_activity_visibility_levels['adminsonly'] = array(
	        'id'        => 'adminsonly',
	        'label'     => __( 'Admins Only', 'bp-activity-privacy' ),
	        'default'   => false,
	        'position'  => 90,
		    'disabled'  => false,        
	    );

	    $this->groups_activity_visibility_levels['onlyme'] = array(
	        'id'        => 'onlyme',
	        'label'     => __( 'Only Me', 'bp-activity-privacy' ),
	        'default'   => false,
	        'position'  => 100,
		    'disabled'  => false,        
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

		// fix / integration with some plugins
		include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-integrations.php' );

		// As an follow of how you might do it manually, let's include the functions used
		// on the WordPress Dashboard conditionally:	
		if ( is_super_admin() && ( is_admin() || is_network_admin() ) ) {
			include( BP_ACTIVITY_PRIVACY_PLUGIN_DIR . '/includes/bp-activity-privacy-admin.php' );
			$this->admin = new BPActivityPrivacy_Admin;
		}
		
	}
}

function bp_activity_privacy_load_core() {
	global $bp, $bp_activity_privacy;

	$bp_activity_privacy = new BP_Activity_Privacy;
	do_action('bp_activity_privacy_load_core');
}
//add_action( 'bp_loaded', 'bp_activity_privacy_load_core', 5 );
add_action( 'bp_init', 'bp_activity_privacy_load_core', 5 );