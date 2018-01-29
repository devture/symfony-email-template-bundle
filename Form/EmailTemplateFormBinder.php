<?php
namespace Devture\Bundle\EmailTemplateBundle\Form;

use Symfony\Component\HttpFoundation\Request;
use Devture\Bundle\EmailTemplateBundle\Validator\EmailTemplateValidator;

class EmailTemplateFormBinder extends LocalizedSetterRequestBinder {


	public function __construct(EmailTemplateValidator $validator, array $locales) {
		$localeKeys = array_map(function (array $localeData): string {
			return $localeData['key'];
		}, $locales);

		parent::__construct($localeKeys, $validator);
	}

	/**
	 * @param \Devture\Bundle\EmailTemplateBundle\Model\EmailTemplate $entity
	 * @param Request $request
	 * @param array $options
	 */
	protected function doBindRequest($entity, Request $request, array $options = array()) {
		$whitelisted = array('subject', 'content', 'memo');
		$this->bindWhitelisted($entity, $request->request->all(), $whitelisted);

		if ($entity->getId() === null) {
			//We allow ids to be bound only for non-added items, and not changed at a later time.
			$this->bindWhitelisted($entity, $request->request->all(), array('id'));
		}
	}

}
