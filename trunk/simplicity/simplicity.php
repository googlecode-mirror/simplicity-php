<?
class Simplicity {

	static $Application;
	static $Session;
	static $Request;
	static $Controller;
	static $Template;
	
	static $ConnectionManager;

	static public function Start() {
		ob_start();
		self::initEnvironment();
		self::loadCore('Error');
		
		try {
			self::loadConfig('core');
			self::loadConfig('errors');
			
			self::loadCore('Utils');
			
			Utils::load('Inflector');
			Utils::load('Sanitize');
			
			self::$Application = self::loadCore('Application');
			self::$Session = self::loadCore('Session');
			self::$Request = self::loadCore('Request');
		
			self::loadCore('Router');
			self::loadCore('Controller');
			
			Router::processRequest(self::$Request);
	
			self::showError();
	
			$controller = array_shift(self::$Request->url);
			$method = array_shift(self::$Request->url);
	
			self::$Request->params = self::$Request->url;
			self::$Request->url = array($controller,$method);
	
			self::showPage($controller,$method);
		} 
		catch (Exception $e) {
			if ($e->getSeverity() <= ini_get('error_reporting')) {
				Error::displayExteption($e);	
			}
		}
	}

	static public function getConnection() {
		return self::$Connection;
	}
		
	static public function templateAssign($name,$value) {
		if (is_object(self::$Template)) {
			self::$Template->set($name, $value);
		}
	}
	
	static public function setTemplate($template) {
		if (is_object(self::$Template)) {
			self::$Template->setTemplate($template);
		}
	}
	
	static public function showPage($controller,$method=null,$params=array()) {		
		if (!($class = self::useController($controller))) die('Error loading controller.');
		
		$object = new $class();
		
		if (!is_object($object)) die('Error loading controller.');
		
		$method = $method ? $method : Inflector::underscore($object->getDefaultView());
		
		$method = Inflector::camelize(Sanitize::string(strtolower($method),"a-z0-9_"));
		
		$action = 'action'.$method;
		if (is_callable(array($object,$action))) {
			$object->$action($params);
			die();
		}
				
		$ajax = 'ajax'.$method;
		if (is_callable(array($object,$ajax))) {
			$ret = $object->$ajax($params);
			$headers = apache_request_headers();
			if (!isset($headers['Simplicity-Ajax-Type'])) $headers['Simplicity-Ajax-Type'] = 'json';
			switch ($headers['Simplicity-Ajax-Type']) {
				case 'json':
					print '('.json_encode($ret).')';
					break;
				case 'xml':
					if ($ret instanceof DOMDocument) {
						print $ret->saveXML();
						break;
					}
					break;
				case 'wddx':
					print wddx_serialize_value($ret, "Simplicity Ajax");
					break;
				case 'raw':
					print $ret;
					break;
			}
			die();
		}
		
		$template = VIEWS.$controller.'/'.Inflector::underscore($method).'.html';
		
		if (!file_exists($template)) self::showError(404);

		self::loadLib('PHPTAL.php');
		
		self::$Template = new PHPTAL();
		
		$view = 'view'.$method;
		
		if (is_callable(array($object,$view))) {
			$object->$view($params);
		}
		
		self::setTemplate($template);
		
		try {
		    echo self::$Template->execute();
		}
		catch (Exception $e) {
		    debug($e);
		}
		die();
	}
	
	static public function showError($err=null) {
		if (isset($err)) {
			Router::setError($err,self::$Request);
		}
		if (self::$Request->error != 200) {
			if (!count(self::$Request->url)) {
				if (!isset(HTTPErrors::$errors[self::$Request->error])) self::$Request->error = 500;
				
				if (isset(HTTPErrors::$errors[self::$Request->error])) {
					$code = self::$Request->error;
					$message = HTTPErrors::$errors[self::$Request->error]; 
				} 
				
				header("HTTP/1.0 {$code} {$message}");
				header("Status: {$code} {$message}");
				die("
					<h1>Error {$code}!</h1>
					<p>{$message}</p>
				");
			}
		}
	}
	 
	static public function useController($controller) {
		$controller = Inflector::underscore($controller);
		$class = 'controller'.Inflector::camelize($controller);
		if (!class_exists($class)) {
			if (!file_exists(CONTROLLERS.$controller.'.php')) return false;
			@include_once(CONTROLLERS.$controller.'.php');
			if (!class_exists($class)) return false;
		}
		return $class;
	}

	static public function useModel($model) {
		if (!is_object(self::$ConnectionManager)) {
			if (!class_exists('Doctrine')) { 
				self::loadLib('Doctrine.php');
				spl_autoload_register(array('Doctrine', 'autoload'));
			}
			self::$ConnectionManager = Doctrine_Manager::getInstance();
			
			$xml = new SimpleXMLElement(file_get_contents(SIMPLICITY_CONF.'db.xml'));	
			
			$cnt = 0;
			foreach ($xml->xpath('//connection') as $connection) {
    			self::$ConnectionManager->openConnection((string) $connection->dsn,(string) $connection->name,(($cnt > 0) ? false : true)); 
			}
		}
		
		$model = Inflector::underscore($model);
		$class = Inflector::camelize($model);
		if (!class_exists($class)) {
			if (!file_exists(MODELS.$model.'.php')) return false;
			@include_once(MODELS.$model.'.php');
			if (!class_exists($class)) return false;
		}
		return $class;
	}
	
	static private function initEnvironment() {
		error_reporting(E_STRICT);
		ini_set('display_errors',true);
		date_default_timezone_set("UTC");
		
		if (stristr($_SERVER['SERVER_SOFTWARE'],'win32')) {
			define("SIMPLICITY_WIN32",true);
		} else {
			define("SIMPLICITY_WIN32",false);
		}
			
		define("DS",DIRECTORY_SEPARATOR);
				
		define('SIMPLICITY_WEBROOT',$_SERVER['DOCUMENT_ROOT']);
				
		$spl = explode(DS,__FILE__);
		
		array_pop($spl);array_pop($spl);$spl[] = 'app';

		define('SIMPLICITY_APP',implode(DS,$spl).DS);

		array_pop($spl);$spl[] = 'simplicity';

		define('SIMPLICITY_ROOT',implode(DS,$spl).DS);
		
		array_pop($spl);$spl[] = 'config';

		define('SIMPLICITY_CONF',implode(DS,$spl).DS);

		define('SIMPLICITY_CORE',SIMPLICITY_ROOT.'core'.DS);
		define('SIMPLICITY_LIBS',SIMPLICITY_ROOT.'libs'.DS);
		define('SIMPLICITY_CLASSES',SIMPLICITY_CORE.DS);

		define('TEMP',SIMPLICITY_APP.'temp'.DS);

		define('CACHE',TEMP.'cache'.DS);

		define('LIBS',SIMPLICITY_APP.'libs'.DS);
		
		$old = ini_get('include_path');
		ini_set('include_path', $old.':'.LIBS.':'.SIMPLICITY_LIBS);
		
		define('RESOURCES',SIMPLICITY_APP.'resources'.DS);
		
		define('CONTROLLERS',RESOURCES.'controllers'.DS);
		define('MODELS',RESOURCES.'models'.DS);
		define('VIEWS',RESOURCES.'views'.DS);
		
		define('PHPTAL_FORCE_REPARSE', 0);		
		define('PHPTAL_PHP_CODE_DESTINATION', TEMP);
		define('PHPTAL_TEMPLATE_REPOSITORY', VIEWS);
	}

	static private function loadConfig($config) {
		require_once(SIMPLICITY_CONF."{$config}.php");
	}
	
	static private function loadCore($core) {
		if (class_exists('Inflector')) {
			$core = Inflector::underscore($core);
		}
		$core = strtolower($core);
		
		if (!file_exists(SIMPLICITY_CLASSES.$core.DS."{$core}.php")) {
			return false;
		}
		
		require_once(SIMPLICITY_CLASSES.$core.DS."{$core}.php");
		
		if (class_exists('Inflector')) {
			$core = Inflector::camelize($core);
		}
		
		if (eval("return {$core}::checkStatic();")) {
			return new $core;
		}
		return true;
	}

	static private function loadLib($lib) {
		if (!@require_once($lib)) return false;
		return true;
	}
}

class Core {
	const LOAD_STATIC = true;
	
	static public function checkStatic() {
		return self::LOAD_STATIC;
	}
}

function exceptions_error_handler($severity, $message, $filename, $lineno) {
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
    return false;
}

set_error_handler('exceptions_error_handler');
?>