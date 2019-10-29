<?php

class themer { 

	var $filename; 
	public function __construct($filename) {
		$this->filename = $filename;
	}
	public function mk($filename) {
		$this->filename = $filename;
		return $this->make();
	}

	public function make() { 
		global $CONF;
		$file = sprintf('./'.$CONF['template_path'].'/'.$CONF['template_name'].'/html/%s.html', $this->filename);

		//$file = 'template/index.html';
		$fh_skin = fopen($file, 'r');
		$skin = @fread($fh_skin, filesize($file));
		fclose($fh_skin);
		return $this->parse($skin);

	}

	public static function parse($skin) {
		$skin = preg_replace_callback('/{\$lng->(.+?)}/i', function($matches) {
			global $LANG;
			return $LANG[$matches[1]];
		}, $skin);

		$skin = preg_replace_callback('/{\$([a-zA-Z0-9_]+)}/', function($matches) {
			global $PTMPL;
			return (isset($PTMPL[$matches[1]])?$PTMPL[$matches[1]]:"");
		}, $skin);

		return $skin;
	}
}

