<?php
namespace Devture\Bundle\EmailTemplateBundle\Form;

use Devture\Component\Form\Binder\SetterRequestBinder;
use Devture\Component\Form\Validator\ValidatorInterface;

abstract class LocalizedSetterRequestBinder extends SetterRequestBinder {

	private $localeKeys;

	public function __construct(array $localeKeys, ValidatorInterface $validator = null) {
		parent::__construct($validator);
		$this->localeKeys = $localeKeys;
	}

	protected function bindAll(object $entity, array $values): void {
		foreach ($values as $key => $value) {
			$setter = 'set' . ucfirst($key);
			if (!method_exists($entity, $setter)) {
				continue;
			}

			if (is_array($value)) {
				foreach ($value as $localeKey => $valueForLocale) {
					if (in_array($localeKey, $this->localeKeys)) {
						$entity->$setter($localeKey, $valueForLocale);
					}
				}
			} else {
				$entity->$setter($value);
			}
		}
	}

}
