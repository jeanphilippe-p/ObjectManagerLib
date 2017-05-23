<?php

use Comhon\Model\Singleton\ModelManager;
use Comhon\Object\ComhonObject as Object;
use Comhon\Model\Model;
use Comhon\Model\MainModel;
use Comhon\Model\ModelForeign;
use Comhon\Object\ComhonDateTime;
use Comhon\Interfacer\StdObjectInterfacer;

$time_start = microtime(true);

if (!ModelManager::getInstance()->hasInstanceModel('config')) {
	throw new Exception('model not initialized');
}
if (!ModelManager::getInstance()->isModelLoaded('config')) {
	throw new Exception('model must be loaded');
}
if (ModelManager::getInstance()->hasInstanceModel('sqlTable')) {
	throw new Exception('model already initialized');
}

$lTestModel    = ModelManager::getInstance()->getInstanceModel('test');
$lTestModelTow = ModelManager::getInstance()->getInstanceModel('test');

/** ****************************** same test model instance ****************************** **/
if ($lTestModel !== $lTestModelTow) {
	throw new Exception('models haven\'t same instance');
}

/** ****************************** basic test for model 'test' ****************************** **/
if ($lTestModel->getName() !== 'test') {
	throw new Exception('model hasn\'t good name');
}
if (json_encode($lTestModel->getPropertiesNames()) !== '["name","stringValue","floatValue","booleanValue","dateValue","objectValue","objectValues","objectContainer","foreignObjectValues","enumValue","enumIntArray","enumFloatArray","objectRefParent"]') {
	throw new Exception("model {$lTestModel->getName()} hasn't good properties : ".json_encode($lTestModel->getPropertiesNames()));
}

/** ******************** test local model 'personLocal' load status ******************** **/
if (!ModelManager::getInstance()->hasInstanceModel('personLocal', 'test')) {
	throw new Exception('model not initialized');
}
if (ModelManager::getInstance()->isModelLoaded('personLocal', 'test')) {
	throw new Exception('model must be not loaded');
}
/** ******************** load model 'personLocal' by calling getmodel() ******************** **/
$lLocalPersonModel = $lTestModel->getProperty('objectContainer')->getModel()->getProperty('person')->getModel();

/** ******************** test local model 'personLocal' load status ******************** **/
if (!ModelManager::getInstance()->isModelLoaded('personLocal', 'test')) {
	throw new Exception('model must be loaded');
}
if (!$lLocalPersonModel->isLoaded()) {
	throw new Exception('model must be loaded');
}

/** ****************************** same model instance ****************************** **/
if ($lLocalPersonModel !== ModelManager::getInstance()->getInstanceModel('personLocal', 'test')) {
	throw new Exception('models haven\'t same instance');
}

/** ****************************** basic test for model 'personLocal' ****************************** **/
if ($lLocalPersonModel->getName() !== 'personLocal') {
	throw new Exception('model hasn\'t good name');
}
if (json_encode($lLocalPersonModel->getPropertiesNames()) !== '["id","firstName","lastName","birthDate","birthPlace","bestFriend","father","mother","children","homes"]') {
	throw new Exception("model {$lLocalPersonModel->getName()} hasn't good properties : ".json_encode($lLocalPersonModel->getPropertiesNames()));
}

/** ****************************** test load status of model 'place' ****************************** **/

if (!ModelManager::getInstance()->hasInstanceModel('place')) {
	throw new Exception('model \'place\' not initialized');
}
if (ModelManager::getInstance()->isModelLoaded('place')) {
	throw new Exception('model must be not loaded');
}

$lPlaceForeignModel = $lLocalPersonModel->getProperty('birthPlace')->getModel();

if (!($lPlaceForeignModel instanceof ModelForeign)) {
	throw new Exception('model of property \'birthPlace\' is not a foreign model');
}
$lPlaceModel = $lPlaceForeignModel->getModel();
if (!($lPlaceModel instanceof MainModel)) {
	throw new Exception('foreign model of property \'birthPlace\' is not a main model');
}


if (!ModelManager::getInstance()->hasInstanceModel('place')) {
	throw new Exception('model \'place\' not initialized');
}
if (!ModelManager::getInstance()->isModelLoaded('place')) {
	throw new Exception('model must be loaded');
}

$lPlaceModelTow = ModelManager::getInstance()->getInstanceModel('place');

/** ****************************** same place model instance ****************************** **/
if ($lPlaceModel !== $lPlaceModelTow) {
	throw new Exception('models haven\'t same instance');
}


/** ****************************** basic test for model 'testDb' ****************************** **/

/*
 if (ModelManager::getInstance()->hasInstanceModel('sqlDatabase')) {
throw new Exception("model must be not initialized");
}
if (ModelManager::getInstance()->isModelLoaded('sqlDatabase')) {
throw new Exception("model must be not loaded");
}
*/

$lTestDbModel = ModelManager::getInstance()->getInstanceModel('testDb');

if ($lTestDbModel->getName() !== 'testDb') {
	throw new Exception('model hasn\'t good name');
}
if (json_encode($lTestDbModel->getPropertiesNames()) !== '["id1","id2","date","timestamp","object","objectWithId","string","integer","mainParentTestDb","objectsWithId","foreignObjects","lonelyForeignObject","lonelyForeignObjectTwo","defaultValue","manBodyJson","womanXml","notSerializedValue","notSerializedForeignObject","boolean","boolean2","childrenTestDb"]') {
	throw new Exception("model {$lTestDbModel->getName()} hasn't good properties : ".json_encode($lTestDbModel->getPropertiesNames()));
}
$lDbModel = $lTestDbModel->getSerialization()->getSettings()->getProperty('database')->getModel();
if ($lDbModel->getName() !== 'sqlDatabase') {
	throw new Exception('model hasn\'t good name');
}
if ($lTestDbModel->getProperty('integer')->isPrivate()) {
	throw new Exception('is private');
}
if (!$lTestDbModel->getProperty('string')->isPrivate()) {
	throw new Exception('is not private');
}
if (!$lTestDbModel->getProperty('string')->isPrivate()) {
	throw new Exception('is not private');
}
$lLocalModel = ModelManager::getInstance()->getInstanceModel('objectWithIdAndMoreMore', 'testDb');
if (!$lLocalModel->getProperty('plop3')->isPrivate()) {
	throw new Exception('is not private');
}

if (!$lTestDbModel->getProperty('timestamp')->isSerializable()) {
	throw new Exception('is not serializable');
}
if ($lTestDbModel->getProperty('notSerializedValue')->isSerializable()) {
	throw new Exception('is serializable');
}
if ($lTestDbModel->getProperty('notSerializedForeignObject')->isSerializable()) {
	throw new Exception('is serializable');
}

/** ****************************** test serialization before load ****************************** **/

$lStdPrivateInterfacer = new StdObjectInterfacer();
$lStdPrivateInterfacer->setPrivateContext(true);

if (json_encode($lTestDbModel->getSerialization()->getSettings()->export($lStdPrivateInterfacer)) !== '{"name":"test","database":"1"}') {
	throw new Exception("model {$lTestDbModel->getName()} hasn't good values");
}

if (json_encode($lTestDbModel->getSerialization()->getSettings()->getValue('database')->export($lStdPrivateInterfacer)) !== '{"id":"1"}') {
	throw new Exception("model {$lTestDbModel->getName()} hasn't good values : ".json_encode($lTestDbModel->getSerialization()->getSettings()->getValue('database')->export($lStdPrivateInterfacer)));
}
if ($lTestDbModel->getSerialization()->getSettings()->getValue('database')->isLoaded()) {
	throw new Exception('object must be not loaded');
}

// LOAD VALUE
$lTestDbModel->getSerialization()->getSettings()->loadValue('database');

/** ****************************** test serialization after load ****************************** **/
if (json_encode($lTestDbModel->getSerialization()->getSettings()->export($lStdPrivateInterfacer)) !== '{"name":"test","database":"1"}') {
	throw new Exception("model {$lTestDbModel->getName()} hasn't good values");
}
$lStdPublicInterfacer = new StdObjectInterfacer();
$lObjDb = $lTestDbModel->getSerialization()->getSettings()->getValue('database')->export($lStdPublicInterfacer);
if (!compareJson(json_encode($lObjDb), '{"id":"1","DBMS":"mysql","host":"localhost","name":"database","user":"root"}')) {
	throw new Exception("model {$lTestDbModel->getName()} hasn't good values");
}
if (!$lTestDbModel->getSerialization()->getSettings()->getValue('database')->isLoaded()) {
	throw new Exception('object must be loaded');
}

/** ****************************** test load status of model 'sqlDatabase' ****************************** **/
if (!ModelManager::getInstance()->hasInstanceModel('sqlDatabase')) {
	throw new Exception('model \'sqlDatabase\' not initialized');
}
if (!ModelManager::getInstance()->isModelLoaded('sqlDatabase')) {
	throw new Exception('model must be loaded');
}

/** ****************************** same serialization object and model instance ****************************** **/
if ($lPlaceModel->getSerialization()->getSettings()->getValue('database') !== $lTestDbModel->getSerialization()->getSettings()->getValue('database')) {
	throw new Exception('models haven\'t same serialization');
}

if ($lPlaceModel->getSerialization()->getSettings()->getModel() !== $lTestDbModel->getSerialization()->getSettings()->getModel()) {
	throw new Exception('models haven\'t same instance');
}

if (ModelManager::getInstance()->getInstanceModel('sqlDatabase') !== $lTestDbModel->getSerialization()->getSettings()->getValue('database')->getModel()) {
	throw new Exception('models haven\'t same instance');
}

if (ModelManager::getInstance()->getInstanceModel('sqlTable') !== $lTestDbModel->getSerialization()->getSettings()->getModel()) {
	throw new Exception('models haven\'t same instance');
}

$lObj        = $lTestModel->getObjectInstance();
$lModelArray = $lObj->getProperty('objectValues')->getModel();
$lObjArray   = $lModelArray->getObjectInstance();
$lObjValue   = $lObj->getproperty('objectValue')->getModel()->getObjectInstance();

$lObj->setId('sddsdfffff');
$lObj->setValue('objectValue', $lObjValue);
$lObj->setValue('objectValues', $lObjArray);
$lObj->setValue('foreignObjectValues', $lObjArray);

if (!ModelManager::getInstance()->hasInstanceModel('sqlTable')) {
	throw new Exception('model already initialized');
}

/** **************** test Comhon DateTime ****************** **/

$lDateTime = new ComhonDateTime('now');
if ($lDateTime->isUpdated()) {
	throw new Exception('should not be updated');
}

$lDateTime->add(new DateInterval('P0Y0M0DT5H0M0S'));
if (!$lDateTime->isUpdated()) {
	throw new Exception('should be updated');
}
$lDateTime->resetUpdatedStatus();
if ($lDateTime->isUpdated()) {
	throw new Exception('should not be updated');
}

$lDateTime->modify('+1 day');
if (!$lDateTime->isUpdated()) {
	throw new Exception('should be updated');
}
$lDateTime->resetUpdatedStatus();
if ($lDateTime->isUpdated()) {
	throw new Exception('should not be updated');
}

$lDateTime->setDate(2001, 2, 3);
if (!$lDateTime->isUpdated()) {
	throw new Exception('should be updated');
}
$lDateTime->resetUpdatedStatus();
if ($lDateTime->isUpdated()) {
	throw new Exception('should not be updated');
}

$lDateTime->setISODate(2008, 2);
if (!$lDateTime->isUpdated()) {
	throw new Exception('should be updated');
}
$lDateTime->resetUpdatedStatus();
if ($lDateTime->isUpdated()) {
	throw new Exception('should not be updated');
}

$lDateTime->setTime(14, 55);
if (!$lDateTime->isUpdated()) {
	throw new Exception('should be updated');
}
$lDateTime->resetUpdatedStatus();
if ($lDateTime->isUpdated()) {
	throw new Exception('should not be updated');
}

$lDateTime->setTimestamp(1171502725);
if (!$lDateTime->isUpdated()) {
	throw new Exception('should be updated');
}
$lDateTime->resetUpdatedStatus();
if ($lDateTime->isUpdated()) {
	throw new Exception('should not be updated');
}

$lDateTime->sub(new DateInterval('P10D'));
if (!$lDateTime->isUpdated()) {
	throw new Exception('should be updated');
}
$lDateTime->resetUpdatedStatus();
if ($lDateTime->isUpdated()) {
	throw new Exception('should not be updated');
}

$time_end = microtime(true);
var_dump('model test exec time '.($time_end - $time_start));

