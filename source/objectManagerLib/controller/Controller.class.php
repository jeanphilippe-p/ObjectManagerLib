<?php
namespace objectManagerLib\controller;

use objectManagerLib\object\object\Object;
use objectManagerLib\object\object\ObjectArray;
use objectManagerLib\object\model\Model;
use objectManagerLib\object\model\ModelArray;
use objectManagerLib\object\model\SimpleModel;
use objectManagerLib\object\model\ModelContainer;
use objectManagerLib\object\model\ModelCustom;
use objectManagerLib\object\model\Property;
use objectManagerLib\object\model\ForeignProperty;
use objectManagerLib\exception\ControllerParameterException;

abstract class Controller {
	
	protected $mMainObject;
	protected $mParams;
	private   $mInstanceObjectHash = array();
	private   $mPropertyNameStack;
	
	/**
	 * execute controller
	 * @param Oject $pObject
	 * @param array $pParams
	 * @return unknown|boolean
	 */
	public final function execute(Object $pObject, $pParams = null) {
		$this->_verifParameters($pParams);
		if (($pObject->getModel() instanceof Model) || ($pObject->getModel() instanceof ModelArray)) {
			$this->mPropertyNameStack = array();
			$this->mMainObject        = $pObject;
			$this->mParams            = $pParams;	
			$lModel                   = $pObject->getModel();
			$lModelName               = $lModel->getModelName();
			
			if ($pObject->getModel() instanceof ModelArray) {
				$lSerializations      = $lModel->getModel()->getSerializations();
				$lSerializationUnit   = $lModel->getModel()->getFirstSerialization();
			} else {
				$lSerializations      = $lModel->getSerializations();
				$lSerializationUnit   = $lModel->getFirstSerialization();
			}
			
			$lProperty = is_null($lSerializations)
							? new Property($lModel, $lModelName)
							: new ForeignProperty($lModel, $lModelName, null, $lSerializations);
			
			$lCustomModel             = new ModelCustom('modelCustom', array($lProperty));
			$lRootCustomObject        = $lCustomModel->getObjectInstance();

			$lRootCustomObject->setValue($lModelName, $pObject);
			$this->_init($pObject);
			$this->_accept($lRootCustomObject, $lModelName, $lModelName, $lSerializationUnit);
			
			return $this->_finalize($pObject);
		}
		return false;
	}
	
	private function _accept($pParentObject, $pKey, $pPropertyName, $pSerializationUnit) {
		if (!is_null($pParentObject->getValue($pKey))) {
			$this->mPropertyNameStack[] = $pPropertyName;
			$lVisitChild = $this->_visit($pParentObject, $pKey, $this->mPropertyNameStack, $pSerializationUnit);
			if ($lVisitChild) {
				$this->_acceptChildren($pParentObject->getValue($pKey), $pSerializationUnit);
			}
			$this->_postVisit($pParentObject, $pKey, $this->mPropertyNameStack, $pSerializationUnit);
			array_pop($this->mPropertyNameStack);
		}
	}
	
	private function _acceptChildren($pObject, $pSerializationUnit) {
		if (is_null($pObject)) {
			return;
		}
		if ($pObject->getModel() instanceof ModelArray && $pObject instanceof ObjectArray) {
			$lPropertyName = $pObject->getModel()->getElementName();
			foreach ($pObject->getValues() as $lKey => $lObject) {
				$this->_accept($pObject, $lKey, $lPropertyName, $pSerializationUnit);
			}
		}
		else if (!array_key_exists(spl_object_hash($pObject), $this->mInstanceObjectHash)) {
			$this->mInstanceObjectHash[spl_object_hash($pObject)] = $pObject;
			foreach ($pObject->getModel()->getProperties() as $lPropertyName => $lProperty) {
				$lModel = ($lProperty->getModel() instanceof ModelContainer) ? $lProperty->getModel()->getModel() : $lProperty->getModel();
				if (! ($lModel instanceof SimpleModel)) {
					$this->_accept($pObject, $lPropertyName, $lPropertyName, $lProperty->getFirstSerialization());
				}
			}
			unset($this->mInstanceObjectHash[spl_object_hash($pObject)]);
		}
	}
	
	private function _verifParameters($pParams) {
		$lParameters = $this->_getMandatoryParameters();
		if (is_array($lParameters)) {
			if (count($lParameters) > 0) {
				if (!is_array($pParams)) {
					throw new ControllerParameterException(implode(', ', $lParameters));
				}
				foreach ($lParameters as $lParameterName) {
					if (!array_key_exists($lParameterName, $pParams)) {
						throw new ControllerParameterException($lParameterName);
					}
				}
			}
		} else if (!is_null($lParameters)) {
			throw new ControllerParameterException(null);
		}
	}

	protected abstract function _getMandatoryParameters();

	protected abstract function _init($pObject);
	
	protected abstract function _visit($pParentObject, $pKey, $pPropertyNameStack, $pSerializationUnit);
	
	protected abstract function _postVisit($pParentObject, $pKey, $pPropertyNameStack, $pSerializationUnit);
	
	protected abstract function _finalize($pObject);
}