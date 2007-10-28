<?
class Utils extends Core {

	static public function debug($var,$output=false) {

		if (SIMPLICITY_DEBUG) {
			if ($output) ob_start();

			print "<pre class='simplicity_debug'>";
			print_r($var);
			print "<pre class='simplicity_debug'>";

			if ($output) return ob_get_clean();
		}

	}

	static public function array_strip_empty($arr) {
		if (is_array($arr)) {
			$func = create_function('$arr','return Utils::array_strip_empty($arr);');
			return array_filter($arr,$func);

		} else {
			$arr = trim($arr);
			if (strlen($arr)) {
				return true;
			} else {
				return false;
			}
		}
	}

	static public function flush() {
		ob_flush();flush();
	}

	static public function cache($path,&$data=null,$age=null) {
		if (!isset($data) || !isset($age)) {
			if (!file_exists($path)) return false;
			$file = unserialize(file_get_contents($path));
			if ((time() - filemtime($path)) > $file['maxage']) {
				unlink($path);
				return false;
			}
			return $file['data'];
		} else {
			if (is_string($age)) $age = strtotime($age) - time();
			$file['maxage'] = intval($age);
			$file['data'] = $data;
			file_put_contents($path,serialize($file));
		}
	}
	
	static public function load($util) {
		if (class_exists('Inflector')) {
			$util = Inflector::underscore($util);
		} else {
			$util = strtolower($util);
		}
		if (!file_exists(SIMPLICITY_CLASSES.'utils'.DS.$util.DS.$util.'.php')) {
			return false;
		}
		require_once(SIMPLICITY_CLASSES.'utils'.DS.$util.DS.$util.'.php');
		return true;
	}
}

//Short-hand functions
function debug($var) {
	return Utils::debug($var);
}

?>