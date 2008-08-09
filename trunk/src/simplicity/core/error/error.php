<?php

class smp_Error extends smp_Module {

  private $_display = true;	
	
  private $_exception_map = array(
    'default' => 'smp_ExceptionDisplay',
  	'ErrorException' => 'smp_ErrorExceptionDisplay'
  );
  
  public function __construct($display_errors = true) {
    error_reporting(E_ALL | E_STRICT | E_NOTICE);
    ini_set('display_errors',false);
    
    $this->_display = $display_errors;
    
    set_error_handler(array($this,'handleError'));
    set_exception_handler(array($this,'handleException'));
	register_shutdown_function(array($this,'shutdownFunction'));
  }
  
  private function display(Exception $e) {
  	if (!$this->_display) return;
  	
    $class = get_class($e);
    if (!isset($this->_exception_map[$class])) {
      $class = 'default';
    }

    if (!($this->_exception_map[$class] instanceof smp_ExceptionDisplay)) {
      $this->_exception_map[$class] = new $this->_exception_map[$class]($e);
      if (!($this->_exception_map[$class] instanceof smp_ExceptionDisplay)) {
        $class = 'default';
        $this->_exception_map[$class] = new $this->_exception_map[$class]($e);        
      }
    }
    
    $this->_exception_map[$class]->display();
  }
  
  public function handleError($severity,$message,$file='', $line=0) {
    throw new ErrorException($message, 0, $severity, $file, $line);
  }
  
  public function handleException(Exception $e) {
  		restore_error_handler();
  		restore_exception_handler();
  		$this->display($e);
  } 
  
  public function shutdownFunction() {
  	restore_error_handler();
  	restore_exception_handler();
  	ini_set('display_errors',true);
  	
  	$error = error_get_last();
  	if (is_array($error) && ($error['type'] == E_PARSE || $error['type'] == E_ERROR)) { 
  		$this->display(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
  	}
  	
  	ob_end_flush();
  } 
}