<?php

/*
 * This file is part of the Comhon package.
 *
 * (c) Jean-Philippe <jeanphilippe.perrotton@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Comhon\Serialization\File;

use Comhon\Serialization\SerializationFile;
use Comhon\Interfacer\XMLInterfacer;
use Comhon\Interfacer\Interfacer;

class XmlFile extends SerializationFile {
	
	/**
	 * @var string xml serialization type
	 */
	const MODEL_NAME = 'Comhon\File\XmlFile';
	
	/**
	 * @var \Comhon\Serialization\File\XmlFile
	 */
	private static $instance;
	
	/**
	 * {@inheritDoc}
	 * @see \Comhon\Serialization\SerializationUnit::getInstance()
	 * 
	 * @return \Comhon\Serialization\File\XmlFile
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Comhon\Serialization\ValidatedSerializationUnit::getModelName()
	 */
	public static function getModelName() {
		return self::MODEL_NAME;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Comhon\Serialization\SerializationFile::_initInterfacer()
	 * 
	 * @return \Comhon\Interfacer\XMLInterfacer
	 */
	protected static function _initInterfacer() {
		$interfacer = new XMLInterfacer();
		$interfacer->setSerialContext(true);
		$interfacer->setPrivateContext(true);
		$interfacer->setFlagValuesAsUpdated(false);
		$interfacer->setMergeType(Interfacer::OVERWRITE);
		
		return $interfacer;
	}
	
}