<?php

/*
 * This file is part of the Comhon package.
 *
 * (c) Jean-Philippe <jeanphilippe.perrotton@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Comhon\Model;

use Comhon\Exception\Value\UnexpectedValueTypeException;

class ModelIndex extends ModelInteger {
	
	/** @var string */
	const ID = 'index';
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Comhon\Model\SimpleModel::_initializeModelName()
	 */
	protected function _initializeModelName() {
		$this->modelName = self::ID;
	}
	
	/**
	 * verify if value is a string
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function verifValue($value) {
		parent::verifValue($value);
		if ($value < 0) {
			throw new UnexpectedValueTypeException($value, 'positive integer (including 0)');
		}
		return true;
	}
	
}