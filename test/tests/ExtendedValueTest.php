<?php

use Comhon\Model\Singleton\ModelManager;
use Comhon\Object\Collection\MainObjectCollection;
use Test\Comhon\Object\Person;
use Test\Comhon\Object\Man;
use Test\Comhon\Object\Woman;
use Comhon\Object\ComhonDateTime;
use Comhon\Interfacer\StdObjectInterfacer;
use Comhon\Interfacer\XMLInterfacer;
use Comhon\Interfacer\AssocArrayInterfacer;
use Comhon\Interfacer\Interfacer;

$time_start = microtime(true);

$stdPrivateInterfacer = new StdObjectInterfacer();
$stdPrivateInterfacer->setPrivateContext(true);

$stdPublicInterfacer = new StdObjectInterfacer();
$stdPublicInterfacer->setPrivateContext(false);

$stdSerialInterfacer = new StdObjectInterfacer();
$stdSerialInterfacer->setPrivateContext(true);
$stdSerialInterfacer->setSerialContext(true);

$xmlPrivateInterfacer = new XMLInterfacer();
$xmlPrivateInterfacer->setPrivateContext(true);

$xmlPublicInterfacer= new XMLInterfacer();
$xmlPublicInterfacer->setPrivateContext(false);

$xmlSerialInterfacer = new XMLInterfacer();
$xmlSerialInterfacer->setPrivateContext(true);
$xmlSerialInterfacer->setSerialContext(true);

$flattenArrayPrivateInterfacer = new AssocArrayInterfacer();
$flattenArrayPrivateInterfacer->setPrivateContext(true);
$flattenArrayPrivateInterfacer->setFlattenValues(true);

$flattenArrayPublicInterfacer = new AssocArrayInterfacer();
$flattenArrayPublicInterfacer->setPrivateContext(false);
$flattenArrayPublicInterfacer->setFlattenValues(true);

$flattenArraySerialInterfacer = new AssocArrayInterfacer();
$flattenArraySerialInterfacer->setPrivateContext(true);
$flattenArraySerialInterfacer->setFlattenValues(true);
$flattenArraySerialInterfacer->setSerialContext(true);

$womanModel  = ModelManager::getInstance()->getInstanceModel('Test\Person\Woman');
$manModel    = ModelManager::getInstance()->getInstanceModel('Test\Person\Man');
$personModel = ModelManager::getInstance()->getInstanceModel('Test\Person');

$woman  = MainObjectCollection::getInstance()->getObject(2, 'Test\Person\Woman');
$person = MainObjectCollection::getInstance()->getObject(2, 'Test\Person');

if (!is_null($person) || !is_null($woman)) {
	throw new \Exception('object already initialized');
}
if ($personModel->getSerializationSettings() !== $womanModel->getSerializationSettings()) {
	throw new \Exception('not same serialization instance');
}
if ($personModel->getSerializationSettings() !== $manModel->getSerializationSettings()) {
	throw new \Exception('not same serialization instance');
}
if ($personModel->getSerialization()->getInheritanceKey() != 'sex') {
	throw new \Exception('bad inheritance key');
}

$person = $personModel->getObjectInstance(false);
$person->setId(2);
$woman = $personModel->loadObject(2);

if ($woman->getModel() !== $womanModel) {
	throw new \Exception('not good model');
}
if ($woman !== $person) {
	throw new \Exception('not same instance object 0');
}
if ($woman !== MainObjectCollection::getInstance()->getObject(2, 'Test\Person\Woman')) {
	throw new \Exception('object not in objectcollection');
}
if ($woman !== MainObjectCollection::getInstance()->getObject(2, 'Test\Person')) {
	throw new \Exception('object not in objectcollection');
}

MainObjectCollection::getInstance()->removeObject($woman);

$woman2  = MainObjectCollection::getInstance()->getObject(2, 'Test\Person\Woman');
$person2 = MainObjectCollection::getInstance()->getObject(2, 'Test\Person');

if (!is_null($person2) || !is_null($woman2)) {
	throw new \Exception('object not removed');
}

MainObjectCollection::getInstance()->addObject($woman);

$woman  = MainObjectCollection::getInstance()->getObject(2, 'Test\Person\Woman');
$person = MainObjectCollection::getInstance()->getObject(2, 'Test\Person');

if (is_null($person) || is_null($woman)) {
	throw new \Exception('object not added');
}


if (!is_null($manModel->loadObject(7))) {
	throw new \Exception('load \'woman\' with \'man\' model should not work, and return null');
}

$woman = MainObjectCollection::getInstance()->getObject(2, 'Test\Person\Woman');
$woman->loadValue('bodies');

$man = $manModel->loadObject(1);
$man->loadValue('children');
if ($man->getValue('children')->count() != 3) {
	throw new \Exception('bad children count');
}
foreach ($man->getValue('children') as $child) {
	switch ($child->getId()) {
		case 5:  if ($child->getModel()->getName() !== 'Test\Person\Man') throw new \Exception('bad model : '.$child->getModel()->getName()); break;
		case 6:  if ($child->getModel()->getName() !== 'Test\Person\Man') throw new \Exception('bad model : '.$child->getModel()->getName()); break;
		case 11: if ($child->getModel()->getName() !== 'Test\Person\Woman') throw new \Exception('bad model : '.$child->getModel()->getName()); break;
		default: throw new \Exception('bad id '.$child->getId());
	}
}

foreach (MainObjectCollection::getInstance()->getModelObjects('Test\Person') as $testPerson) {
	if ($testPerson->getId() === 1 ) {
		if (!($testPerson instanceof \Comhon\Object\ComhonObject)) {
			throw new \Exception('wrong class '.get_class($testPerson).' , \Comhon\Object\ComhonObject');
		}
	} else if ($testPerson->getId() === 11) {
		if (!($testPerson instanceof Woman)) {
			throw new \Exception('wrong class '.get_class($testPerson).' , Woman');
		}
	} else if (!($testPerson instanceof Person)) {
		throw new \Exception('wrong class '.get_class($testPerson).' , Person');
	}
}

/** @var AbstractComhonObject $body */
$body = $woman->getValue('bodies')->getValue(0);
if (json_encode($body->getValue('tatoos')->export($stdPrivateInterfacer)) !== '[{"type":"sentence","location":"shoulder","tatooArtist":{"id":5,"inheritance-":"Test\\\\Person\\\\Man"}},{"type":"sentence","location":"arm","tatooArtist":{"id":6,"inheritance-":"Test\\\\Person\\\\Man"}},{"type":"sentence","location":"leg","tatooArtist":{"id":5,"inheritance-":"Test\\\\Person\\\\Man"}}]') {
	throw new \Exception('not same object values');
}
$body->setValue('arts', $body->getModel()->getProperty('arts')->getModel()->getObjectInstance());
$body->getValue('arts')->pushValue($body->getValue('tatoos')->getValue(0));
$body->getValue('arts')->pushValue($body->getValue('piercings')->getValue(0));

if (!compareJson(json_encode($body->getValue('arts')->export($stdPrivateInterfacer)), '[{"type":"sentence","location":"shoulder","tatooArtist":{"id":5,"inheritance-":"Test\\\\Person\\\\Man"},"inheritance-":"Test\\\\Body\\\\Tatoo"},{"type":"earring","location":"ear","piercer":{"id":5,"inheritance-":"Test\\\\Person\\\\Man"},"inheritance-":"Test\\\\Body\\\\Piercing"}]')) {
	throw new \Exception('not same object values');
}

$bodyTwo = $body->getModel()->getObjectInstance();
$xmlPrivateInterfacer->setMergeType(Interfacer::OVERWRITE);
$bodyTwo->fill($body->export($xmlPrivateInterfacer), $xmlPrivateInterfacer);
$xmlPrivateInterfacer->setMergeType(Interfacer::MERGE);
if ($bodyTwo === $body) {
	throw new \Exception('same object instance');
}
if (json_encode($bodyTwo->getValue('arts')->export($stdPrivateInterfacer)) !== '[{"type":"sentence","location":"shoulder","tatooArtist":{"id":5,"inheritance-":"Test\\\\Person\\\\Man"},"inheritance-":"Test\\\\Body\\\\Tatoo"},{"type":"earring","location":"ear","piercer":{"id":5,"inheritance-":"Test\\\\Person\\\\Man"},"inheritance-":"Test\\\\Body\\\\Piercing"}]') {
	throw new \Exception('not same object values');
}
$bodyTwo = $body->getModel()->import($body->export($xmlPrivateInterfacer), $xmlPrivateInterfacer);
if ($bodyTwo !== $body) {
	throw new \Exception('not same object instance');
}

$woman->reorderValues();
if (!compareJson(json_encode($woman->export($stdPrivateInterfacer)), '{"id":2,"firstName":"Marie","lastName":"Smith","birthDate":"2016-11-13T20:04:05+01:00","birthPlace":null,"bestFriend":{"id":5,"inheritance-":"Test\\\\Person\\\\Man"},"father":null,"mother":null,"bodies":[1]}')) {
	throw new \Exception('not same object values : '.json_encode($woman->export($stdPrivateInterfacer)));
}
if (!compareXML($xmlPrivateInterfacer->toString($woman->export($xmlPrivateInterfacer)), '<root xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" id="2" firstName="Marie" lastName="Smith" birthDate="2016-11-13T20:04:05+01:00"><birthPlace xsi:nil="true"/><bestFriend id="5" inheritance-="Test\Person\Man"/><father xsi:nil="true"/><mother xsi:nil="true"/><bodies><body>1</body></bodies></root>')) {
	throw new \Exception('not same object values');
}
if (!compareJson(json_encode($woman->export($flattenArraySerialInterfacer)), '{"id":2,"first_name":"Marie","last_name":"Smith","birth_date":"2016-11-13T20:04:05+01:00","birth_place":null,"best_friend":5,"father_id":null,"mother_id":null,"sex":"Test\\\\Person\\\\Woman"}')) {
	throw new \Exception('not same object values : '.json_encode($woman->export($flattenArraySerialInterfacer)));
}

$man = MainObjectCollection::getInstance()->getObject(5, 'Test\Person\Man');

if ($man->getValue('bestFriend')->isLoaded()) {
	throw new \Exception('object already loaded');
}
if ($man->getValue('bestFriend')->getModel() !== $personModel) {
	throw new \Exception('bad model');
}
$man->loadValue('bestFriend');
if (!$man->getValue('bestFriend')->isLoaded()) {
	throw new \Exception('object already loaded');
}
if ($man->getValue('bestFriend')->getModel() !== $womanModel) {
	throw new \Exception('bad model');
}
$woman = $womanModel->loadObject(8);

if ($woman->getValue('bestFriend')->isLoaded()) {
	throw new \Exception('object already loaded');
}
if ($woman->getValue('bestFriend')->getModel() !== $personModel) {
	throw new \Exception('bad model');
}
$womanNine = $womanModel->loadObject(9);
if ($womanNine->getModel() !== $womanModel) {
	throw new \Exception('bad model');
}

if ($woman->getValue('bestFriend') !== $womanNine) {
	throw new \Exception('not same object instance');
}

$bestFriend = $man->getValue('bestFriend');
$father = $man->getValue('father');
$mother = $man->getValue('mother');

$manStdObject = $man->export($stdPrivateInterfacer);
$manXml = $man->export($xmlPrivateInterfacer);
$manSql = $man->export($flattenArrayPrivateInterfacer);

$manImported = $manModel->import($man->export($stdPrivateInterfacer), $stdPrivateInterfacer);
if ($manImported !== $man) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('bestFriend') !== $bestFriend) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('father') !== $father) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('mother') !== $mother) {
	throw new \Exception('not same object instance');
}

$manImported = $manModel->import($manImported->export($xmlPrivateInterfacer), $xmlPrivateInterfacer);
if ($manImported !== $man) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('bestFriend') !== $bestFriend) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('father') !== $father) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('mother') !== $mother) {
	throw new \Exception('not same object instance');
}

$manImported = $manModel->import($manImported->export($flattenArrayPrivateInterfacer), $flattenArrayPrivateInterfacer);
if ($manImported !== $man) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('bestFriend') !== $bestFriend) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('father') !== $father) {
	throw new \Exception('not same object instance');
}
if ($manImported->getValue('mother') !== $mother) {
	throw new \Exception('not same object instance');
}

if (!compareJson(json_encode($manStdObject), json_encode($manImported->export($stdPrivateInterfacer)))) {
	throw new \Exception('not same string object');
}
if (!compareDomElement($manXml, $manImported->export($xmlPrivateInterfacer))) {
	throw new \Exception('not same string object');
}
if (!compareJson(json_encode($manSql), json_encode($manImported->export($flattenArrayPrivateInterfacer)))) {
	throw new \Exception('not same string object');
}

$dbTestModel = ModelManager::getInstance()->getInstanceModel('Test\TestDb');
$object = $dbTestModel->loadObject('[1,"1501774389"]');
$object->reorderValues();

if (!compareJson(json_encode($object->export($stdPrivateInterfacer)), '{"id1":1,"id2":"1501774389","date":"2016-04-12T05:14:33+02:00","timestamp":"2016-10-13T11:50:19+02:00","object":{"plop":"plop","plop2":"plop2"},"objectWithId":{"plop":"plop","plop2":"plop2"},"string":"nnnn","integer":2,"mainParentTestDb":1,"objectsWithId":[{"plop":"1","plop2":"heyplop2","plop3":"heyplop3","plop4":"heyplop4","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMoreMore"},{"plop":"1","plop2":"heyplop2","plop3":"heyplop3","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},{"plop":"1","plop2":"heyplop2"},{"plop":"11","plop2":"heyplop22"},{"plop":"11","plop2":"heyplop22","plop3":"heyplop33","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"}],"foreignObjects":[{"id":"1","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMoreMore"},{"id":"1","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},"1","11",{"id":"11","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"}],"lonelyForeignObject":{"id":"11","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},"lonelyForeignObjectTwo":"11","defaultValue":"default","manBodyJson":null,"womanXml":null,"boolean":false,"boolean2":true}')) {
	throw new \Exception('bad private object value 0 '.json_encode($object->export($stdPrivateInterfacer)));
}

if (!compareJson(json_encode($object->export($stdPublicInterfacer)), '{"id1":1,"id2":"1501774389","date":"2016-04-12T05:14:33+02:00","timestamp":"2016-10-13T11:50:19+02:00","object":{"plop":"plop","plop2":"plop2"},"objectWithId":{"plop":"plop","plop2":"plop2"},"integer":2,"mainParentTestDb":1,"objectsWithId":[{"plop":"1","plop2":"heyplop2","plop4":"heyplop4","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMoreMore"},{"plop":"1","plop2":"heyplop2","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},{"plop":"1","plop2":"heyplop2"},{"plop":"11","plop2":"heyplop22"},{"plop":"11","plop2":"heyplop22","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"}],"foreignObjects":[{"id":"1","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMoreMore"},{"id":"1","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},"1","11",{"id":"11","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"}],"lonelyForeignObject":{"id":"11","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},"lonelyForeignObjectTwo":"11","defaultValue":"default","manBodyJson":null,"womanXml":null,"boolean":false,"boolean2":true}')) {
	throw new \Exception('bad public object value');
}
$stdObject = $object->export($stdPrivateInterfacer);
$stdObject->string = 'azeazeazeazeaze';
$stdObject->objectsWithId[0]->plop3 = 'azeazeazeazeaze';
$object->fill($stdObject, $stdPublicInterfacer);
if (!compareJson(json_encode($object->export($stdPrivateInterfacer)), '{"id1":1,"id2":"1501774389","date":"2016-04-12T05:14:33+02:00","timestamp":"2016-10-13T11:50:19+02:00","object":{"plop":"plop","plop2":"plop2"},"objectWithId":{"plop":"plop","plop2":"plop2"},"string":"nnnn","integer":2,"mainParentTestDb":1,"objectsWithId":[{"plop":"1","plop2":"heyplop2","plop4":"heyplop4","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMoreMore"},{"plop":"1","plop2":"heyplop2","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},{"plop":"1","plop2":"heyplop2"},{"plop":"11","plop2":"heyplop22"},{"plop":"11","plop2":"heyplop22","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"}],"foreignObjects":[{"id":"1","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMoreMore"},{"id":"1","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},"1","11",{"id":"11","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"}],"lonelyForeignObject":{"id":"11","inheritance-":"Test\\\\TestDb\\\\ObjectWithIdAndMore"},"lonelyForeignObjectTwo":"11","defaultValue":"default","manBodyJson":null,"womanXml":null,"boolean":false,"boolean2":true}')) {
	throw new \Exception('bad private object value 1 '.json_encode($object->export($stdPrivateInterfacer)));
}

if ($object->getValue('objectsWithId')->count() !== 5) {
	throw new \Exception('bad count objectsWithId');
}
for ($i = 0; $i < 5; $i++) {
	if (!is_object($object->getValue('objectsWithId')->getValue($i))) {
		throw new \Exception('not object');
	}
	if ($object->getValue('objectsWithId')->getValue($i) !== $object->getValue('foreignObjects')->getValue($i)) {
		throw new \Exception('not same object instance');
	}
}
if ($object->getValue('objectsWithId')->getValue(0) === $object->getValue('objectsWithId')->getValue(1)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(0) === $object->getValue('objectsWithId')->getValue(2)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(1) === $object->getValue('objectsWithId')->getValue(2)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(4) !== $object->getValue('lonelyForeignObject')) {
	throw new \Exception('not same object instance');
}
if ($object->getValue('lonelyForeignObjectTwo') !== $object->getValue('lonelyForeignObject')) {
	throw new \Exception('not same object instance');
}

$objectOne = $object;
$object = $dbTestModel->getObjectInstance();
$stdPrivateInterfacer->setMergeType(Interfacer::OVERWRITE);
$object->fill($objectOne->export($stdPrivateInterfacer), $stdPrivateInterfacer);
$stdPrivateInterfacer->setMergeType(Interfacer::MERGE);
if ($objectOne === $object) {
	throw new \Exception('same object instance');
}

if ($object->getValue('objectsWithId')->count() !== 5) {
	throw new \Exception('bad count objectsWithId');
}
for ($i = 0; $i < 5; $i++) {
	if (!is_object($object->getValue('objectsWithId')->getValue($i))) {
		throw new \Exception('not object');
	}
	if ($object->getValue('objectsWithId')->getValue($i) !== $object->getValue('foreignObjects')->getValue($i)) {
		throw new \Exception('not same object instance');
	}
}
if ($object->getValue('objectsWithId')->getValue(0) === $object->getValue('objectsWithId')->getValue(1)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(0) === $object->getValue('objectsWithId')->getValue(2)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(1) === $object->getValue('objectsWithId')->getValue(2)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(4) !== $object->getValue('lonelyForeignObject')) {
	throw new \Exception('not same object instance');
}
if ($object->getValue('lonelyForeignObjectTwo') !== $object->getValue('lonelyForeignObject')) {
	throw new \Exception('not same object instance');
}

$objectTemp = $object;
$object = $dbTestModel->getObjectInstance();
$xmlPrivateInterfacer->setMergeType(Interfacer::OVERWRITE);
$object->fill($objectTemp->export($xmlPrivateInterfacer), $xmlPrivateInterfacer);
$xmlPrivateInterfacer->setMergeType(Interfacer::MERGE);
if ($objectOne === $object) {
	throw new \Exception('same object instance');
}

if ($object->getValue('objectsWithId')->count() !== 5) {
	throw new \Exception('bad count objectsWithId');
}
for ($i = 0; $i < 5; $i++) {
	if (!is_object($object->getValue('objectsWithId')->getValue($i))) {
		throw new \Exception('not object');
	}
	if ($object->getValue('objectsWithId')->getValue($i) !== $object->getValue('foreignObjects')->getValue($i)) {
		throw new \Exception('not same object instance');
	}
}
if ($object->getValue('objectsWithId')->getValue(0) === $object->getValue('objectsWithId')->getValue(1)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(0) === $object->getValue('objectsWithId')->getValue(2)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(1) === $object->getValue('objectsWithId')->getValue(2)) {
	throw new \Exception('same object instance');
}
if ($object->getValue('objectsWithId')->getValue(4) !== $object->getValue('lonelyForeignObject')) {
	throw new \Exception('not same object instance');
}
if ($object->getValue('lonelyForeignObjectTwo') !== $object->getValue('lonelyForeignObject')) {
	throw new \Exception('not same object instance');
}
/** ****************************** test load new value ****************************** **/

$womanModelXml   = ModelManager::getInstance()->getInstanceModel('Test\Person\WomanXml');
$womanModelXmlEX = ModelManager::getInstance()->getInstanceModel('Test\Person\WomanXmlExtended');
$manModelJson    = ModelManager::getInstance()->getInstanceModel('Test\Body\ManJson');
$manModelJsonEx  = ModelManager::getInstance()->getInstanceModel('Test\Body\ManJsonExtended');

$obj = $manModelJson->loadObject(156);
if ($obj->getModel()->getName() !== $manModelJson->getName()) {
	throw new \Exception('bad model name');
}
$obj->save();

$obj = $manModelJson->loadObject(1567);
$obj1567 = $obj;
if ($obj->getModel()->getName() !== $manModelJsonEx->getName()) {
	throw new \Exception("bad model name : {$obj->getModel()->getName()} !== {$manModelJsonEx->getName()}");
}
$obj->save();
$obj = $womanModelXml->loadObject(2);
if ($obj->getModel()->getName() !== $womanModelXml->getName()) {
	throw new \Exception('bad model name');
}
$obj->save();

$obj = $womanModelXml->loadObject(3);
$obj3 = $obj;
if ($obj->getModel()->getName() !== $womanModelXmlEX->getName()) {
	throw new \Exception('bad model name');
}
$obj->save();

$dbTestModel = ModelManager::getInstance()->getInstanceModel('Test\TestDb');
$object = $dbTestModel->loadObject('[4,"50"]');

if ($object->getValue('womanXml')->isLoaded()) {
	throw new \Exception('object already loaded');
}
$object->loadValue('womanXml');
if (!$object->getValue('womanXml')->isLoaded()) {
	throw new \Exception('object not loaded');
}
$obj = $womanModelXml->loadObject(4);
if ($obj !== $object->getValue('womanXml')) {
	throw new \Exception('not same instance object 1');
}

if ($object->getValue('manBodyJson')->isLoaded()) {
	throw new \Exception('object already loaded');
}
$obj = $manModelJson->loadObject(4567);
if ($obj !== $object->getValue('manBodyJson')) {
	throw new \Exception('not same instance object 2');
}

/** ****************** test extended object class ********************* **/

$person = new Person(false);
if ($person->getModel() !== ModelManager::getInstance()->getInstanceModel('Test\Person')) {
	throw new \Exception('not same instance model');
}
$person->cast(ModelManager::getInstance()->getInstanceModel('Test\Person\Man'));
if ($person->getModel() !== ModelManager::getInstance()->getInstanceModel('Test\Person\Man')) {
	throw new \Exception('not same instance model');
}

$man = new Man();
if ($man->getModel() !== ModelManager::getInstance()->getInstanceModel('Test\Person\Man')) {
	throw new \Exception('not same instance model');
}

$man->setFirstName('Jean');
$man->setLastName('De La Fontaine');
$man->setBirthDate(new ComhonDateTime('1674-03-02'));

if (!compareJson(json_encode($man->export($stdPrivateInterfacer)), '{"firstName":"Jean","lastName":"De La Fontaine","birthDate":"1674-03-02T00:00:00+01:00"}')) {
	throw new \Exception('bad value');
}

$time_end = microtime(true);
var_dump('extended value test exec time '.($time_end - $time_start));