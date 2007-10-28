<?
class Router extends Core {
	
	static private $routes;

	static function processRequest(&$request) {
		if (is_array($request->url)) {
			if (!self::loadRoutes()) return false;
			
			if (count($request->url) == 0 ) $request->url = isset(self::$routes['default']) ? self::$routes['default'] : array();

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

	static private function loadRoutes() {
		if (!file_exists(SIMPLICITY_CONF.'routes.php')) return false;
		@include(SIMPLICITY_CONF.'routes.php');
		if (!isset($routes) || !is_array($routes)) return false;
		self::$routes = $routes;
		return true;
	}
	
	static private function isValidPath($url) {
		if (!is_array($url)) $url = explode('/',$url);
		
		if (!($class = Simplicity::useController($url[0]))) return false;
		
		$object = new $class();
		
		if (!is_object($object)) return false;
		
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
		if (self::isValidPath($url)) return $url;
		
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
