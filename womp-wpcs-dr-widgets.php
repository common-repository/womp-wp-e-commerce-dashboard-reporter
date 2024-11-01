<?php

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

include ("womp-wpcs-dr-ajax.php");

//***************************
// List of per-product sales
//***************************


function womp_wpcs_dr_product_sales() {

	$period = womp_wpcs_dr_product_sales_period();

?>
<div width="100%" class="womp-wpcs-dr-right">
	<form method="POST" action="#" id="womp-wpcs-dr-product-sales-form">
		<input type="hidden" name="action" value="womp-wpcs-dr-product-sales">
		<input type="hidden" name="gType" id="womp-wpcs-dr-product-sales-gType" value="table">
		<label id="womp-wpcs-dr-product-sales-switch">
		<img src="<?php echo plugins_url('img/switch.png', __FILE__ ); ?>"
			onclick="womp_wpsc_dr.product_sales.gSwitch(); return false;"
			title="Switch to charts or table"
			class="womp-wpcs-dr-image">
		</label>
		<label id="womp-wpcs-dr-product-sales-reload">
		<img src="<?php echo plugins_url('img/reload.png', __FILE__ ); ?>"
			onclick="womp_wpsc_dr.product_sales.do_ajax(); return false;"
			title="Click to reload data"
			class="womp-wpcs-dr-image">
		</label>
		<label id="womp-wpcs-dr-product-sales-loading">
		<img src="<?php echo plugins_url('img/loading.gif', __FILE__ ); ?>"
			class="womp-wpcs-dr-image">
		</label>
		<input type="text" name="start-date" id="womp-wpcs-dr-product-sales-start-date" size="8" style="display:none">&nbsp;
		<input type="text" name="end-date" id="womp-wpcs-dr-product-sales-end-date" size="8" style="display:none">
		<input type="button" id="womp-wpcs-dr-product-sales-custom-submit" value="ok">
		<select name="period" id="womp-wpcs-dr-product-sales-period">
<?php
			womp_wpsc_dr_html_select_option($period,'today', __('Today'));
			womp_wpsc_dr_html_select_option($period,'yesterday', __('Yesterday'));
			womp_wpsc_dr_html_select_option($period,'7days', __('Last 7 days'));
			womp_wpsc_dr_html_select_option($period,'thismonth', __('This month'));
			womp_wpsc_dr_html_select_option($period,'lastmonth', __('Last month'));
			womp_wpsc_dr_html_select_option($period,'thisyear', __('This year'));
			womp_wpsc_dr_html_select_option($period,'lastyear', __('Last year'));
			womp_wpsc_dr_html_select_option($period,'alltime', __('All time'));
			womp_wpsc_dr_html_select_option($period,'custom', __('Custom'));
?>
		</select>
	</form>
</div>
<?php
	echo '<div id="womp-wpcs-dr-product-sales"></div>';
}

//"
add_action('wp_ajax_womp-wpcs-dr-product-sales', 'womp_wpcs_dr_ajax_product_sales');


//***********************
// List of recent orders
//***********************

function womp_wpcs_dr_recent_orders() {

	$period = womp_wpcs_dr_product_sales_period();

	$div = 'womp-wpcs-dr-recent-orders';

	echo '<div width="100%" class="womp-wpcs-dr-right">';
	echo '<form method="POST" action="#" id="'.$div.'-form">';
	echo '<input type="hidden" name="action" value="'.$div.'">';
	echo '<input type="hidden" name="gType" id="'.$div.'-gType" value="table">';
	echo '<label id="'.$div.'-reload">';
	echo '<img src="'.plugins_url('img/reload.png', __FILE__ ).'"
		onclick="womp_wpsc_dr.recent_orders.do_ajax(); return false;"
		title="Click to reload data"
		class="womp-wpcs-dr-image">';
	echo '</label>';
	echo '<label id="'.$div.'-loading">';
	echo '<img src="'.plugins_url('img/loading.gif', __FILE__ ).'"
		class="womp-wpcs-dr-image">';
	echo '</label>';
	echo '</form>';
	echo '</div>';
	echo '<div id="'.$div.'"></div>';
}


add_action('wp_ajax_womp-wpcs-dr-recent-orders', 'womp_wpcs_dr_ajax_recent_orders');


//***************************
// List of per-product sales
//***************************


function womp_wpcs_dr_sales() {

	$interval = womp_wpcs_dr_sales_interval();

?>
<div width="100%" class="womp-wpcs-dr-right">
	<form method="POST" action="#" id="womp-wpcs-dr-sales-form">
		<input type="hidden" name="action" value="womp-wpcs-dr-sales">
		<input type="hidden" name="gType" id="womp-wpcs-dr-sales-gType" value="ColumnChart">
		<label id="womp-wpcs-dr-sales-reload">
		<img src="<?php echo plugins_url('img/reload.png', __FILE__ ); ?>"
			onclick="womp_wpsc_dr.sales.do_ajax(); return false;"
			title="Click to reload data"
			class="womp-wpcs-dr-image">
		</label>
		<label id="womp-wpcs-dr-sales-loading">
		<img src="<?php echo plugins_url('img/loading.gif', __FILE__ ); ?>"
			class="womp-wpcs-dr-image">
		</label>

		<input type="checkbox" name="values" value="1" id="womp-wpcs-dr-sales-values"> Value
		<input type="checkbox" name="details" value="1" id="womp-wpcs-dr-sales-details"> Details

		<select name="interval" id="womp-wpcs-dr-sales-interval">
<?php
			womp_wpsc_dr_html_select_option($interval,'daily', __('Daily'));
			womp_wpsc_dr_html_select_option($interval,'weekly', __('Weekly'));
			womp_wpsc_dr_html_select_option($interval,'monthly', __('Monthly'));
			womp_wpsc_dr_html_select_option($interval,'yearly', __('Yearly'));
?>
		</select>
	</form>
</div>
<?php
	echo '<div id="womp-wpcs-dr-sales"></div>';
}

//"
add_action('wp_ajax_womp-wpcs-dr-sales', 'womp_wpcs_dr_ajax_sales');



?>
