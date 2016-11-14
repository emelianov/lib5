<?php
//printjob.php	v5.0.1/MySQL

include_once(OLIB_PATH.'/class.mysql.php');

global	$init_tpl;
$init_tpl[]	= 'initPrintJob';

function initPrintJob() {
 global $base_tpl;
 $base_tpl->define(	Array(	'tr_checkbox'		=> 'html.tr.checkbox.tpl',
				'tr_checkbox_header_2'	=> 'html.tr.checkbox.header.2.tpl',
				'tr_checkbox_header_3'	=> 'html.tr.checkbox.header.3.tpl',
				'tr_checkbox_header_4'	=> 'html.tr.checkbox.header.4.tpl',				
				'tr_header_3'		=> 'html.tr.header.3.tpl',
				'tr_header_6'		=> 'html.tr.header.6.tpl',
				'tr_header_7'		=> 'html.tr.header.7.tpl'				
		    ));
 return true;
}

class PrintJob extends _ObjMySql {
 function PrintJob() {
  $this->_ObjMySql(Array('prnname', 'docum', 'time', 'username', 'pcname', 'paper', 'bsize', 'pages', 'jt', 'cp', 'ts'));
  $this->_db	= 'printjobs';
  $this->_tpl_tr= 'tr_checkbox';
  $this->where($this->_from().'.pages < 9999');	//5.0.2 PCounter fix
 }
 function _timeConvert($time) {        
  return $this->toUnixTimestamp($time);
 }                                     
 function _timeToString($time) {       
  return date('d-m-y H:i', $time);     
 }                                     
 function _timeNative($t) {
  $d	= "'".$this->toTimestamp($t)."'";
  return $d;
 }
 function range($date, $interval) {             
  parent::timeRange($date, $interval, 'time');
 }
 function retrive() {
//  $this->where($this->_from().'.pages < 9999');	//5.0.2 PCounter fix
  $this->_query	= "
  SELECT	prnname, docum, time,
		username, pcname, paper, bsize, pages, jt, cp, ts
  FROM		printjobs
  ";
  $this->_tpl_tr= 'tr';
  if (strpos($this->_groupBy, 'prnname') !== FALSE) {
   $this->_tpl_tr= 'tr_checkbox';
   if (strpos($this->_groupBy, 'username') !== FALSE) {
    $this->_query	= '
			    SELECT	prnname, username, SUM(pages) AS pages
			    FROM		printjobs
			';

   } else {
    $this->_query	= '
			    SELECT	prnname, SUM(pages) AS pages
			    FROM		printjobs
			';
   }
  }
  $this->_execute();
 }
 function tplTableRow($var, $tds) {
  $this->_tpl->assign('TR_CHECKBOX_TEXT', '');
  $this->_tpl->assign('TR_CHECKBOX_VALUE', $tds[0]['field'].'.'.$tds[0]['value']);
  parent::tplTableRow($var, $tds);
 }
}


?>