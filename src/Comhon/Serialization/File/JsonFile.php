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
use Comhon\Interfacer\StdObjectInterfacer;

class JsonFile extends SerializationFile {
	
	/**
	 *
	 * @return StdObjectInterfacer
	 */
	protected function _getInterfacer() {
		$lInterfacer = new StdObjectInterfacer();
		$lInterfacer->setSerialContext(true);
		$lInterfacer->setPrivateContext(true);
		$lInterfacer->setFlagValuesAsUpdated(false);
		return $lInterfacer;
	}
	
}