<?

class Application extends Core {
	
	const LOAD_STATIC = false;
	
	private $_backend;

	static $BACKEND;
	static $BACKEND_PATH;
	
	public function __construct() {
				
		self::$BACKEND = ucfirst(strtolower(APPLICATION_BACKEND)).'ApplicationBackend';
		self::$BACKEND_PATH = SIMPLICITY_CLASSES.'application'.DS.'backend'.DS;
		
		//TODO: Make way less assumptions here. Add some error checking / logging.
	
		if (!class_exists(self::$BACKEND)) {
			include(self::$BACKEND_PATH.strtolower(APPLICATION_BACKEND).'.php');
		}
		
		if (class_exists(self::$BACKEND) && in_array('iApplicationBackend',array_keys(class_implements(self::$BACKEND)))) {
			$this->_backend = new self::$BACKEND();
		}
	}

	public function Set($name,$val) {
		if (is_object($this->_backend)) {
			$this->_backend->Add($name,$val);
		} else {
			$this->startSession();
			if (is_object($this->_backend)) {
				$this->_backend->Add($name,$val);
			}
		}

	}

	public function Get($name) {
		if (is_object($this->_backend)) {
			return $this->_backend->Get($name);
		} else {
			$this->startSession();
			if (is_object($this->_backend)) {
				return $this->_backend->Get($name);
			} else {
				return false;
			}
		}
	}

	public function Remove($name) {
		if (is_object($this->_backend)) {
			$this->_backend->Remove($name);
		}
	}

	public function Reset() {
		if (is_object($this->_backend)) {
			$this->_backend->Reset();
		}
	}

	public function getApplication() {
		if (is_object($this->_backend)) {
			return $this->_backend;
		} else {
			return false;
		}
	}
}

interface  iApplicationBackend {
	public function __construct();
	public function Add($name,$val);
	public function Get($name);
	public function Remove($name);
	public function Reset();
}

?>