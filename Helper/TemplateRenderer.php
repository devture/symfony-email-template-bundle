<?php
namespace Devture\Bundle\EmailTemplateBundle\Helper;

class TemplateRenderer {

	private $twig;
	private $templateKeyPrefix;

	public function __construct(\Twig\Environment $twig, string $templateKeyPrefix) {
		$this->twig = $twig;
		$this->templateKeyPrefix = $templateKeyPrefix;
	}

	/**
	 * @throws \Twig\Error\RuntimeError - if template rendering fails (missing variables, etc.)
	 */
	public function renderSubject(array $data): string {
		return $this->twig->render($this->templateKeyPrefix . '_' . 'subject', $data);
	}

	/**
	 * @throws \Twig\Error\RuntimeError - if template rendering fails (missing variables, etc.)
	 */
	public function renderContent(array $data): string {
		return $this->twig->render($this->templateKeyPrefix . '_' . 'content', $data);
	}

}
