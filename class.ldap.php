<?php
//ldap.php	v5.1.0/AD
/*
5.1.0
- Multi DN support
*/

require_once(OLIB_PATH.'/class.base.php');

global		$oldap_ds;
$oldap_ds	= ldap_connect(OLDAP_DS);
ldap_set_option($oldap_ds, LDAP_OPT_PROTOCOL_VERSION, 2);
$oldap_bind	= ldap_bind($oldap_ds, OLDAP_USER.'@'.OLDAP_DOMAIN, OLDAP_PASS);

class _Objldap extends _Obj {
 var		$ds;
 var		$bind;
 var		$dn	= array();
 function __construct($dn=NULL, $fields=NULL, $ds=NULL, $bind=NULL) {
  global	$oldap_ds;
  global	$oldap_bind;

  parent::__construct();
  if ($dn === NULL) {
   $this->dn[]	= OLDAP_DN1;
   $this->dn[]	= OLDAP_DN;
  } else {
   if (is_array($dn)) {
    foreach ($dn as $d) {
     $this->dn[]	= $d;
    }
   } else {
    $this->dn[0]	= $dn;
   }
  }
  if ($ds === NULL) {
   $this->ds	= $oldap_ds;
  } else {
   $this->ds	= $ds;
  }
  if ($bind === NULL) {
   $this->bind	= $oldap_bind;
  } else {
   $this->dind	= $bind;
  }
  if ($fields === NULL) {

  } else {
   foreach ($fields as $name) {
    $this->_addField($name);
   }
  }
 }
 function _execute($query=NULL, $fields=NULL) {
  if ($query === NULL) {
   $curQuery	= $this->_query;
  } else {
   $curQuery	= $query;
  }
//trigger_error( $curQuery );
//echo( $curQuery ."<BR>");
  foreach ($this->dn as $d) {
   $sr		= ldap_search($this->ds, $d, str_replace('"','\"',$curQuery), $this->_fields); //5.0.2
   $info	= ldap_get_entries($this->ds, $sr);
   $this->count	= $info['count'];
//echo $this->count."<BR>";
   if ($info['count'] > 0) {
    if ($info['count'] == 1) {
     foreach ($this->_fields as $name) {
      if (isset($info[0][$name]) && $info[0][$name]['count'] > 1) {
       $tmp	= array();
       for ($i = 0; $i < $info[0][$name]['count']; $i++) {
        $funcName          = '_'.$name.'Convert';         
        if (method_exists($this, $funcName)) {            
         $tmp[]		= $this->$funcName($info[0][$name][$i]);
        } else {
         $tmp[]	= $info[0][$name][$i];
        }
       }
       if(!is_array($this->$name)) {
        $this->$name	= $tmp;
       } else {
        $this->$name= array_merge($this->$name, $tmp);
       }
      } else {
       $this->$name= isset($info[0][$name][0])?($info[0][$name][0]):(false);
       $funcName          = '_'.$name.'Convert';         
       if (method_exists($this, $funcName)) {             
        $this->$name	= $this->$funcName($this->$name);
       }
      }
     }
    } else {
     foreach ($this->_fields as $name) {
      $tmp	= array();
      for ($i = 0; $i < $info['count']; $i++) {
       $tmp[$i]	= isset($info[$i][$name][0])?($info[$i][$name][0]):(false);
       $funcName          = '_'.$name.'Convert';         
       if (method_exists($this, $funcName)) {             
        $tmp[$i]		= $this->$funcName($tmp[$i]);
       }
      }
      if(!is_array($this->$name)) {
       $this->$name	= $tmp;
      } else {
       $this->$name= array_merge($this->$name, $tmp);
      }
     }
    }
   }
  }
 }
}

         
?>