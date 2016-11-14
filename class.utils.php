<?php
//utils.php	v5.0.3

include_once(OLIB_PATH.'/class.base.php');

class Dbg {
 var $chkPoint		= false;
 var $start		= 0;
 function __construct() {
  $this->chkPoint	= array();
  $this->start		= $this->utime();
 }
 function utime() {                                          
  $time = explode( " ", microtime());
  $usec = (double)$time[0];          
  $sec = (double)$time[1];           
  return $sec + $usec;               
 }
 function chk($name=null) {
  $pos			= count($this->chkPoint);
  if (is_null($name)) {
   $name		= $pos + 1;
  }
  $this->chkPoint[]	= array('name'	=> $name,
				'time'	=> $this->utime() - $this->start
				);
 }
 function toString(){
  $res			= '';
  foreach ($this->chkPoint as $str) {
   $res			.= '('.$str['name'].'--'.$str['time'].')';
  }
  return $res;
 }
}

function genFileName($ext='') {                 
 $name          = mt_rand(10000000, 99999999);  
 while (file_exists(PWD_FILES.'/'.$name.$ext)) {
  $name         = mt_rand(10000000, 99999999);  
 }                                              
 return $name.$ext;
}                                               

function utime() {                                          
 $time = explode( " ", microtime());
 $usec = (double)$time[0];          
 $sec = (double)$time[1];           
 return $sec + $usec;               
}

function expiresIn($sec) {
 $expire = 'Expires: '.gmdate("D, d M Y H:i:s", time()+ $sec - 3600 * 5).' GMT';
 header($expire);
// echo $expire."\n\n";
}

?>