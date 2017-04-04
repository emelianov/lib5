<?php
////////////////////////////////////////////////
// lib5
// PHP database helper library
// (c)2006-2016 a.m.emelianov@gmail.com
//
// base.php	v2016

if (!defined(OLIB_CONFIG)) {
 define('OLIB_CONFIG', 'config.php');
}
require_once(OLIB_CONFIG);
if (!defined(OLIB_PATH)) {
 define('OLIB_PATH', '../lib5');
}
define('OTYPE_LDAP',	1);
define('OTYPE_ORACLE',	2);
define('OTYPE_MSSQL',	3);
define('OSET_AUTO',	0);
define('OSET_SINGLE',	1);
define('OSET_MULTI',	2);
define('OID_PERSON',	1000000);
define('ON',		true);
define('OFF',		false);

function obj($id) {
}

class _Obj {
 var		$createTime	= false;
 var		$id;
 var		$_type;
 var		$_fields	= array();
 var		$_map		= array();
 var		$count;
 var		$_query;
 var		$debug		= false;	//5.0.2
 var		$utime		= 0;

 function __construct($set=OSET_AUTO) {
  $this->createTime = time();
  $this->count	= 0;				//5.0.2 (was = 1)
  $this->_type	= $set;
 }
 function _addField($name, $realName=false) {
  if (is_array($name)) {			//5.1.0
   foreach ($name as $n) {
    $this->_fields[]	= $n;
    $this->$n		= false;
    $this->_map[]	= $realName?($realName.'.'.$n):$n;
   }
  } else {
   $this->_fields[]	= $name;
   $this->$name		= false;
   $this->_map[]	= $realName?$realName:$name;	//5.1.0
  }
 }
 function save() {
  return false;
 }
 function _execute($query, $fields=false) {
 }
 public function __clone() {
  foreach ($this->_fields as $name) {
   $this->$name	= $this->$name;
  }
 }
 function extract($element) {			//Buggy!!!
  $tmp		= clone $this;			//5.0.2 PHP5
  if ($this->count > 1) {
   foreach ($this->_fields as $name) {
    $tmpRow	= $tmp->$name;
    $tmp->$name	= $tmpRow[$element];
    $tmp->count	= 1;
   }
  }
  return $tmp;
 }
 function push($obj=false, $appendFields=false) {
  if ($obj) {					//5.0.2
   for ($i = 0; $i < $obj->count; $i++) {
    foreach ($obj->_fields as $item) {
     if (isset($this->$item)) {
      $t	= & $this->$item;
      $s	= & $obj->$item;
      $t[]	= $s[$i];
     }
    }
   }
  } else {					//5.0.2
   foreach ($this->_fields as $item) {
    if (isset($this->$item)) {
     $t	= & $this->$item;
     $t[]	= false;
    }
   }
  }
  $this->count++;
 }
 function _toMulti() {
  foreach ($this->_fields as $item) {
   if (isset($this->$item) && !is_array($this->$item)) {
    $tmp	= array();
    $tmp[]	= $this->$item;
    $this->$item= $tmp;
   }
  }
  $this->_type	= OSET_MULTI;
  $this->count	= 1;
 }
 function toMulti() {
  foreach ($this->_fields as $item) {
//   if (isset($this->$item) && !is_array($this->$item)) {
   if (isset($this->$item)) {			//5.1.1
    $tmp	= array();
    $tmp[]	= $this->$item;
    $this->$item= $tmp;
   } else {					//5.0.2
    $tmp	= array();
    $tmp[]	= false;
    $this->$item= $tmp;
   }
  }
  $this->_type	= OSET_MULTI;
  $this->count	= 1;
 }

 function sortBy($fieldName, $sortType=SORT_STRING, $sortFlag=SORT_ASC) {
//  $evalStr	= '@array_multisort(& $this->'.$fieldName.', $sortType, $sortFlag';
  $evalStr	= 'array_multisort($this->'.$fieldName.', $sortType, $sortFlag';	//5.1.4
  foreach ($this->_fields as $name) {
   if ($name != $fieldName) {
//    $evalStr	.= ', & $this->'.$name;
    $evalStr	.= ', $this->'.$name;							//5.1.4
   }
  }
  $evalStr	.= ');';
  eval($evalStr);
 }
 function fieldUpper($name) {
  $target	= & $this->$name;
  for ($i = 0; $i < count($target); $i++) {
   $target[$i]	= strtoupper($target[$i]);
  }
 }
 function fieldLower($name) {
  $target	= & $this->$name;
  for ($i = 0; $i < count($target); $i++) {
   $target[$i]	= strtoupper($target[$i]);
  }
 }
 
 function search($field, $value) {		//5.0.2
  return array_search($value, $this->$field);	//5.1.0
 }
 
 function delete($i, $count=1) {		//5.0.2
  foreach ($this->_fields as $item) {
//   @array_splice(& $this->$item, $i, $count);
    array_splice($this->$item, $i, $count);	//5.1.4
  }
  $this->count	= $this->count - $count;
 }
 
 function unixTimestampToString($t) {
  if (is_null($t)) {				//5.1.3
   return '-';
  } else {
   return date('Y-m-d H:i:s', $t);
  }
 }
 
 function text2html($s) {			//5.1.2
  return nl2br(htmlspecialchars($s));
 }
}

?>