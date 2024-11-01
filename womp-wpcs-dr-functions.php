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

class womp_google_visualisation_data {
	private $data = array();
	private $Cols = array();
	private $Rows = array();
	private $CellFormat = array();
	private $TableProperty = array();
	private $tabFooter;
	private $GraphProperty = array();

	function __construct() {
		$this->data['status'] = 'ok';
		$this->data['message'] = 'ok';
	}
	public function result() {
		$ret = "{\n";
		foreach ($this->data as $k => $v) {
		    $ret .= "'$k':'$v',\n";
		}
		if (count($this->Cols)+count($this->Rows)) {
			$ret .= '"table":{'."\n";
			if (count($this->Cols)) {
				$ret .= '"cols":['."\n";
				$coma="";
				foreach ($this->Cols as $k => $v) {
				    $ret .= "$coma{'id':'$k'";
				    foreach ($v as $k => $v) {
					$ret .= ",'$k':'$v'";
				    }
				    $ret .= "}";
				    $coma=",\n";
				}
				$ret .= "\n],\n";
			}
			if (count($this->Rows)) {
				$ret .= '"rows":['."\n";
				$r=0;
				foreach ($this->Rows as $k => $v) {
				    if ($r) {$ret .= ",\n";}
				    $ret .= "{'c':[";
				    $coma='';
				    $c=0;
				    foreach ($v as $a) {
					$ret .= "$coma{'v':$a";
					if (!empty($this->CellFormat[$r][$c])) {
						$ret .= ",'f':'".$this->CellFormat[$r][$c]."'";
					}
					$ret .= "}";
					$coma=',';
					$c++;
				    }
				    $ret .= "]}";
				    $r++;
				}
				$ret .= "\n]";
			}
			$ret .= "\n},";

			$ret .= "\n".'"TableProperty":{';
			$coma='';
			foreach ($this->TableProperty as $k => $v) {
				$ret .= $coma . "'$k':$v";
				$coma = ',';
			}
			$ret .= '}';

			$ret .= ',"GraphProperty":{'."\n";
			$coma='';
			foreach ($this->GraphProperty as $k => $v) {
				$ret .= $coma . "'$k':$v";
				$coma = ',';
			}
			$ret .= '}';

			$ret .= ',"tabFooter":['.$this->tabFooter.']';
		}
		$ret .= '}';
		return $ret;
	}
	public function setTabFooter($val) {
		$this->tabFooter=$val;
	}
	public function setTableProperty($key,$val) {
		$this->TableProperty[$key]=$val;
	}
	public function setGraphProperty($key,$val) {
		$this->GraphProperty[$key]=$val;
	}
	public function set($key,$val) {
		$this->data[$key] = $val;
	}
	private function addColumn($label,$type) {
		array_push($this->Cols, array('label'=>$label, 'type'=>$type));
	}
	public function addStringColumn($label) {
		$this->addColumn($label, 'string');
	}
	public function addBoolColumn($label) {
		$this->addColumn($label,'boolean');
	}
	public function addDateColumn($label) {
		$this->addColumn($label,'date');
	}
	public function addDateTimeColumn($label) {
		$this->addColumn($label,'datetime');
	}
	public function addNumberColumn($label) {
		$this->addColumn($label,'number');
	}
	public function addRow() {
		$tmp = array();
		foreach (func_get_args() as $e) {
			if (is_array($e)) {
				foreach ($e as $a) array_push($tmp,$a);
			} else {
				array_push($tmp,$e);
			}
		}
		$arr = array();
		$i=0;
		foreach ($tmp as $e) {
			if ($this->Cols[$i]['type'] == 'string') {
				$e = str_replace("'","\'",$e);
				$e = "'$e'";
			} else if ($this->Cols[$i]['type'] == 'boolean') {
				$e = $e ? 'true' : 'false';
			} else if ($this->Cols[$i]['type'] == 'date') {
				$e = "new Date($e)";
			} else if ($this->Cols[$i]['type'] == 'datetime') {
				$e = "new Date($e)";
			}
			array_push($arr,$e);
			$i++;
		}
		array_push($this->Rows, $arr);
		return count($this->Rows)-1;
	}
	public function setCellFormat($r,$c,$val) {
		$this->CellFormat[$r][$c]=$val;
	}
}

function womp_wpcs_dr_product_sales_period() {
	if (isset($_POST['period'])) {
		$period = $_POST['period'];
		if ($period != 'custom') {
			setcookie('womp_wpcs_dr_product_sales_period', $period, time()+86400);
		}
	} elseif (isset($_COOKIE['womp_wpcs_dr_product_sales_period'])) {
		$period = $_COOKIE['womp_wpcs_dr_product_sales_period'];
	} else {
		$period = 'thismonth';
	}

	return $period;
}

function womp_wpcs_dr_sales_interval() {
	if (isset($_POST['interval'])) {
		$interval = $_POST['interval'];
		setcookie('womp_wpcs_dr_sales_interval', $interval, time()+86400);
	} elseif (isset($_COOKIE['womp_wpcs_dr_sales_interval'])) {
		$interval = $_COOKIE['womp_wpcs_dr_sales_interval'];
	} else {
		$interval = 'monthly';
	}

	return $interval;
}

function womp_wpsc_dr_html_select_option($p,$v,$n) {
	echo "<option value=\"$v\"".(($p==$v)?' selected':'').">$n</option>";
}


?>
