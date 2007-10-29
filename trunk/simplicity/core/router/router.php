<?
class Router extends Core {
	
	static private $routes;
	
	static public function processRequest(&$request) {
		if (is_array($request->url)) {
			if (!self::loadRoutes()) return false;
			
			if (count($request->url) == 0 ) $request->url = isset(self::$routes['default']) ? explode('/',substr(self::$routes['default'],1)) : array();

			$request->url = explode('/',self::mapPath($request->url));
			$request->error = self::isValidPath($request->url) ? 200 : 404;
		}
		
		if ($request->error != 200) {
			$request->url = isset(self::$routes[$request->error]) ? self::$routes[$request->error] : array();
			$request->url = substr($request->url,1);
			$request->url = explode('/',$request->url);
			if (!self::isValidPath($request->url)) $request->url = array();
		}
		
		return count($request->url) ? true : false;
	}

	static public function setError($err,&$request) {
		$request->error = $err;
		$request->url = isset(self::$routes[$err]) ? self::$routes[$err] : array();
		$request->url = substr($request->url,1);
		$request->url = explode('/',$request->url);
		if (!self::isValidPath($request->url)) $request->url = array(); 
	}
	
	static private function loadRoutes() {
		if (!file_exists(SIMPLICITY_CONF.'routes.xml')) {
			self::$routes = array();
			return true;
		}
		$xml = new SimpleXMLElement(file_get_contents(SIMPLICITY_CONF.'routes.xml'));
		
		$routes = array();

		$top = $xml->xpath('//routes');
		if ((string) $top[0]['default']) {
			$routes['default'] = (string) $top[0]['default'];
		}
		
		foreach ($xml->xpath('//route') as $route) {
    		$routes[(string) $route->match] = (string) $route->target; 
		}
		
		foreach ($xml->xpath('//error') as $error) {
    		$routes[(string) $error->code] = (string) $error->target;
		}
			
		self::$routes = $routes;
		return true;
	}
	
	static private function isValidPath($url) {
		if (!is_array($url)) $url = explode('/',$url);
		
		if (!($class = Simplicity::useController($url[0]))) return false;
		
		$object = new $class();
		
		if (!is_object($object)) return false;
		
		$url[1] = isset($url[1]) ? $url[1] : Inflector::underscore($object->getDefaultView());
		
		$method = Inflector::camelize(Sanitize::string(strtolower($url[1]),"a-z0-9_"));
		
		$action = 'action'.$method;
		if (is_callable(array($object,$action))) return true;
				
		$ajax = 'ajax'.$method;
		if (is_callable(array($object,$ajax))) return true;
		
		$view = 'view'.$method;
		if (is_callable(array($object,$view))) return true;

		return false;
	}
	
	static private function mapPath($url) {
		if (self::isValidPath($url)) return implode('/',$url);

		$map = '/'.implode('/',$url);
		
		if (isset(self::$routes[$map])) return substr(self::$routes[$map],1);

		foreach (self::$routes as $route => $target) {
			if (substr($route,0,1) == '/') {
				$preg = str_replace('/','\/',$route);
				$preg = "/^{$preg}$/";
				if (preg_match($preg,$map,$params)) {
					array_shift($params);
					$target = $target.'/'.implode('/',$params);
					return substr($target,1);
				}
			}
		}
		return false;
	}
}
?>
