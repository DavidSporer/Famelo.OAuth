<?php
namespace Famelo\Oauth\Services;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Famelo.Oauth".          *
 *                                                                        *
 *                                                                        */

use Famelo\Oauth\Api\ServiceEntity;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * @Flow\Scope("singleton")
 */
class TransformationService {
	public function transform($source, $propertyConfiguration) {
		$source = $this->getPath($source, $propertyConfiguration);
		switch ($propertyConfiguration['type']) {
			case 'array':
				$result = $this->transformArray($source, $propertyConfiguration);
				break;

			case 'object':
				$result = $this->transformObject($source, $propertyConfiguration);
				break;

			default:
				return 'foo';
		}
		return $result;
	}

	public function getPath($source, $configuration) {
		if (isset($configuration['path'])) {
			$source = ObjectAccess::getPropertyPath($source, $configuration['path']);
		}
		return $source;
	}

	public function transformArray($sourceItems, $propertyConfiguration) {
		$result = array();
		foreach ($sourceItems as $key => $sourceItem) {
			$result[$key] = $this->transform($sourceItem, $propertyConfiguration['children']);
		}
		return $result;
	}

	public function transformObject($sourceItem, $objectConfiguration) {
		$result = new ServiceEntity();
		if (!isset($objectConfiguration['properties'])) {
			foreach (get_object_vars($sourceItem) as $propertyName => $propertyValue) {
				$propertyName = lcfirst(preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $propertyName));
				$result->$propertyName = $propertyValue;
			}
		} else {
			foreach ($objectConfiguration['properties'] as $propertyName => $propertyConfiguration) {
				$result->$propertyName = ObjectAccess::getPropertyPath($sourceItem, $propertyConfiguration['path']);
			}
		}
		if (isset($objectConfiguration['relations'])) {
			foreach ($objectConfiguration['relations'] as $relationName => $relationConfiguration) {
				$arguments = array();
				foreach ($relationConfiguration['arguments'] as $argumentName => $argumentConfiguration) {
					$arguments[$argumentName] = ObjectAccess::getPropertyPath($sourceItem, $argumentConfiguration['path']);
				}
				$result->addRelation($relationName, $relationConfiguration['requestPath'], $arguments);
			}
		}
		return $result;
	}
}