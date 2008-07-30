<?php
class smp_InitRegistry extends smp_BootstrapStage
{

  /**
   * @see smp_BootstrapStage::exec()
   *
   * @return bool
   */
  public function exec ($args=array())
  { 
    if (!($args['simplicity'] instanceof Simplicity)) 
    {
      return -1;
    }
    
    $reg = new smp_Registry();
    $args['simplicity']->set('registry',$reg);
    return true;
  }
}