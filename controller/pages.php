<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $user; 

	$PTMPL['page_title'] = $LANG['welcome'];	 
	
	$PTMPL['site_url'] = $CONF['url'];

	$PTMPL['name'] = $LANG['name'];

	if (isset($_GET['section'])) {
		$page = 'pages/'.$_GET['section'];
		if (isset($_GET['about'])) { 
			$page = 'about/'.$_GET['about']; 
		}
	} else {
		$page = 'pages/content';
	}

	// Set the active landing page_title 
	$theme = new themer($page);
	return $theme->make();
}
?>
