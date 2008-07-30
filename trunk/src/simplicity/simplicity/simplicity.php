<?php

class Simplicity
{
  
  private static $_instance;
  
  private $_init;
  
  private $_id;
  
  private $_root;
  
  private $_temp;
  
  private $_bootstrap;
  
  private $_bootstrap_queue = array('smp_InitRegistry');
  
  private $_shared = array();

  private function __construct ()
  {}

  private function __clone ()
  {}

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
  public function init ()
  {
  	ob_start();
    $this->initRoot()->initId()->initTemp()->initUtils()->initLoader()->initError();
    $this->_init = true;
    return $this;
  }

  /**
   * Detects the application root and sets the include path.
   *
   * @return Simplicity
   */
  private function initRoot ()
  {
    define("DS", DIRECTORY_SEPARATOR);
    $path = realpath($_SERVER['DOCUMENT_ROOT']);
    if (substr($path, 0, - 1) != DS)
    {
      $path = $path . DS;
    }
    $this->_root = $path;
    set_include_path(get_include_path() . ':' . $this->_root);
    return $this;
  }

  /**
   * Generates the unique application id for this Simplicity instance.
   *
   * @return Simplicity
   */
  private function initId ()
  {
    $this->_id = md5(__FILE__);
    return $this;
  }

  /**
   * Initializes the temp directory.
   *
   * @return Simplicity
   */
  private function initTemp ()
  {
    $temp = $this->_root . 'app' . DS . 'temp' . DS;
    if (is_writable($temp))
    {
      $this->_temp = $temp;
    } else
    {
      $temp = sys_get_temp_dir();
      if (substr($temp, 0, - 1) != DS)
        $temp = $temp . DS;
      $this->_temp = $temp . $this->_id . DS;
      if (! file_exists($this->_temp))
      {
        mkdir($this->_temp, 0777, true);
      }
    }
    return $this;
  }

  /**
   * Loads the core utilities.
   *
   * @return Simplicity
   */
  private function initUtils ()
  {
    $this->load('simplicity/core/utils/file.php');
    return $this;
  }

  /**
   * Configures the autoloader.
   *
   * @return Simplicity
   */
  private function initLoader ()
  {
    $this->load('simplicity/core/loader/loader.php');
    $opts = array('paths' => array('simplicity' . DS . 'core' , 'simplicity' . DS . 'bootstrap' , 'simplicity' . DS . 'interface' , 'app' . DS . 'modules') , 'temp' => $this->_temp);
    $loader = new smp_Loader($this->_root, $opts);
    $this->set('loader', $loader);
    spl_autoload_register(array($this , 'load'));
    return $this;
  }

  /**
   * Initializes the error handler.
   *
   * @return Simplicity
   */
  private function initError ()
  {
    $error = new smp_Error();
    $this->set('error', $error);
    return $this;
  }

  /**
   * Initializes the bootstrap queue optionally overriding the default with array $queue.
   *
   * @see smp_Bootstrap
   * @param array $queue
   */
  public function initBootstrap ($queue = null)
  {
    if (! isset($queue))
    {
      $queue = $this->_bootstrap_queue;
    }
    $bs = new smp_Bootstrap($queue, array('args' => array('simplicity' => $this)));
    $this->set('bootstrap', $bs);
    return $this;
  }

  /**
   * Load the requested file or class. If a class is requested, the class loader module must be loaded.
   *
   * @param string $class_or_path
   * @return bool
   */
  public function load ($class_or_path)
  {
    $class_or_path = str_replace('/', DS, $class_or_path);
    if (file_exists($class_or_path))
    {
      if (include ($class_or_path))
      {
        return true;
      }
    } elseif (file_exists($this->_root . $class_or_path))
    {
      return $this->load($this->_root . $class_or_path);
    } else
    {
      if ($this->get('loader') instanceof smp_Loader)
      {
        $path = $this->get('loader')->find($class_or_path);
        if ($path)
        {
          return $this->load($path);
        }
        return false;
      }
    }
    return false;
  }

  /**
   * Register a shared object $instance to the given $key.  
   *
   * @param string $name
   * @param object $obj
   */
  public function set ($key, $instance)
  {
    if (! isset($this->_shared[$key]))
    {
      $this->_shared[$key] = $instance;
    } else
    {
      throw new Exception("Attempted to write to existing key {$key}.");
    }
  }

  /**
   * Retrieve a shared object instance from the given $key.
   *
   * @param string $key
   */
  public function get ($key)
  {
    return isset($this->_shared[$key]) ? $this->_shared[$key] : null;
  }

  /**
   * Runs the Simplicity framework.
   */
  public function start ()
  {
    if (! $this->_init)
    {
      $this->init();
    }
    if (! ($this->get('bootstrap') instanceof smp_Bootstrap))
    {
      $this->initBootstrap();
    }
    $this->get('bootstrap')->execQueue();
  }
}