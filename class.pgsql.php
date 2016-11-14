<?php
// lib5 (c)2006 - 2016 a.m.emelianov@gmail.com
// pgsql.php	v2016

require_once(OLIB_PATH.'/class.sql.php');

global		$opgsql;
$opgsql		= pg_connect("host=".OPG_HOST." dbname=".OPG_DB." user=". OPG_USER." password=".OPG_PASS);

class _ObjPgSql extends _ObjSql {
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
  global $opgsql;
//  error_log($query);
//  if ($fhandle	= fopen(DBG_LOG, 'a')) {
//   fwrite($fhandle, $query."\n");
//   fclose($fhandle);
//  }
  if ($result = pg_query($opgsql, $query)) {                                                               
   $trow	= array();
   while ($row = pg_fetch_assoc($result)) {
    $trow[]	= $row;
   }
   pg_free_result($result);
//echo count($trow);
   return (count($trow) > 0)?$trow:false;                                                                        
  } else {
//   echo('!');
  }                                                                                      
  return false;                                                                          
 }                                                                                       

 function _insert($query) {                                                              
  global $opgsql;
  if ($result = pg_query($opgsql, $query)) {                                                               
//   mysqli_free_result($result);
   return true;                                                                          
  } else {                                                                               
//   echo $query."\n".mysqli_error($opgsql)."\n";
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
  global $opgsql;
  if ($mode) {
   pg_query($opgsql, 'SET AUTOCOMMIT=1');
  } else {
   pg_query($opgsql, 'SET AUTOCOMMIT=0');
  }
 }
 function commit() {
  global $opgsql;
  pg_query($opgsql, 'COMMIT');
 }
 function lastid($field=false, $query=false) {
  if (!$this->_lastid) {
   $query	= "select lastval() as id";
   $row		= $this->_select($query);
   $this->_lastid= $row[0]['id'];
   return $this->_lastid;
  } else {
   return $this->_lastid;
  }
 }
}
?>
