<?php
/*
Plugin Name: UCF Health locations taxonomy
Plugin URI: https://github.com/schrauger/ucf-health-locations
Description: A custom taxonomy with custom fields for latitude, longitude, phone, etc.
Version: 0.1
Author: Stephen Schrauger
Author URI: https://www.schrauger.com/
License: GPLv2 or later
*/
require_once( get_plugin_directory() . "/Tax-meta-class/Tax-meta-class/Tax-meta-class.php" ); // lets the 'location' taxonomy have custom fields for lat/long etc
require_once ( get_plugin_directory() . "/ucf_health_locations.php" );

?>
