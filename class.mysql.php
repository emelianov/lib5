<?php
//mysql.php	v5.1.1
/*
5.1.1
- Compatibility fixes
5.1.0
- Explict codepage select (Windows compatibility)
- SQL-based range of rows select
*/

require_once(OLIB_PATH.'/class.sql.php');

global		$omysqli;
$omysqli	= mysqli_connect(OMY_HOST, OMY_USER, OMY_PASS, OMY_DB);
$query		= "SET character_set_client='cp1251'";		//5.1.0
mysqli_query($omysqli, $query);					//5.1.0
$query		= "SET character_set_connection='cp1251'";	//5.1.0
mysqli_query($omysqli, $query);					//5.1.0
$query		= "SET character_set_results='cp1251'";		//5.1.0
mysqli_query($omysqli, $query);					//5.1.0



class _ObjMysql extends _ObjSql {
 var		$_fromRowMy;		//5.1.0
 var		$_rowCountMy;		//5.1.0
 var		$_types	= array(	//5.0.4
				'tinyint',
				'smallint',
				'mediumint',
				'int',
				'bigint',
				'float',
				'double',
				'decimal',
				'date',
				'datetime',
				'timestamp',
				'time',
				'year',
				'char',
				'varchar',
				'tinyblob',
				'tinytext',
				'blob',
				'text',
				'mediumblob',
				'mediumtext',
				'longblob',
				'longtext',
				'enum',
				'set'
			);
 function __construct($fields=false) {	//5.1.1
  parent::__construct($fields);
 }
 function toUnixTimestamp($d) {
  // YYYY-MM-DD HH:MM:SS
  if ($d && $d != '0000-00-00 00:00:00') {		//5.0.2
   return mktime(substr($d, 11, 2), substr($d, 14, 2), substr($d, 17, 2), substr($d, 5, 2), substr($d, 8, 2), substr($d, 0, 4));
  } else {
   return false;
  }
 }
 function toTimestamp($date) {
  if ($date != false) {			//5.0.2
   return date('Y-m-d H:i:s', $date);                                           
  } else {
   return '';
  }
 }

 function _prepare() {
  $query		= parent::_prepare();
  if ($this->_fromRowMy) {		//5.1.0
   $query		.= ' LIMIT '.$this->_fromRowMy;
   if ($this->_rowCountMy) {
    $query		.= ', '.$this->_rowCountMy;
   } else {
    $query		.= ', 1000000';
   }
  } else {
   if ($this->_rowCountMy) {
    $query		.= ' LIMIT '.$this->_rowCountMy;
   }
  }
  return		$query;
 }

 function fromRow($offset=0) {		//5.1.0
  $this->_fromRowMy	= $offset;
 }
 function rowCount($count=false) {	//5.1.0
  $this->_rowCountMy	= $count;
 }
 
 function timeRange($date, $interval, $name) {
  $this->date           = $date;                                                                
  $this->interval       = $interval;                                                            
  $sdate                = $this->toTimestamp($date);
  $fdate		= $this->toTimestamp($date + $interval);
  $this->findBy("
    (
    '$sdate' <= $name AND
    $name < '$fdate'
    )"
  );  
 }

 function _select($query) {                                                              
  global $omysqli;
//  echo $query;
//  if ($fhandle	= fopen(DBG_LOG, 'a')) {
//   fwrite($fhandle, $query."\n");
//   fclose($fhandle);
//  }
  if ($result = mysqli_query($omysqli, $query)) {                                                               
   $trow	= array();
   while ($row = mysqli_fetch_assoc($result)) {
    $trow[]	= $row;
   }
   mysqli_free_result($result);
//echo count($trow);
   return (count($trow) > 0)?$trow:false;                                                                        
  } else {
//   echo('!');
  }                                                                                      
  return false;                                                                          
 }                                                                                       

 function _insert($query) {                                                              
  global $omysqli;
  if ($result = mysqli_query($omysqli, $query)) {                                                               
//   mysqli_free_result($result);
   return true;                                                                          
  } else {                                                                               
//   echo $query."\n".mysqli_error($omysqli)."\n";
//  echo $query;
  if ($fhandle	= fopen(DBG_LOG, 'a')) {
   fwrite($fhandle, $query."\n");
   fclose($fhandle);
  }
   return false;                                                                         
  }                                                                                      
//  echo $query."\n";
 }                                                                                       
 function autocommit($mode=true) {
  global $omysqli;
  if ($mode) {
   mysqli_query($omysqli, 'SET AUTOCOMMIT=1');
  } else {
   mysqli_query($omysqli, 'SET AUTOCOMMIT=0');
  }
 }
 function commit() {
  global $omysqli;
  mysqli_query($omysqli, 'COMMIT');
 }
 function lastid() {
  if (!$this->_lastid) {
   $query	= "SELECT LAST_INSERT_ID() AS id";
   $row		= $this->_select($query);
   $this->_lastid= $row[0]['id'];
   return $this->_lastid;
  } else {
   return $this->_lastid;
  }
 }
}                                       
?>