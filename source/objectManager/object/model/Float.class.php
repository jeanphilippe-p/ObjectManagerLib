<?php
namespace ObjectManagerLib\objectManager\Model;

class Float extends SimpleModel {
	
	const ID = "float";
	
	protected function _init() {
		$this->mModelName = self::ID;
	}
	
}