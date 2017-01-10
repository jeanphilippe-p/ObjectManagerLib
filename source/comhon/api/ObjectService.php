<?php
namespace comhon\api;

use comhon\database\LogicalJunction;
use comhon\database\Literal;
use comhon\object\ComplexLoadRequest;
use comhon\object\singleton\InstanceModel;
use comhon\object\SimpleLoadRequest;

class ObjectService {
	
	public static function getObject($pParams) {
		try {
			if (!isset($pParams->id)) {
				throw new \Exception('request doesn\'t have id');
			}
			$lObjects = SimpleLoadRequest::buildObjectLoadRequest($pParams)->execute($pParams->id);
			return self::_setSuccessReturn($lObjects->toPublicStdObject());
		} catch (\Exception $e) {
			return self::_setErrorReturn($e);
		}
	}
	
	public static function getObjects($pParams) {
		try {
			$lObjects = ComplexLoadRequest::buildObjectLoadRequest($pParams)->execute();
			return self::_setSuccessReturn($lObjects->toPublicStdObject());
		} catch (\Exception $e) {
			return self::_setErrorReturn($e);
		}
	}
	
	private static function _setSuccessReturn($pReturnValue) {
		$lReturn = new \stdClass();
		$lReturn->success = true;
		$lReturn->result  = $pReturnValue;
		return $lReturn;
	}
	
	private static function _setErrorReturn(\Exception $pException) {
		$lReturn = new \stdClass();
		$lReturn->success        = false;
		$lReturn->error          = new \stdClass();
		$lReturn->error->message = $pException->getMessage();
		$lReturn->error->trace   = $pException->getTrace();
		return $lReturn;
	}
	
}