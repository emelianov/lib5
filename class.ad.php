<?php
//ad.php	v5.2.2

include_once(OLIB_PATH.'/class.ldap.php');
//include_once(OLIB_PATH.'/class.smtp.php');	//5.2.0

define('OUSER_ALL',	'*');
define('OUSER_LOGIN',	0);
define('OUSER_ID',	1);
define('OUSER_FULLNAME',2);
define('OUSER_CN',	3);
define('OUSER_OBJECT',	4);
define('OUSER_TAB',	5);

class AD extends _ObjLdap {		//5.1.0
 function __construct($dn, $fields) {
  parent::__construct($dn, $fields);
 }
 function toUnixtimestamp($t) {
  return mktime (0, 0, 0, 1, 1, 2003) + substr($t, 0, 11) - 12685849200 + 4*3600;
 }
 function toUnixtimestamp2($d) {
  // YYYYMMDDHHMMSS.0Z
  return mktime(substr($d, 8, 2), substr($d, 10, 2), substr($d, 12, 2), substr($d, 4, 2), substr($d, 6, 2), substr($d, 0, 4));
 }
 function _pwdlastsetConvert($time) {
  return $this->toUnixtimestamp($time);
 }
 function _lastlogonConvert($time) {
  return $this->toUnixtimestamp($time);
 }
 function _lastlogontimestampConvert($time) {
  return $this->toUnixtimestamp($time);
 }
 function _lastlogoffConvert($time) {
  return ($time!=0)?$this->toUnixtimestamp($time):false;
 }
 function _whencreatedConvert($time) {
  return $this->toUnixtimestamp2($time);
 }
 function _whenchangedConvert($time) {
  return $this->toUnixtimestamp2($time);
 }
 function _modifytimestampConvert($time) {
  return $this->toUnixtimestamp2($time);
 }
 function _createtimestampConvert($time) {
  return $this->toUnixtimestamp2($time);
 }
 function groups() {	//5.1.1
  if (is_array($this->memberof)) {
   $users              = new Group($this->memberof[0], OUSER_FULLNAME);
   $users->toMulti();                                                 
   for ($i = 1; $i < count($this->memberof); $i++) {                   
    $u                 = new Group($this->memberof[$i], OUSER_FULLNAME);
    $u->toMulti();                                                    
    $users->push($u);                                                 
   }                                                                  
  } else {
   $users              = new Group($this->memberof, OUSER_FULLNAME);
   $users->toMulti();                                                 
  }
  return $users;
 }
}

class Group extends AD {
 function Group($uid, $uidType=OUSER_CN) {
  parent::__construct(array(OLDAP_DN1,OLDAP_DN), array(		'cn', 
					'location', 
					'description', 
					'name', 
					'member',
					'membetof',	//5.1.0
					'samaccountname',
					'objectsid'
			)); 
  $uid		= str_replace(array('(',')'),array('\(','\)'),$uid);
  switch ($uidType) {
  case OUSER_FULLNAME:
   $i	= strpos($uid, ',');
   $uid = substr($uid, 3, $i - 3); 
  default:
   $this->_execute('(&(cn='.$uid.')(objectClass=group))');
  }
 }
 function isMember($uid, $uidType=OUSER_LOGIN) {
  if ($uid != OUSER_OBJECT) {
   $user	= new User($uid, $uidType);
  } else {
   $user	= $uid;
  }
  $members	= $this->member;
  if (!is_array($members)) {
   $members= array();
   $members[0]= $this->member;
  }
  foreach ($members as $item) {
   $i		= strpos($item, ',');      
   $item	= substr($item, 3, $i - 3);
   if ($user->cn == $item) {
    return true;
   }
  }
  return false;
 }
 function users() {	//5.0.2
  if (is_array($this->member)) {
   $users              = new User($this->member[0], OUSER_FULLNAME);
   $users->toMulti();                                                 
   for ($i = 1; $i < count($this->member); $i++) {                   
    $u                 = new User($this->member[$i], OUSER_FULLNAME);
    $u->toMulti();                                                    
    $users->push($u);                                                 
   }                                                                  
  } else {
   $users              = new User($this->member, OUSER_FULLNAME);
   $users->toMulti();                                                 
  }
  return $users;
 }
}

class Computer extends AD {
 function Computer($oid) {
  parent::__construct(array(OLDAP_DN, OLDAP_DN1), array(		'cn',
					'location',
					'description',
					'name',
					'createtimestamp',
					'lastlogon',
					'lastlogontimestamp',
					'logoncount',
					'modifytimestamp',
					'operatingsystem',
					'operatingsystemservicepack',
					'operatingsystemversion',
					'pwdlastset',
					'whencreated',
					'whenchanged',
					'memberof'
				));
  $uid		= str_replace(array('(',')'),array('\(','\)'),$uid);
  $this->_execute('(&(cn='.$oid.')(objectClass=computer))');  
 }
}

class User extends AD {
 var	$smtp				= false;
 function __construct($uid, $uidType=OUSER_LOGIN) {
//  parent::__construct(array(OLDAP_DN,OLDAP_DN1), array(		'cn',
  parent::__construct(OLDAP_DN, array(		'cn',
					'samaccountname',
					'telephonenumber',
					'manager',
					'mail',
					'department',
					'description',
					'useraccountcontrol',
					'directreports',
					'userworkstations',			//5.0.2
					'lastlogon',				//5.0.4
					'lastlogoff',				//5.0.4
					'lastlogontimestamp',
					'badpasswordtime',
					'logoncount',
					'memberof',
					'pwdlastset',
					'whencreated',
					'whenchanged',
					'employeeid'				//5.2.0
				));
  $uid		= str_replace(array('(',')'),array('\(','\)'),$uid);
  switch ($uidType) {
  case OUSER_FULLNAME:
   $i	= strpos($uid, ',');
   $uid = substr($uid, 3, $i - 3); 
  case OUSER_CN:
   $this->_execute('(&(cn='.$uid.')(&(objectClass=user)(!(objectClass=computer))))');
   break;
  case OUSER_TAB:								//5.2.2
   $this->_execute('(&(employeeid='.$uid.')(&(objectClass=user)(!(objectClass=computer))))');
   break;
  default:
   $this->_execute('(&(|(userPrincipalName='.$uid.')(SAMAccountName='.$uid.'))(&(objectClass=user)(!(objectClass=computer))))');
  }
  if ($this->count > 1) {
   for ($i=0; $i < $this->count; $i++) {
    $this->userworkstations[$i]	= explode(',', $this->userworkstations[$i]);	//5.0.2
   }
  } else { 
   $this->userworkstations	= explode(',', $this->userworkstations);	//5.0.2
  }
 }
 function _badpasswordtimeConvert($time) {
  return $this->toUnixtimestamp($time);
 }
 function _pwdlastsetConvert($time) {
  return $this->toUnixtimestamp($time);
 }
 function checkPass($pass) {
  $ds       = ldap_connect(OLDAP_DS);
  ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 2);
  if (ldap_bind($ds, $this->samaccountname.'@'.OLDAP_DOMAIN, $pass)) {
   ldap_unbind($ds);
   return TRUE;
  } else {
   return FALSE;
  }
 }
 function removeDisabled() {
/* Disabled 5.1.0.TEMP
  foreach ($this->_fields as $field) {
   $tmp->$field	= array();
  }
  $count	= 0;
  for ($i = 0; $i < $this->count; $i++) {
   if (!($this->useraccountcontrol[$i] & 2)) {
    foreach ($this->_fields as $field) {
     $f		= & $tmp->$field;
     $t		= & $this->$field;
     $f[]	= $t[$i];

    }
    $count++;
   }
  }
  foreach ($this->_fields as $field) {
   $this->$field= $tmp->$field;
   $this->count	= $count;
  }
*/
 }
 
  function send($subject, $body, $from=OUSER_MAILFROM) {		//5.0.2
//  if (!$this->smtp) {
//   $this->smtp	= new SMTPClient(SMTP_SERVER, SMTP_PORT, SMTP_USER, SMTP_PASS);
//  }
  if (is_array($this->mail)) {
   foreach ($this->mail as $item) {
    $index	= strpos($item, '@');					//Patch A/    
    $pmail	= substr($item, 0, $index).'@oao.surgutneftegas.ru';	//Patch A
    mail($pmail, $subject, $body, "From: $from\r\n");
//   mail($item, $subject, $body, "From: $from\r\n");
//   $this->smtp->SendMail($from, $pmail, $subject, $body);
   }
  } else {
   $index	= strpos($this->mail, '@');				//Patch A
   $pmail	= substr($this->mail, 0, $index).'@oao.surgutneftegas.ru';//Patch A
//   mail($this->mail, $subject, $body, "From: $from\r\n");
//   $this->smtp->SendMail($from, $pmail, $subject, $body);
   mail($pmail, $subject, $body, "From: $from\r\n");
  }
 }
}


?>
