<?
class FileSessionBackend implements iSessionBackend {
	
	private $_id;
	
	private $_path;
	private $_timeout;
	private $_age;
	
	private $_data;
	
	static $DATA_PATH;
	
	public function __construct($id) {
		$this->_id = $id;

		self::$DATA_PATH = TEMP.'sessions'.DS;

		if (!file_exists(self::$DATA_PATH)) {
			mkdir(self::$DATA_PATH);
		}
		
		if ($this->_id) {
			$this->continueSession();
		} else {
			$this->startSession();
		}
		
		$this->cleanupSessions();
	}
	
	public function startSession() {
		if (file_exists($this->_path)) {
			$this->Destroy();
		}
		$id = md5(microtime().rand(0,1000));
		$this->_path = self::$DATA_PATH.$id;

		$this->_data['id'] = $id;
		$this->_id = $id;
		$this->_age = 0;
		
		$this->saveToDisk();
	}

	public function continueSession() {
		$this->_path = self::$DATA_PATH.$this->_id;

		if (file_exists($this->_path)) {
			$this->_age = (time() - fileatime($this->_path));
			if ($this->_age > Session::$TIMEOUT) {
				$this->startSession();
				return;
			} else {
				$this->_data = unserialize(file_get_contents($this->_path));
				touch($this->_path);
			}
			return;
		} else {
			$this->startSession();
			return;
		}
	}

	public function Destroy() {
		if ($this->_id) {
			if (file_exists($this->_path)) {
				unlink($this->_path);
			}
		}
		$this->_id = null;
		return;
	}

	public function Remove($name) {
		if (isset($this->_data[$name])) {
			unset($this->_data[$name]);
		}
		$this->saveToDisk();
	}

	public function Add($name,$value) {
		$this->_data[$name] = $value;
		$this->saveToDisk();
		return;
	}

	public function Get($name) {
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		return false;
	}

	public function getId() {
		return $this->_id;
	}

	public function getAge() {
		return $this->_age;
	}

	private function saveToDisk() {
		file_put_contents($this->_path, serialize($this->_data));
	}

	public function __destruct() {
		$this->saveToDisk();
	}

	protected function cleanupSessions() {
		$path = self::$DATA_PATH.'*';
		foreach (glob($path) as $session_file) {
			$age = time() - fileatime($session_file);
			if ($age > Session::$TIMEOUT) {
				unlink($session_file);
			}
		}
	}
}

?>