<?php

/*
 * This file is part of the Comhon package.
 *
 * (c) Jean-Philippe <jeanphilippe.perrotton@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Comhon\Exception\Model;

use Comhon\Model\Model;
use Comhon\Exception\ComhonException;
use Comhon\Exception\ConstantException;

class CastComhonObjectException extends ComhonException {
	
	/**
	 * @param \Comhon\Model\Model $sourceModel
	 * @param \Comhon\Model\Model $destModel
	 */
	public function __construct(Model $sourceModel, Model $destModel) {
		$message = "Cannot cast object, '{$sourceModel->getName()}' is not inherited from '{$destModel->getName()}'";
		parent::__construct($message, ConstantException::CAST_EXCEPTION);
	}
	
}