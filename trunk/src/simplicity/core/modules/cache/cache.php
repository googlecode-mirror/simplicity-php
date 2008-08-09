<?php
class smp_Cache
{

  private $_root;

  private $_opts = array();

  private $_from_cache = false;

  private $_config;

  public function __construct ($root, $opts = array())
  {
    $this->_root = $root;
    $this->_opts = $opts;
    if (! isset($this->_opts['paths']) || ! is_array($this->_opts['paths']))
    {
      return;
    }
    $tmp = $this->_opts['temp'];
    if (file_exists($tmp . 'config.php'))
    {
      $this->_config = smp_File::unserialize($tmp . 'config.php');
      if (is_array($this->_config) && count($this->_config))
      {
        $this->_from_cache = true;
      } else
      {
        $this->_config = false;
      }
    }
    if (! $this->_from_cache)
    {
      foreach ($this->_opts['paths'] as $path)
      {
        if (file_exists($path)) {
          $this->harvest($path);  
        }
      }
    }
  }

  public function __destruct ()
  {
    if (! $this->_from_cache && is_array($this->_config) && count($this->_config))
    {
      $tmp = $this->_opts['temp'];
      smp_File::serialize($tmp . 'config.php', $this->_config);
    }
  }

  private function harvest ($path)
  {
    $iterator = new RecursiveDirectoryIterator($path);
    foreach (new RecursiveIteratorIterator($iterator) as $file)
    {
      $ext = substr($file, - 3);
      if ($ext == 'xml')
      {
        $xml = simplexml_load_file($file);
        $dir = basename(dirname($file));
 		foreach ($xml->xpath('//setting') as $setting) {
 			$path = $this->getPath($setting);	
 			//$reg[self::getPath($setting)] = $dir.$file;
 		}       
      }
    }
  }
  
  private function getPath($node,$dom=false) {
	if (!$dom) $node = dom_import_simplexml($node);
	if ($node->parentNode != $node->ownnerDocument) {
		return $this->getPath($node->parentNode,true).'.'.$node->getAttribute('name');  
	}
	return $node->getAttribute('name'); 
  }
}