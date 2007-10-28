<?
class MemcacheSessionBackend implements iSessionBackend {
	
	private $_id;
	private $_path;
	
	private $_memcache;
	private $_ns;
	private $_age;
	
	private $_index;
	private $_data;
	
	public $_sessions;
	
	public function __construct($id) {
		$this->_id = $id;
	
		$this->_memcache = new Memcache;
		$this->_memcache->pconnect(MEMCACHE_HOST, MEMCACHE_PORT);
		
		$this->_ns = SIMPLICITY_APPNAME.'.sessions';
		
		if ($this->_id) {
			$this->continueSession();
		} else {
			$this->startSession();
		}
		
		$this->cleanupSessions();
	}

	public function startSession() {
		if ($this->_path && $this->_memcache->get($this->_path)) {
			$this->Destroy();
		}
		
		$id = md5(microtime().rand(0,1000));
		$this->_id = $id;
		
		$this->_path = $this->_ns.'.'.$this->_id;
		
		$this->_index['id'] = $this->_id;
		$this->_index['timestamp'] = time();
		$this->_index['keys'] = array();
				
		$this->_memcache->set($this->_path,$this->_index);
		
		$this->registerSession();
	}

	public function continueSession() {
		$this->_path = $this->_ns.'.'.$this->_id;

		if ($this->_index = $this->_memcache->get($this->_path)) {
			$this->_age = (time() - $this->_index['timestamp']);
			if ($this->_age > Session::$TIMEOUT) {
				$this->startSession();
				return;
			} else {
				$this->_index['timestamp'] = time();
				$this->_memcache->set($this->_path,$this->_index);
			}
			return;
		} else {
			$this->startSession();
			return;
		}
	}
	
	public function Add($name,$value) {
		$this->_data[$name] = $value;
		$this->_index['keys'][$name] = $this->_path.'.'.$name;
		$this->_memcache->set($this->_index['keys'][$name],$value);
		$this->_memcache->set($this->_path,$this->_index);
	}
	
	public function Remove($name) {
		unset($this->_data[$name]);
		$this->_memcache->delete($this->_index['keys'][$name]);
		unset($this->_index['keys'][$name]);
		$this->_memcache->set($this->_path,$this->_index);
	}
	
	public function Get($name) {
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		} elseif (isset($this->_index['keys'][$name])) {
			$this->_data[$name] = $this->_memcache->get($this->_index['keys'][$name]);
			return $this->_data[$name];
		}
		return false;
	}
	
	public function Destroy() {
		foreach ($this->_index['keys'][$name] as $name => $mckey) {
			$this->_memcache->delete($mckey);
		}
		$this->_index = array();
		$this->_data = array();
	}
	
	public function getAge() {
		return (time() - $this->_index['timestamp']);
	}
	
	public function getId() {
		return $this->_id;
	}
	
	private function registerSession() {
		$sessions = $this->_memcache->get($this->_ns);
		$sessions[$this->_id] = $this->_path;
		$sessions = $this->_memcache->set($this->_ns,$sessions);
		$this->_sessions = $sessions;
	}
	
	private function unRegisterSession($id) {
		$sessions = $this->_memcache->get($this->_ns);
		unset($sessions[$this->_id]);
		$sessions = $this->_memcache->set($this->_ns,$sessions);		 
	}	
	
	private function cleanupSessions() {
		$sessions = $this->_memcache->get($this->_ns);
		foreach ($sessions as $s => $path) {
			$sess = $this->_memcache->get($path);
			$age  = (time() - $sess['timestamp']);
			if ($age > Session::$TIMEOUT) {
				if (is_array($sess['keys'])) {
					foreach ($sess['keys'] as $skey => $mckey) {
						$this->_memcache->delete($mckey);
					}
				}
				$this->_memcache->delete($path);
				$this->unRegisterSession($skey);
			}
		}
	}
}

?>