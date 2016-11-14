<?php
//parser.php	v5.0.1

include_once(OLIB_PATH.'/class.base.php');

class Parser extends _Obj {
 var	$_delimeter;
 var	$_data	= Array();
 var	$_fname;
 var	$_fhandle;
 var	$_cfgName;
 var	$_cfg;
 function Parser($fields, $delimeter) {
  foreach ($fields as $name) {
   $this->_addField($name);   
  }
  $this->_delimeter = $delimeter;
 }
 function fileOpen($fname, $fcfg) {
  $this->_cfgName	= $fcfg;
  $this->_cfg		= new Cfg($this->_cfgName);
  $this->_cfg->autocommit(OFF);
  $this->_fhandle	= fopen($fname, 'r');
  fseek($this->_fhandle, $this->_cfg->$fcfg);
 echo $this->_cfg->$fcfg."\n";
 }
 function fileStr() {
  if (!feof($this->_fhandle)) {
   $str			= fgets($this->_fhandle);
   $name		= $this->_cfgName;
   $this->_cfg->$name	= ftell($this->_fhandle);
   $this->_cfg->update();
   return $str;
  } else {
   return false;
  }
 }
 function load($str) {
  $ar		= explode($this->_delimeter, $str);
//  $this->count++;
//  if ($this->count = 2) {
//   for ($i = 0; $i < count($this->_fields); $i++) {
//    $field	= $this->_fields[$i];
//    $f		= & $this->$field;
//    $f[] = $f;
//   }
//  }
  for ($i = 0; $i < count($ar); $i++) {
   $field	= $this->_fields[$i];
//   if ($this->count > 1) {
//    $f		= & $this->$field;
//    $f[] = $ar[$i];
//   } else {
    $this->$field = $ar[$i];
//   }
  }
 }
 function commit() {
  $this->_cfg->commit();
 }
}


?>