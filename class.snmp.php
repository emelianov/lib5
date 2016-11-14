<?php
//smnp.php	v5.0.0

require_once(OLIB_PATH.'/class.base.php');

class _Objsnmp extends _Obj {
 var $_host;
 var $_community;
 var $index             = array();
 function _Objsnmp($host, $community=OSNMP_CREAD, $timeout=OSNMP_TIMEOUT, $retry=OSNMP_RETRY) {
  $this->_Obj();
  $this->_host		= $host;
  $this->_community	= $community;
  $this->_timeout	= $timeout;
  $this->_retry		= $retry;
 }
 function retrive($name, $keyname=false) {
  $this->_fields[]	= $name;
  $this->_map[]		= $name;
  $a 			= snmpwalkoid($this->_host, $this->_community, $name, 120000, 3);
  for (reset($a); $i = key($a); next($a)) {
   $j			= strpos($i, '::') + 2;          
   $v			= substr($i, $j);                
   $j			= strpos($v, '.');               
   $index		= substr($v, $j + 1);    
   $fmap	       	= substr($v, 0, $j);     
   $fname		= str_replace(	array(	'-', '.'),
					'',
					$fmap);
   $j  = strpos($a[$i], ':');           
   $value      = substr($a[$i], $j + 2);
   if (($j = array_search($index, $this->index)) === false) {
    $this->index[]	= $index;
    $j			= count($this->index) - 1;
   }
   if (!isset($this->$fname)) {
    $this->$fname	= array();
    $this->_fields[]	= $fname;
    $this->_map[]	= $fmap;
   }
   $field		= & $this->$fname;
   $field[$j]		= $value;
  }
  $this->count		= count($this->index);
 }
 function formatMAC($mac, $separator=':') {
//  $tmp			= str_replace(array(' ', '-'), ':', $mac);
  $tmp			= $mac;
  for ($i = 0; $i < count($tmp); $i++) {
   $tmp[$i]		= str_replace(array(' ', '-'), ':', $tmp[$i]);
   $tmp[$i]		= strtoupper($tmp[$i]);
   $tmp[$i]		= str_replace(	array(	':0:', ':1:', ':2:', ':3:', ':4:', ':5:', ':6:', ':7:',
						':8:', ':9:', ':A:', ':B:', ':C:', ':D:', ':E:', ':F:'),
					array(	':00:', ':01:', ':02:', ':03:', ':04:', ':05:', ':06:', ':07:',
						':08:', ':09:', ':0A:', ':0B:', ':0C:', ':0D:', ':0E:', ':0F:'),
					$tmp[$i]);
   if ($tmp[$i][0] == ':') {
    $tmp[$i]		= '0'.$tmp[$i];
   }
   if ($tmp[$i][1] == ':') {
    $tmp[$i]		= '0'.$tmp[$i];
   }
   $tmp[$i]		= substr($tmp[$i], 0, 15).str_repeat('0', 17 - strlen($tmp[$i])).substr($tmp[$i], 15);
  }
  return $tmp;
 }
}

         
?>