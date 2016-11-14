<?php
//class.cache.php v4.3.0

define('CACHE_UNLIM',	-1);
define('CACHE_NAMES',	0);
define('CACHE_EXPIRES',	1);
define('CACHE_HIT',	4);
define('CACHE_MISS',	5);
define('CACHE_STORE',	6);
define('CACHE_EXPIRED', 7);

class Cache {
 var $shm_key;
 var $shm_id;
 var $sem_key;
 var $sem_id;
 function Cache() {
 }
 function valueOf($id, $value=false, $expiresIn=CACHE_UNLIM) {
  return false;
 }
 function delete($id) {
 }
 function destroy() {
 }
 function getId($name) {
  return false;
 }
}

?>