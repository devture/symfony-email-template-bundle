<?php
namespace Devture\Bundle\EmailTemplateBundle\Helper;

use Devture\Bundle\EmailTemplateBundle\Model\EmailTemplate;
use Devture\Bundle\EmailTemplateBundle\Exception\TemplateSyntaxException;
use Devture\Bundle\EmailTemplateBundle\Repository\EmailTemplateRepositoryInterface;

class TemplateRendererFactory {

	private $repository;

	private $fallbackLocaleKey;

	private $twigDebug = false;

	/**
	 * @var \Twig\Extension\ExtensionInterface[]
	 */
	private $twigExtensions = array();

	public function __construct(
		EmailTemplateRepositoryInterface $repository,
		string $fallbackLocaleKey,
		bool $twigDebug
	) {
		$this->repository = $repository;
		$this->fallbackLocaleKey = $fallbackLocaleKey;
		$this->twigDebug = $twigDebug;
	}

	/**
	 * @throws \Devture\Component\DBAL\Exception\NotFound
	 * @throws \Devture\Bundle\EmailTemplateBundle\Exception\TemplateSyntaxException
	 */
	public function createRendererById(string $templateId, string $localeKey, bool $allowFallbackLocale): TemplateRenderer {
		/** @var \Devture\Bundle\EmailTemplateBundle\Model\EmailTemplate $template **/
		$template = $this->repository->find($templateId);
		return $this->createRenderer($template, $localeKey, $allowFallbackLocale);
	}

	/**
	 * @throws TemplateSyntaxException
	 */
	public function createRenderer(EmailTemplate $template, string $localeKey, bool $allowFallbackLocale): TemplateRenderer {
		//Prefixing the template allows for easier debugging.
		//Imagine Twig saying: `Variable "someVariable" does not exist in "content" at line 1`.
		//That's not very useful, compared to: `Variable someVariable" does not exist in "EmailTemplateBundle/my/template_ja_content" at line 1`.
		$templateKeyPrefix = sprintf('EmailTemplate/%s_%s', $template->getId(), $localeKey);

		$subject = $template->getSubject($localeKey);
		if ($allowFallbackLocale) {
			if (!$subject && $this->fallbackLocaleKey) {
				$subject = $template->getSubject($this->fallbackLocaleKey);
			}
			if (!$subject) {
				$subject = $template->getSubjectFirst();
			}
		}

		$content = $template->getContent($localeKey);
		if ($allowFallbackLocale) {
			if (!$content && $this->fallbackLocaleKey) {
				$content = $template->getContent($this->fallbackLocaleKey);
			}
			if (!$content) {
				$content = $template->getContentFirst();
			}
		}

		$templates = array(
			$templateKeyPrefix . '_subject' => (string) $subject,
			$templateKeyPrefix . '_content' => (string) $content,
		);
		$loader = new \Twig\Loader\ArrayLoader($templates);

		$twig = $this->createTwigEnvironment($loader);

		try {
			//Force load all templates now, so we can fail at "renderer creation"
			//if some template is broken, and not during rendering.
			foreach (array_keys($templates) as $name) {
				$twig->load($name);
			}
		} catch (\Twig\Error\SyntaxError $e) {
			$fieldName = str_replace($templateKeyPrefix . '_', '', $name);
			throw new TemplateSyntaxException($templateKeyPrefix, $fieldName, $localeKey, $e);
		}

		return new TemplateRenderer($twig, $templateKeyPrefix);
	}

	/**
	 * Creates and initializes the Twig environment that would render a specific template.
	 *
	 * @param \Twig\Loader\LoaderInterface $loader
	 * @return \Twig\Environment
	 */
	private function createTwigEnvironment(\Twig\Loader\LoaderInterface $loader) {
		$twig = new \Twig\Environment($loader, array());

		//Let's make it throw exceptions whenever expected variables are missing,
		//so that we can easily catch such template mistakes.
		$twig->enableStrictVariables();

		if ($this->twigDebug) {
			$twig->enableDebug();
		}

		foreach ($this->twigExtensions as $extension) {
			$twig->addExtension($extension);
		}

		$twig->addFunction(new \Twig\TwigFunction('devture_email_template_render_content', function ($templateId, $localeKey, array $data = array()) {
			$renderer = $this->createRendererById($templateId, $localeKey);
			$data['localeKey'] = $localeKey;
			return $renderer->renderContent($data);
		}, array(
			'is_safe' => array('html'),
		)));

		return $twig;
	}

	/**
	 * Register a Twig extension with the underlying Twig environment.
	 *
	 * Extenders could use this to add their own custom extensions.
	 */
	public function addExtension(\Twig\Extension\ExtensionInterface $extension) {
		$this->twigExtensions[] = $extension;
	}

	public function addExtensions(iterable $extensions) {
		foreach ($extensions as $extension) {
			$this->addExtension($extension);
		}
	}

}
