<?php
/** 
 * Plugin Name: Simple Redirect
 * Plugin URI: https://irnanto.com
 * Description: create a table to list url redirect and function redirect
 * Author: Irnanto Dwi Saputra
 * Author URI: https://irnanto.com
 * Version: 1.0.1
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: irnanto_redirection
 */

/*
'Simple Redirect' is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
'Simple Redirect' is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with 'Simple Redirect'. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// If this file is accessed directory, then abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'IRNANTO_REDIRECT_URL' ) ) {
	define( 'IRNANTO_REDIRECT_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'IRNANTO_REDIRECT_PATH' ) ) {
	define( 'IRNANTO_REDIRECT_PATH', plugin_dir_path( __FILE__ ) );
}

require IRNANTO_REDIRECT_PATH . '/inc/class-irnanto-redirect-module.php';
require IRNANTO_REDIRECT_PATH . '/inc/table.php';
require IRNANTO_REDIRECT_PATH . '/inc/admin-page.php';

/**
 * Class IrnantoRedirect
 *
 * This class creates tabel 
 */
class IrnantoRedirect {

	public function __construct() {	
		register_activation_hook(__FILE__, array($this, 'create_table'));
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_settings_link' ) );
		add_action('template_redirect', array($this, 'redirection'), 1000);		
	}

	/**
	 * add link plugin setting page
	 *
	 * @param  array $links link
	 * @return array        link
	 */
	function plugin_settings_link( $links ) {
		$url           = esc_url(
			add_query_arg(
				'page',
				'irnanto-redirect',
				get_admin_url() . 'admin.php'
			)
		);
		$settings_link = '<a href="' . $url . '">' . __( 'List Redirect', 'vdmi_alumni' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	public function create_table(){
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();		

		// create db table.
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . "irnanto_rediction";
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {			
			$sql = "CREATE TABLE `" . $table_name . "` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `url` varchar(255) NOT NULL,
			  `status` int(20) NOT NULL DEFAULT '0',
			  `new_url` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `url` (`url`(191))
			)$charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

	public function redirection() {
		if ( ! is_admin() ) {
	        global $wp;
	        global $wpdb;
	        // $url = add_query_arg( $wp->query_vars, home_url( $wp->request ) );
	        $url = home_url( $wp->request );
	        $final_url 	= '';
	        $table_name = $wpdb->prefix . "irnanto_rediction";
            $query 		= $wpdb->prepare("SELECT * FROM $table_name WHERE url = %s OR url = %s", $url, trailingslashit($url) );
            $row 		= $wpdb->get_row($query);

            if( $row ){ 
            	$redirect_path = $row->new_url;
            	$status = ( '' != $row->status) ? $row->status : 302;
            	if ('' != $redirect_path) {
                	wp_redirect($redirect_path, $status);
                	die();
            	}                
            }
	    }
	}

	
}

new IrnantoRedirect();