<?php
namespace GenLib\objectManager\Model;

class Integer extends SimpleModel {
	
	const ID = "integer";
	
	protected function _init() {
		$this->mModelName = self::ID;
	}
	
}