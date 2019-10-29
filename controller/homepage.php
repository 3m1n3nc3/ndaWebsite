<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $user; 

	$PTMPL['page_title'] = $LANG['welcome'];	 
	
	$PTMPL['site_url'] = $CONF['url'];

	$PTMPL['name'] = $LANG['name']; 

	// Set the active landing page_title 
	$theme = new themer('homepage/content');
	return $theme->make();
}
?>
