<?php
require_once(__DIR__ . '/includes/autoload.php'); 
 
if(isset($_GET['page']) && isset($action[$_GET['page']])) {
	$page_name = $action[$_GET['page']];
} else {
	$page_name = 'homepage';
} 
 
require_once("controller/{$page_name}.php");  

$PTMPL['site_title'] = 'NDA Faculty of Management';//$settings['site_name']; 
$PTMPL['site_url'] = $CONF['url'];
$PTMPL['favicon'] = 'favicon.ico';

$captcha_url = '/includes/vendor/goCaptcha/goCaptcha.php?gocache='.strtotime('now');
$PTMPL['captcha_url'] = $CONF['url'].$captcha_url;

$PTMPL['global_header'] = globalTemplate(1, 1);
$PTMPL['global_footer'] = globalTemplate(2, 1);

//$PTMPL['token'] = $_SESSION['token_id'];  
  
$PTMPL['language'] = isset($_COOKIE['lang']) ? $_COOKIE['lang'] : '';

// Render the page
$PTMPL['content'] = mainContent();   

$theme = new themer('container');
echo $theme->make();
 
?>
