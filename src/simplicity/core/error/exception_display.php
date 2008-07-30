<?php
class smp_ExceptionDisplay 
{
  public function display (Exception $e)
  {
    print '<pre>';
    var_dump($e);
    print '</pre>';
  }
}