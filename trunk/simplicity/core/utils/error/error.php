<?

class Error {

	private static $_errors = array();
	private static $_count = 0;

	static function throwError($msg,$level=1,$module='undefined') {
		self::$_count++;
		self::$_errors[$level][$module][self::$_count]['time'] = date('Y-m-d H:i:s');
		self::$_errors[$level][$module][self::$_count]['msg'] = $module;
		self::$_errors[$level][$module][self::$_count]['msg'] = $msg;
	}

	static function getCount($level=null,$module=null) {
		if (!isset($level) && !isset($module)) return(self::$_count);

		$cnt = 0;
		if (is_int($level)) { $level = intval($level); }
		else {$module = $level;$level = null;}

		foreach (self::$_errors as $err_lev => $err_mods) {
			foreach ($err_mods as $err_mod => $err_msgs) {
				if ($level && !$module) {
					if ($err_lev == $level) {
						$cnt += count($err_msgs);
					}
				} elseif ($module && !$level) {
					if ($err_mod == $module) {
						$cnt += count($err_msgs);
					}
				} else {
					if ($err_mod == $module && $err_lev == $level) {
						$cnt += count($err_msgs);
					}
				}
			}
		}
		return $cnt;
	}

	static function getErrors($level=null,$module=null) {
		if (is_int($level)) { $level = intval($level); }
		else {$module = $level;$level = null; }
		if (!isset($level) && !isset($module)) return(self::$_errors);
		if (isset($level) && !isset($module)) return(self::$_errors[$level]);
		if (isset($level) && isset($module)) return(self::$_errors[$level][$module]);
		if (!isset($level) && isset($module)) {
			$ret = array();
			foreach (self::$_errors as $err_lev => $err_mods) {
				foreach ($err_mods as $err_mod => $err_msgs) {
					if ($err_mod == $module) {
						$ret = array_merge_recursive($ret,$err_msgs);
					}
				}
			}
			return $ret;
		}
	}
}

?>