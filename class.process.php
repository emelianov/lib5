<?php
//process.php	v6.0.0/MySQL

include_once(OLIB_PATH.'/class.mysql.php');

class Process extends _ObjMySql {
 function __construct($id=false) {
  $fields	= array(	'id',		//Uniqueue ID
				'computer',
				'process',
				'start',
				'stop'
			);
  $this->_fieldsCreate	= array('computer',
				'process',
				'start'
			);
  $this->_fieldsUpdate	= array('computer',
				'process',
				'start',
				'stop'
			);
  $this->_keyFields	= array('id');
  parent::__construct();
  $this->_db	= 'process';
  $this->_addField($fields, $this->_from());
  if ($id) {
   $this->where("id=$id");
   $this->_execute();
  }
 }
 function _startConvert($time) {        
  return $this->toUnixTimestamp($time);
 }                                     
 function _startToString($time) {
  return $time?date('d-m-y H:i', $time):'';     
 }                                     
 function _startNative($t) {
  $d	= "'".$this->toTimestamp($t)."'";
  return $d;
 }
 function _stopConvert($time) {        
  return $this->toUnixTimestamp($time);
 }                                     
 function _stopToString($time) {       
  return $time?date('d-m-y H:i', $time):'';     
 }                                     
 function _stopNative($t) {
  $d	= "'".$this->toTimestamp($t)."'";
  return $d;
 }
 function range($date, $interval) {             
  parent::timeRange($date, $interval, 'start');
 }
 function retrive($id=false) {
  return $this->_execute();
 }
 function selectComputer($comp) {
  $this->where("computer='$comp'");
 }
 function selectActiveProcesses($comp=false) {
  if ($comp !== false) {
   $this->selectComputer($comp);
  }
  $this->where("(stop='0000-00-00 00:00' or stop IS NULL)");
 }
}

?>