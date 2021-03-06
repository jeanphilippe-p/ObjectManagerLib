<?php

use PHPUnit\Framework\TestCase;
use Comhon\Model\Singleton\ModelManager;
use Test\Comhon\Data;
use Comhon\Object\Config\Config;
use Comhon\Interfacer\Interfacer;
use Comhon\Exception\Serialization\ManifestSerializationException;

class ManifestManifestTest extends TestCase
{
	public static function setUpBeforeClass()
	{
		Config::setLoadPath(Data::$config);
		date_default_timezone_set('Europe/Berlin');
		
	}
	
	public static function tearDownAfterClass()
	{
		Config::setLoadPath(Data::$config);
		date_default_timezone_set('UTC');
	}
	
	/**
	 * @dataProvider manifestObjectLoadData
	 */
	public function testManifestObjectLoad($prefix, $suffix)
	{
		$manifestPath = ModelManager::getInstance()->getManifestPath($prefix, $suffix);
		$this->assertFileExists($manifestPath);
		$manifestFileContent = file_get_contents($manifestPath);
		$this->assertNotFalse($manifestFileContent);
		
		$modelName = $prefix.'\\'.$suffix;
		$model = ModelManager::getInstance()->getInstanceModel('Comhon\Manifest');
		$manifest = $model->loadObject($modelName);
		$this->assertNotNull($manifest);
		
		$interfacer = Interfacer::getInstance(Config::getInstance()->getManifestFormat());
		$interfacer->setSerialContext(true);
		$interfacer->setPrivateContext(true);
		$interfacer->setFlagValuesAsUpdated(false);
		$interfacer->setMergeType(Interfacer::OVERWRITE);
		
		$this->assertEquals($manifestFileContent, $interfacer->toString($interfacer->export($manifest), true));
	}
	
	public function manifestObjectLoadData($param) {
		return [
			['Test', 'Test'],
			['Test', 'TestDb'], // is_main ...
			['Test', 'TestXml'], // xml node
			['Test', 'Person'], // auto incremental
			['Test', 'Person\Woman'], // extends, object_class
			['Test', 'Extends\ShareId\GrandParent\ParentThree\Child'], // shared_id
			['Comhon', 'Model'], // is_abstract, share_parent_id, is_model_name
			['Test', 'TestRestricted'],
			['Test', 'Validate'], // depends, conflicts
			['Test', 'TestAssociativeArray'], // associative array
		];
	}
	
	public function testManifestObjectSave()
	{
		$modelName = 'Test\Test';
		$model = ModelManager::getInstance()->getInstanceModel('Comhon\Manifest');
		$manifest = $model->loadObject($modelName);
		$manifest->setId('Test\TestCopy');
		
		$this->assertEquals(1, $manifest->save());
	}
	
	/**
	 * @depends testManifestObjectSave
	 */
	public function testManifestObjectDelete()
	{
		$modelNameOrigin = 'Test\Test';
		$modelNameCopied = 'Test\TestCopy';
		$model = ModelManager::getInstance()->getInstanceModel('Comhon\Manifest');
		
		$interfacer = Interfacer::getInstance(Config::getInstance()->getManifestFormat());
		$interfacer->setSerialContext(true);
		$interfacer->setPrivateContext(true);
		$interfacer->setFlagValuesAsUpdated(false);
		$interfacer->setMergeType(Interfacer::OVERWRITE);
		
		$manifestOrigin = $model->loadObject($modelNameOrigin);
		$manifestCopied = $model->loadObject($modelNameCopied);
		$this->assertNotNull($manifestOrigin);
		$this->assertNotNull($manifestCopied);
		
		// set same random id to compare values
		$manifestOrigin->setId('my_id');
		$manifestCopied->setId('my_id');
		
		$this->assertEquals(
			$interfacer->toString($interfacer->export($manifestOrigin), true),
			$interfacer->toString($interfacer->export($manifestCopied), true)
		);
		
		// reset original ids;
		$manifestOrigin->setId('Test\Test');
		$manifestCopied->setId('Test\TestCopy');
		
		$this->assertEquals(1, $manifestCopied->delete());
	}
	
	public function testManifestObjectSaveWrongVersion()
	{
		$model = ModelManager::getInstance()->getInstanceModel('Comhon\Manifest');
		$manifest = $model->getObjectInstance();
		$manifest->setId('Test\WrongVersion');
		$manifest->setValue('version', '2.0');
		
		$this->expectException(ManifestSerializationException::class);
		$this->expectExceptionCode(804);
		$this->expectExceptionMessage('only manifest with version 3.0 may be saved');
		$manifest->save();
	}
	
	/**
	 *
	 * @dataProvider manifestNotDefinedParentData
	 */
	public function testManifestObjectSaveNotDefinedParent($extendsValue, $message)
	{
		$model = ModelManager::getInstance()->getInstanceModel('Comhon\Manifest');
		$manifest = $model->getObjectInstance();
		$manifest->setId('Test\New');
		$manifest->setValue('version', '3.0');
		$extends = $manifest->initValue('extends');
		$extends->pushValue($extendsValue);
		
		$this->expectException(ManifestSerializationException::class);
		$this->expectExceptionCode(804);
		$this->expectExceptionMessage($message);
		$manifest->save();
	}
	
	public function manifestNotDefinedParentData()
	{
		return [
			[
				'Test\NotDefinedParent',
				"invalid extends value 'Test\NotDefinedParent'. model 'Test\New\Test\NotDefinedParent' is not defined"
			],
			[
				'\Test\NotDefinedParent',
				"invalid extends value '\Test\NotDefinedParent'. model 'Test\NotDefinedParent' is not defined"
			]
		];
	}
}
