<?php
/*
Plugin Name: UCF Health locations taxonomy
Plugin URI: https://github.com/schrauger/ucf-health-locations
Description: A custom taxonomy with custom fields for latitude, longitude, phone, etc.
Version: 2.2
Author: Stephen Schrauger
Author URI: https://www.schrauger.com/
License: GPLv2 or later
*/
require_once( plugin_dir_path( __FILE__ ) . "Tax-Meta-Class/Tax-meta-class/Tax-meta-class.php" ); // lets the 'location' taxonomy have custom fields for lat/long etc
require_once ( plugin_dir_path( __FILE__ ) . "ucf_health_locations.php" );

?>
