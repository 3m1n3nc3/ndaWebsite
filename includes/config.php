<?php
//error_reporting(E_ALL);
/*
 * turn off magic-quotes support, for runtime e, as it will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);


$CONF = $PTMPL = array();

/* 
* set currentPage in the local scope
*/
$CONF['current_page'] = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);

/* 
* The MySQL credentials
*/	
define('DB_PREFIX', '');	
$CONF['dbdriver'] = 'mysql'; 
$CONF['dbhost'] = 'localhost'; 
$CONF['dbuser'] = 'root'; 
$CONF['dbpass'] = 'idontknow1A@'; 
$CONF['dbname'] = 'ndafms';

/* 
* The Installation URL 
* https is enforced in .HTACCESS, to use the auto protocol feature remove the .HTACCESS https enforcement
*/
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
$CONF['url'] = $protocol.'://'.$_SERVER['HTTP_HOST'];

/* 
* The Notifications e-mail
*/
$CONF['email'] = 'support@ndafms.com';  

/* 
* The templates directory
*/
$CONF['template_path'] = 'templates';

$action = array('admin'						=> 'admin',
				'contest'					=> 'contest',
				'homepage'					=> 'homepage',
				'pages'						=> 'pages',
				'about'						=> 'about'
				); 

/* 
* Define the cookies path
*/				
define('COOKIE_PATH', preg_replace('|'.$protocol.'?://[^/]+|i', '', $CONF['url']).'/');

?>
