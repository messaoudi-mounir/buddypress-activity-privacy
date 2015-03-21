<?php
/**
 * BP Activity Privacy Admin functions
 *
 * @package BP-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Loads Buddypress Activity privacy plugin admin area
 *
 */
class BPActivityPrivacy_Admin {

	var $setting_page = '';

	function __construct() {
		$this->setup_actions();

	}

	function setup_actions(){
		add_action( bp_core_admin_hook(), array( &$this, 'admin_menu' ) );
		//Welcome page redirect
		add_action( 'admin_init', array( &$this, 'do_activation_redirect' ), 1 );
        // Catch save submits
		add_action( 'admin_init', array( &$this, 'admin_submit' ) );


		// Modify Buddypress Activity Privacy admin links
		add_filter( 'plugin_action_links',               array( $this, 'modify_plugin_action_links' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'modify_plugin_action_links' ), 10, 2 );

	}

	function admin_menu() {
		$welcome_page = add_dashboard_page(
				__( 'Welcome to Buddypress Activity Privacy',  'bp-activity-privacy' ),
				__( 'Welcome to BP Activity Privacy',  'bp-activity-privacy' ),
				'manage_options',
				'bp-activity-privacy-about',
				array( $this, 'about_screen' )
		);

		$this->settings_page = bp_core_do_network_admin() ? 'settings.php' : 'options-general.php';
	    $hook = add_submenu_page( $this->settings_page, __( 'BuddyPress Activity Privacy', 'bp-activity-privacy' ), __( 'BP Activity Privacy', 'bp-activity-privacy' ), 'manage_options', 'bp-activity-privacy', array( &$this, 'admin_page' ) );

	    //add_action( "admin_print_styles-$hook", 'bp_core_add_admin_menu_styles' );
	    add_action( "admin_print_scripts-$hook", array( &$this, 'enqueue_scripts' ) );
	    add_action( "admin_print_styles-$hook", array( &$this, 'enqueue_styles' ) );

	    remove_submenu_page( 'index.php', 'bp-activity-privacy-about' );

	}

	/**
	 * Modifies the links in plugins table
	 * 
	 */
	public function modify_plugin_action_links( $links, $file ) {

		// Return normal links if not BuddyPress
		if ( plugin_basename( BP_ACTIVITY_PRIVACY_PLUGIN_FILE_LOADER ) != $file )
			return $links;

		// Add a few links to the existing links array
		return array_merge( $links, array(
			'settings' => '<a href="' . add_query_arg( array( 'page' => 'bp-activity-privacy'      ), bp_get_admin_url( $this->settings_page ) ) . '">' . esc_html__( 'Settings', 'bp-activity-privacy' ) . '</a>',
			'about'    => '<a href="' . add_query_arg( array( 'page' => 'bp-activity-privacy-about'      ), bp_get_admin_url( 'index.php'          ) ) . '">' . esc_html__( 'About',    'bp-activity-privacy' ) . '</a>'
		) );
	}

	function admin_submit() {
	    if ( isset( $_POST['bpap-submit'] ) || isset( $_POST['bpap-reset'] )  ) {
	      if ( !is_super_admin() ) {
	        return;
	      }

	      check_admin_referer( 'bpap-settings' );

	      if( isset( $_POST['bpap-submit'] ) ){
	      	// settings 
	      	$allow_admin_ve_privacy_levels =  ( $_POST['allow-admin-view-edit-privacy-levels'] )  ?  true : false;
	      	bp_update_option( 'bp_ap_allow_admin_ve_pl', $allow_admin_ve_privacy_levels );
	      
	      	$allow_members_e_privacy_levels = ( $_POST['allow-members-edit-privacy-levels'] )  ? true : false;
	      	bp_update_option( 'bp_ap_allow_members_e_pl', $allow_members_e_privacy_levels );
	      
			$use_fontawsome = ( $_POST['use-fontawsome'] )  ? true : false;
	      	bp_update_option( 'bp_ap_use_fontawsome', $use_fontawsome );
	      
			$use_custom_styled_selectbox = ( $_POST['use-custom-styled-selectbox'] )  ? true : false;
	      	bp_update_option( 'bp_ap_use_custom_styled_selectbox', $use_custom_styled_selectbox );
	      
			$show_privacy_levels_label = ( $_POST['show-privacy-levels-label'] )  ? true : false;
	      	bp_update_option( 'bp_ap_show_privacy_ll', $show_privacy_levels_label );

			$show_privacy_in_activity_meta = ( $_POST['show-privacy-in-activity-meta'] )  ? true : false;
			bp_update_option( 'bp_ap_show_privacy_in_am', $show_privacy_in_activity_meta);

	        $pavl = $_POST['pavl'];
	        $pavl_enabled = $_POST['pavl_enabled'];
	        $pavl_default = $_POST['pavl_default'];

	        // Register the visibility levels
	        $profile_activity_visibility_levels  = array(
	              'public' => array(
	                  'id'        => 'public',
	                  'label'     => __( 'Anyone', 'bp-activity-privacy' ),
	                  'default'   => ( $pavl_default ==  'public' )  ? true : false,
	                  'position'  => 10*( 1 + array_search('public', array_keys($pavl))),
	                  'disabled'  => ( $pavl_enabled ['public'] )  ? false : true
	              ),
	              'loggedin' => array(
	                  'id'        => 'loggedin',
	                  'label'     => __( 'Logged In Users', 'bp-activity-privacy' ),
	                  'default'   => ( $pavl_default == 'loggedin')  ? true : false,
	                  'position'  => 10*( 1 + array_search('loggedin', array_keys($pavl))),
	                  'disabled'  => ( $pavl_enabled ['loggedin'] )  ? false : true
	              )
	          );

	          if ( bp_is_active( 'friends' ) ) {
	              $profile_activity_visibility_levels['friends'] = array(
	                  'id'        => 'friends',
	                  'label'     => __( 'My Friends', 'bp-activity-privacy' ),
	                  'default'   => ( $pavl_default == 'friends')  ? true : false,
	                  'position'  => 10*( 1 + array_search('friends', array_keys($pavl))),
	                  'disabled'  => ( $pavl_enabled ['friends'] )  ? false : true
	              );
	          }

			  // https://buddypress.trac.wordpress.org/changeset/7193
			  if ( function_exists('bp_activity_do_mentions') ) {
		          if ( bp_activity_do_mentions() ) {
		              $profile_activity_visibility_levels['mentionedonly'] = array(
		                  'id'        => 'mentionedonly',
		                  'label'     => __( 'Mentioned Only', 'bp-activity-privacy' ),
		                  'default'   => ( $pavl_default == 'mentionedonly')  ? true : false,
		                  'position'  => 10*( 1 + array_search('mentionedonly', array_keys($pavl))),
		                  'disabled'  => ( $pavl_enabled ['mentionedonly'] )  ? false : true
		              );
		          }

	      	  } else {
	      	  	/*
	              $profile_activity_visibility_levels['mentionedonly'] = array(
	                  'id'        => 'mentionedonly',
	                  'label'     => __( 'Mentioned Only', 'bp-activity-privacy' ),
	                  'default'   => ( $pavl_default == 'mentionedonly')  ? true : false,
	                  'position'  => 10*( 1 + array_search('mentionedonly', array_keys($pavl))),
	                  'disabled'  => ( $pavl_enabled ['mentionedonly'] )  ? false : true
	              ); */     	  	
	      	  }

	          $profile_activity_visibility_levels['adminsonly'] = array(
	              'id'      => 'adminsonly',
	              'label'   => __( 'Admins Only', 'bp-activity-privacy' ),
	              'default'   => ( $pavl_default == 'adminsonly')  ? true : false,
	              'position'  => 10*( 1 + array_search('adminsonly', array_keys($pavl))),
	              'disabled'  => ( $pavl_enabled ['adminsonly'] )  ? false : true
	          );

	          $profile_activity_visibility_levels['onlyme'] = array(
	              'id'        => 'onlyme',
	              'label'     => __( 'Only Me', 'bp-activity-privacy' ),
	              'default'   => ( $pavl_default == 'onlyme')  ? true : false,
	              'position'  => 10*( 1 + array_search('onlyme', array_keys($pavl))),
	              'disabled'  => ( $pavl_enabled ['onlyme'] )  ? false : true
	          );	

	          //followers plugin integration
			  if( function_exists('bp_follow_is_following') ) {
		          $profile_activity_visibility_levels['followers'] = array(
		              'id'        => 'followers',
		              'label'     => __( 'My Followers', 'bp-activity-privacy' ),
		              'default'   => ( $pavl_default == 'followers')  ? true : false,
		              'position'  => 10*( 1 + array_search('followers', array_keys($pavl))),
		              'disabled'  => ( $pavl_enabled ['followers'] )  ? false : true
		          );
			  }

	          bp_update_option( 'bp_ap_profile_activity_visibility_levels', $profile_activity_visibility_levels );
	      
	          //Groups activity privacy

	          $gavl = $_POST['gavl'];
	          $gavl_enabled = $_POST['gavl_enabled'];
	          $gavl_default = $_POST['gavl_default'];

	          $groups_activity_visibility_levels = array(
	              'public' => array(
	                  'id'        => 'public',
	                  'label'     => __( 'Anyone', 'bp-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'public')  ? true : false,
	                  'position'  => 10*( 1 + array_search('public', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['public'] )  ? false : true       
	              ),
	              'loggedin' => array(
	                  'id'        => 'loggedin',
	                  'label'     => __( 'Logged In Users', 'bp-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'loggedin')  ? true : false,
	                  'position'  => 10*( 1 + array_search('loggedin', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['loggedin'] )  ? false : true         
	              )
	          );

	          if ( bp_is_active( 'friends' ) ) {
	              $groups_activity_visibility_levels['friends'] = array(
	                  'id'        => 'friends',
	                  'label'     => __( 'My Friends', 'bp-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'friends')  ? true : false,
	                  'position'  => 10*( 1 + array_search('friends', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['friends'] )  ? false : true            
	              );
	              if ( bp_is_active( 'groups' ) ) {
	                $groups_activity_visibility_levels['groupfriends'] = array(
	                    'id'        => 'groupfriends',
	                    'label'     => __( 'My Friends in Group', 'bp-activity-privacy' ),
	                    'default'   => ( $gavl_default == 'groupfriends')  ? true : false,
	                    'position'  => 10*( 1 + array_search('groupfriends', array_keys($gavl))),
	                    'disabled'  => ( $gavl_enabled ['groupfriends'] )  ? false : true          
	                );
	            }
	          }

	         if ( bp_activity_do_mentions() ) {
	                $groups_activity_visibility_levels['mentionedonly'] = array(
	                   'id'        => 'mentionedonly',
	                   'label'     => __( 'Mentioned Only', 'bp-activity-privacy' ),
	                   'default'   => ( $gavl_default == 'mentionedonly')  ? true : false,
	                   'position'  => 10*( 1 + array_search('mentionedonly', array_keys($gavl))),
	                   'disabled'  => ( $gavl_enabled ['mentionedonly'] )  ? false : true            
	                );
	          }

	          if ( bp_is_active( 'groups' ) ) {
	              $groups_activity_visibility_levels['grouponly'] = array(
	                  'id'        => 'grouponly',
	                  'label'     => __( 'Group Members', 'bp-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'grouponly')  ? true : false,
	                  'position'  => 10*( 1 + array_search('grouponly', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['grouponly'] )  ? false : true         
	              );

	              $groups_activity_visibility_levels['groupmoderators'] = array(
	                  'id'        => 'groupmoderators',
	                  'label'     => __( 'Group Moderators', 'bp-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'groupmoderators')  ? true : false,
	                  'position'  => 10*( 1 + array_search('groupmoderators', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['groupmoderators'] )  ? false : true          
	              );

	              $groups_activity_visibility_levels['groupadmins'] = array(
	                  'id'        => 'groupadmins',
	                  'label'     => __( 'Group Admins', 'bp-activity-privacy' ),
	                  'default'   => ( $gavl_default == 'groupadmins')  ? true : false,
	                  'position'  => 10*( 1 + array_search('groupadmins', array_keys($gavl))),
	                  'disabled'  => ( $gavl_enabled ['groupadmins'] )  ? false : true          
	              );
	        }   

	        $groups_activity_visibility_levels['adminsonly'] = array(
	              'id'        => 'adminsonly',
	              'label'     => __( 'Admins Only', 'bp-activity-privacy' ),
	              'default'   => ( $gavl_default == 'adminsonly')  ? true : false,
	              'position'  => 10*( 1 + array_search('adminsonly', array_keys($gavl))),
	              'disabled'  => ( $gavl_enabled ['adminsonly'] )  ? false : true      
	        );

	        $groups_activity_visibility_levels['onlyme'] = array(
	              'id'        => 'onlyme',
	              'label'     => __( 'Only Me', 'bp-activity-privacy' ),
	              'default'   => ( $gavl_default == 'onlyme')  ? true : false,
	              'position'  => 10*( 1 + array_search('onlyme', array_keys($gavl))),
	              'disabled'  => ( $gavl_enabled ['onlyme'] )  ? false : true    
	        );   

            //followers plugin integration
		    if( function_exists('bp_follow_is_following') ) {
		        $groups_activity_visibility_levels['followers'] = array(
		              'id'        => 'followers',
		              'label'     => __( 'My Followers', 'bp-activity-privacy' ),
		              'default'   => ( $gavl_default == 'followers')  ? true : false,
		              'position'  => 10*( 1 + array_search('followers', array_keys($gavl))),
		              'disabled'  => ( $gavl_enabled ['followers'] )  ? false : true    
		        );   	    	
		        $groups_activity_visibility_levels['groupfollowers'] = array(
		              'id'        => 'groupfollowers',
		              'label'     => __( 'My Followers in Group', 'bp-activity-privacy' ),
		              'default'   => ( $gavl_default == 'groupfollowers')  ? true : false,
		              'position'  => 10*( 1 + array_search('groupfollowers', array_keys($gavl))),
		              'disabled'  => ( $gavl_enabled ['groupfollowers'] )  ? false : true    
		        );   
		    }

	        bp_update_option( 'bp_ap_groups_activity_visibility_levels', $groups_activity_visibility_levels );
	        ?>
	        <div id="message" class="updated"><p><?php _e( 'Settings saved.', 'bp-activity-privacy' );?></p></div>
	        <?php

	      } else {
				global $bp_activity_privacy;
	          
	          	bp_update_option( 'bp_ap_profile_activity_visibility_levels', $bp_activity_privacy->profile_activity_visibility_levels );
	          	bp_update_option( 'bp_ap_groups_activity_visibility_levels', $bp_activity_privacy->groups_activity_visibility_levels );
	        
	      		bp_update_option( 'bp_ap_allow_admin_ve_pl', false );
	     	    bp_update_option( 'bp_ap_allow_members_e_pl', true );
	      		bp_update_option( 'bp_ap_use_fontawsome', true );
	     	    bp_update_option( 'bp_ap_use_custom_styled_selectbox', true );
	      		bp_update_option( 'bp_ap_show_privacy_ll', true );
	      		bp_update_option( 'bp_ap_show_privacy_in_am', true);
	        ?>
	        <div id="message" class="updated"><p><?php _e( 'Settings reseted.', 'bp-activity-privacy' );?></p></div>
	        <?php
	      } 
	    }
	}

	function admin_page() {  
	  ?>
	    <div class="wrap">
	    	<?php screen_icon( 'buddypress' ); ?>
	    	<h2><?php _e( 'BuddyPress Activity Privacy', 'bp-activity-privacy' ); ?> <sup>v <?php echo BP_ACTIVITY_PRIVACY_VERSION ?> </sup></h2>
	     
	      	<form method="post" action="">

		      <h3><label><?php _e('Profil Activity privacy', 'bp-activity-privacy') ?></label></h3>     
		      <div class="bpap-options-box options-box ui-sortable">
		      <h4><?php _e('Please check the box to enable the privacy and Drag&Drop to sort :', 'bp-activity-privacy') ?></h4> 
		      <?php 
		      //$html = "<ul>";
		      //
		      $html = "";
		      $profile_activity_visibility_levels = bp_get_profile_activity_visibility_levels();
		      uasort ($profile_activity_visibility_levels, 'bp_activity_privacy_cmp_position');
		      foreach ($profile_activity_visibility_levels as  $key => $pavl) {
		        $disabled = ( !$pavl["disabled"] ) ? 'checked' : '';
		        $default = ( $pavl["default"] ) ? 'checked' : '';
		        
		        $html .= ' <p class="sortable" style=""><span style="cursor: default;"> Ξ </span><label for="' . $pavl["id"] .'"><input type="checkbox" name="pavl_enabled[' . $pavl["id"] .']" ' . $disabled  .' /> &nbsp; ' . $pavl["label"] .'</label>';
		        $html .= '<input type="hidden" name="pavl[' . $key .']" value="' . $pavl["id"] .'" /><input name="pavl_default" id="pavl_default" value="' . $key .'" type="radio" ' . $default . '><span style="cursor: move;">Default Value</span>';
		        $html .= ' </p>';

		      //  $html .= ' <li><label for="' . $pavl["id"] .'">Position: <input type="text" name="position[' . $pavl["id"] .']" value="' . $pavl["position"] .'" /></label></p>';

		      }
		     // $html .= "</ul>";
		      echo $html;
		      ?>  
	      	 </div>
	           
	      	<h3><label><?php _e('Groups Activity privacy', 'bp-activity-privacy') ?></label></h3>
	     	 <div class="bpap-options-box options-box ui-sortable">
		          <h4><?php _e('Please check the box to enable the privacy and Drag&Drop to sort :', 'bp-activity-privacy') ?></h4> 
		          <?php 
		          $groups_activity_visibility_levels = bp_get_groups_activity_visibility_levels();
		          uasort ($groups_activity_visibility_levels, 'bp_activity_privacy_cmp_position');	
		          $html = '';
		          foreach ($groups_activity_visibility_levels as  $key => $pavl) {
		            $disabled = ( !$pavl["disabled"] ) ? 'checked' : '';
		            $default = ( $pavl["default"] ) ? 'checked' : '';
		            
		            $html .= ' <p class="sortable" style=""><span style="cursor: default;"> Ξ </span><label for="' . $pavl["id"] .'"><input type="checkbox" name="gavl_enabled[' . $pavl["id"] .']" ' . $disabled  .' /> &nbsp; ' . $pavl["label"] .'</label>';
		            $html .= '<input type="hidden" name="gavl[' . $key .']" value="' . $pavl["id"] .'" /><input name="gavl_default" id="gavl_default" value="' . $key .'" type="radio" ' . $default . '><span style="cursor: move;">Default Value</span>';
		            $html .= ' </p>';
		          }
		          echo $html;
		          ?>  
		      </div>

			<?php
		      	$allow_admin_ve_privacy_levels = bp_get_option('bp_ap_allow_admin_ve_pl');
		      	$allow_members_e_privacy_levels = bp_get_option( 'bp_ap_allow_members_e_pl');
				$use_fontawsome = bp_get_option( 'bp_ap_use_fontawsome');
				$use_custom_styled_selectbox =bp_get_option('bp_ap_use_custom_styled_selectbox');
				$show_privacy_levels_label = bp_get_option( 'bp_ap_show_privacy_ll');
				$show_privacy_in_activity_meta = bp_get_option( 'bp_ap_show_privacy_in_am');
			?>

		    <h3><label><?php _e('Settings', 'bp-activity-privacy') ?></label></h3>     
  				<h4><?php _e('Main settings', 'bp-activity-privacy') ?></h4> 
				<table class="form-table">
					<tbody><tr><th scope="row"><?php _e('Admin privileges', 'bp-activity-privacy') ?></th><td>
					<input id="allow-admin-view-edit-privacy-levels" name="allow-admin-view-edit-privacy-levels" <?= ($allow_admin_ve_privacy_levels) ? 'checked' : ''; ?> type="checkbox" />
					<label for="allow-admin-view-edit-privacy-levels"><?php _e('Allow admin to view and edit the prviacy of all activities', 'bp-activity-privacy') ?></label>

					</td></tr><tr><th scope="row">Members privileges</th><td>
					<input id="allow-members-edit-privacy-levels" name="allow-members-edit-privacy-levels" <?= ($allow_members_e_privacy_levels) ? 'checked' : ''; ?> type="checkbox" />
					<label for="allow-members-edit-privacy-levels"><?php _e('Allow members to edit the privacy of their activities', 'bp-activity-privacy') ?></label>

					</td></tr>
					</tbody>
				</table>
				<h4><?php _e('UI settings', 'bp-activity-privacy') ?></h4>
				<table class="form-table">
					<tbody>
					<tr>
						<th scope="row"><?php _e('Font Awesome Icons', 'bp-activity-privacy') ?></th>
						<td>
						<input id="use-fontawsome" name="use-fontawsome" <?= ($use_fontawsome) ? 'checked' : ''; ?> type="checkbox" />
						<label for="use-fontawsome"><?php _e('Use FontAwesome Icons', 'bp-activity-privacy') ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Custom styled selectbox', 'bp-activity-privacy') ?></th>
						<td>
						<input id="use-custom-styled-selectbox" name="use-custom-styled-selectbox" <?= ($use_custom_styled_selectbox) ? 'checked' : ''; ?> type="checkbox" />
						<label for="use-custom-styled-selectbox"><?php _e('Use custom styled selectbox', 'bp-activity-privacy') ?></label>

						</td>
					</tr>

					<tr>
						<th scope="row"><?php _e('Privacy labels', 'bp-activity-privacy') ?></th>
						<td>
						<input id="show-privacy-levels-label" name="show-privacy-levels-label" <?= ($show_privacy_levels_label) ? 'checked' : ''; ?> type="checkbox">
						<label for="show-privacy-levels-label"><?php _e('Show the privacy label in selectbox (Use FontAwesome Icons should be checked if this is unchecked)', 'bp-activity-privacy') ?></label>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e('Privacy in activity meta', 'bp-activity-privacy') ?></th>
						<td>
						<input id="show-privacy-in-activity-meta" name="show-privacy-in-activity-meta" <?= ($show_privacy_in_activity_meta) ? 'checked' : ''; ?> type="checkbox">
						<label for="show-privacy-in-activity-meta"><?php _e('Show the privacy in activity meta', 'bp-activity-privacy') ?></label>
						</td>
					</tr>
					</tbody>
				</table>

		      <?php wp_nonce_field( 'bpap-settings' ) ?>
		      <br />
		      <input type="submit" name="bpap-submit" class="button-primary" value="<?php _e( "Save Settings", 'bp-activity-privacy' ) ?>" />
		      <input type="submit" name="bpap-reset" class="button-secondary" value="<?php _e( "Reset", 'bp-activity-privacy' ) ?>" />
		  </form>
		</div><!-- end-wrap -->  
	  <?php     

	}

  	public function about_screen() {
		$display_version = BP_ACTIVITY_PRIVACY_VERSION;
		$settings_url = add_query_arg( array( 'page' => 'bp-activity-privacy'), bp_get_admin_url( $this->settings_page ) );
		?>
		<style type="text/css">
			/* Changelog / Update screen */

			.about-wrap .feature-section img {
				border: none;
				margin: 0 1.94% 10px 0;
				-webkit-border-radius: 3px;
				border-radius: 3px;
			}

			.about-wrap .feature-section.three-col img {
				margin: 0.5em 0 0.5em 5px;
				max-width: 100%;
				float: none;
			}

			.ie8 .about-wrap .feature-section.three-col img {
				margin-left: 0;
			}

			.about-wrap .feature-section.images-stagger-right img {
				float: right;
				margin: 0 5px 12px 2em;
			}

			.about-wrap .feature-section.images-stagger-left img {
				float: left;
				margin: 0 2em 12px 5px;
			}

			.about-wrap .feature-section img.image-100 {
				margin: 0 0 2em 0;
				width: 100%;
			}

			.about-wrap .feature-section img.image-66 {
				width: 65%;
			}

			.about-wrap .feature-section img.image-50 {
				max-width: 50%;
			}

			.about-wrap .feature-section img.image-30 {
				max-width: 31.2381%;
			}

			.ie8 .about-wrap .feature-section img {
				border-width: 1px;
				border-style: solid;
			}	

			.about-wrap .images-stagger-right img.image-30:nth-child(2) {
				margin-left: 1em;
			}

			.about-wrap .feature-section img {
			    background: none repeat scroll 0% 0% #FFF;
			    border: 1px solid #CCC;
			    box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.3);
			}

			.bpap-admin-badge {
				position: absolute;
				top: 0px;
				right: 0px;
				padding-top: 190px;
				height: 25px;
				width: 173px;
				color: #555;
				font-weight: bold;
				font-size: 11px;
				text-align: center;
				margin: 0px -5px;
				background: url('<?php echo BP_ACTIVITY_PRIVACY_PLUGIN_URL; ?>includes/images/badge.png') no-repeat scroll 0% 0% transparent;
			}
		</style>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to BuddyPress Activity Privacy %s', 'bp-activity-privacy' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for upgrading to the latest version of BP Activity Privacy! <br \> BP Activity Privacy %s is ready to manage the activity privacy of your Site!', 'bp-activity-privacy' ), $display_version ); ?></div>
			<div class="bpap-admin-badge" style=""><?php printf( __( 'Version %s', 'bp-activity-privacy' ), $display_version ); ?></div>

			<div class="warning-text" style="color:red;font-weight:bold;text-align:center;"><?php _e( 'Please go to BP Acitivity Privacy Configuration and save your settings to apply new update.', 'bp-activity-privacy'  ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url(  bp_get_admin_url( add_query_arg( array( 'page' => 'bp-activity-privacy-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'About', 'bp-activity-privacy' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Add Privacy Controls To The BuddyPress Activity Stream!', 'bp-activity-privacy' ); ?></h3>

				<div class="feature-section">
					<p><?php _e( 'BP Activity Privacy is a BuddyPress plugin who gives users the ability to restrict who can see their activity posts. ', 'bp-activity-privacy' ); ?></p>
					<p><?php _e( 'It gives each member multiple privacy options on activity posts and should encourage more confident participation on your social network.', 'bp-activity-privacy' );?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php printf( __( 'What&#39; new in %s ?', 'bp-activity-privacy' ), $display_version ); ?></h3>

				<div class="feature-section">
					<ul>
						<li><?php _e( 'Allow admin to view and edit the prviacy of all activities (Check Admin privileges in plugin settings).', 'bp-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a control to enable/disable the members to edit the privacy of their activities.', 'bp-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a control to enable/disable the FontAwesome icon.', 'bp-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a finer control to enable/disable FontAwesome icon.', 'bp-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a control to enable/disable the custom styled selectbox.', 'bp-activity-privacy' );?></li>
						<li><?php _e( 'Admin now have a control to show/hide printing the privacy of activities in their meta.', 'bp-activity-privacy' );?></li>
						
						<strong><?php _e( 'Updates before current version','bp-activity-privacy' ); ?></strong>
						<br />
						<br />	
						<li class=""><?php _e( 'The plugin work now on Multisite Network!', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'A New privacy level (Mentioned only). When a member choose this privacy level, only mentioned members (and admin of course) can see the activity.', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( '<strong>Remark:</strong> <i>Members mentioned in activity can see it\'s content whatever the privacy level.</i>', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'New Dropdown system with a nice icons ( By <a target="_BLANK" href="http://fontawesome.io/">Font Awesome</a> ).', 'bp-activity-privacy' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'How it\'s Work ?' , 'bp-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-1.png" class="image-50" />
					<p><?php _e( 'Once installed and activated, BuddyPress Activity Privacy adds following privacy controls to the post update box for members:', 'bp-activity-privacy' ); ?></p>
					<ul>
						<li><?php _e( 'Anyone', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Logged In Users', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'My Friends', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Mentioned Only', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Admin Only', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Only Me', 'bp-activity-privacy' ); ?></li>
					</ul>
					<p><?php _e( 'Certain privacy controls are component-dependent. For example, the "Friends Only" privacy option in the dropdown will not show up unless you have the Friends component activated in the BuddyPress settings panel.', 'bp-activity-privacy' ); ?></p>

				</div>
			</div>

			<div class="changelog">
			
				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-2.png" class="image-50" />
					<p>
					<?php _e( 'When posting within a group the group-specific privacy options will be added to the dropdown, inlcluding:', 'bp-activity-privacy' ); ?>&nbsp;
					<ul>
						<li><?php _e( 'My Friends in a Group', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Group Members', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Group Moderators', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Group Admins', 'bp-activity-privacy' ); ?></li>
					</ul>
				</div>
			</div>


			<div class="changelog">
				<h3><?php _e( 'Update the Privacy of the ol ', 'bp-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-6.png" class="image-50" />
					<p><?php _e( 'Members can update the privacy of the old activity stream (new selectbox in activity meta).', 'bp-activity-privacy' ); ?></p>
					<p><?php _e( 'Admin can also update the privacy of all the old activity stream.', 'bp-activity-privacy' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Privacy control for Followers Plugin', 'bp-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-3.png" class="image-50" />
					<h4><?php _e( 'Fully integrated with Buddypress Follow', 'bp-activity-privacy' ); ?></h4>
					<p><?php _e( 'If you have <a href="http://wordpress.org/plugins/buddypress-followers/">BuddyPress Follow</a> installed in your site, BP Activity Privacy adds new privacy levels :', 'bp-activity-privacy' ); ?></p>
					<ul>
						<li><?php _e( 'My Followers', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'My followers in Group', 'bp-activity-privacy' ); ?></li>
					</ul>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Integration for Buddypress Activity Plus Plugin', 'bp-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-5.png" class="image-50" />
					<p><?php _e( 'BP Activity Privacy is released with Integration for <a href="http://wordpress.org/plugins/buddypress-activity-plus/">Buddypress Activity Plus</a>.', 'bp-activity-privacy' ); ?></p>
				</div>
			</div>			

			<div class="changelog">
				<h3><?php _e( 'BP Acitivity Privacy Configuration', 'bp-activity-privacy' ); ?></h3>

				<div class="feature-section images-stagger-right">
					<img alt="" src="<?php echo BP_ACTIVITY_PRIVACY_PLUGIN_URL;?>/screenshot-7.png" class="image-50" />
					<h4><a href="<?php echo $settings_url;?>" title="<?php _e( 'Configure BP Activity Privacy', 'bp-activity-privacy' ); ?>"><?php _e( 'Configure BP Activity Privacy', 'bp-activity-privacy' ); ?></a></h4>
					<p><?php _e( 'From the settings menu of his WordPress administration, the administrator is able to configure BP Activity Privacy by :', 'bp-activity-privacy' ); ?></p>
					<ul>
						<li><?php _e( 'Enable/Disable a privacy level.', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Sort the privacy levels.', 'bp-activity-privacy' ); ?></li>
						<li><?php _e( 'Change the default privacy level.', 'bp-activity-privacy' ); ?></li>
					</ul>
				</div>
				
				<div class="return-to-dashboard">
					<a href="<?php echo $settings_url;?>" title="<?php _e( 'Configure BP Activity Privacy', 'bp-activity-privacy' ); ?>"><?php _e( 'Go to the BP Activity Privacy Settings page', 'bp-activity-privacy' );?></a>
				</div>
			</div>

		</div>
	<?php
  	}

	/**
	 * Welcome screen redirect
	 */
	function do_activation_redirect() {
		// Bail if no activation redirect
		if ( ! get_transient( '_bp_activity_privacy_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_bp_activity_privacy_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( isset( $_GET['activate-multi'] ) )
			return;

		$query_args = array( 'page' => 'bp-activity-privacy-about' );

		// Redirect to Buddypress Activity privacy about page
		wp_safe_redirect( add_query_arg( $query_args, bp_get_admin_url( 'index.php' ) ) );
	}  	

  	function enqueue_scripts() {
    	wp_enqueue_script( 'bpap-admin-js',  plugins_url( 'js/admin.js' ,  __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ) );

  	}

  	function enqueue_styles() {
  		if(bp_ap_is_use_fontawsome()){
   	 		wp_enqueue_style( 'bp-font-awesome-css', plugins_url( 'css/font-awesome/css/font-awesome.min.css' ,  __FILE__ )); 
    	}
    	wp_enqueue_style( 'bp-activity-privacy-admin-css', plugins_url( 'css/admin.css' ,  __FILE__ )); 
  	}

  	//@TODO
  	function update(){
  	}
}