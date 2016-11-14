<?php
//class.session.php v4.0.2

define('DEF_COUNT',	100);
if (!defined('LOGIN_URL')) {
 define('LOGIN_URL',	'/tmn/login.php');
}

class Session {
 function Session($auth = FALSE) {
  global $aclCallback;
  session_start();
  $this->ip($_SERVER['REMOTE_ADDR']);
  if (!isset($_SESSION['uid']) && !$auth) {
   $this->url($_SERVER['REQUEST_URI']);
   $login_url = LOGIN_URL;
   header("Location: $login_url");
   exit;
   return false;
  } else {
   if (!$auth) {
    $this->url($_SERVER['REQUEST_URI']);
   }
   if (!$this->importGet('start')) {
    $this->dispose('start');
   }
   if (!$this->importGet('count')) {
    $this->valueOf('count', DEF_COUNT);
   }
   $aclCallback	= create_function('$user_id, $obj_id, $acc', 'header("Location: /tmn/deny.php?user_id=$user_id&obj_id=$obj_id&access=$acc"); exit;');
   return $this;
  }
 }
 function valueOf() {
  if(func_num_args() > 1) {
   $_SESSION[''.func_get_arg(0)] = func_get_arg(1);
  } else {
   if (isset($_SESSION[''.func_get_arg(0)])) {
    return $_SESSION[''.func_get_arg(0)];
   } else {
    return false;
   }
  }
 }
 function importGet($v) {
  if (isset($_GET[$v])) {
   $this->valueOf($v, $_GET[$v]);
   return true;
  } else {
   return false;
  }
 }
 function dispose($v) {
  unset($_SESSION[$v]);
 }
 function uid($v = FALSE) {
  if ($v) {
   return $this->valueOf('uid', $v);
  } else {
   return $this->valueOf('uid');
  }
 }
 function url($v = FALSE) {
  if ($v) {
   return $this->valueOf('url', $v);
  } else {
   return $this->valueOf('url');
  }
 }
 function ip($v = FALSE) {
  if ($v) {
   return $this->valueOf('ip', $v);
  } else {
   return $this->valueOf('ip');
  }
 }
 function login($v = FALSE) {
  if ($v) {
   return $this->valueOf('login', $v);
  } else {
   return $this->valueOf('login');
  }
 }
 function msg($v = FALSE) {
  if ($v) {
   return $this->valueOf('msg', $v);
  } else {
   $ret = $this->valueOf('msg');
   $this->dispose('msg');
   return $ret;
  }
 }
}

?>