<?php
/**
 * BP Activity Privacy Admin functions
 *
 * @package BP-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


add_action( 'bp_init', 'bp_admin_activity_privacy_init' );

function bp_admin_activity_privacy_init() {
    add_action( bp_core_admin_hook(), 'bp_activity_privacy_admin_page', 99 );
}

function bp_activity_privacy_admin_page() {
    if ( ! is_super_admin() )
        return;

    add_submenu_page(
        is_multisite()?'settings.php':'tools.php',
        __( 'BuddyPress Activity Privacy', 'bpdd' ),
        __( 'BP Activity Privacy', 'bpdd' ),
        'manage_options',
        'bpdd-setup',
        'bp_activity_privacy_admin_page_content'
    );
}

function bp_activity_privacy_admin_page_content() {
?>
    <div class="wrap">
    <?php screen_icon( 'buddypress' ); ?>

    <style type="text/css">
        ul li.users{border-bottom: 1px solid #EEEEEE;margin: 0 0 10px;padding: 5px 0}
        ul li.users ul,ul li.groups ul{margin:5px 0 0 20px}
        #message ul.results li{list-style:disc;margin-left:25px}
    </style>
    <h2><?php _e( 'BuddyPress Activity Privacy', 'bpdd' ); ?> <sup>v <?php echo BP_ACTIVITY_PRIVACY_VERSION ?> </sup></h2>
     


      Groups Activity privacy

      <ul>
            <li>
             	<label for="public">
             		<select name="update-activity-privacy" id="update-activity-privacy">
             			<option value="all">All Members</option>
             			<option value="adminonly">Admin Only</option>
             			<option value="none">None</option>
             		</select>	
             	</label>
            </li>	
      </ul>

      	<span>Profil Activity privacy</span> 		
      
  		<?php 
  		$html = "<ul>";
  		$profile_activity_visibility_levels = bp_get_profile_activity_visibility_levels();
        foreach ($profile_activity_visibility_levels as $pavl) {
        	$html .= ' <li><label for="' . $pavl["id"] .'"><input type="checkbox" name="bpdd[' . $pavl["id"] .']"/> &nbsp; ' . $pavl["label"] .'</label>';
        	$html .= ' <li><label for="' . $pavl["id"] .'">Position: <input type="text" name="position[' . $pavl["id"] .']" value="' . $pavl["position"] .'" /></label>';
        }
        $html .= "</ul>";
        echo $html ;
        ?>	
           

     
      	<span>Groups Activity privacy</span>
       
   		<?php 
  		$html = "<ul>";
  		$groups_activity_visibility_levels = bp_get_groups_activity_visibility_levels();
        foreach ($groups_activity_visibility_levels as $gavl) {
        	$html .= ' <li><label for="' . $gavl["id"] .'"><input type="checkbox" name="bpdd[' . $gavl["id"] .']"/> &nbsp; ' . $gavl["label"] .'</label>';
        	$html .= ' <li><label for="' . $gavl["id"] .'">Position: <input type="text" name="position[' . $gavl["id"] .']" value="' . $gavl["position"] .'" /></label>';
        }
        $html .= "</ul>";
        echo $html ;
        ?>	

<?php     
}            