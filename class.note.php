<?php
//note.php	v2013/MySQL

include_once(OLIB_PATH.'/class.mysql.php');

class Note extends _ObjMySql {
 function __construct ($ptype = false, $pid = false) {
 $fields	= array(	'id',		//Unique ID
				'ptype',	//Parent type
				'pid',		//Parent ID
				'name',		//Bref name
				'content',
				'owner',	//Owner/Creator
				'created'	//Create date
		);
  $this->_fieldsCreate	= array('ptype',	//Parent type
				'pid',		//Parent ID
				'name',		//Bref name
				'content',
				'owner',	//Owner/Creator
				'created'	//Create date'cid',
			);
  $this->_fieldsUpdate	= array('name',		//Bref name
				'content',
			);
  $this->_keyFields	= array('id');
  parent::__construct();
  $this->_db	= 'notes';
  $this->_addField($fields, $this->_from());
  if ($ptype && $pid) {
   $this->findBy(array(	"ptype='$ptype'",
			"pid='$pid'"
		));
   $this->_execute();
  }
 }
 function _createdConvert($time) {        
  return $this->toUnixTimestamp($time);
 }                                     
 function _createdToString($time) {
  return $time?date('d-m-y H:i', $time):'';     
 }                                     
 function _createdNative($t) {
  $d	= "'".$this->toTimestamp($t)."'";
  return $d;
 }
}

?>
