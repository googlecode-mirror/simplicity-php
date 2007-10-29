<?
class Sanitize {

	public function string($val,$accept=null) {
		if (is_array($val)) {
			foreach ($val as $k => $v) {
				$val[$k] = Sanitize::string($v,$accept);
			}
			return $val;
		} else {
			$accept = str_replace('/','\/',$accept);
			return preg_replace('/[^a-z0-9A-Z'.$accept.']*/','',$val);
		}
	}

	public function integer($val) {
		$val = preg_replace('/[^0-9]*/','',$val);
		return (strlen($val) >= 1) ? $val : false;
	}

	public function decimal($val) {
		$val = preg_replace('/[^0-9.]*/','',$val);
		return (strlen($val) >= 3) ? $val : false;
	}

	public function date($val) {
		return preg_replace('/[^\w]*/','',$val);
	}

	public function email($val) {
		return preg_replace('/[^\+\-\.0-9A-Za-z_@]*/','',$val);
	}
}

?>