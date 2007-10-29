<?
class Controller extends Core {
	
	const LOAD_STATIC = true;
	
	private $_default_view = 'View';

	protected function setDefaultView($view) {
		$action = 'action'.$view;
		if (is_callable(array($this,$action))) return ($this->_default_view = $view);
				
		$ajax = 'ajax'.$view;
		if (is_callable(array($this,$ajax)))return ($this->_default_view = $view);
		
		$view = 'view'.$view;
		if (is_callable(array($this,$view))) return ($this->_default_view = $view);
		
		return false;
	}
	
	public function getDefaultView() {
		return $this->_default_view;
	}
	
}
?>