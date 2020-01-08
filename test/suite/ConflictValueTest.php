<?php

use PHPUnit\Framework\TestCase;
use Comhon\Model\Singleton\ModelManager;
use Test\Comhon\Data;
use Comhon\Object\Config\Config;
use Comhon\Interfacer\AssocArrayInterfacer;
use Comhon\Exception\Interfacer\ImportException;
use Comhon\Exception\ConstantException;
use Comhon\Exception\Interfacer\ExportException;
use Comhon\Exception\Object\ConflictValuesException;

class ConflictValueTest extends TestCase
{
	public static function setUpBeforeClass()
	{
		Config::setLoadPath(Data::$config);
	}
	
	public function testConflictProperty()
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Validate');
		$this->assertEquals(
			['baseValue', 'value'],
			$model->getProperty('conflict')->getConflicts()
		);
		$this->assertEquals(
			['value'],
			$model->getProperty('dependsConflict')->getConflicts()
		);
		$this->assertEquals(
			[],
			$model->getProperty('conflict')->getDependencies()
		);
	}
	
	public function testConflictSet()
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Validate');
		$obj = $model->getObjectInstance(false);
		$obj->setValue('valueRequired', 'my_value');
		$obj->setIsLoaded(true);
		$obj->setValue('conflict', 'my_value');
		$obj->unsetValue('conflict');
		
		$obj->setValue('value', 'my_value');
		$obj->setValue('baseValue', 'my_value');
		
		$this->expectException(ConflictValuesException::class);
		$this->expectExceptionMessage("properties values [\"conflict\",\"baseValue\"] cannot coexist for model 'Test\Validate'");
		$obj->setValue('conflict', 'my_value');
	}
	
	public function testValidateConflict()
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Validate');
		$obj = $model->getObjectInstance(false);
		$obj->setValue('valueRequired', 'my_value');
		$obj->setValue('conflict', 'my_value');
		$obj->validate();
		
		$obj->setValue('baseValue', 'my_value');
		
		$this->expectException(ConflictValuesException::class);
		$this->expectExceptionMessage('properties values ["conflict","baseValue"] cannot coexist for model \'Test\Validate\'');
		$obj->validate();
	}
	
	public function testImportSuccessful()
	{
		$interfacer = new AssocArrayInterfacer();
		
		// root object NOT flaged as loaded so root object is not validated
		$interfacer->setFlagObjectAsLoaded(false);
		
		$model = ModelManager::getInstance()->getInstanceModel('Test\Validate');
		$obj = $model->import(['valueRequired' => 'aaaa', 'value' => 'my_value', 'conflict' => 'my_value'], $interfacer);
		$this->assertFalse($obj->isLoaded());
		$this->assertEquals('my_value', $obj->getValue('value'));
		$this->assertEquals('my_value', $obj->getValue('conflict'));
		
		// root object flaged as loaded so root object is validated
		$interfacer->setFlagObjectAsLoaded(true);
		
		$model = ModelManager::getInstance()->getInstanceModel('Test\Validate');
		$obj = $model->import(['valueRequired' => 'aaaa', 'conflict' => 'my_value'], $interfacer);
		$this->assertTrue($obj->isLoaded());
		$this->assertEquals('my_value', $obj->getValue('conflict'));
	}
	
	public function testImportFailureRoot()
	{
		$interfacer = new AssocArrayInterfacer();
		$interfacer->setFlagObjectAsLoaded(true);
		$model = ModelManager::getInstance()->getInstanceModel('Test\Validate');
		
		$thrown = false;
		try {
			$obj = $model->import(['valueRequired' => 'aaaa', 'value' => 'my_value', 'conflict' => 'my_value'], $interfacer);
		} catch (ImportException $e) {
			$thrown = true;
			$this->assertEquals($e->getStringifiedProperties(), '.');
			$this->assertEquals(get_class($e->getOriginalException()), ConflictValuesException::class);
			$this->assertEquals($e->getOriginalException()->getCode(), ConstantException::CONFLICT_VALUES_EXCEPTION);
			$this->assertEquals($e->getOriginalException()->getMessage(), "properties values [\"conflict\",\"value\"] cannot coexist for model 'Test\Validate'");
		}
		$this->assertTrue($thrown);
	}
	
	public function testExportSuccessful() {
		
		$interfacer = new AssocArrayInterfacer();
		$interfacer->setFlagObjectAsLoaded(false);
		$model = ModelManager::getInstance()->getInstanceModel('Test\Validate');
		$obj = $model->import(['valueRequired' => 'aaaa', 'conflict' => 'my_value'], $interfacer);
		
		$this->assertEquals(
			'{"valueRequired":"aaaa","conflict":"my_value"}',
			$interfacer->toString($obj->export($interfacer))
		);
	}
	
	public function testExportFailure() {
		
		$interfacer = new AssocArrayInterfacer();
		$interfacer->setFlagObjectAsLoaded(false);
		$model = ModelManager::getInstance()->getInstanceModel('Test\Validate');
		$obj = $model->getObjectInstance(false);
		$obj->setValue('valueRequired', 'aaaa');
		$obj->setValue('value', 'aaaa');
		$obj->setValue('conflict', 'aaaa');
		
		$thrown = false;
		try {
			$obj->export($interfacer);
		} catch (ExportException $e) {
			$thrown = true;
			$this->assertEquals($e->getStringifiedProperties(), '.');
			$this->assertEquals(get_class($e->getOriginalException()), ConflictValuesException::class);
			$this->assertEquals($e->getOriginalException()->getCode(), ConstantException::CONFLICT_VALUES_EXCEPTION);
			$this->assertEquals($e->getOriginalException()->getMessage(), "properties values [\"conflict\",\"value\"] cannot coexist for model 'Test\Validate'");
		}
		$this->assertTrue($thrown);
	}
	
}
