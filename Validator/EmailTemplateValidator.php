<?php
namespace Devture\Bundle\EmailTemplateBundle\Validator;

use Devture\Component\Form\Validator\BaseValidator;
use Devture\Component\DBAL\Exception\NotFound;
use Devture\Bundle\EmailTemplateBundle\Repository\EmailTemplateRepositoryInterface;
use Devture\Bundle\EmailTemplateBundle\Helper\TemplateRendererFactory;
use Devture\Bundle\EmailTemplateBundle\Exception\TemplateSyntaxException;
use Devture\Component\Form\Validator\ViolationsList;

class EmailTemplateValidator extends BaseValidator {

	private $repository;
	private $factory;
	private $locales;

	public function __construct(
		EmailTemplateRepositoryInterface $repository,
		TemplateRendererFactory $factory,
		array $locales
	) {
		$this->repository = $repository;
		$this->factory = $factory;
		$this->locales = $locales;
	}

	public function validate($entity, array $options = array()): ViolationsList {
		$violations = parent::validate($entity, $options);

		if (count($entity->getLocales()) === 0) {
			$violations->add('__other__', 'devture_email_template.validation.not_localized');
		}

		$id = $entity->getId();
		if (strlen($id) < 3 || !preg_match("/^[a-z][a-z0-9\._\-\/]+$/", $id)) {
			$violations->add('id', 'devture_email_template.validation.invalid_id');
		} else {
			try {
				$ent = $this->repository->find($id);
				if (spl_object_hash($ent) !== spl_object_hash($entity)) {
					$violations->add('id', 'devture_email_template.validation.id_in_use');
				}
			} catch (NotFound $e) {

			}
		}

		foreach ($this->locales as $localeData) {
			$localeKey = $localeData['key'];

			try {
				//We don't allow fallback locale usage, because we want to validate each one separately.
				$renderer = $this->factory->createRenderer($entity, $localeKey, /* $allowFallbackLocale */ false);
			} catch (TemplateSyntaxException $e) {
				$violations->add($e->getFieldName() . '_' . $e->getLocaleKey(), 'devture_email_template.validation.invalid_template_syntax');
			}
		}

		return $violations;
	}

}
