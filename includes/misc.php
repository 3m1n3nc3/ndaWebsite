<?php      

// Set the defult timezone
date_default_timezone_set("Africa/Lagos");

// Store the theme path and theme name into the CONF and TMPL
$PTMPL['template_path'] = $CONF['template_path'];
$PTMPL['template_name'] = $CONF['template_name'] = 'default';//$settings['template'];
$PTMPL['template_url'] = $CONF['template_url'] = $CONF['template_path'].'/'.$CONF['template_name'];
 
$_SESSION['username'] = 'marxemi';
if (isset($_SESSION['username'])) {
	$user = $framework->userData($_SESSION['username']);
} else {
	$user =  $framework->authenticateUser();
}
