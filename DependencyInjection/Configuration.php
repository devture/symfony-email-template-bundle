<?php
namespace Devture\Bundle\EmailTemplateBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {

	public function getConfigTreeBuilder(): TreeBuilder {
		$treeBuilder = new TreeBuilder('devture_email_template');

		$rootNode = $treeBuilder->getRootNode();

		$rootNode
			->children()
				->scalarNode('email_template_storage_path')->end()
				->arrayNode('locales')
					->arrayPrototype()
						->children()
							->scalarNode('key')->end()
							->scalarNode('name')->end()
						->end()
					->end()
				->end()
				->scalarNode('fallback_locale_key')->end()
				->scalarNode('email_wrapper_path')->end()
				->scalarNode('webui_twig_layout_path')->end()
				->booleanNode('editable')->end()
			->end()
		;

		return $treeBuilder;
	}

}
