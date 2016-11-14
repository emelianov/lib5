<?php
//wp2.php	v5.1.3 /2013

require_once(OLIB_PATH.'/class.mssql.php');

define('DB1', 'SAPR_VIEW_people');
define('DB2', 'SAPR_VIEW_atc');

class WpPerson extends _ObjMsSql {
 function __construct($f=false, $i=false, $o=false) {
  $fields	= array(	'firstname',	//Firstname
				'lastname',	//Lastname
				'note1',	//department
				'note2',	//title
				'note3',	//Middlename
				'note6',	//tab.id
				'note7',	//laboratory
				'note8',	//room
				'note9',	//building
//				'note10',	//birthday
				'recordid',	//id ???
				'dolg',		//new title
				'prior_dolg',
				'otdel',	//new department
//				'prior_otdel',
				'laba',		//new laboratory
				'lastlaba',	//L2 laboratory
//				'mesto',	//new room removed as double
//				'corpname',	//building
				'recordid',	//CardHolder recordid 5.1.2 ???
				'boss',		//chieff 5.2.0 Fam_IO
				'fio'
			);
  $fields2	= array(	'phone',
				'model',
				'mesto',
				'id_user',
				'hidephone'
			);
  $this->_from(DB1);
  $this->_from(DB2);
  parent::__construct();
  $this->_addField($fields, DB1);
  $this->_addField($fields2, DB2);
  $this->_addField('room', DB1.'.mesto');	//5.0.5
  $a		= DB1;
  $b		= DB2;
  $this->where("($b.hidephone=NULL OR $b.hidephone=0)");
//  $this->where(DB1.'.recordid='.DB2.'.id_user');
  if ($f) {
   if (!$i) {
    list($f, $i, $o) = explode(' ', $f);
   } else {
    if (!$o) {
     $j		= strpos($j, ' ');
     $o		= substr($i, $j + 1);
     $i		= substr($i, 0, $j);
    }
   }
   $this->where(DB1.".firstname='$i'");
   $this->where(DB1.".lastname='$f'");
   $this->where(DB1.".note3='$o'");
  }
 }
 function _from($db=false) {
  if ($db) {
   return parent::_from($db);
  } else {
   $a		= DB1;
   $b		= DB2;
   return "$a LEFT JOIN $b ON $a.recordid=$b.id_user";
  }
 }
 function toString() {
  return $this->firstname.' '.$thid->lastname;
 }
}
                                                
class WpCard extends _ObjMsSql {
 function __construct($hid=false) {
  $fields	= array(	'cardnumber',
				'cardholderid');
  $this->_db	= 'card';
  parent::__construct($fields);
  if ($hid) {
   $this->where("cadrholderid='$hid'");
  }
  $this->_execute();
 }
}

class WpHistory extends _ObjMsSql {
 function __construct($f=false, $io=false) {
  $fields	= array(	'gentime',
				'param3',	//tab.id
				'firstname',	//Firstname Middlename
				'lastname',	//Lastname
				'name',		//in-out name
				'link1',	//in-out id
				'link3'		//CardHolder recordid 5.1.2
		    );
  $this->_db	= 'history';
  parent::__construct($fields);
  if ($f) {
   if (!$io) {
    $i		= strpos($f, ' ');
    $io		= substr($f, 0, $i - 1);
    $f		= substr($f, $i + 1);
   }
   $this->where("firstname='$f'");
   $this->where("lastname='$io'");
  }
 }
 function _gentimeConvert($val) {
  return $this->toUnixTimestamp($val);
 }
 function _gentimeToString($time) {
  return $time?date('d-m-y H:i', $time):'';
 }
 function timeRange($date, $interval) {
  parent::timeRange($date, $interval, 'gentime');
 }
 function isIn($txt) {
  return $txt="Допу";
 }
}

class WpList extends _ObjMsSql {
 function __construct($id_0=false) {
  $fields	= array(	'id',	//ID
				'name',	//Name
				'id_0',	//Type (1 - otdel)
				'id_1',	//Subordination parent
				'id_2',
				'id_3'
			);
  $this->_db	= 'sapr_direct';
  parent::__construct($fields);
  if ($id_0) {
   $this->where('id_0=\''.$id_0.'\'');
  }
 }
}

class WpTechnik extends _ObjMsSql {	//5.1.0
 function __construct($user=false) {
  $fields	= array(	'id_user',	//users.recordid
				'inn',		//Inventar Nr
				'name',		//name
				'ser',		//serial nr
				'corp',		//Building+room
				'type',		//Type (PC, Printer, etc)
				'id_type'	//Type (401 -- Soft)
			);
  $this->_db	= 'sapr_view_technik';
  parent::__construct($fields);
  if($user !== false) {
   $this->where("id_user=$user");
  }
 }
}

class WpLeave extends _ObjMsSql {	//5.2.0
 function __construct($id=false) {
  $fields	= array(	'tabel',	//users.note6
				'fio',
				'vidotpusk',	//OC
				'txtotpusk',	//
				'dfrom',
				'tfrom',
				'dto',
				'tto',
				'viza',
				'id',
				'vizafio'//5.2.1
			);
  $this->_fieldsUpdate	= array('tabel',
				'fio',
				'vidotpusk',
				'txtotpusk',
				'dfrom',
				'tfrom',
				'dto',
				'tto',
				'viza',
			);
  $this->_keyFields	= array('id');
  $this->_db	= 'WT_leave';
  parent::__construct($fields);
  if ($id !== false)
   $this->where("id='$id'");
 }
 function _tfromConvert($val) {
  return mktime(substr($val, 0, 2), substr($val, 3,2), 0, 0, 0, 0);
 }
 function _tfromToString($time) {
  return $time?date('H:i', $time):'';
 }
 function _dfromConvert($val) {
  return $this->toUnixTimestamp($val);
 }
 function _dfromToString($time) {
  return $time?date('d-m-y', $time):'';
 }

 function _ttoConvert($val) {
  return mktime(substr($val, 0, 2), substr($val, 3,2), 0, 0, 0, 0);
 }
 function _ttoToString($time) {
  return $time?date('H:i', $time):'';
 }
 function _dtoConvert($val) {
  return $this->toUnixTimestamp($val);
 }
 function _dtoToString($time) {
  return $time?date('d-m-y', $time):'';
 }
 function timeRange($date, $interval) {
  parent::timeRange($date, $interval, 'dfrom');
 }
 function update() {
  return parent::update('id');
 }
}

class WpBehalf extends _ObjMsSql {		//5.2.1
 function __construct($tab=false) {
  $fields		= array('id',		//
				'tab',		//Original TAB
				'tabio',	//Replacer TAB
				'typeio',	//Replacement type
				'datafrom',	//from
				'datato'	//to
			);
  $this->_fieldsCreate	= array('tab',		//Original TAB
				'tabio',	//Replacer TAB
				'typeio'	//Replacement type
			);
  $this->_keyFields	= array('id');

  $this->_db	= 'wt_io';
  parent::__construct($fields);
  if($tab !== false) {
   $this->where("tab=$tab");
  }
 }
}

?>
