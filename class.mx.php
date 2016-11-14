<?php
// lib5 (c)2006 - 2016, a.m.emelianov@gmail.com
// mx.php	v2016

require_once(OLIB_PATH.'/class.pgsql.php');

class Lpu extends _ObjPgSql {
 function __construct() {
  $fields	= array(	'id',
				'name',
				'shortname'
			);
  $this->_fieldsCreate = array(	'name',
				'shortname'
    );
 $this->_fieldsUpdate = array(	'name',
				'shortname'
    );
  $this->_keyFields = array(	'id');
  parent::__construct();
  $this->_db	= 'lpu';
  $this->_addField($fields, $this->_from());
 }
}
                                                
class Doctor extends _ObjPgSql {
 function __construct($id=false) {
  $fields	= array(	'id',
				'name',
				'loging'
);
  $this->_db	= 'doctor';
  parent::__construct($fields);
  if ($hid) {
   $this->where("id='$id'");
  }
  $this->_execute();
 }
}

class EmptyDb extends _ObjPgSql {
}

?>
