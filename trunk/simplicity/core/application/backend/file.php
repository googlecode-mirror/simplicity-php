<?
class FileApplicationBackend implements iApplicationBackend {
	
	private $_name;
	private $_path;
	private $_data;
	
	public function __construct() {
		$this->_name = SIMPLICITY_APPNAME;
		$this->_path = TEMP.$this->_name;
		if (!file_exists($this->_path)) {
			$this->initApplication();
		} else {
			$this->_data = unserialize(file_get_contents($this->_path));	
		}
	}
	
	private function initApplication() {
		$this->_data = array();
		$this->_data['name'] = $this->_name;
		$this->save();
	}

	public function Reset() {
		$this->initApplication();
		return;
	}

	public function Remove($name) {
		if (isset($this->_data[$name])) {
			unset($this->_data[$name]);
		}
		$this->save();
	}

	public function Add($name,$value) {
		$this->_data[$name] = $value;
		$this->save();
		return;
	}

	public function Get($name) {
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		return false;
	}

	private function save() {
		if (file_exists($this->_path))
		file_put_contents($this->_path, serialize($this->_data));
	}
}

?>