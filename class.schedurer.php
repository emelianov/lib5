<?php
//scheduler.php	v5.0.0/MySQL

include_once(OLIB_PATH.'/class.mysql.php');

class Sched extends _ObjMySql {
 function __construct ($id = false) {
 $fields	= array(	'id',		//Unique ID
				'name',		//Sched name
				'status',	//Active/Complete
				'otype',	//Target object type
				'oid',		//Target object ID
				'oaction',	//Action on target
				'oargument',	//Action additional argument
				'time',		//Execute time
				'interval',	//Execute interval
				'owner',	//Owner/Creator
				'created');	//Create date
  $this->parent::__construct();
  $this->_db	= 'sched';
  $this->_addField($fields);
  if ($id) {
   $this->findBy("id='$id'");
   $this->_execute();
  }
 }
}

//Actions
function REQUEST_DELAY($oid, $oargument){
}

function PERSON_WAIT($oid,$oargument) {
}

?>