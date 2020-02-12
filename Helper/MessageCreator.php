<?php
namespace Devture\Bundle\EmailTemplateBundle\Helper;

use Devture\Component\DBAL\Exception\NotFound;

class MessageCreator {

	private $factory;
	private $twig;
	private $wrappingTemplatePath;

	public function __construct(
		TemplateRendererFactory $factory,
		\Twig\Environment $twig,
		string $wrappingTemplatePath
	) {
		$this->factory = $factory;
		$this->twig = $twig;
		$this->wrappingTemplatePath = $wrappingTemplatePath;
	}

	/**
	 * @throws NotFound - when the template cannot be found
	 * @throws \Devture\Bundle\EmailTemplateBundle\Exception\TemplateSyntaxException
	 */
	public function createRendererById(string $templateId, string $localeKey, bool $allowFallbackLocale): TemplateRenderer {
		return $this->factory->createRendererById($templateId, $localeKey, $allowFallbackLocale);
	}

	/**
	 * @throws NotFound - when the template cannot be found
	 * @throws \Devture\Bundle\EmailTemplateBundle\Exception\TemplateSyntaxException
	 * @throws \Twig\Error\RuntimeError - if template rendering fails (missing variables, etc.)
	 */
	public function createMessage(string $templateId, string $localeKey, array $templateData): \Swift_Message {
		$renderer = $this->createRendererById($templateId, $localeKey, /* $allowFallbackLocale */ true);
		return $this->createMessageByRenderer($renderer, $localeKey, $templateData);
	}

	/**
	 * @throws \Twig\Error\RuntimeError - if template rendering fails (missing variables, etc.)
	 */
	public function createMessageByRenderer(TemplateRenderer $renderer, string $localeKey, array $templateData): \Swift_Message {
		//Let's make sure we pass the localeKey to the templates,
		//as they may wish to include other templates.. Which can only be done if this is available.
		$templateData['localeKey'] = $localeKey;

		$subject = $renderer->renderSubject($templateData);
		$htmlContent = $renderer->renderContent($templateData);

		$html = $this->twig->render($this->wrappingTemplatePath, [
			'localeKey' => $localeKey,
			'emailMessageSubject' => $subject,
			'emailMessageContent' => $htmlContent,
		]);

		$html2Text = new \Html2Text\Html2Text($html);
		$plainText = $html2Text->getText();

		$message = new \Swift_Message();
		$message->setSubject($subject);
		$message->setBody($html, 'text/html');
		$message->addPart($plainText, 'text/plain');

		return $message;
	}

}
