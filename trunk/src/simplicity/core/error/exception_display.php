<?php
class smp_ExceptionDisplay 
{
  protected $_exception;	
	
  public function __construct(Exception $e) {
  	$this->_exception = $e;
  }
	
  public function display()
  {
  	echo $this->getHeader('Error');
  	echo $this->getMessage();
  	echo $this->getTrace();
  	echo $this->getCode();
  }
  
  protected function getHeader($msg) {
  	return "<h1>{$msg}</h1>";
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
  		foreach ($trc['args'] as &$arg) {
  			if (is_scalar($arg)) {
  				if (!is_numeric($arg)) {
  					if (stristr($arg,'"')) {
  						$arg = "'{$arg}'";		
  					}
  					else {
  						$arg = '"'.$arg."'";
  					}
  				} 
  			}
  		}
  	}
  	return "<h2>Trace</h2>".print_r($trace,1);
  }

  protected function getCode($lines = 10) {
  		 
  }
  
  private function getCodeHTML($lines = 10,$file) {
  	$file = $this->_exception->getFile();
  	$aline = $this->_exception->getLine() - 1; 
  	
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
  	
  	return "<h2>Code</h2><div style='width:600px;border-top:1px solid #ccc'>".implode($file)."</div>";
  }
}