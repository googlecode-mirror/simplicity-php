<?php
class smp_Loader
{

  private $_root;

  private $_opts = array();

  private $_from_cache = false;

  private $_class_registry;

  public function __construct ($root, $opts = array())
  {
    $this->_root = $root;
    $this->_opts = $opts;
    if (! isset($this->_opts['paths']) || ! is_array($this->_opts['paths']))
    {
      return;
    }
    $tmp = $this->_opts['temp'];
    if (file_exists($tmp . 'class_reg.php'))
    {
      $this->_class_registry = smp_File::unserialize($tmp . 'class_reg.php');
      if (is_array($this->_class_registry) && count($this->_class_registry))
      {
        $this->_from_cache = true;
      } else
      {
        $this->_class_registry = false;
      }
    }
    if (! $this->_from_cache)
    {
      foreach ($this->_opts['paths'] as $path)
      {
      	$path = $this->_root.$path;
      	print_r($path);
        if (file_exists($path)) {
          $this->harvest($path);  
        }
      }
    }
    
    spl_autoload_register(array($this , 'load'));
  }

  public function __destruct ()
  {
    if (! $this->_from_cache && is_array($this->_class_registry) && count($this->_class_registry))
    {
      $tmp = $this->_opts['temp'];
      smp_File::serialize($tmp . 'class_reg.php', $this->_class_registry);
    }
  }
	
  public function load ($class) {
  	$path = $this->find($class);
  	if (file_exists($path)) {
  		include $path;
  		return true;
  	}
  	throw new Exception("Failed to load class '{$class}'.");
  	return false;
  }
  
  public function find ($class)
  {
    if (class_exists($class))
    {
      return true;
    }
    if (isset($this->_class_registry[$class]))
    {
      return $this->_class_registry[$class];
    }
    return false;
  }

  private function harvest ($path)
  {
    $iterator = new RecursiveDirectoryIterator($path);
    foreach (new RecursiveIteratorIterator($iterator) as $file)
    {
      $ext = substr($file, - 3);
      if ($ext == 'php')
      {
        $code = smp_File::read($file);
        $tokens = token_get_all($code);
        foreach ($tokens as $k => $token)
        {
          if ($token[0] == T_CLASS || $token[0] == T_INTERFACE)
          {
            $class_token = ($k + 2);
            $class_token = $tokens[$class_token];
            if ($class_token[0] == T_STRING && $class_token[2] == $token[2])
            {            	
              $this->_class_registry[$class_token[1]] = (string) $file;
            }
          }
        }
      }
    }
  }
}