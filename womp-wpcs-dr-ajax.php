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

function womp_wpcs_dr_ajax_product_sales() {
	global $table_prefix, $wpdb;

	$period	= womp_wpcs_dr_product_sales_period();

	$year	= date_i18n('Y');
	$month	= date_i18n('n');
	$day	= date_i18n('j');

	switch($period) {
		case '7days':
			// Actually today + 6 previous days
			$mindate = mktime(0,0,0,$month,$day,$year) - 6*60*60*24;
			$maxdate = mktime(23,59,59,$month,$day,$year);
			break;

		case 'today':
			$mindate = mktime(0,0,0,$month,$day,$year);
			$maxdate = mktime(23,59,59,$month,$day,$year);
			break;

		case 'yesterday':
			$mindate = mktime(0,0,0,$month,$day-1,$year);
			$maxdate = mktime(0,0,0,$month,$day,$year)-1;
			break;

		case 'lastmonth':
			$mindate = mktime(0,0,0,$month-1,1,$year);
			$maxdate = mktime(0,0,0,$month,1,$year)-1;
			break;

		case 'thisyear':
			$mindate = mktime(0,0,0,1,1,$year);
			$maxdate = mktime(0,0,0,1,1,$year+1)-1;
			break;

		case 'lastyear':
			$mindate = mktime(0,0,0,1,1,$year-1);
			$maxdate = mktime(0,0,0,1,1,$year)-1;
			break;

		case 'custom':
			$min = $_POST['start-date'];
			$max = $_POST['end-date'];
			$mindate = mktime( 0, 0, 0,substr($min,3,2),substr($min,0,2),substr($min,6,4));
			$maxdate = mktime(23,59,59,substr($max,3,2),substr($max,0,2),substr($max,6,4));
			break;

		case 'thismonth':
		default:
			$mindate = mktime(0,0,0,$month,1,$year);
			$maxdate = mktime(0,0,0,$month+1,1,$year)-1;
			break;

	}

	$sql = "SELECT
			cc.prodid,
			cc.name,
	                SUM(cc.quantity) AS items,
	                SUM(cc.quantity * cc.price) AS value
		FROM	{$table_prefix}wpsc_cart_contents cc
		INNER JOIN {$table_prefix}wpsc_purchase_logs pl
			ON cc.purchaseid = pl.id
	        WHERE	
	    		pl.processed in (2,3,4)
	    		".(($period == 'alltime') ? '' : "AND pl.date BETWEEN $mindate AND $maxdate")."
	        GROUP BY
	    		cc.prodid
 		ORDER BY
 			value DESC, items DESC ";
	// XXX Limit?

	$rows = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);

	$c = new womp_google_visualisation_data;

	if (!count($rows)) {
		$c->set('status', 'err');
		$c->set('message', 'No sales in selected period.');
		echo $c->result();
		exit;
	}

	$c->addStringColumn(__('Product'));
	$c->addNumberColumn(__('Value'));
	$c->addNumberColumn(__('Items'));

	$totI=0;
	$totV=0;
	foreach ($rows as $row) {
		$r = $c->addRow($row['name'], $row['value'], $row['items']);
		$c->setCellFormat($r,1,sprintf('%.2f',$row['value']));
		$totI += $row['items'];
		$totV += $row['value'];
	}
	$c->setTabFooter("['Total',2],['".sprintf('%.2f',$totV)."',1],['".$totI."',1]");

	$c->setTableProperty('showRowNumber', true);

	$c->setGraphProperty('is3D', 'true');
	$c->setGraphProperty('height', 250);
	$c->setGraphProperty('backgroundColor', '"transparent"');
	$c->setGraphProperty('chartArea', "{left:20,top:20,botom:20,width:'90%',height:'90%'}");

	echo $c->result();
	exit;

}

function womp_wpcs_dr_ajax_recent_orders() {
	global $table_prefix, $wpdb;

	$sql = "
		SELECT pl.id AS pid,
			pl.date AS tdate,
			SUM(cc.quantity) AS items,
			SUM(cc.quantity * cc.price) AS value,
			pl.totalprice AS total,
			IF(pl.processed IN (2,3,4), 1, 0) AS status
		FROM {$table_prefix}wpsc_purchase_logs pl
		INNER JOIN {$table_prefix}wpsc_cart_contents cc
			ON pl.id = cc.purchaseid
		GROUP BY pid
		ORDER BY tdate DESC LIMIT 20";

	$rows = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);
	// XXX Limit?


	$c = new womp_google_visualisation_data;

	if (!count($rows)) {
		$c->set('status', 'err');
		$c->set('message', 'No orders yet.');
		echo $c->result();
		exit;
	}

	$c->addNumberColumn(__('ID'));
	$c->addDateTimeColumn(__('Date'));
	$c->addNumberColumn(__('Items'));
	$c->addNumberColumn(__('Value'));
	$c->addNumberColumn(__('Total'));
	$c->addBoolColumn(__('Status'));

	foreach ($rows as $row) {
		$id=$row['pid'];
		$r=$c->addRow($id, 1000*$row['tdate'], $row['items'], $row['value'], $row['total'], $row['status']);
		$c->setCellFormat($r,0,"<a href=\"admin.php?page=wpsc-purchase-logs&c=item_details&id=$id\">$id</a>");
		$c->setCellFormat($r,1,date( 'Y-m-d H:i', $row['tdate']));
		$c->setCellFormat($r,3,sprintf('%.2f',$row['value']));
		$c->setCellFormat($r,4,sprintf('%.2f',$row['total']));
	}

	$c->setTableProperty('allowHtml', 'true');
	$c->setTableProperty('page', "'enable'");
	$c->setTableProperty('pageSize', 5);

	echo $c->result();
	exit;

}


function womp_wpcs_dr_ajax_sales() {
	global $table_prefix, $wpdb;

	$interval=womp_wpcs_dr_sales_interval();
	$details=$_POST['details'];
	$valData=$_POST['values'];

	switch($interval) {
		default:
		case 'daily':
			$dateFormat='%%Y-%%m-%%d';
			break;
		case 'weekly':
			$dateFormat='%%Y, %%u';
			break;
		case 'monthly':
			$dateFormat='%%Y-%%m';
			break;
		case 'yearly':
			$dateFormat='%%Y';
			break;
	}


	if ($details) {
		$groupby='cdate,pid';
		$sql = "
			SELECT	cc.prodid AS pid,
				cc.name AS name
			FROM	{$table_prefix}wpsc_cart_contents cc
			GROUP BY pid
			";
	} else {
		$groupby='cdate';
		$sql = "
			SELECT	1 AS pid,
				'All products' AS name,
				sum(1) as sum
			FROM	{$table_prefix}wpsc_cart_contents cc
			GROUP BY pid
			";
	}

	$rows = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);
	$n_prod = count($rows);
	if (!($details) && !($rows[0]['sum'])) {
		$n_prod = 0;
	}

	$c = new womp_google_visualisation_data;

	if (!$n_prod) {
		$c->set('status', 'err');
		$c->set('message', 'No sales.');
		echo $c->result();
		exit;
	}

	$c->addStringColumn(__('Date'));

	$idx=array();
	$i=1;
	foreach ($rows as $row) {
		$c->addNumberColumn($row['name']);
		$idx[$row['pid']] = $i;
		$i++;
	}

	// XXX hack: pl.date -> local time
	$toLocalTime = $wpdb->get_var("SELECT 3600*TIMESTAMPDIFF(HOUR,NOW(),UTC_TIMESTAMP()) LIMIT 1");

	$sql = "
		SELECT
			date_format(from_unixtime(pl.date+$toLocalTime),'$dateFormat') AS cdate,
			c.prodid as pid,
			SUM(c.quantity * c.price) AS value,
			SUM(c.quantity) AS items
		FROM	{$table_prefix}wpsc_purchase_logs pl
		INNER JOIN {$table_prefix}wpsc_cart_contents c
		ON c.purchaseid = pl.id
		WHERE
			pl.processed in (2,3,4)
		GROUP BY $groupby
		ORDER BY cdate,pid ASC";

	$rows = $wpdb->get_results($wpdb->prepare($sql), ARRAY_A);

	$arr=array();
	foreach ($rows as $row) {
		$dt=$row['cdate'];
		if(empty($arr[$dt][0])) {
			$arr[$dt][0]=$dt;
			for($i=1;$i<=$n_prod;$i++)
				$arr[$dt][$i]=0;
		}
		if ($details) {
			$arr[$dt][$idx[$row['pid']]]=$valData?$row['value']:$row['items'];
		} else {
			$arr[$dt][1]=$valData?$row['value']:$row['items'];
		}
	}
	foreach ($arr as $a) {
		$c->addRow($a);
	}

	$c->setGraphProperty('height', 250);
	$c->setGraphProperty('isStacked', 1);
	$c->setGraphProperty('backgroundColor', '"transparent"');
	$c->setGraphProperty('legend', '"none"');
	$c->setGraphProperty('chartArea', "{left:40,top:5,botom:1,width:'100%',height:200}");
	echo $c->result();
	exit;

}


?>
