<?php
class smp_ExceptionDisplay 
{
  protected $_exception;	
	
  public function __construct(Exception $e) {
  	$this->_exception = $e;
  }
	
  public function display()
  {
  	echo $this->pageHeader('PHP Error');
  	echo $this->getMessage();
  	echo $this->getCode();
  	echo $this->getTrace();
  	echo $this->getGlobals();
  	echo $this->pageFooter();
  }
  
  protected function pageHeader($title="PHP Error") { 
  	$out = "";
  	$out .= "<html>";
  	$out .= "
  		<head>
  			<title>{$title}</title>
  			<script type='text/javascript' src='http://code.jquery.com/jquery-1.2.6.min.js'></script>
  			<script type='text/javascript'>
  				$(document).ready(function() {
  					$('.hiddencode').toggle();
  					$('a.showcode').click(function () {
  						var id = $(this).attr('id').replace('tog','');
  						if ($(this).html().indexOf('Show') != '-1') {
  							var txt = $(this).html().replace('Show','Hide');
  						} else {
  							var txt = $(this).html().replace('Hide','Show');
  						}
  						$(this).html(txt);
      					$('#'+id).slideToggle();
    				});
  				});
  			</script>
  		</head>
  	";
  	$out .= "<body><h1>{$title}</h1>";
  	return $out;
  }
  
  protected function pageFooter() {
  	$out = "</body></html>";
  	return $out;
  }
  
  protected function getGlobals() {
  	$out  = "<h2>Globals</h2><p><a id='togglobals' class='showcode' style='cursor:pointer;text-decoration:underline;'>Show Globals</a></p>";
  	$out .= "<pre id='globals' class='hiddencode'>";
  	$out .= print_r($GLOBALS,1);
  	$out .= "</pre>";
  	return $out; 
  }
  
  protected function getMessage($msg = '<p>{msg} on line {line} in file {file}</p>') {
  	$msg = str_replace('{msg}',$this->_exception->getMessage(),$msg);
  	$msg = str_replace('{line}',$this->_exception->getLine(),$msg);
  	$msg = str_replace('{file}',$this->_exception->getFile(),$msg);
  	return $msg;
  }
  
  protected function getTrace() {
  	$trace = $this->_exception->getTrace();
  	array_shift($trace);
  	foreach ($trace as &$trc) {
  		if (isset($trc['args']) && is_array($trc['args'])) {
	  		foreach ($trc['args'] as &$arg) {
	  			if (is_scalar($arg)) {
	  				if (!is_numeric($arg)) {
	  					if (stristr($arg,'"')) {
	  						$arg = "'{$arg}'";		
	  					}
	  					else {
	  						$arg = '"'.$arg.'"';
	  					}
	  				} 
	  			}
	  		}
  		} else {
  			$trc['args'] = array();
  		}
  	}
  	
  	ob_start();
  	foreach ($trace as $k => $item) {
  		print "<div style='margin-bottom:10px;border-bottom:1px solid #aaa;padding-bottom:5px;width:900px'>";
  		$args = implode(',',$item['args']);
  		print "<p><b>line {$item['line']}</b> in file <b>{$item['file']}</b></p>";
  		print "<pre>{$item['function']}({$args});</pre>";
  		print "<p><a id='togcode{$k}' class='showcode' style='cursor:pointer;text-decoration:underline;'>Show Code</a></p>";
  		print "<div class='hiddencode' id='code{$k}'>";
  		print $this->getCodeHTML($item['file'],$item['line']);
  		print "</div>";
  		print "</div>";	
  	} 
  	return "<h2>Trace</h2>".ob_get_clean();
  }

  protected function getCode($lines = 15) {
  	$out  = "";
  	print "<p><a id='togmaincode' class='showcode' style='cursor:pointer;text-decoration:underline;'>Show Code</a></p>";
  	$out .= "<div class='hiddencode' id='maincode'>";
  	$out .= $this->getCodeHTML($this->_exception->getFile(),$this->_exception->getLine(),$lines);
  	$out .= "</div>";
  	return $out; 
  }
  
  private function getCodeHTML($file,$aline,$lines = 10) { 
  	
  	$start = ($aline - $lines);

  	if ($start < 0) {
  		$start = 0;
  	} 

  	$file = file($file);
  	$file = array_map("htmlentities",$file);
  	$file = array_slice($file,$start,($lines*2),true);
  	
  	foreach ($file as $line => &$str) {
  		$line = $line + 1;
  		$background = "#eee";
  		if ($line == $aline) $background = "#fcc";
  		$str = "<pre style='background:{$background};padding:3px;margin:0px;border-bottom:1px solid #ccc'>{$line} {$str}</pre>";
  	}
  	
  	return "<div style='width:600px;border-top:1px solid #ccc'>".implode($file)."</div>";
  }
}