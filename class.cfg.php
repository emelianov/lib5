<?php
//cfg.php	v5.0.1/MySQL

include_once(OLIB_PATH.'/class.mysql.php');

class Cfg extends _ObjMySql {
 function Cfg($name) {
  $this->_ObjMySql(Array('name', 'value'));
  $this->_db	= 'cfg';
  $this->name[0]= $name;
  $this->findBy("name='$name'");
  $this->_execute();
  $this->$name	= $this->value[0];
 }
 function insert() {
  $name			= $this->name[0];
  $this->value[0]	= $this->$name;
  parent::insert();
 }
 function update() {
  $name			= $this->name[0];
  $this->value[0]	= $this->$name;
  parent::update('name');
 }
}

?>