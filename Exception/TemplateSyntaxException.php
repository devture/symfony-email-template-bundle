<?php
namespace Devture\Bundle\EmailTemplateBundle\Exception;

class TemplateSyntaxException extends \Exception {

	/**
	 * The id of the template that failed
	 */
	private $templateId;

	/**
	 * The field that failed
	 */
	private $fieldName;

	private $localeKey;

	public function __construct(string $templateId, string $fieldName, string $localeKey, \Twig\Error\SyntaxError $e) {
		parent::__construct($e->getMessage(), $e->getCode(), $e);
		$this->templateId = $templateId;
		$this->fieldName = $fieldName;
		$this->localeKey = $localeKey;
	}

	public function getTemplateId(): string {
		return $this->templateId;
	}

	public function getFieldName(): string {
		return $this->fieldName;
	}

	public function getLocaleKey(): string {
		return $this->localeKey;
	}

}
