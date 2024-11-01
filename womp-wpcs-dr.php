<?php
/*
 Plugin Name: WOMP WP E-Commerce Dashboard Reporter
 Plugin URI:
 Description: Yet another WP E-Commerce dashboard reporter.
 Version: 0.2.4
 Author: Womp Team
 Author URI: http://wompteam.wordpress.com
 */

/*
 * Copyright (C) 2011 Womp Team
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 */

// Hooks
register_activation_hook ( __FILE__, 'womp_wpcs_dr_activate' );
register_deactivation_hook ( __FILE__, 'womp_wpcs_dr_deactivate' );

function womp_wpcs_dr_activate() {
	// Registers a plugin function to be run when the plugin is activated.
	global $table_prefix, $wpdb;

	$var = $wpdb->get_var( "SHOW TABLES LIKE '{$table_prefix}wpsc_purchase_logs'" );
	if ( empty($var) )
		exit ( __( 'This plugin requires <a href="http://getshopped.org/">WP e-Commerce</a>.' ) );

	$var = $wpdb->get_var( "SHOW TABLES LIKE '{$table_prefix}wpsc_product_list'");
	if ( !empty($var) )
		exit ( __( 'This plugin is not compatible with <a href="http://getshopped.org/">WP e-Commerce</a> version 3.7 or below.' ) );

}

function womp_wpcs_dr_deactivate() {
	// Registers a plugin function to be run when the plugin is deactivated.
}

// If not in admin area just drop straight out
if (is_admin()) {

	//
	// Let's rock
	//

	include ("womp-wpcs-dr-functions.php");
	include ("womp-wpcs-dr-widgets.php");

	function womp_wpcs_dr_add_dashboard_widgets() {
		if (current_user_can('manage_options')) {
			wp_add_dashboard_widget('womp-wpcs-dr-widget-product-sales', __('Product Sales'), 'womp_wpcs_dr_product_sales');
			wp_add_dashboard_widget('womp-wpcs-dr-widget-sales', __('Sales'), 'womp_wpcs_dr_sales');
			wp_add_dashboard_widget('womp-wpcs-dr-widget-recent-orders', __('Recent Orders'), 'womp_wpcs_dr_recent_orders');
			wp_enqueue_style('womp-wpsc-dr-styles', plugins_url('womp-wpcs-dr.css', __FILE__));
		}
	}

	add_action('wp_dashboard_setup', 'womp_wpcs_dr_add_dashboard_widgets' );



	function womp_wpcs_dr_init() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('google-visualisation', 'https://www.google.com/jsapi');
		wp_enqueue_script('womp-wpcs-dr', plugins_url('womp-wpcs-dr.js', __FILE__));
	}

	add_action('init', 'womp_wpcs_dr_init');

}

?>
