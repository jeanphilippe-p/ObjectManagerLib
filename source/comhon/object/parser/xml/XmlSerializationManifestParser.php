<?php

namespace comhon\object\parser\xml;

use comhon\object\model\Model;
use comhon\object\model\MainModel;
use comhon\object\parser\SerializationManifestParser;
use comhon\object\singleton\InstanceModel;

class XmlSerializationManifestParser extends SerializationManifestParser {

	
	/**
	 * @param string $pManifestPath_afe
	 */
	protected function _loadManifest($pManifestPath_afe) {
		$this->mManifest = simplexml_load_file($pManifestPath_afe);
		
		if ($this->mManifest === false || is_null($this->mManifest)) {
			throw new \Exception("serialization manifest file not found '$pManifestPath_afe'");
		}
	}	

	
	public function getPropertySerializationInfos($pPropertyName) {
		$lSerializationName = null;
		$lAggregations      = null;
		$lIsSerializable    = true;
		
		if (isset($this->mManifest->properties->$pPropertyName)) {
			$lSerializationNode = $this->mManifest->properties->$pPropertyName;
			if (isset($lSerializationNode['serializationName'])) {
				$lSerializationName = (string) $lSerializationNode['serializationName'];
			}
			if (isset($lSerializationNode->aggregations->aggregation)) {
				$lAggregations = [];
				foreach ($lSerializationNode->aggregations->aggregation as $lAggregation) {
					$lAggregations[] = (string) $lAggregation;
				}
			}
			if (isset($lSerializationNode["serializable"])) {
				$lIsSerializable = (string) $lSerializationNode["serializable"] !== "0";
			}
		}
		
		return array($lSerializationName, $lAggregations, $lIsSerializable);
	}
	
	protected function _getSerialization() {
		return isset($this->mManifest->serialization)
					? $this->_buildSerialization($this->mManifest->serialization)
					: null;
	}
	
	private function _buildSerialization($pSerializationNode) {
		$lType = (string) $pSerializationNode['type'];
		if (isset($pSerializationNode->$lType)) {
			$lObjectXml = $pSerializationNode->$lType;
			$lSerialization = InstanceModel::getInstance()->getInstanceModel($lType)->getObjectInstance();
			$lSerialization->fromXml($lObjectXml, true, true);
		} else {
			$lId = (string) $pSerializationNode;
			if (empty($lId)) {
				throw new \Exception('malformed serialization, must have description or id');
			}
			$lSerialization =  InstanceModel::getInstance()->getInstanceModel($lType)->loadObject($lId);
			if (is_null($lSerialization)) {
				throw new \Exception("impossible to load $lType serialization with id '$lId'");
			}
		}
		if (isset($pSerializationNode['inheritanceKey'])) {
			$lSerialization->setInheritanceKey((string) $pSerializationNode['inheritanceKey']);
		}
		return $lSerialization;
	}
	
}