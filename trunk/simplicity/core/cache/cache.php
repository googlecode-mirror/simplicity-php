<?
class Cache {
	
	const LOAD_STATIC = true;
	
	static private $_backend;

	static $BACKEND;
	static $BACKEND_PATH;
	
	static public function init() {
				
		self::$BACKEND = ucfirst(strtolower(CACHE_BACKEND)).'CacheBackend';
		self::$BACKEND_PATH = SIMPLICITY_CLASSES.'cache'.DS.'backend'.DS;
		
		//TODO: Make way less assumptions here. Add some error checking / logging.
	
		if (!class_exists(self::$BACKEND)) {
			include(self::$BACKEND_PATH.strtolower(CACHE_BACKEND).'.php');
		}
		
		if (class_exists(self::$BACKEND) && in_array('iCacheBackend',array_keys(class_implements(self::$BACKEND)))) {
			self::$_backend = new self::$BACKEND();
		}
	}

	public function Set($name,$val) {
		if (is_object(self::$_backend)) {
			self::$_backend->Set($name,$val);
		} else {
			if (is_object(self::$_backend)) {
				self::$_backend->Set($name,$val);
			}
		}

	}

	public function Get($name) {
		if (is_object(self::$_backend)) {
			return self::$_backend->Get($name);
		} else {
			if (is_object(self::$_backend)) {
				return self::$_backend->Get($name);
			} else {
				return false;
			}
		}
	}

	public function Remove($name) {
		if (is_object(self::$_backend)) {
			self::$_backend->Remove($name);
		}
	}
}


interface  iCacheBackend {
	public function __construct();
	public function Set($name,$val);
	public function Get($name);
	public function Remove($name);
}
?>