<?php

use PHPUnit\Framework\TestCase;
use Comhon\Model\Singleton\ModelManager;
use Comhon\Interfacer\StdObjectInterfacer;
use Comhon\Exception\Interfacer\ImportException;
use Comhon\Exception\Value\UnexpectedValueTypeException;
use Test\Comhon\Data;
use Comhon\Object\Config\Config;
use Comhon\Exception\Interfacer\DuplicatedIdException;
use Comhon\Exception\ConstantException;
use Comhon\Interfacer\Interfacer;
use Comhon\Object\Collection\MainObjectCollection;
use Comhon\Exception\Interfacer\NotReferencedValueException;

class ImportExportTest extends TestCase
{
	public static function setUpBeforeClass()
	{
		Config::setLoadPath(Data::$config);
	}
	
	public function setUp()
	{
		MainObjectCollection::getInstance()->reset();
	}
	
	public function testNoMergeFillObject()
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Duplicated');
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		
		$jsonValue = '{
			"id":1,
			"containerOne":{
				"id":1,
				"objOneProp":{"id":3}
			}
		}';
		
		$stdInterfacer->setMergeType(Interfacer::NO_MERGE);
		$test->fill(json_decode($jsonValue), $stdInterfacer);
		
		// during fillObject Interfacer::NO_MERGE is not persistent
		// so it is implicitely transformed at the beginning to Interfacer::OVERWRITE and reset to Interfacer::NO_MERGE at the end
		// we verify that at the end we still have Interfacer::NO_MERGE
		$this->assertEquals(Interfacer::NO_MERGE, $stdInterfacer->getMergeType());
		
		$this->assertEquals(1, $test->getId());
		$this->assertTrue($test->hasValue('containerOne'));
		$this->assertTrue($test->getValue('containerOne')->hasValue('objOneProp'));
		$this->assertEquals(3, $test->getValue('containerOne')->getValue('objOneProp')->getId());
		
		$jsonValue = '{
			"id":1
		}';
		
		$test->fill(json_decode($jsonValue), $stdInterfacer);
		$this->assertEquals(1, $test->getId());
		$this->assertFalse($test->hasValue('containerOne'));
	}
	
	/**
	 * test a fill function (with interfacer merge type set to Interfacer::MERGE by default).
	 * 
	 * @dataProvider importData
	 */
	public function testSimpleFillObject($baseJson)
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Duplicated');
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		$this->assertEquals(Interfacer::MERGE, $stdInterfacer->getMergeType());
		
		$test->fill(json_decode($baseJson), $stdInterfacer);
		$this->assertSame($test->getValue('containerOne')->getValue('objOneProp'), $test->getValue('dupliForeignProp'));
		$this->assertSame($test->getValue('containerOne')->getValue('objMainProp'), $test->getValue('containerTwo')->getValue('objMainForeignProp'));
		$this->assertSame($test, $test->getValue('containerTwo')->getValue('objOneForeignProp')->getValue(0));
		$this->assertNotSame($test->getValue('containerOne')->getValue('objTwoProp'), $test->getValue('containerOne')->getValue('objOneProp'));
		$this->assertTrue($test->isLoaded());
		$this->assertTrue($test->getValue('containerOne')->getValue('objOneProp')->isLoaded());
		$this->assertTrue($test->getValue('containerOne')->getValue('objTwoProp')->isLoaded());
		$this->assertTrue($test->getValue('containerOne')->getValue('dupliProp')->isLoaded());
		$this->assertTrue($test->getValue('containerOne')->getValue('objMainProp')->isLoaded());
	}
	
	/**
	 * test a fill function called twice with same interfaced object (with interfacer merge type set to Interfacer::MERGE)
	 *
	 * @dataProvider importData
	 */
	public function testFillObjectAgain($baseJson)
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Duplicated');
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		
		// first fill
		$test->fill(json_decode($baseJson), $stdInterfacer);
		
		$test->fill(json_decode($baseJson), $stdInterfacer);
		$this->assertSame($test->getValue('containerOne')->getValue('objOneProp'), $test->getValue('dupliForeignProp'));
		$this->assertNotSame($test->getValue('containerOne')->getValue('objTwoProp'), $test->getValue('containerOne')->getValue('objOneProp'));
		$this->assertSame($test, $test->getValue('containerTwo')->getValue('objOneForeignProp')->getValue(0));
		$this->assertTrue($test->isLoaded());
		$this->assertTrue($test->getValue('containerOne')->getValue('objOneProp')->isLoaded());
		$this->assertTrue($test->getValue('containerOne')->getValue('objTwoProp')->isLoaded());
		$this->assertTrue($test->getValue('containerOne')->getValue('dupliProp')->isLoaded());
		
	}
	
	/**
	 * test a fill function called twice (with interfacer merge type set to Interfacer::MERGE).
	 * second fill call import interfaced object without some values
	 *
	 * @dataProvider importData
	 */
	public function testFillObjectAgainPartial($baseJson, $partialJson)
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Duplicated');
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		
		// first fill
		$test->fill(json_decode($baseJson), $stdInterfacer);
		
		$test->fill(json_decode($partialJson), $stdInterfacer);
		$this->assertTrue($test->getValue('containerOne')->hasValue('objOneProp'));
		$this->assertSame($test->getValue('containerOne')->getValue('objOneProp'), $test->getValue('dupliForeignProp'));
		$this->assertTrue($test->getValue('containerOne')->hasValue('objMainProp'));
		$this->assertSame($test->getValue('containerOne')->getValue('objMainProp'), $test->getValue('containerTwo')->getValue('objMainForeignProp'));
		$this->assertTrue($test->getValue('dupliForeignProp')->isLoaded());
	}
	
	/**
	 * test a fill function called twice (with interfacer merge type set to Interfacer::MERGE).
	 * second fill call import interfaced object without some values
	 *
	 * @dataProvider importData
	 */
	public function testFillObjectAgainPartialOverwrite($baseJson, $partialJson)
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Duplicated');
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		
		// first fill
		$test->fill(json_decode($baseJson), $stdInterfacer);
		
		$stdInterfacer->setMergeType(Interfacer::OVERWRITE);
		$stdInterfacer->setVerifyReferences(false); // some foreign values are not referenced so we modify setting
		$test->fill(json_decode($partialJson), $stdInterfacer);
		$this->assertFalse($test->getValue('dupliForeignProp')->isLoaded());
		$this->assertNull($test->getValue('containerOne')->getValue('objMainProp'));
		$this->assertTrue($test->getValue('containerTwo')->getValue('objMainForeignProp')->isLoaded());
		$this->assertNull($test->getValue('containerOne')->getValue('objOneProp'));
		$this->assertTrue($test->getValue('containerTwo')->getValue('objOneForeignProp')->getValue(0)->isLoaded());
		$this->assertSame($test, $test->getValue('containerTwo')->getValue('objOneForeignProp')->getValue(0));
	}
	
	/**
	 * test import after fill with objects that have main models
	 *
	 * @dataProvider importData
	 */
	public function testImportAfterFillObject($baseJson, $partialJson)
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Duplicated');
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		
		// first fill
		$test->fill(json_decode($baseJson), $stdInterfacer);
		
		$stdInterfacer->setVerifyReferences(false); // some foreign values are not referenced so we modify setting
		$test = $model->import(json_decode($partialJson), $stdInterfacer);
		$this->assertFalse($test->getValue('dupliForeignProp')->isLoaded());
		$this->assertNull($test->getValue('containerOne')->getValue('objMainProp'));
		$this->assertTrue($test->getValue('containerTwo')->getValue('objMainForeignProp')->isLoaded());
	}
	
	/**
	 * test a fill function called twice (with interfacer merge type set to Interfacer::OVERWRITE).
	 * second fill has not referenced foreign values so  : 
	 * - exception must be thrown for values with NOT main model.
	 * - exception must NOT be thrown for values with main model.
	 * (values are present in first imported object and are not present in new imported object)
	 *
	 * @dataProvider importData
	 */
	public function testNotReferencedDuringFill($baseJson, $partialJson)
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Duplicated');
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		
		// first fill
		$test->fill(json_decode($baseJson), $stdInterfacer);
		
		$interfacedObj = json_decode($partialJson);
		$stdInterfacer->setMergeType(Interfacer::OVERWRITE);
		try {
			$hasThrownEx = false;
			$test->fill($interfacedObj, $stdInterfacer);
		} catch (ImportException $e) {
			$hasThrownEx = true;
			$this->asserttrue($e->getOriginalException() instanceof NotReferencedValueException);
			$this->assertEquals($e->getCode(), ConstantException::NOT_REFERENCED_VALUE_EXCEPTION);
		}
		// should failed before
		$this->assertTrue($hasThrownEx);
		
		unset($interfacedObj->dupliForeignProp);
		// after unset 'dupliForeignProp', only foreign value 'objMainForeignProp' is not referenced
		// and it has a main model so import must NOT throw exception
		// because values with main model doesn't need to be referenced in current object
		$test->fill($interfacedObj, $stdInterfacer);
	}
	
	/**
	 * test import (after fill) that has not referenced foreign values so : 
	 * - exception must be thrown for values with NOT main model.
	 * - exception must NOT be thrown for values with main model.
	 * (values are present in first imported object and are not present in new imported object)
	 *
	 * @dataProvider importData
	 */
	public function testNotReferencedDuringImport($baseJson, $partialJson)
	{
		$model = ModelManager::getInstance()->getInstanceModel('Test\Duplicated');
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		
		// first fill
		$test->fill(json_decode($baseJson), $stdInterfacer);
		
		$interfacedObj = json_decode($partialJson);
		try {
			$hasThrownEx = false;
			$test = $model->import($interfacedObj, $stdInterfacer);
		} catch (ImportException $e) {
			$hasThrownEx = true;
			$this->asserttrue($e->getOriginalException() instanceof NotReferencedValueException);
			$this->assertEquals($e->getCode(), ConstantException::NOT_REFERENCED_VALUE_EXCEPTION);
		}
		// should failed before
		$this->assertTrue($hasThrownEx);
		
		unset($interfacedObj->dupliForeignProp);
		// after unset 'dupliForeignProp', only foreign value 'objMainForeignProp' is not referenced 
		// and it has a main model so import must NOT throw exception
		// because values with main model doesn't need to be referenced in current object
		$test = $model->import($interfacedObj, $stdInterfacer);
	}
	
	public function importData()
	{
		return [
			[
				'{
					"id":1,
					"dupliForeignProp":3,
					"containerOne":{
						"id":1,
						"dupliProp":{"id":2},
						"objOneProp":{"id":3},
						"objTwoProp":{"id":3},
						"objMainProp":{"id":4}
					},
					"containerTwo":{
						"id":1,
						"objOneForeignProp":[1],
						"objMainForeignProp":4
					}
				}',
				'{
					"id":1,
					"dupliForeignProp":3,
					"containerOne":{
						"id":1,
						"dupliProp":{"id":2},
						"objTwoProp":{"id":3}
					},
					"containerTwo":{
						"id":1,
						"objOneForeignProp":[1],
						"objMainForeignProp":4
					}
				}'
			]
		];
	}
	
	/**
	 * @dataProvider thrownExceptionImportData
	 */
	public function testThrownExceptionImport($modelName, $jsonValue, $stringProperties, $exClass, $exCode, $exMessage)
	{
		$model = ModelManager::getInstance()->getInstanceModel($modelName);
		$test = $model->getObjectInstance();
		$stdInterfacer = new StdObjectInterfacer();
		
		try {
			$test->fill(json_decode($jsonValue), $stdInterfacer);
		} catch (ImportException $e) {
			$this->assertEquals($e->getStringifiedProperties(), $stringProperties);
			$this->assertEquals(get_class($e->getOriginalException()), $exClass);
			$this->assertEquals($e->getOriginalException()->getCode(), $exCode);
			$this->assertEquals($e->getOriginalException()->getMessage(), $exMessage);
		}
	}
	
	public function thrownExceptionImportData()
	{
		return [
			[
				'Test\Test',
				'{"objectContainer":{"person":{"recursiveLocal":{"firstName":true}}}}',
				'.objectContainer.person.recursiveLocal.firstName',
				UnexpectedValueTypeException::class,
				ConstantException::UNEXPECTED_VALUE_TYPE_EXCEPTION,
				'value must be a string, boolean \'true\' given',
			],
			[
				'Test\Duplicated',
				'{"id":1,"containerOne":{"dupliProp":{"id":1}}}',
				'.containerOne.dupliProp',
				DuplicatedIdException::class,
				ConstantException::DUPLICATED_ID_EXCEPTION,
				'Duplicated id \'1\'',
			],
			[
				'Test\Duplicated',
				'{"id":2,"containerOne":{"objOneProp":{"id":2}}}',
				'.containerOne.objOneProp',
				DuplicatedIdException::class,
				ConstantException::DUPLICATED_ID_EXCEPTION,
				'Duplicated id \'2\'',
			],
			[
				'Test\Duplicated',
				'{"id":3,"containerOne":{"dupliProp":{"id":4},"objOneProp":{"id":4}}}',
				'.containerOne.objOneProp',
				DuplicatedIdException::class,
				ConstantException::DUPLICATED_ID_EXCEPTION,
				'Duplicated id \'4\'',
			],
			[
				'Test\Duplicated',
				'{"id":5,"containerTwo":{"objOneForeignProp":[6]}}',
				'.containerTwo.objOneForeignProp.0',
				NotReferencedValueException::class,
				ConstantException::NOT_REFERENCED_VALUE_EXCEPTION,
				'foreign value with model \'Test\Duplicated\ObjectOne\' and id \'6\' not referenced in  interfaced object',
			]
		];
	}

}
