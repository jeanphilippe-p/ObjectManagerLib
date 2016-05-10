<?php
namespace objectManagerLib\controller;

use objectManagerLib\object\object\Object;
use objectManagerLib\object\object\ObjectArray;
use objectManagerLib\object\model\ForeignProperty;
use objectManagerLib\object\model\ModelArray;
use objectManagerLib\object\model\ModelContainer;
use objectManagerLib\object\ObjectCollection;

class ForeignObjectLoader extends Controller {

	private $mLoadCompositions      = true;
	private $mLoadedValues          = array();
	
	protected function _init($pObject) {
		if (array_key_exists(0, $this->mParams)) {
			$this->mLoadCompositions = $this->mParams[0];
		}
	}
	
	protected function _getMandatoryParameters() {
		return array();
	}
	
	protected function _visit($pParentObject, $pKey, $pPropertyNameStack) {
		$lVisitChildren = true;
		$lObject = $pParentObject->getValue($pKey);
		if (!is_null($lObject)) {
			$lIsComposition = $pParentObject->hasProperty($pKey) && $pParentObject->getProperty($pKey)->isComposition();
			if (!$lObject->isLoaded() && (!$lIsComposition || $this->mLoadCompositions)) {
				$pParentObject->loadValue($pKey);
				$this->mLoadedValues[spl_object_hash($lObject)] = null;
			}
			$lVisitChildren = !array_key_exists(spl_object_hash($lObject), $this->mLoadedValues);
		}
		return $lVisitChildren;
	}
	
	protected function _postVisit($pParentObject, $pKey, $pPropertyNameStack) {}
	
	protected function _finalize($pObject) {
		$this->mLoadedValues = array();
	}
	
}