<?php
/*

   lib5 PHP database helper library
   (c)2006 - 2016, a.m.emelianov@gmail.com

   sql.php	v5.4.0

5.4.0
- where() and having() expanding
5.3.2
- Compatibility fixes
5.3.1
- Update method fix
5.3.0
- ODBC support
- Added common Sequence class
5.2.2
- PHP5 compatibility fix
5.2.1
- Partial (not all) fields update support
5.2.0
- Cursor support
5.1.1
- Multi-server connection support
- Per-object (not global) date format settings
- Partial (not all) fields update support
*/

require_once(OLIB_PATH.'/class.base.php');

class _ObjSql extends _Obj {
 var		$_where		= false;
 var		$_orderBy	= false;
 var		$_groupBy	= false;
 var		$_having	= false;
 var		$_db		= false;
 var		$date		= false;
 var		$interval	= false;
 var		$_dbfields	= array();	//5.0.2
 var		$_lastid	= false;	//5.0.2
 var		$_fromRow	= 0;		//5.0.3
 var		$_rowCount	= false;	//5.0.3
 var		$_LINK		= false;	//5.1.1
 var		$dateFormat	= 'd-m-Y H:i:s';//5.1.1
 var		$cursor		= false;	//5.2.0
 var		$_keyFields	= false;	//5.3.0
 var		$_lastID_field	= false;	//5.3.0
 var		$_lastID_query	= false;	//5.3.0

 function __construct($fields=false) {		//5.2.2
  parent::__construct();
  if ($fields !== false) {
   $this->_addField($fields);
//   foreach ($fields as $name) {
//    $this->_addField($name);
//   }
  }
 }
 function toUnixTimestamp($d) {
  // DD-MM-YYYY HH:MM:SS
  return mktime(substr($d, 11, 2), substr($d, 14, 2), substr($d, 17, 2), substr($d, 3, 2), substr($d, 0, 2), substr($d, 6, 4));
 }
 function toTimestamp($date) {
  return date($this->dateFormat, $date);
  //  return "'".date($this->dateFormat, $date)."'"
 }
 function _from($name=false) {			//5.1.0
  if ($name) {
   if ($this->_db === false) {
    $this->_db		= $name;
   } else {
    if (is_array($this->_db)) {
     $this->_db[]	= $name;
    } else {
     $tmp		= $this->_db;
     $this->_db		= array();
     $this->_db[]	= $tmp;
     $this->_db[]	= $name;
    }
   }
  } else {
   if (is_array($this->_db)) {
    $result		= '';
    foreach($this->_db as $item) {
     $result		.= $item.',';
    }
    $result		= substr($result, 0, strlen($result) - 1);
    return $result;
   } else {
    return $this->_db;
   }
  }
 }
 function orderBy($row=false, $reverse = false) {           
  if ($row) {
   if ($this->_orderBy !== false) {
    $this->_orderBy	.= ',';
   }
   $this->_orderBy	.= "$row".(($reverse)?' DESC':'');
  } else {
   $this->_orderBy	= '';
  }
 }
 function groupBy($row=false) {
  if ($row) {
   if ($this->_groupBy !== false) {
    $this->_groupBy	.= ',';
   }
   $this->_groupBy	.= "$row";
  } else {
   $this->_groupBy	= '';
  }
 }
 function __having($cond, $union) {		//5.4.0
  if ($this->_having) {
   $this->_having	.= " $union ";
  }
  $this->_having	.= " $cond";
 }
 function having($cond=false, $union='AND') {
  if ($cond !== false) {
   if (is_array($cond)) {			//5.4.0
    foreach ($cond as $c) {
     $this->__having($c, $union);
    }
   } else {
    $this->__having($cond, $union);
   }
  } else {
   $this->_having	= '';
  }
 }
 function __findBy($cond, $union) {		//5.4.0
  if ($this->_where) {
   $this->_where	.= " $union ";
  }
  $this->_where		.= " $cond";
 }
 function where($cond=false, $union='AND') {	//5.0.2
  $this->findBy($cond, $union);
 }
 function findBy($cond=false, $union='AND') {
  if ($cond !== false) {
   if (is_array($cond)) {			//5.4.0
    foreach ($cond as $c) {
     $this->__findBy($c, $union);
    }
   } else {
    $this->__findBy($cond, $union);
   }
  } else {
   $this->_where	= '';
  }
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

 function _prepare() {
  if ($this->_query === false or is_null($this->_query)) {	//5.3.2
   $set			= '';
   for ($i=0; $i < count($this->_map); $i++) {
    $set		.= $this->_map[$i].' as '.$this->_fields[$i].',';
   }
   $set		= substr($set, 0, strlen($set) - 1);
   $query		= "SELECT $set FROM ".$this->_from();
  } else {
   $query                = $this->_query;
  }
  if ($this->_where) {                                 
   $query              .= ' WHERE '.$this->_where;     
  }                                                    
  if ($this->_groupBy) {                               
   $query              .= ' GROUP BY '.$this->_groupBy;
  }                                                    
  if ($this->_orderBy) {                               
   $query              .= ' ORDER BY '.$this->_orderBy;
  }                                                    
  if ($this->_having) {                                
   $query              .= ' HAVING '.$this->_having;   
  }
  return $query;
 }
 function _select($query) {
// log_error($query);
  if ($result = odbc_exec($this->_LINK, $query)) {
   $trow        = array();
   //odbc_longreadlen($result, 10240000);                        
   while (odbc_fetch_row($result)) {
    $row	= array();
    foreach ($this->_fields as $field) {
     $row[$field]	= odbc_result($result, $field);
     //echo $row[$field];
    }
    $trow[]     = $row;                           
   }
   odbc_free_result($result);
   return (count($trow) > 0)?$trow:false;                                                        
  }
  return false;                                   
 }
 function _insert($query) {
// echo $query;
  if ($result = odbc_exec($this->_LINK, $query)) {
   odbc_free_result($result);
   return true;                                   
  }                                        
  return false;                                  
 }
 function _cursor($name, $query) {				//5.2.0
  $q		= "DECLARE $name CURSOR STATIC READ_ONLY FOR $query";
  return $this->_insert($q)?$name:false;
 }
 function _cursorOpen($name) {					//5.2.0
  $q		= "OPEN $name";
  return $this->_insert($q);
 }
 function _cursorFetch($name, $i=false) {			//5.2.0
  if ($i === false) {
   $q		= "FETCH NEXT FROM $name";
  } else {
   $q		= "FETCH ABSOLUTE $i FROM $name";
  }
  return $this->_select($q);
 }
 function _execute($query=false, $fields=false) {
  if ($query === false) {
   $curQuery		= $this->_prepare();
  } else {
   $curQuery		= $query;
  }
//error_log($curQuery);
//echo $curQuery.'<BR>';
  if ($this->debug) {						//5.1.2
   echo $curQuery;
  }
  if ($this->cursor !== false) {				//5.2.0
   if ($cursorName = $this->_cursor($this->cursor, $curQuery)) {
    $this->_cursorOpen($this->cursor);
    $trow		= $this->_cursorFetch($this->cursor, (($this->_fromRow === 0)?false:$this->_fromRow));
    $i			= 0;
    while ($trow && (($this->_rowCount === false)?true:($i < $this->_rowCount))) {
     $row		= $trow[0];
     $row		= array_change_key_case($row, CASE_LOWER);
     for ($k = 0; $k < count($this->_map); $k++) {
      $name		= $this->_fields[$k];
      $fname		= $this->_fields[$k];
      $field		= & $this->$fname;
      $field[$i]	= isset($row[$name])?$row[$name]:false;
      $funcName		= '_'.$fname.'Convert';
      if (method_exists($this, $funcName)) {
       $field[$i]	= $this->$funcName($field[$i]);
      }
     }
     $i++;
     $trow		= $this->_cursorFetch($this->cursor);
    }
    $this->count	= $i;
   }
  } else {
   if ($trow = $this->_select($curQuery)) {
    $i		= 0;
    $jLim	= $this->_rowCount?($this->_fromRow + $this->_rowCount):count($trow);
    $jLim	= ($jLim < count($trow))?$jLim:count($trow);
    $count	= 0;
    for ($j=$this->_fromRow; $j<$jLim; $j++) {
     $count++;
     $row	= $trow[$j];
     $row	= array_change_key_case($row, CASE_LOWER);	//5.0.2
     for ($k = 0; $k < count($this->_map); $k++) {		//5.1.0
      $name		= $this->_fields[$k];			//5.1.0
      $fname		= $this->_fields[$k];			//5.1.0
      $field		= & $this->$fname;			//5.1.0
      $field[$i]	= isset($row[$name])?$row[$name]:false;	//5.0.2
      $funcName		= '_'.$fname.'Convert';
      if (method_exists($this, $funcName)) {
       $field[$i]	= $this->$funcName($field[$i]);
      }
     }
     $i++;
    }
    $this->count	= $count;
    return true;
   } else {
    return false;
   }
  }
 }
 function save() {
  $field		= $this->_keyFields[0];
  $keys			= $this->$field;
  if ($keys === false) {
   $this->insert();
  } else {
   $this->update($field);
  }
 }
 function insert($keyField=false) {
  $rows			= '';
  $values		= '';
  $db		= $this->_from();
  if (!$keyField) {				//5.3.0
   $keyField	= $this->_keyFields[0];
  }
  if (count($this->_fieldsCreate) > 0) {	//5.2.1
   $tarFields	= & $this->_fieldsCreate;
  } else {
   $tarFields	= & $this->_fields;
  }
  $keyFieldName	= $this->_keyFields[0];
  $key		= & $this->$keyFieldName;
  for ($i = 0; $i < $this->count; $i++) {
   foreach ($tarFields as $name) {
    $field	= & $this->$name;
    $funcName	= '_'.$name.'Native';
    if (method_exists($this, $funcName)) {
     $value	= $this->$funcName($field[$i]);
    } else {
     $value	= "'".addslashes($field[$i])."'";
    }
    if (strlen($rows) > 0) {
     $rows	.= ',';
     $values	.= ',';
    }
    $rows	.= $name;
    $values	.= $value;
   }
   $this->_query	= "INSERT INTO $db ($rows) VALUES ($values)";
// error_log($this->_query);
   if ($this->_insert($this->_query)) {
   //echo "Insert Ok.\n";
    $kid	= $this->lastID();
    $key[$i]	= $kid;
    return true;
   } else {
    echo $this->_query."\n";
    return false;
   }
  }
 }

 function update($keyField=false) {		//5.0.2
  $pare			= '';
  $db		= $this->_from();
  if (!$keyField) {				//5.3.0
   $keyField	= $this->_keyFields[0];
  }
  if (count($this->_fieldsUpdate) > 0) {	//5.1.1, 5.2.1
   $tarFields	= & $this->_fieldsUpdate;
  } else {
   $tarFields	= & $this->_fields;
  }
  for ($i = 0; $i < $this->count; $i++) {
   foreach ($tarFields as $name) {
    if ($name != $keyField) {
     $field	= & $this->$name;
     $funcName	= '_'.$name.'Native';
     if (method_exists($this, $funcName)) {
      $value	= $this->$funcName($field[$i]);
     } else {
      $value	= "'".addslashes((string)$field[$i])."'";
     }
     if (strlen($pare) > 0) {
      $pare	.= ',';
     }
     $pare	.= $name.'='.$value;
    }
   }
   $fl	= $this->$keyField;
   $this->_query	= "UPDATE $db SET $pare WHERE ".$this->_from().".$keyField='".$fl[$i]."'"; //5.1.1
//   echo $this->_query."\n";
   if ($this->_insert($this->_query)) {
//    echo $this->_query."\n";
//    return true;
   } else {
//    echo $this->_query."\n";
//    return false;
   }
  }
 }
 function erase($keyField=false) {		//5.3.0
  if ($this->count > 0) {
   $db		= $this->_from();
   if (!$keyField) {
    $keyField	= $this->_keyFields[0];
   }
   $fl		= $this->$keyField;
   $where	= "$db.$keyField='".$fl[0]."'";
   for ($i = 1; $i < $this->count; $i++)
    $where	.= "OR $db.$keyField='".$fl[$i]."'";
   $this->_query	= "DELETE FROM $db WHERE $where";
//   echo $this->_query."\n";
   return $this->_insert($this->_query);
  }
  return false;
 }

 function autocommit($mode=true) {
  return odbc_autocommit($this->_LINK, $mode);
 }
 function commit() {
  return odbc_commit($this->_LINK);
 }
 function rollback() {			//5.3.0
  return odbc_rollback($this->_LINK);
 }
 function retrive() {			//5.0.2
  $this->_execute();
 }
 function lastID($field=false, $query=false) {	//5.0.2, 5.3.0
  if (!$field) {
   $field	= $this->_lastID_field;
   $query	= $this->_lastID_query;
  }
  $res		= false;
/*
  if ($result = odbc_exec($this->_LINK, $query)) {
   if (odbc_fetch_row($result)) {
     $res	= odbc_result($result, $field);
   }
   odbc_free_result($result);
  }
*/
  $trow 	= $this->_select($query);
  if ($trow) {
   $res		= $trow[0][0];
  }
  return $res;
 }
 function fromRow($offset=0) {		//5.0.3
  $this->_fromRow	= $offset;
 }
 function rowCount($count=false) {	//5.0.3
  $this->_rowCount	= $count;
 }
}                                       

class _ObjSqlSeq extends _ObjSql {		//5.3.0
 function __construct($name) {
  $this->_db			= $name;
 }
 function curr() {
 }
 function next() {
  return $this->lastID();
 }
}

?>