# Description

Bundle providing:

- a web UI for managing email templates
- system for preparing [Swift](https://swiftmailer.symfony.com/) email messages out of these templates. See [Usage](#usage).

Email Templates managed by this bundle are [Twig](http://twig.sensiolabs.org/) templates,
which you edit through the web UI. Each "template" can be localized to multiple languages.

The templates are stored on the filesystem as [YAML](https://en.wikipedia.org/wiki/YAML) files using [Gaufrette](http://knplabs.github.io/Gaufrette/).
This allows you to version-control them along with your project's source code.


# Prerequisites

This bundles depends on [devture/form](https://packagist.org/packages/devture/form).

Before you can get this bundle working, you'd need a working `devture/form` setup.

Minimally, you need to define the following services somewhere (possibly in a `devture-form.yaml` file in your `AppBundle`):

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  Devture\Component\Form\Token\TemporaryTokenManager:
    arguments:
      $validityTime: 3600
      $secret: "%env(APP_SECRET)%"
      $hashFunction: sha256

  Devture\Component\Form\Token\TokenManagerInterface:
    alias: Devture\Component\Form\Token\TemporaryTokenManager

  Devture\Component\Form\Twig\FormExtension:
    tags: [twig.extension]

  Devture\Component\Form\Twig\TokenExtension:
    tags: [twig.extension]
```

Additionally, your `config/packages/twig.yaml` needs to have this additional path added to it: `"%kernel.project_dir%/vendor/devture/form/src/Devture/Component/Form/Resources/views"`


# Installation

Install through composer (`composer require devture/symfony-email-template-bundle`).

Add to `config/bundles.php`:

```php
Devture\Bundle\EmailTemplateBundle\DevtureEmailTemplateBundle::class => ['all' => true],
```


## Configuration

You can drop the following configuration in `config/packages/devture_email_template.yaml`

```yaml
devture_email_template:
  email_template_storage_path: "%kernel.project_dir%/asset/email-template"
  locales:
    - {"key": "en", "name": "English"}
    - {"key": "ja", "name": "Japanese"}
  fallback_locale_key: en
  email_wrapper_path: "@DevtureEmailTemplateBundle/email-wrapper.html.twig"
  webui_twig_layout_path: "base.html.twig"
  editable: "%kernel.debug%"
```

`email_template_storage_path` is the directory where the templates would be stored. It needs to be writable by your web server user.

`locales` needs to contain all languages that you're translating your email templates to.

`fallback_locale_key` specifies which language to fall back to in case a template is not available in the language requested.

`email_wrapper_path` is a layout file for the actual email message. A sample one is provided in the bundle (`@DevtureEmailTemplateBundle/email-wrapper.html.twig`), but feel free to make your own.

`webui_twig_layout_path` is the path to your layout file, which would contain the email template system's web UI.
The only requirement is that it defines a `content` block. The translation system would render its content within it.

Example layout file:

```twig
<!doctype html>
<html>
	<body>
		<h1>Website</h1>
		{% block content %}{% endblock %}
	</body>
</html>
```

`editable` controls whether the templates are editable through the web UI or if they'd be displayed as readonly. In any case, your production environment would not even mount the web UI routes, thus preventing all edits.


## Routing example

You most likely want this bundle's web UI active only for your development (`dev`) environment.
Thus, you can drop the following routing config in `config/routes/dev/DevtureEmailTemplateBundle.yaml`:

```yaml
DevtureEmailTemplateBundleWebsite:
    prefix: /{_locale}/email-template
    resource: "@DevtureEmailTemplateBundle/Resources/config/routes/website.yaml"
    requirements:
        _locale: "en|ja"
```

The Web UI is available at the `devture_email_template.manage` route.


## Web UI

Templates can be edited as HTML (they're Twig "files", after all).
This bundle relies on [CKEDITOR 4](https://ckeditor.com/ckeditor-4/) as a rich-text editor.

You can load it somewhere in your `webui_twig_layout_path` template file with a regular `<script>` tag.

Alternatively, you can load it via [comploader](https://github.com/spantaleev/comploader) by definining it as a library named `ckeditor4`, like this:

```html
<script>
comploader.register("ckeditor4", {
	"scripts": [
		{
			"url": "https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.8.0/ckeditor.js",
			"integrity": "sha384-O5mWK3ANYOTVEe9IPtX3AglPo9YiJ4txOJlcCuY1DJf9Bawr3wUvQiVe1H5NvNlh"
		}
	]
});
</script>
```


## Styling

This bundle relies on [Bootstrap](http://getbootstrap.com/) v4 for styling.
Unless you install and include it (somewhere in your `webui_twig_layout_path` template), things would look ugly.

Additionally, you can make the pages look prettier by including a flag icon for each language somewhere in your `webui_twig_layout_path` template or CSS file.

```html
<style>
	.devture-email-template-flag {
		border: 1px solid #dbdbdb;
		width: 20px;
		height: 13px;
		display: inline-block;
		vertical-align: text-top;
	}
	.devture-email-template-flag.en {
		background: url('/images/flag/en_US.png') no-repeat;
	}
	.devture-email-template-flag..ja {
		background: url('/images/flag/ja_JP.png') no-repeat;
	}
</style>
```

# Usage

Suppose you have created a template called `user/registered`, which contains some content like this:

```twig
Hello {{ user.name }}!

Welcome to <a href="{{ path('homepage') }}">our website</a>!
```

To send an email using this template you'd do this:

```php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Devture\Bundle\EmailTemplateBundle\Helper\MessageCreator;

class UserRegistrationController extends AbstractController {

	public function register(Request $request, MessageCreator $messageCreator, \Swift_Mailer $mailer) {
		//Actually handle registration here..
		$user = $this->registerUser($request);

		$templateData = [
			'user' => $user,
		];
		$message = $messageCreator->createMessage('user/registered', $request->getLocale(), $templateData);

		$mailer->send($message);
	}

}
