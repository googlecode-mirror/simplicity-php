<?php
class smp_Bootstrap
{

  private $_queue = array();

  private $_opts;

  /**
   * Initializes the bootstrap queue optionally overriding the default with array $queue.
   *
   * You may also provide an associative array of options with the following keys:
   *
   *  'args'       -  an arguments arrag to be passed to each bootstrap stage. 
   * 
   * @param array   $queue
   * @param array   $opts
   */
  public function __construct ($queue = array(), $opts = array())
  {
    $this->_queue = $queue;
    $this->_opts = $opts;
  }

  /**
   * Adds a bootstrap $class to the bootstrap command queue.
   *
   * @param string $class
   */
  public function add ($class)
  {
    $this->_bootstrap[$class] = $class;
    return $this;
  }

  /**
   * Removes a bootstrap $class from the bootstrap command queue.
   *
   * @param string $class
   */
  public function del ($class)
  {
    unset($this->_bootstrap[$class]);
    return $this;
  }

  /**
   * Replaces the $old bootstrap command with the $new bootstrap command.
   *
   * @param string $old
   * @param string $new
   */
  public function replace ($old, $new)
  {
    $this->delBootstrap($old);
    $this->addBootstrap($new);
    return $this;
  }

  /**
   * Returns the current bootstrap queue.
   *
   * @return array
   */
  public function getQueue ()
  {
    return $this->_queue;
  }

  /**
   * Execute the current bootstrap queue.
   */
  public function execQueue ()
  {
    foreach ($this->_queue as $class)
    {
      $ret = $this->execCommand($class);
      if (is_array($ret)) {
        throw new Exception("Bootstrap halted at command {$class}::{$ret['method']} with code {$ret['code']}.");
      }
    }
  }

  /**
   * Executes the specified command class and returns the result.
   *
   * @param string $class
   * @return mixed
   */
  public function execCommand ($class)
  {
    $obj = new $class();
    if (! ($obj instanceof smp_BootstrapStage))
    {
      throw new Exception("Bootstrap command class '{$class}' does not extend the required class smp_BootstrapStage.");
    }
    
    $args = isset($this->_opts['args']) ? $this->_opts['args'] : null;
    $exec = array('preExec','exec','postExec');
    
    foreach ($exec as $method) {
      $ret = $obj->$method($args);
      if ($ret < 0) {
        return array('method' => $method,'code' => $ret);
      } elseif ($ret) {
        continue;  
      } else {
        return $ret;
      }
    }
  }
}