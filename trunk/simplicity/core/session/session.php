<?

class Session extends Core {
	
	private $_backend;

	static $COOKIE_NAME;
	static $BACKEND;
	static $TIMEOUT;
	static $BACKEND_PATH;
		
	public function __construct() {
				
		self::$BACKEND = ucfirst(strtolower(SESSION_BACKEND)).'SessionBackend';
		self::$TIMEOUT = SESSION_TIMEOUT;
		self::$COOKIE_NAME = md5(SIMPLICITY_APPNAME."_session_id");
		self::$BACKEND_PATH = SIMPLICITY_CLASSES.'session'.DS.'backend'.DS;
		
		if (!class_exists(self::$BACKEND)) {
			include(self::$BACKEND_PATH.strtolower(SESSION_BACKEND).'.php');
		}

		if (class_exists(self::$BACKEND) && in_array('iSessionBackend',array_keys(class_implements(self::$BACKEND)))) {
			$_COOKIE[self::$COOKIE_NAME] = isset($_COOKIE[self::$COOKIE_NAME]) ? $_COOKIE[self::$COOKIE_NAME] : false;

			$this->_backend = new self::$BACKEND($_COOKIE[self::$COOKIE_NAME]);

			header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');
			setcookie(self::$COOKIE_NAME, $this->_backend->getId(), false, '/', false, 0);
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

	public function Destroy() {
		if (is_object($this->_backend)) {
			$this->_backend->Destroy();
		}
	}

	public function getAge() {
		if (is_object($this->_backend)) {
			return $this->_backend->getAge();
		} else {
			return 0;
		}
	}

	public function getSession() {
		if (is_object($this->_backend)) {
			return $this->_backend;
		} else {
			return false;
		}
	}
}

interface  iSessionBackend {
	public function __construct($id);
	public function Add($name,$val);
	public function Get($name);
	public function Remove($name);
	public function Destroy();
	public function getAge();
	public function getId();
}

?>