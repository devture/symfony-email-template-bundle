<?php
namespace Devture\Bundle\EmailTemplateBundle\Model;

class EmailTemplate extends BaseModelLocalized {

	public function getLocales(): array {
		$locales = array();
		$subjects = $this->getAttribute('subject', array());
		foreach ($subjects as $localeKey => $value) {
			if (!in_array($value, array('', null), true)) {
				$locales[] = $localeKey;
			}
		}
		return $locales;
	}

	public function setSubject($localeKey, $value) {
		$this->setAttributeLocalized($localeKey, 'subject', $value);
	}

	public function getSubject($localeKey): ?string {
		return $this->getAttributeLocalized($localeKey, 'subject', null);
	}

	public function getSubjectFirst(): ?string {
		return $this->getAttributeFirstLocalized('subject', null);
	}

	public function setContent($localeKey, $value) {
		$this->setAttributeLocalized($localeKey, 'content', $value);
	}

	public function getContent($localeKey): ?string {
		return $this->getAttributeLocalized($localeKey, 'content', null);
	}

	public function getContentFirst(): ?string {
		return $this->getAttributeFirstLocalized('content', null);
	}

	public function setMemo($localeKey, $value) {
		$this->setAttributeLocalized($localeKey, 'memo', $value);
	}

	public function getMemo($localeKey): ?string {
		return $this->getAttributeLocalized($localeKey, 'memo', null);
	}

	public function getMemoFirst(): ?string {
		return $this->getAttributeFirstLocalized('memo', null);
	}

}
