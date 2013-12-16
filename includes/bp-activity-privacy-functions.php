<?php
/**
 * BP Activity Privacy Functions 
 *  
 * @package BP-Activity-Privacy
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function bp_activity_privacy_cmp_position($a, $b) {
    if ($a['position'] == $b['position']) {
        return 0;
    }
    return ($a['position'] < $b['position']) ? -1 : 1;
}