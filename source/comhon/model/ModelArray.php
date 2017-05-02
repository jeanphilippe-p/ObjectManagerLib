<?php
namespace comhon\model;

use comhon\object\ObjectArray;
use comhon\model\MainModel;
use comhon\object\Object;
use comhon\interfacer\Interfacer;
use comhon\object\collection\ObjectCollection;

class ModelArray extends ModelContainer {
	
	/**
	 * name of each element
	 * for exemple if we have a ModelArray 'children', each element name would be 'child'
	 * @var string
	 */
	private $mElementName;
	
	public function __construct($pModel, $pElementName) {
		parent::__construct($pModel);
		$this->mElementName = $pElementName;
	}
	
	public function getElementName() {
		return $this->mElementName;
	}
	
	public function getObjectClass() {
		return 'comhon\object\ObjectArray';
	}
	
	public function getObjectInstance($pIsloaded = true) {
		return new ObjectArray($this, $pIsloaded);
	}
	
	/** ********************************************************************* **/
	
	/**
	 *
	 * @param ObjectArray $pObject
	 * @param array $pMainForeignObjects
	 */
	protected function _addMainCurrentObject(Object $pObject, &$pMainForeignObjects = null, Interfacer $pInterfacer = null) {
		if (!($pObject instanceof ObjectArray)) {
			throw new \Exception('first parameter should be ObjectArray');
		}
		if (is_array($pMainForeignObjects)) {
			foreach ($pObject->getValues() as $lObject) {
				if (!is_null($lObject) && ($lObject->getModel() instanceof MainModel) && !is_null($lObject->getId()) && $lObject->hasCompleteId()) {
					$pMainForeignObjects[$lObject->getModel()->getName()][$lObject->getId()] = null;
				}
			}
		}
		if (!is_null($pInterfacer) && $pInterfacer->hasToExportMainForeignObjects()) {
			foreach ($pObject->getValues() as $lObject) {
				if (!is_null($lObject) && ($lObject->getModel() instanceof MainModel) && !is_null($lObject->getId()) && $lObject->hasCompleteId()) {
					$pInterfacer->addMainForeignObject($pInterfacer->createNode('empty'), $lObject->getId(), $lObject->getModel());
				}
			}
		}
	}
	
	/**
	 *
	 * @param ObjectArray $pObject
	 * @param array $pMainForeignObjects
	 */
	protected function _removeMainCurrentObject(Object $pObject, &$pMainForeignObjects = null, Interfacer $pInterfacer = null) {
		if (!($pObject instanceof ObjectArray)) {
			throw new \Exception('first parameter should be ObjectArray');
		}
		if (is_array($pMainForeignObjects)) {
			foreach ($pObject->getValues() as $lObject) {
				if (!is_null($lObject) && ($lObject->getModel() instanceof MainModel) && !is_null($lObject->getId()) && $lObject->hasCompleteId()) {
					unset($pMainForeignObjects[$lObject->getModel()->getName()][$lObject->getId()]);
				}
			}
		}
		if (!is_null($pInterfacer) && $pInterfacer->hasToExportMainForeignObjects()) {
			foreach ($pObject->getValues() as $lObject) {
				if (!is_null($lObject) && ($lObject->getModel() instanceof MainModel) && !is_null($lObject->getId()) && $lObject->hasCompleteId()) {
					$pInterfacer->removeMainForeignObject($lObject->getId(), $lObject->getModel());
				}
			}
		}
	}
	
	/**
	 *
	 * @param Object $pObjectArray
	 * @param string $pNodeName
	 * @param Interfacer $pInterfacer
	 * @param boolean $pIsFirstLevel
	 * @throws \Exception
	 * @return mixed|null
	 */
	protected function _export($pObjectArray, $pNodeName, Interfacer $pInterfacer, $pIsFirstLevel) {
		if (is_null($pObjectArray)) {
			return null;
		}
		if (!$pObjectArray->isLoaded()) {
			return  Interfacer::__UNLOAD__;
		}
		$lNodeArray = $pInterfacer->createNodeArray($pNodeName);
		
		if ($this->getModel() instanceof ModelEnum) {
			$lEnum = $this->getModel()->getEnum();
			foreach ($pObjectArray->getValues() as $lValue) {
				if (in_array($lValue, $lEnum)) {
					$pInterfacer->addValue($lNodeArray, $this->getModel()->_export($lValue, $this->mElementName, $pInterfacer, $pIsFirstLevel), $this->mElementName);
				}
			}
		} else {
			foreach ($pObjectArray->getValues() as $lValue) {
				$pInterfacer->addValue($lNodeArray, $this->getModel()->_export($lValue, $this->mElementName, $pInterfacer, $pIsFirstLevel), $this->mElementName);
			}
		}
		return $lNodeArray;
	}
	
	/**
	 *
	 * @param ObjectArray $pObject
	 * @param string $pNodeName
	 * @param Interfacer $pInterfacer
	 * @throws \Exception
	 * @return mixed|null
	 */
	protected function _exportId(Object $pObjectArray, $pNodeName, Interfacer $pInterfacer) {
		if (is_null($pObjectArray)) {
			return null;
		}
		if (!$pObjectArray->isLoaded()) {
			return  Interfacer::__UNLOAD__;
		}
		$lNodeArray = $pInterfacer->createNodeArray($pNodeName);
		if (!is_null($pObjectArray)) {
			foreach ($pObjectArray->getValues() as $lValue) {
				$pInterfacer->addValue($lNodeArray, $this->getModel()->_exportId($lValue, $this->mElementName, $pInterfacer), $this->mElementName);
			}
		}
		return $lNodeArray;
	}
	
	
	/**
	 *
	 * @param mixed $pValue
	 * @param Interfacer $pInterfacer
	 * @param ObjectCollection $pLocalObjectCollection
	 * @param boolean $pIsFirstLevel
	 * @return Object
	 */
	protected function _import($pInterfacedObject, Interfacer $pInterfacer, ObjectCollection $pLocalObjectCollection = null, $pIsFirstLevel = false) {
		if (is_null($pInterfacedObject)) {
			return null;
		}
		$lObjectArray = $this->getObjectInstance();
		foreach ($pInterfacer->getTraversableNode($pInterfacedObject) as $lElement) {
			$lObjectArray->pushValue($this->getModel()->_import($lElement, $pInterfacer, $pLocalObjectCollection, $pIsFirstLevel), $pInterfacer->hasToFlagValuesAsUpdated());
		}
		return $lObjectArray;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \comhon\model\Model::_importId()
	 */
	protected function _importId($pInterfacedObject, Interfacer $pInterfacer, ObjectCollection $pLocalObjectCollection) {
		if (is_null($pInterfacedObject)) {
			return null;
		}
		$lObjectArray = $this->getObjectInstance();
		foreach ($pInterfacer->getTraversableNode($pInterfacedObject) as $lElement) {
			$lObjectArray->pushValue($this->getModel()->_importId($lElement, $pInterfacer, $pLocalObjectCollection), $pInterfacer->hasToFlagValuesAsUpdated());
		}
		return $lObjectArray;
	}
	
	/**
	 *
	 * @param mixed $pInterfacedObject
	 * @param Interfacer $pInterfacer
	 * @throws \Exception
	 * @return ObjectArray
	 */
	public function import($pInterfacedObject, Interfacer $pInterfacer) {
		$this->load();
		if (is_null($pInterfacedObject)) {
			return null;
		}
		if (!($this->getModel() instanceof MainModel)) {
			throw new \Exception('can\'t apply function. Only callable for array with MainModel');
		}
		$lObjectArray = $this->getObjectInstance();
		foreach ($pInterfacer->getTraversableNode($pInterfacedObject) as $lElement) {
			$lObjectArray->pushValue($this->getModel()->import($lElement, $pInterfacer), $pInterfacer->hasToFlagValuesAsUpdated());
		}
		return $lObjectArray;
	}
	
	/**
	 *
	 * @param Object $pObject
	 * @param mixed $pInterfacedObject
	 * @param Interfacer $pInterfacer
	 * @throws \Exception
	 */
	public function fillObject(Object $pObjectArray, $pInterfacedObject, Interfacer $pInterfacer) {
		$this->load();
		if (!($pObjectArray instanceof ObjectArray)) {
			throw new \Exception('first parameter should be ObjectArray');
		}
		if (!($pObjectArray->getModel() instanceof ModelArray) || $pObjectArray->getModel()->getModel() !== $this->getModel()) {
			throw new \Exception('current model instance must be same instance of object model');
		}
		if (!($this->getModel() instanceof MainModel)) {
			throw new \Exception('can\'t apply function. Only callable for array with MainModel');
		}
		$pObjectArray->reset();
		foreach ($pInterfacer->getTraversableNode($pInterfacedObject) as $lElement) {
			$pObjectArray->pushValue($this->getModel()->import($lElement, $pInterfacer), $pInterfacer->hasToFlagValuesAsUpdated());
		}
		$pObjectArray->setIsLoaded(true);
	}
	
	/** ********************************************************************* **/
	
	/**
	 * get object with filtered values, return new instance of object array
	 * and possible new insance oject for each array element
	 * @param Object $pObject
	 * @param string[]|null $pPropertiesFilter
	 * @param boolean $pCheckProperties
	 * @return Object
	 */
	protected function _getFilteredObject(Object $pObjectArray, $pPropertiesFilter, $pCheckProperties = true) {
		if (!($pObjectArray instanceof ObjectArray)) {
			throw new \Exception('first parameter should be ObjectArray');
		}
		if (empty($pPropertiesFilter)) {
			return $pObjectArray;
		}
		$lNewObjectArray   = new ObjectArray($pObjectArray->getModel());
		$lFlagAsUpdated    = $pObjectArray->isFlagedAsUpdated();
		
		foreach ($pObjectArray->getValues() as $lKey => $lValue) {
			if ($lValue instanceof Object) {
				$lNewObjectArray->setValue($lKey, parent::_getFilteredObject($lValue, array_unique($pPropertiesFilter), false), $lFlagAsUpdated, false);
			} else {
				$lNewObjectArray->setValue($lKey, $lValue, $lFlagAsUpdated, false);
			}
		}
		return $lNewObjectArray;
	}
	
	protected function _toStdObject($pObjectArray, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, &$pMainForeignObjects = null) {
		if (is_null($pObjectArray)) {
			return null;
		}
		if (!$pObjectArray->isLoaded()) {
			return  Interfacer::__UNLOAD__;
		}
		$lReturn = [];
		
		if ($this->getModel() instanceof ModelEnum) {
			$lEnum = $this->getModel()->getEnum();
			foreach ($pObjectArray->getValues() as $lKey => $lValue) {
				if (in_array($lValue, $lEnum)) {
					$lReturn[$lKey] = $this->getModel()->_toStdObject($lValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
				}
			}
		} else {
			foreach ($pObjectArray->getValues() as $lKey => $lValue) {
				$lReturn[$lKey] = $this->getModel()->_toStdObject($lValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
			}
		}
		return $lReturn;
	}
	
	protected function _toStdObjectId(Object $pObjectArray, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, &$pMainForeignObjects = null) {
		if (is_null($pObjectArray)) {
			return null;
		}
		if (!$pObjectArray->isLoaded()) {
			return  Interfacer::__UNLOAD__;
		}
		$lReturn = [];
		if (!is_null($pObjectArray)) {
			foreach ($pObjectArray->getValues() as $lKey => $lValue) {
				$lReturn[$lKey] = $this->getModel()->_toStdObjectId($lValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
			}
		}
		return $lReturn;
	}
	
	public function fillObjectFromSerializedStdObject(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromStdObject($pObjectArray, $pArray, true, true, $pTimeZone, true, false);
	}
	
	public function fillObjectFromPublicStdObject(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromStdObject($pObjectArray, $pArray, false, false, $pTimeZone, true, true);
	}
	
	public function fillObjectFromPrivateStdObject(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromStdObject($pObjectArray, $pArray, true, false, $pTimeZone, true, true);
	}
	
	public function fillObjectFromStdObject(Object $pObjectArray, $pArray, $pPrivate = false, $pUseSerializationName = false, $pTimeZone = null, $pUpdateLoadStatus = true, $pFlagAsUpdated = true) {
		if (!($pObjectArray instanceof ObjectArray)) {
			throw new \Exception('first parameter should be ObjectArray');
		}
		
		$this->load();
		$lDateTimeZone = new \DateTimeZone(is_null($pTimeZone) ? date_default_timezone_get() : $pTimeZone);
	
		if (!($pObjectArray->getModel() instanceof ModelArray) || $pObjectArray->getModel()->getModel() !== $this->getModel()) {
			throw new \Exception('current model instance must be same instance of object model');
		}
		if (!($this->getModel() instanceof MainModel)) {
			throw new \Exception('can\'t apply function. Only callable for array with MainModel');
		}
		$pObjectArray->reset();
		foreach ($pArray as $lKey => $lStdValue) {
			$pObjectArray->setValue($lKey, $this->getModel()->_fromStdObject($lStdValue, $pPrivate, $pUseSerializationName, $lDateTimeZone, $pFlagAsUpdated, null), $pFlagAsUpdated);
		}
		if ($pUpdateLoadStatus) {
			$pObjectArray->setIsLoaded(true);
		}
	}
	
	public function fromSerializedStdObject($pArray, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromStdObject($pArray, true, true, $pMergeType, $pTimeZone, false);
	}
	
	public function fromPublicStdObject($pArray, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromStdObject($pArray, false, false, $pMergeType, $pTimeZone, true);
	}
	
	public function fromPrivateStdObject($pArray, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromStdObject($pArray, true, false, $pMergeType, $pTimeZone, true);
	}
	
	public function fromStdObject($pArray, $pPrivate = false, $pUseSerializationName = false, $pMergeType = self::MERGE, $pTimeZone = null, $pFlagAsUpdated = true) {
		if (is_null($pArray)) {
			return null;
		}
		if (!($this->getModel() instanceof MainModel)) {
			throw new \Exception('can\'t apply function. Only callable for array with MainModel');
		}
		$lObjectArray = $this->getObjectInstance();
		foreach ($pArray as $lKey => $lStdValue) {
			$lObjectArray->setValue($lKey, $this->getModel()->fromStdObject($lStdValue, $pPrivate, $pUseSerializationName, $pMergeType, $pTimeZone, $pFlagAsUpdated), $pFlagAsUpdated);
		}
		return $lObjectArray;
	}
	
	protected function _fromStdObject($pArray, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pFlagAsUpdated, $pLocalObjectCollection) {
		if (is_null($pArray)) {
			return null;
		}
		if (is_string($pArray) && $pArray == Interfacer::__UNLOAD__) {
			return $this->getObjectInstance(false);
		}
		$lObjectArray = $this->getObjectInstance();
		foreach ($pArray as $lKey => $lStdValue) {
			$lObjectArray->setValue($lKey, $this->getModel()->_fromStdObject($lStdValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pFlagAsUpdated, $pLocalObjectCollection), $pFlagAsUpdated);
		}
		return $lObjectArray;
	}
	
	protected function _fromStdObjectId($pArray, $pFlagAsUpdated, $pLocalObjectCollection) {
		if (is_null($pArray)) {
			return null;
		}
		if (is_string($pArray) && $pArray == Interfacer::__UNLOAD__) {
			return $this->getObjectInstance(false);
		}
		$lReturn = $this->getObjectInstance();
		foreach ($pArray as $lKey => $lValue) {
			$lReturn->setValue($lKey, $this->getModel()->_fromStdObjectId($lValue, $pFlagAsUpdated, $pLocalObjectCollection), $pFlagAsUpdated);
		}
		return $lReturn;
	}
	
	protected function _toFlattenedArray($pObjectArray, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, &$pMainForeignObjects = null) {
		if (is_null($pObjectArray)) {
			return null;
		}
		if (!$pObjectArray->isLoaded()) {
			return  Interfacer::__UNLOAD__;
		}
		$lReturn = [];
		
		if ($this->getModel() instanceof ModelEnum) {
			$lEnum = $this->getModel()->getEnum();
			foreach ($pObjectArray->getValues() as $lKey => $lValue) {
				if (in_array($lValue, $lEnum)) {
					$lReturn[$lKey] = $this->getModel()->_toFlattenedArray($lValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
				}
			}
		} else {
			foreach ($pObjectArray->getValues() as $lKey => $lValue) {
				$lReturn[$lKey] = $this->getModel()->_toFlattenedArray($lValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
			}
		}
		return $lReturn;
	}
	
	protected function _toFlattenedValue($pObjectArray, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, &$pMainForeignObjects = null) {
		if (is_null($pObjectArray)) {
			return null;
		}
		if (!$pObjectArray->isLoaded()) {
			return  Interfacer::__UNLOAD__;
		}
		$lReturn = [];
	
		if ($this->getModel() instanceof ModelEnum) {
			$lEnum = $this->getModel()->getEnum();
			foreach ($pObjectArray->getValues() as $lKey => $lValue) {
				if (in_array($lValue, $lEnum)) {
					$lReturn[$lKey] = $this->getModel()->_toFlattenedValue($lValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
				}
			}
		} else {
			foreach ($pObjectArray->getValues() as $lKey => $lValue) {
				$lReturn[$lKey] = $this->getModel()->_toFlattenedValue($lValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
			}
		}
		return $lReturn;
	}
	
	protected function _toFlattenedValueId(Object $pObjectArray, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, &$pMainForeignObjects = null) {
		if (is_null($pObjectArray)) {
			return null;
		}
		if (!$pObjectArray->isLoaded()) {
			return  Interfacer::__UNLOAD__;
		}
		$lReturn = [];
		if (!is_null($pObjectArray)) {
			foreach ($pObjectArray->getValues() as $lKey => $lValue) {
				$lReturn[$lKey] = $this->getModel()->_toFlattenedValueId($lValue, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
			}
		}
		return $lReturn;
	}
	
	public function fillObjectFromSqlDatabase(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromFlattenedArray($pObjectArray, $pArray, true, true, $pTimeZone, true, false);
	}
	
	public function fillObjectFromPublicFlattenedArray(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromFlattenedArray($pObjectArray, $pArray, false, false, $pTimeZone, true, true);
	}
	
	public function fillObjectFromPrivateFlattenedArray(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromFlattenedArray($pObjectArray, $pArray, true, false, $pTimeZone, true, true);
	}
	
	public function fillObjectFromFlattenedArray(Object $pObjectArray, $pArray, $pPrivate = false, $pUseSerializationName = false, $pTimeZone = null, $pUpdateLoadStatus = true, $pFlagAsUpdated = true) {
		if (!($pObjectArray instanceof ObjectArray)) {
			throw new \Exception('first parameter should be ObjectArray');
		}
		
		$this->load();
		$lDateTimeZone = new \DateTimeZone(is_null($pTimeZone) ? date_default_timezone_get() : $pTimeZone);
	
		if (!($pObjectArray->getModel() instanceof ModelArray) || $pObjectArray->getModel()->getModel() !== $this->getModel()) {
			throw new \Exception('current model instance must be same instance of object model');
		}
		if (!($this->getModel() instanceof MainModel)) {
			throw new \Exception('can\'t apply function. Only callable for array with MainModel');
		}
		$pObjectArray->reset();
		foreach ($pArray as $lKey => $lFlattenedArray) {
			$pObjectArray->setValue($lKey, $this->getModel()->_fromFlattenedArray($lFlattenedArray, $pPrivate, $pUseSerializationName, $lDateTimeZone, $pFlagAsUpdated, null), $pFlagAsUpdated);
		}
		if ($pUpdateLoadStatus) {
			$pObjectArray->setIsLoaded(true);
		}
	}
	
	protected function _fromFlattenedValue($pJsonEncodedObject, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pFlagAsUpdated, $pLocalObjectCollection) {
		if (is_null($pJsonEncodedObject)) {
			return null;
		}
		if (is_string($pJsonEncodedObject) && $pJsonEncodedObject == Interfacer::__UNLOAD__) {
			return $this->getObjectInstance(false);
		}
		$lStdObject = json_decode($pJsonEncodedObject);
		return $this->_fromStdObject($lStdObject, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pFlagAsUpdated, $pLocalObjectCollection);
	}
	
	public function fromSqlDatabase($pRows, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromFlattenedArray($pRows, true, true, $pMergeType, $pTimeZone, false);
	}
	
	public function fromPublicFlattenedArray($pRows, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromFlattenedArray($pRows, false, false, $pMergeType, $pTimeZone, true);
	}
	
	public function fromPrivateFlattenedArray(array $pRows, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromFlattenedArray($pRows, true, false, $pMergeType, $pTimeZone, true);
	}
	
	public function fromFlattenedArray($pRows, $pPrivate = false, $pUseSerializationName = false, $pMergeType = self::MERGE, $pTimeZone = null, $pFlagAsUpdated = true) {
		if (!($this->getModel() instanceof MainModel)) {
			throw new \Exception('can\'t apply function. Only callable for array with MainModel');
		}
		$lObjectArray = $this->getObjectInstance();
		foreach ($pRows as $lKey => $lRow) {
			$lObjectArray->setValue($lKey, $this->getModel()->fromFlattenedArray($lRow, $pPrivate, $pUseSerializationName, $pMergeType, $pTimeZone, $pFlagAsUpdated), $pFlagAsUpdated);
		}
		return $lObjectArray;
	}
	
	protected function _toXml($pObjectArray, $pXmlNode, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, &$pMainForeignObjects = null) {
		if (!is_null($pObjectArray)) {
			if (!$pObjectArray->isLoaded()) {
				$pXmlNode[Interfacer::__UNLOAD__] = '1';
			}
			else if ($this->getModel() instanceof ModelEnum) {
				$lEnum = $this->getModel()->getEnum();
				foreach ($pObjectArray->getValues() as $lKey => $lValue) {
					if (in_array($lValue, $lEnum)) {
						$lValue = $this->getModel()->_toXml($lValue, $pXmlNode, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
						$lXmlChildNode = $pXmlNode->addChild($this->mElementName, $lValue);
					}
				}
			} else if ($this->getModel() instanceof SimpleModel) {
				foreach ($pObjectArray->getValues() as $lKey => $lValue) {
					$lValue = $this->getModel()->_toXml($lValue, $pXmlNode, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
					$lXmlChildNode = $pXmlNode->addChild($this->mElementName, $lValue);
				}
			} else {
				foreach ($pObjectArray->getValues() as $lKey => $lValue) {
					$lXmlChildNode = $pXmlNode->addChild($this->mElementName);
					$this->getModel()->_toXml($lValue, $lXmlChildNode, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
				}
			}
		}
	}
	
	protected function _toXmlId(Object $pObjectArray, $pXmlNode, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, &$pMainForeignObjects = null) {
		if (!is_null($pObjectArray)) {
			if (!$pObjectArray->isLoaded()) {
				$pXmlNode[Interfacer::__UNLOAD__] = '1';
			}
			else {
				foreach ($pObjectArray->getValues() as $lKey => $lValue) {
					$lXmlChildNode = $pXmlNode->addChild($this->mElementName);
					$this->getModel()->_toXmlId($lValue, $lXmlChildNode, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pUpdatedValueOnly, $pOriginalUpdatedValueOnly, $pMainForeignObjects);
				}
			}
		}
	}
	
	public function fillObjectFromSerializedXml(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromXml($pObjectArray, $pArray, true, true, $pTimeZone, true, false);
	}
	
	public function fillObjectFromPublicXml(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromXml($pObjectArray, $pArray, false, false, $pTimeZone, true, true);
	}
	
	public function fillObjectFromPrivateXml(Object $pObjectArray, $pArray, $pTimeZone = null) {
		$this->fillObjectFromXml($pObjectArray, $pArray, true, false, $pTimeZone, true, true);
	}
	
	public function fillObjectFromXml(Object $pObjectArray, $pXml, $pPrivate = false, $pUseSerializationName = false, $pTimeZone = null, $pUpdateLoadStatus = true, $pFlagAsUpdated = true) {
		if (!($pObjectArray instanceof ObjectArray)) {
			throw new \Exception('first parameter should be ObjectArray');
		}
		
		$this->load();
		$lDateTimeZone = new \DateTimeZone(is_null($pTimeZone) ? date_default_timezone_get() : $pTimeZone);
	
		if (!($pObjectArray->getModel() instanceof ModelArray) || $pObjectArray->getModel()->getModel() !== $this->getModel()) {
			throw new \Exception('current model instance must be same instance of object model');
		}
		if (!($this->getModel() instanceof MainModel)) {
			throw new \Exception('can\'t apply function. Only callable for array with MainModel');
		}
		$pObjectArray->reset();
		foreach ($pXml->children() as $lChild) {
			$pObjectArray->pushValue($this->getModel()->_fromXml($lChild, $pPrivate, $pUseSerializationName, $lDateTimeZone, $pFlagAsUpdated, null), $pFlagAsUpdated);
		}
		if ($pUpdateLoadStatus) {
			$pObjectArray->setIsLoaded(true);
		}
	}
	
	public function fromSerializedXml($pXml, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromXml($pXml, true, true, $pMergeType, $pTimeZone, false);
	}
	
	public function fromPublicXml($pXml, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromXml($pXml, false, false, $pMergeType, $pTimeZone, true);
	}
	
	public function fromPrivateXml($pXml, $pMergeType = self::MERGE, $pTimeZone = null) {
		return $this->fromXml($pXml, true, false, $pMergeType, $pTimeZone, true);
	}
	
	public function fromXml($pXml, $pPrivate = false, $pUseSerializationName = false, $pMergeType = self::MERGE, $pTimeZone = null, $pFlagAsUpdated = true) {
		if (!($this->getModel() instanceof MainModel)) {
			throw new \Exception('can\'t apply function. Only callable for array with MainModel');
		}
		$lObjectArray = $this->getObjectInstance();
		foreach ($pXml->{$this->mElementName} as $lChild) {
			$lObjectArray->pushValue($this->getModel()->fromXml($lChild, $pPrivate, $pUseSerializationName, $pMergeType, $pTimeZone, $pFlagAsUpdated), $pFlagAsUpdated);
		}
		return $lObjectArray;
	}
	
	protected function _fromXml($pXml, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pFlagAsUpdated, $pLocalObjectCollection) {
		if (isset($pXml[Interfacer::__UNLOAD__]) && ((string) $pXml[Interfacer::__UNLOAD__] == '1')) {
			$lObjectArray = $this->getObjectInstance(false);
		} else {
			$lObjectArray = $this->getObjectInstance();
			foreach ($pXml->{$this->mElementName} as $lChild) {
				$lObjectArray->pushValue($this->getModel()->_fromXml($lChild, $pPrivate, $pUseSerializationName, $pDateTimeZone, $pFlagAsUpdated, $pLocalObjectCollection), $pFlagAsUpdated);
			}
		}
		return $lObjectArray;
	}
	
	protected function _fromXmlId($pXml, $pFlagAsUpdated, $pLocalObjectCollection) {
		if (isset($pXml[Interfacer::__UNLOAD__]) && ((string) $pXml[Interfacer::__UNLOAD__] == '1')) {
			$lValue = $this->getObjectInstance(false);
		} else {
			$lValue = $this->getObjectInstance();
			foreach ($pXml->{$this->mElementName} as $lChild) {
				$lValue->pushValue($this->getModel()->_fromXmlId($lChild, $pFlagAsUpdated, $pLocalObjectCollection), $pFlagAsUpdated);
			}
		}
		return $lValue;
	}

	protected function _fromFlattenedValueId($pValue, $pFlagAsUpdated, $pLocalObjectCollection) {
		if ($pValue == Interfacer::__UNLOAD__) {
			return $this->getObjectInstance(false);
		}
		return $this->_fromStdObjectId(json_decode($pValue), $pFlagAsUpdated, $pLocalObjectCollection);
	}
	
	public function verifValue($pValue) {
		if (!($pValue instanceof ObjectArray) || ($pValue->getModel()->getModel() !== $this->getModel() && !$pValue->getModel()->getModel()->isInheritedFrom($this->getModel()))) {
			$lNodes = debug_backtrace();
			$lClass = gettype($pValue) == 'object' ? get_class($pValue): gettype($pValue);
			throw new \Exception("Argument 2 passed to {$lNodes[1]['class']}::{$lNodes[1]['function']}() must be an instance of {$this->getObjectClass()}, instance of $lClass given, called in {$lNodes[1]['file']} on line {$lNodes[1]['line']} and defined in {$lNodes[0]['file']}");
		}
		return true;
	}
	
}