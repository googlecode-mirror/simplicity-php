<?php

class Simplicity
{
  private static $_instance;
  
  private $_id;
  
  private $_root;
  private $_www_root;
  private $_app_root;
  
  private $_temp;
  
  private $_core_modules = array(
  	"smp_Loader"
  );
  private $_modules = array();
  private $_loaded_modules = array();
  
  private $_opts = array();
  
  private $_init = false;
  
  private function __construct ($opts = array()) {
  	$this->_opts = $opts;
  }

  private function __clone () {}

  /**
   * Returns an instance of the Simplicity class or supplied string name
   * that must extend the main Simplicity class.
   *
   * @param string $class Optional string name of subclass to instanciate
   * @param array $opts Optional array options.
   * @return Simplicity
   */
  final public static function getInstance ($class = 'Simplicity', $opts = array())
  {
    if (! is_object(self::$_instance))
    {
      if (! class_exists($class))
      {
        throw new Exception("Class '{$class}' does not exist.");
      }
      self::$_instance = new $class($opts);
      if (! (self::$_instance instanceof Simplicity))
      {
        throw new Exception("Class '{$class}' does not extend the root class Simplicity.");
      }
    }
    return self::$_instance;
  }

  public function getId ()
  {
    return $this->_id;
  }
  
  /**
   * Initializes Simplicity.
   *
   * @return Simplicity
   */
  public function init ($mode = 'live')
  {
  	$this->_mode = $mode;
  	ob_start();
    
  	$this->initRoot();
    $this->initId();
    $this->initTemp();
    $this->initUtils();
    $this->initModules();
    
    $this->_init = true;
  }

  /**
   * Detects the application root and sets the include path.
   *
   * @return Simplicity
   */
  private function initRoot ()
  {
    define("DS", DIRECTORY_SEPARATOR);
    
    $path = realpath(dirname(dirname(__FILE__)));
    if (substr($path, 0, - 1) != DS)
    {
      $path = $path . DS;
    }
    $this->_root = $path;
    
    $path = isset($this->_opts['www_root']) ? $this->_opts['www_root'] : realpath($_SERVER['DOCUMENT_ROOT']);
    if (substr($path, 0, - 1) != DS)
    {
      $path = $path . DS;
    }
    $this->_www_root = $path;
    
    $path = isset($this->_opts['app_root']) ? $this->_opts['app_root'] : $this->_root.DS.'app';
    if (substr($path, 0, - 1) != DS)
    {
      $path = $path . DS;
    }
    $this->_app_root = $path;

    set_include_path(get_include_path() . ':' . $this->_root.':'.$this->_app_root);
  }

  /**
   * Generates the unique application id for this Simplicity instance.
   *
   * @return Simplicity
   */
  private function initId ()
  {
    $this->_id = md5(__FILE__);
  }

  /**
   * Initializes the temp directory.
   *
   * @return Simplicity
   */
  private function initTemp ()
  {
    $temp = isset($this->_opts['temp']) ? $this->_opts['temp'] : $this->_root . 'app' . DS . 'temp' . DS;
    if (substr($temp, 0, - 1) != DS) {
    	$temp = $temp . DS;
    }
    if (is_writable($temp))
    {
      $this->_temp = $temp;
    } else
    {
      $temp = sys_get_temp_dir();
      if (substr($temp, 0, - 1) != DS) {
        $temp = $temp . DS;
      }
      $this->_temp = $temp . $this->_id . DS;
      if (! file_exists($this->_temp))
      {
        mkdir($this->_temp, 0777, true);
      }
    }
  }

  /**
   * Loads the core utilities.
   *
   * @return Simplicity
   */
  private function initUtils ()
  {
    require $this->_root.'simplicity/core/utils/file.php';
  }

  private function initModules () {
  	require $this->_root.'simplicity/core/module/module.php';
  }
  
  /**
   * Register a Simplicity module $class.
   *
   * @param string $class
   */
  public function registerModule ($class)
  {
  	$this->_modules[$class] = $class;
  }

  /**
   * Enter description here...
   *
   */
  private function loadModules() {
  	
  }
  
  /**
   * Retrieve a previously loaded module.
   *
   * @param string $key
   */
  public function getModule ($task)
  {
  	if (isset($this->_modules[$task]) && $this->_modules[$task] instanceof smp_Module) {
  		return $this->_modules[$task];	
  	}
  	return false;
  }

  public function getTemp() {
  	return $this->_temp;
  }
  
  public function getRoot() {
  	return $this->_root;
  }
  
  public function getWwwRoot() {
  	return $this->_www_root;
  }
  
  /**
   * Runs the Simplicity framework.
   */
  public function start ($mode = 'live')
  {
    if (!$this->_init)
    {
      $this->init($mode);
    }
    $this->loadModules();
  }
}