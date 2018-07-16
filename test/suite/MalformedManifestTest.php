<?php
use PHPUnit\Framework\TestCase;
use Comhon\Model\Singleton\ModelManager;
use Comhon\Exception\NotDefinedModelException;

class MalformedManifestTest extends TestCase
{
	
	public function testPropertyWithMalformedModel()
	{
		$hasThrownEx = false;
		$model = ModelManager::getInstance()->getInstanceModel('fatherMalformed');
		try {
			// model 'childMalformed' is not loaded yet and must failed when loading
			$model->getProperty('child')->getModel()->getModel();
		} catch (NotDefinedModelException $e) {
			$hasThrownEx = true;
			$this->assertEquals('model childMalformed\wrong-type doesn\'t exist', $e->getMessage());
			
			// model 'childMalformed' shouldn't be tagged as loaded and must failed again when loading
			$this->expectException(NotDefinedModelException::class);
			$childModel = $model->getProperty('child')->getModel()->getModel();
		}
		
		// should failed before 
		$this->assertTrue($hasThrownEx);
	}
	
	/**
	 * @depends testPropertyWithMalformedModel
	 */
	public function testMalformedModel()
	{
		$hasThrownEx = false;
		try {
			// model 'childMalformed' must failed when loading
			$model = ModelManager::getInstance()->getInstanceModel('childMalformed');
		} catch (NotDefinedModelException $e) {
			$hasThrownEx = true;
			$this->assertEquals('model childMalformed\wrong-type doesn\'t exist', $e->getMessage());
			
			// model 'childMalformed' shouldn't be tagged as loaded and must failed again when loading
			$this->expectException(NotDefinedModelException::class);
			$model = ModelManager::getInstance()->getInstanceModel('childMalformed');
		}
		
		// should failed before
		$this->assertTrue($hasThrownEx);
	}
	

}
