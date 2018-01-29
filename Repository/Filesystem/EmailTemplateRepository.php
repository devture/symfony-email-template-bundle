<?php
namespace Devture\Bundle\EmailTemplateBundle\Repository\Filesystem;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Devture\Bundle\EmailTemplateBundle\Model\EmailTemplate;
use Devture\Bundle\EmailTemplateBundle\Event\EmailTemplateEvent;
use Devture\Bundle\EmailTemplateBundle\Repository\EmailTemplateRepositoryInterface;

class EmailTemplateRepository extends GaufretteYamlRepository implements EmailTemplateRepositoryInterface {

	protected function getModelClass() {
		return EmailTemplate::class;
	}

}
