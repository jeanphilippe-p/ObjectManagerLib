<?php

/*
 * This file is part of the Comhon package.
 *
 * (c) Jean-Philippe <jeanphilippe.perrotton@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Comhon\Object\Config;

use Comhon\Object\ExtendableObject;
use Comhon\Object\ComhonObject;
use Comhon\Interfacer\StdObjectInterfacer;

class Config extends ExtendableObject {
	
	private  static $_instance;
	
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			$config_afe = DIRECTORY_SEPARATOR .'etc'.DIRECTORY_SEPARATOR.'comhon'.DIRECTORY_SEPARATOR.'config.json';
			$stdInterfacer = new StdObjectInterfacer();
			$stdInterfacer->setSerialContext(true);
			$stdInterfacer->setPrivateContext(true);
			$jsonConfig = $stdInterfacer->read($config_afe);
			if (is_null($jsonConfig) || $jsonConfig === false) {
				throw new \Exception('failure when try to read comhon config file');
			}
			self::$_instance = new self();
			self::$_instance->fill($jsonConfig, $stdInterfacer);
		}
		
		return self::$_instance;
	}
	
	protected function _getModelName() {
		return 'config';
	}
	
	/**
	 * 
	 * @return ComhonObject|null
	 */
	public function getDataBaseOptions() {
		return $this->getValue('database');
	}
	
	/**
	 *
	 * @return string
	 */
	public function getDataBaseCharset() {
		return ($this->getValue('database') instanceof ComhonObject) && $this->getValue('database')->hasValue('charset')
			? $this->getValue('database')->getValue('charset')
			: 'utf8';
	}
	
	/**
	 *
	 * @return string
	 */
	public function getDataBaseTimezone() {
		return ($this->getValue('database') instanceof ComhonObject) && $this->getValue('database')->hasValue('timezone')
		? $this->getValue('database')->getValue('timezone')
		: 'UTC';
	}
	
	/**
	 *
	 * @return string
	 */
	public function getManifestListPath() {
		return $this->getValue('manifestList');
	}
	
	/**
	 *
	 * @return string
	 */
	public function getSerializationListPath() {
		return $this->getValue('serializationList');
	}
	
	/**
	 *
	 * @return string
	 */
	public function getRegexListPath() {
		return $this->getValue('regexList');
	}
	
}