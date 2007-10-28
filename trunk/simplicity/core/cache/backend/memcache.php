<?
class MemcacheCacheBackend implements iCacheBackend {
	
	private $_name;
	private $_memcache;
	private $_index;
	private $_data;
	
	private $_mcroot;
	
	public function __construct() {
		$this->_name = SIMPLICITY_APPNAME;
		$this->_memcache = new Memcache;
		$this->_memcache->pconnect(MEMCACHE_HOST, MEMCACHE_PORT);
		$this->_root = SIMPLICITY_APPNAME.'.cache';
		$this->_index = $this->_memcache->get($this->_root);
		if (!$this->_index) {
			$this->init();
		}
	}
	
	private function init() {
		foreach ($this->_index as $name => $mckey) {
			$this->_memcache->delete($mckey);	
		}
		$this->_data = array();
		$this->_index = array();
		$this->_memcache->set(SIMPLICITY_APPNAME.'.cache',$this->_index);
	}
	
	public function Add($name,$value) {
		$this->_index[$name] = SIMPLICITY_APPNAME.'.cache.'.$name;
		$this->_data[$name] = $value;
		$this->_memcache->set(SIMPLICITY_APPNAME.'.cache',$this->_index);
		$this->_memcache->set(SIMPLICITY_APPNAME.'.cache.'.$name,$value);
		return;
	}
	
	public function Remove($name) {
		unset($this->_data[$name]);
		$this->_memcache->delete($this->_index[$name]);
		unset($this->_index[$name]);
		$this->_memcache->set(SIMPLICITY_APPNAME.'.cache',$this->_index);
	}

	public function Get($name) {
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		} elseif (isset($this->_index[$name])) {
			$this->_data[$name] = $this->_memcache->get($this->_index[$name]);
			return $this->_data[$name];
		}
		return false;
	}
}
?>