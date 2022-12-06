<?php
namespace Devture\Bundle\EmailTemplateBundle\Helper;

use Devture\Component\DBAL\Exception\NotFound;
use Symfony\Component\Mime\Email;

class MessageCreator {

	public function __construct(
		private TemplateRendererFactory $factory,
		private \Twig\Environment $twig,
		private string $wrappingTemplatePath,
	) {
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
	public function createMessage(string $templateId, string $localeKey, array $templateData): Email {
		$renderer = $this->createRendererById($templateId, $localeKey, /* $allowFallbackLocale */ true);
		return $this->createMessageByRenderer($renderer, $localeKey, $templateData);
	}

	/**
	 * @throws \Twig\Error\RuntimeError - if template rendering fails (missing variables, etc.)
	 */
	public function createMessageByRenderer(TemplateRenderer $renderer, string $localeKey, array $templateData): Email {
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

		$message = new Email();
		$message->subject($subject);

		$message->html($html);
		$message->text($plainText);

		return $message;
	}

}
