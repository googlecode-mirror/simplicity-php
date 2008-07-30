<?php

class smp_Error {

  private $_exception_map = array(
    'default' => 'smp_ExceptionDisplay'
  );
  
  public function __construct() {
    error_reporting(E_ALL | E_STRICT | E_NOTICE);
    ini_set('display_errors',true);
    
    set_error_handler(array($this,'handleError'));
    set_exception_handler(array($this,'handleException'));

  }
  
  private function display(Exception $e) {
    $class = get_class($e);
    if (!isset($this->_exception_map[$class])) {
      $class = 'default';
    }

    if (!($this->_exception_map[$class] instanceof smp_ExceptionDisplay)) {
      $this->_exception_map[$class] = new $this->_exception_map[$class]();
      if (!($this->_exception_map[$class] instanceof smp_ExceptionDisplay)) {
        $class = 'default';
        $this->_exception_map[$class] = new $this->_exception_map[$class]();        
      }
    }
    
    $this->_exception_map[$class]->display($e);
  }
  
  public function handleError($severity,$message,$file='', $line=0) {
    throw new ErrorException($message, 0, $severity, $file, $line);
  }
  
  public function handleException(Exception $e) {
      $this->display($e);
  } 
}