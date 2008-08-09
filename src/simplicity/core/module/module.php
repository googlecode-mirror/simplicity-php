<?php
abstract class smp_Module {
	
	private $_depends 	= array();
	private $_provides 	= array();
	
	private $_opts = array();
	private $_requires 	= "";
	
	final public function __construct($opts=array()) {
		$this->def();
		if (!isset($opts['_noload'])) {
			$this->_opts = $opts;
			foreach ($this->_requires as $require) {
				if (!isset($this->_opts[$require])) {
					$class = get_class($this);
					throw new Exception("Module {$class} failed to load. Must provide a value for option '{$require}'.");
				}
			}
		}
	}
	
	abstract function def();
	
	abstract function init();

	final protected function requiresOption($opt) {
		$this->_requires[$opt] = $opt;
	}
	
	/**
	 * Get the value of the option $name, return null if it does not exist. 
	 *
	 * @return mixed
	 */	
	final protected function getOption($name) {
		return isset($this->_opts[$name]) ? $this->_opts[$name] : null; 
	}

	final public function getDepends() {
		return $this->_depends;
	}
	
	final public function getServices() {
		return $this->_provides;
	}
	
	/**
	 * Tell the module loader that this service depends on another $service being available.  
	 *
	 * @return mixed
	 */		
	final protected function dependsOn($service) {
		if (isset($this->_provides[$service])) {
			$class = get_class($this);
			throw new Exception("Module {$class} can not both provide and depend on the same service.");
		}
		$this->_depends[$service] = $service;
	}

	/**
	 * Tell the module loader that this service provides this $service.  
	 *
	 * @return mixed
	 */		
	final protected function provides($service) {
		if (isset($this->_depends[$service])) {
			$class = get_class($this);
			throw new Exception("Module {$class} can not both provide and depend on the same service.");
		}
		$this->_provides[$service] = $service;
	}
	
}