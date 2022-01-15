<?php
/* 
 * exit uninstall if not called by WP
 */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

global $wpdb;
$table_name 	= $wpdb->prefix . 'irnanto_rediction';
$sql 			= "DROP TABLE IF EXISTS $table_name;";
$wpdb->query($sql);