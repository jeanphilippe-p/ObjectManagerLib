<?php

/*
 * This file is part of the Comhon package.
 *
 * (c) Jean-Philippe <jeanphilippe.perrotton@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Comhon\Model\Property;

use Comhon\Object\UniqueObject;
use Comhon\Model\ModelForeign;
use Comhon\Model\Model;
use Comhon\Model\Singleton\ModelManager;

class ForeignProperty extends Property {
	
	/**
	 * 
	 * @param \Comhon\Model\ModelForeign $model
	 * @param string $name
	 * @param string $serializationName
	 * @param boolean $isPrivate
	 * @param boolean $isRequired
	 * @param boolean $isSerializable
	 * @param boolean $isNotNull
	 * @param boolean $dependencies
	 */
	public function __construct(ModelForeign $model, $name, $serializationName = null, $isPrivate = false, $isRequired = false, $isSerializable = true, $isNotNull = false, $dependencies = []) {
		parent::__construct($model, $name, $serializationName, false, $isPrivate, $isRequired, $isSerializable, $isNotNull, null, null, [], $dependencies);
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Comhon\Model\Property\Property::loadValue()
	 */
	public function loadValue(UniqueObject $object, $propertiesFilter = null, $forceLoad = false) {
		$this->getModel()->verifValue($object);
		if ($object->isLoaded() && !$forceLoad) {
			return false;
		}
		$serialization = $this->getUniqueModel()->getSerialization();
		if (is_null($serialization)) {
			return false;
		}
		return $serialization->getSerializationUnit()->loadObject($object, $propertiesFilter);
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Comhon\Model\Property\Property::isForeign()
	 */
	public function isForeign() {
		return true;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see \Comhon\Model\Property\Property::getLiteralModel()
	 */
	public function getLiteralModel() {
		if (!($this->getModel()->getModel() instanceof Model)) {
			return null;
		}
		$foreignModel = $this->getModel()->getModel();
		return $foreignModel->hasUniqueIdProperty() 
			? $foreignModel->getUniqueIdProperty()->getModel()
			: ModelManager::getInstance()->getInstanceModel('string');
	}
	
}