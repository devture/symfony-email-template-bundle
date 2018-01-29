<?php
namespace Devture\Bundle\EmailTemplateBundle\Model;

use Devture\Component\DBAL\Model\BaseModel;

abstract class BaseModelLocalized extends BaseModel {

	abstract public function getLocales();

	public function isLocalizedIn($localeKey) {
		return in_array($localeKey, $this->getLocales());
	}

	protected function setAttributeLocalized($localeKey, $name, $value) {
		$attributeValues = $this->getAttribute($name, array());
		if (!is_array($attributeValues)) {
			throw new \Exception('Non-array value.');
		}
		$attributeValues[$localeKey] = $value;
		$this->setAttribute($name, $attributeValues);
	}

	protected function getAttributeLocalized($localeKey, $name, $defaultValue) {
		$attributeValues = $this->getAttribute($name, array());
		return isset($attributeValues[$localeKey]) ? $attributeValues[$localeKey]
				: $defaultValue;
	}

	protected function getAttributeFirstLocalized($name, $defaultValue) {
		$attributeValues = $this->getAttribute($name, array());
		foreach ($attributeValues as $localeKey => $value) {
			if (!in_array($value, array('', null), true)) {
				return $value;
			}
		}
		return $defaultValue;
	}

}
