<?php
namespace Famelo\Oauth\Api;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Oauth".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

class ServiceEntity {
	protected $properties = array();
	protected $relations = array();

	public function __get($name) {
		if(isset($this->properties[$name])){
			return $this->properties[$name];
		}
		if(isset($this->relations[$name])){
			$gitHub = new \Famelo\Oauth\Api\ApiService('Harvest');
			$this->properties[$name] = $gitHub->get($this->relations[$name]['path'], $this->relations[$name]['arguments']);
			return $this->properties[$name];
		}
	}

	public function __set($name, $value) {
		$this->properties[$name] = $value;
	}

	public function addRelation($name, $path, $arguments) {
		$this->relations[$name] = array(
			'path' => $path,
			'arguments' => $arguments
		);
	}

	public function __call($method, $arguments) {
		if (substr($method, 0, 3) === 'get') {
			return $this->__get(lcfirst(substr($method, 3)));
		}
	}
}