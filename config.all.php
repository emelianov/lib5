<?php
//config.all.php	v5.2.0/v2016

//Files location
define('PWD_FILES',	$_SERVER['DOCUMENT_ROOT'].'/files');

//PgSql settings
define('OPG_USER',	'mx');
define('OPG_PASS',	'mx');
define('OPG_HOST',	'127.0.0.1');
define('OPG_DB',	'mx');

//MySql settings
define('OMY_USER',	'root');
define('OMY_PASS',	'');
//define('OMY_HOST',	'10.124.13.24');
define('OMY_HOST',	'127.0.0.1');
define('OMY_DB',	'wf');

//MsSql settings
define('OMSSQL_USER',   'OAO\WebServer');           
define('OMSSQL_PASS',   'pass');       
//define('OMSSQL_USER',   'OAO\Emelianov_AM');           
//define('OMSSQL_PASS',   '12345678');       

define('OMSSQL_SERVER', 'TONIPI-SQL-01');
define('OMSSQL_DB',     'WP2');          

//AD LDAP settings
define('OLDAP_DS',	'TONIPI-DC-01v');
define('OLDAP_DN',	'ou=ТО СургутНИПИнефть,ou=Структурные подразделения,dc=oao,dc=sng');
define('OLDAP_DN1',	'ou=СургутНИПИнефть,ou=Структурные подразделения,dc=oao,dc=sng');
define('OLDAP_DOMAIN',	'oao.sng');
define('OLDAP_USER',	'WebServer');
define('OLDAP_PASS',	'pass');
define('OUSER_MAILFROM','WebServer@oao.sng');

//SNMP settings
define('OSNMP_CREAD',	'public');
define('OSNMP_CWRITE',	'private');
define('OSNMP_TIMEOUT',	15000);
define('OSNMP_RETRY',	3);

//Debug settings
define('DBG_LOG',	'/tmp/web.log');
define('SMARTY_DIR',	'/srv/www/htdocs/lib/smarty');

//SMTP settings
define('SMTP_SERVER',	'tonipi-mbx-01v');
define('SMTP_PORT',	"25");
define('SMTP_USER',	FALSE);
define('SMTP_PASS',	FALSE);

//Objects
define('O_REQUEST',	'requests');
define('O_PERSON',	'wpperson');

?>