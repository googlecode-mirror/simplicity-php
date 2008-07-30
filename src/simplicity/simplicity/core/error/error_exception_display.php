<?php
class smp_ErrorExceptionDisplay extends smp_ExceptionDisplay
{ 
  public function display()
  {
  	echo $this->getHeader('PHP Error');
  	echo $this->getMessage();
  	if ($this->_exception->getSeverity() != E_ERROR && $this->_exception->getSeverity() != E_PARSE) {
  		echo $this->getTrace();
  	}
  	echo $this->getCode();
  }
}