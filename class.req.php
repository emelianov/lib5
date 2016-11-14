<?php
/*
request.php	v5.2.0/MySQL
5.2.0
- sql.php v5.3+ compatibility (_createFields,_updateFields,_keyField)
- RequestClass realization
*/

include_once(OLIB_PATH.'/class.mysql.php');

define('OREQ_RESERVED',	0);
define('OREQ_WAIT',	1);
define('OREQ_PROCESS',	2);
define('OREQ_DELAY',	3);
define('OREQ_TIMEOUT',	4);
define('OREQ_DONE',	5);
define('OREQ_COMMENT',	6);
define('OREQ_RESERVE2',	7);
define('OREQ_ANALIZE',	8);
if (!defined('OREQ_TABLE')) {
 define('OREQ_TABLE',	'requests');
}
if (!defined('OREQ_CLASSES')) {
 define('OREQ_CLASSES',	'request_class');
}

class Request extends _ObjMySql {
 function __construct($rid=false) {
  $fields	= array(	'rid',		//Uniqueue request ID
				'time',		//Create time
				'username',	//Creator name
				'pcname',
				'targetname', 
				'action',	//Action code
				'text',		//Action text
				'file',		//Attached file
				'pid',		//Parent request ID
				'done',		//Finish time
				'result',	//Result code
				'time2',	//Processing start time
				'saprname',	//Processor
				'username2',	//Finalizer name
				'cid',		//RequestClass bitmask
				'curator',	//Curator name
				'comment',	//User request comment
				'cid'		//Class IDs
			);
  $this->_fieldsCreate	= array('username', 	//Creator name
				'time',
				'pcname', 
				'targetname', 
				'action', 	//Action code
				'text', 	//Action text
				'file', 	//Attached file
				'pid',		//Parent request ID
				'done',		//Finish time
				'result',	//Result code
				'time2',	//Processing start time
				'saprname',	//Processor
				'username2',	//Finalizer name
				'cid',		//Request class id
				'curator',	//Curator name
				'comment'	//User request comment
			);
  $this->_fieldsUpdate	= array('pcname',
				'targetname', 
				'action', 	//Action code
				'text', 	//Action text
				//'file', 	//Attached file
				'done',		//Finish time
				'result',	//Result code
				'time2',	//Processing start time
				'saprname',	//Processor
				'username2',	//Finalizer name
				'cid',		//Request class id
				'curator',	//Curator name
				'comment'	//User request comment
			);
  $this->_keyFields	= array('rid');
  parent::__construct();
  $this->_db	= OREQ_TABLE;
  $this->_addField($fields, $this->_from());
  if ($rid) {
   $this->where("rid=$rid");
   $this->_execute();
  }
 }
 function _resultToString($a) {
  return $this->_actionToString($a);
 }
 function _actionToString($a) {
  switch ($a) {
  case OREQ_RESERVED:
  case OREQ_RESERVE2:
   $r	= 'ÇÀÐÅÇÅÐÂÈÐÎÂÀÍÎ';
   break;
  case OREQ_DELAY:
   $r	= 'ÎÒËÎÆÅÍÀ';
   break;
  case OREQ_ANALIZE:
   $r	= 'ÀÍÀËÈÇ';
   break;
  case OREQ_PROCESS:
   $r	= 'Â ÐÀÁÎÒÅ';
   break;
  case OREQ_TIMEOUT:
   $r	= 'ÎÒÊËÎÍÅÍ';
   break;
  case OREQ_DONE:
   $r	= 'ÃÎÒÎÂ';
   break;    
  case OREQ_WAIT:
   $r	= 'ÎÆÈÄÀÍÈÅ';
   break;
  case OREQ_COMMENT:
   $r	= 'ÊÎÌÌÅÍÒÀÐÈÉ';
   break;
  default:
   $r	= "Íåèçâåñòíî($a)";
  }
  return $r;
 }
 function _timeConvert($time) {        
  return $this->toUnixTimestamp($time);
 }                                     
 function _timeToString($time) {
  return $time?date('d-m-y H:i', $time):'';     
 }                                     
 function _timeNative($t) {
  $d	= "'".$this->toTimestamp($t)."'";
  return $d;
 }
 function _time2Convert($time) {        
  return $this->toUnixTimestamp($time);
 }                                     
 function _time2ToString($time) {       
  return $time?date('d-m-y H:i', $time):'';     
 }                                     
 function _time2Native($t) {
  $d	= "'".$this->toTimestamp($t)."'";
  return $d;
 }
 function _doneConvert($time) {        
  return $this->toUnixTimestamp($time);
 }                                     
 function _doneToString($time) {       
  return $time?date('d-m-y H:i', $time):'';     
 }                                     
 function _doneNative($t) {
  $d	= "'".$this->toTimestamp($t)."'";
  return $d;
 }
 function range($date, $interval) {             
  parent::timeRange($date, $interval, 'time');
 }
 function retrive($rid=false) {
//  $this->orderBy('time', false);
  if ($rid !== false) {
   $this->findBy("(rid=$rid OR pid=$rid)");
   $this->groupBy('rid');
  } else {
//   $this->findBy("action=1");
//   $this->findBy("(pid IS NULL OR pid = 0)");
   $this->findBy("(pid = 0)");
  }
  return $this->_execute();
 }
 function update() {
  parent::update('rid');
 }
 function contains($txt) {
  $DB2		= "(SELECT IF(pid = 0,rid,pid) AS ftsid FROM requests WHERE MATCH (text) AGAINST ('$txt' IN BOOLEAN MODE)) AS fts";
  $this->_from($DB2);
  $this->where('requests.rid = fts.ftsid');
 }
}

class RequestClass extends _ObjMySql {
 function __construct($id=false) {
  $fields		= array('id',		//Uniqueue ID
				'cid',		//Class ID for bitmask
				'name', 	//Class name
				'text', 	//Class text
				'pid'		//Parent ID
			);
  $this->_fieldsCreate	= array('cid',
				'name',
				'text',
				'pid'
			);
  $this->_fieldsUpdate	= array('cid',
				'name',
				'text',
				'pid'
			);
  $this->_keyFields	= array('id');		//5.2.0
  parent::__construct();
  $this->_db	= OREQ_CLASSES;
  $this->_addField($fields, $this->_from());
  if ($id) {
   $this->where("id='$id'");
   $this->_execute();
  } else {
   $this->orderBy('');
  }
 }
}
?>
