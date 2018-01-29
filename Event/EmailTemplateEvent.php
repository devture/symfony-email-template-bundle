<?php
namespace Devture\Bundle\EmailTemplateBundle\Event;
use Symfony\Component\EventDispatcher\Event;
use Devture\Bundle\EmailTemplateBundle\Model\EmailTemplate;

class EmailTemplateEvent extends Event {

	private $article;

	public function __construct(EmailTemplate $entity) {
		$this->article = $entity;
	}

	public function getEmailTemplate() {
		return $this->article;
	}

}
