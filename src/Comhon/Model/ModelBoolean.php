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

use Comhon\Interfacer\Interfacer;
use Comhon\Interfacer\NoScalarTypedInterfacer;
use Comhon\Exception\Value\UnexpectedValueTypeException;
use Comhon\Exception\Model\CastStringException;

class ModelBoolean extends SimpleModel implements StringCastableModelInterface {
	
	/** @var string */
	const ID = 'boolean';
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Comhon\Model\SimpleModel::_initializeModelName()
	 */
	protected function _initializeModelName() {
		$this->modelName = self::ID;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Comhon\Model\SimpleModel::exportSimple()
	 * 
	 * @return boolean|null
	 */
	public function exportSimple($value, Interfacer $interfacer) {
		if (is_null($value)) {
			return $value;
		}
		if ($interfacer instanceof NoScalarTypedInterfacer) {
			return $value ? '1' : '0';
		}
		return $value;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \Comhon\Model\SimpleModel::_importScalarValue()
	 * 
	 * @return boolean|null
	 */
	protected function _importScalarValue($value, Interfacer $interfacer) {
		return $this->castValue($value);
	}
	
	/**
	 * cast value to boolean
	 * 
	 * @param string $value
	 * @param string $property if value belong to a property, permit to be more specific if an exception is thrown
	 * @return boolean
	 */
	public function castValue($value, $property = null) {
		if (is_bool($value)) {
			return $value;
		}
		if ($value !== '0' && $value !== '1') {
			throw new CastStringException($value, ['0', '1'], $property);
		}
		return (boolean) $value;
	}
	
	/**
	 * verify if value is a boolean
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function verifValue($value) {
		if (!is_bool($value)) {
			throw new UnexpectedValueTypeException($value, 'boolean');
		}
		return true;
	}
	
}