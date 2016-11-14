<?php
//mssql.php	v5.1.0
/*
5.1.0
- Multi-server connection support
*/

require_once(OLIB_PATH.'/class.sql.php');

global		$omssql;
$omssql		= mssql_pconnect(OMSSQL_SERVER, OMSSQL_USER, OMSSQL_PASS);
mssql_select_db(OMSSQL_DB,$omssql);

class _ObjMssql extends _ObjSql {
 function __construct($fields=false) {
  global $omssql;
  parent::__construct($fields);
  $this->_LINK		= $omssql;	//5.1.0
 }
 function toUnixTimestamp($d) {
  // YYYY-MM-DD HH:MM:SS
  return mktime(substr($d, 11, 2), substr($d, 14, 2), substr($d, 17, 2), substr($d, 5, 2), substr($d, 8, 2), substr($d, 0, 4));
 }
// function toUnixTimestamp($d) {
  // DD-MM-YYYY HH:MM:SS
//  return mktime(substr($d, 11, 2), substr($d, 14, 2), substr($d, 17, 2), substr($d, 3, 2), substr($d, 0, 2), substr($d, 6, 4));
// }
 function timeRange($date, $interval, $name) {                  
  $this->date           = $date;                                
  $this->interval       = $interval;                            
  $sdate                = $this->toTimestamp($date);            
  $fdate                = $this->toTimestamp($date + $interval);
  $this->findBy("                                               
	($name BETWEEN '$sdate' AND '$fdate')
    ");                                                            
//echo $this->_where;
 }                                                              
 function _select($query) {                       
// echo $query."<BR>";
  if ($result = mssql_query($query, $this->_LINK)) { 
   $trow        = array();                        
   while ($row = mssql_fetch_array($result)) {   
    $trow[]     = $row;                          
   }                                              
   mssql_free_result($result);                   
   return (count($trow) > 0)?$trow:false;         
  }                                               
  return false;                                   
 }                                                
                                                 
 function _insert($query) {                       
// echo $query;
  if ($result = mssql_query($query, $this->_LINK)) { 
   return true;                                   
  } else {                                        
  return false;                                  
 }                                               
}                                                

}
?>