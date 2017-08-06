<?php

declare(strict_types=1);

namespace Carrooi\Menu\DI;

use Carrooi\Menu\LinkGenerator\NetteLinkGenerator;
use Carrooi\Menu\Loaders\ArrayMenuLoader;
use Carrooi\Menu\Localization\ReturnTranslator;
use Carrooi\Menu\Menu;
use Carrooi\Menu\MenuContainer;
use Carrooi\Menu\MenuItemFactory;
use Carrooi\Menu\Security\OptimisticAuthorizator;
use Carrooi\Menu\UI\IMenuComponentFactory;
use Carrooi\Menu\UI\MenuComponent;
use Nette\Application\Application;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\ServiceDefinition;
use Nette\Http;
use Nette\Localization\ITranslator;
use Nette\Utils\Strings;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class MenuExtension extends CompilerExtension
{


	/** @var array */
	private $menuDefaults = [
		'authorizator' => OptimisticAuthorizator::class,
		'translator' => ReturnTranslator::class,
		'loader' => ArrayMenuLoader::class,
		'linkGenerator' => NetteLinkGenerator::class,
		'items' => [],
		'templates' => [
			'menu' => __DIR__. '/../UI/templates/menu.latte',
			'breadcrumbs' => __DIR__. '/../UI/templates/breadcrumbs.latte',
			'sitemap' => __DIR__. '/../UI/templates/sitemap.latte',
		],
	];

	/** @var array */
	private $itemDefaults = [
		'linkGenerator' => null,
		'title' => null,
		'action' => null,
		'link' => null,
		'data' => [],
		'items' => [],
		'visibility' => [
			'menu' => true,
			'breadcrumbs' => true,
			'sitemap' => true,
		],
	];


	public function loadConfiguration(): void
	{
		$config = $this->config;
		$builder = $this->getContainerBuilder();

		$container = $builder->addDefinition($this->prefix('container'))
			->setClass(MenuContainer::class);

		foreach ($config as $menuName => $menu) {
			$container->addSetup('addMenu', [
				$this->loadMenuConfiguration($builder, $menuName, $menu),
			]);
		}
	}


	private function loadMenuConfiguration(ContainerBuilder $builder, string $menuName, array $config): ServiceDefinition
	{
		$config = $this->validateConfig($this->menuDefaults, $config);

		$translator = $config['translator'];
		$authorizator = $config['authorizator'];
		$loader = $config['loader'];
		$linkGenerator = $config['linkGenerator'];

		if ($config['translator'] === true) {
			$translator = $builder->getDefinitionByType(ITranslator::class);

		} else if (!Strings::startsWith($config['translator'], '@')) {
			$translator = $builder->addDefinition($this->prefix('menu.'. $menuName. '.translator'))
				->setClass($config['translator'])
				->setAutowired(false);
		}

		if (!Strings::startsWith($config['authorizator'], '@')) {
			$authorizator = $builder->addDefinition($this->prefix('menu.'. $menuName. '.authorizator'))
				->setClass($config['authorizator'])
				->setAutowired(false);
		}

		if (!Strings::startsWith($config['loader'], '@')) {
			$loader = $builder->addDefinition($this->prefix('menu.'. $menuName. '.loader'))
				->setClass($config['loader'])
				->setAutowired(false);
		}

		if (!Strings::startsWith($config['linkGenerator'], '@')) {
			$linkGenerator = $builder->addDefinition($this->prefix('menu.'. $menuName. '.linkGenerator'))
				->setClass($config['linkGenerator'])
				->setAutowired(false);
		}

		if ($loader->getClass() === ArrayMenuLoader::class) {
			$loader->setArguments([$this->normalizeMenuItems($config['items'])]);
		}

		$builder->addDefinition($this->prefix('component.menu'))
			->setClass(MenuComponent::class)
			->setImplement(IMenuComponentFactory::class);

		$itemFactory = $builder->addDefinition($this->prefix('menu.'. $menuName. '.factory'))
			->setClass(MenuItemFactory::class);

		return $builder->addDefinition($this->prefix('menu.' . $menuName))
			->setClass(Menu::class, [
				$linkGenerator,
				$translator,
				$authorizator,
				'@'. Application::class,
				'@'. Http\Request::class,
				$itemFactory,
				$loader,
				$menuName,
				$config['templates']['menu'],
				$config['templates']['breadcrumbs'],
				$config['templates']['sitemap'],
			])
			->addSetup('init')
			->setAutowired(false);
	}


	private function normalizeMenuItems(array $items): array
	{
		array_walk($items, function(array &$item, string $key) {
			$item = $this->validateConfig($this->itemDefaults, $item);

			if ($item['title'] === null) {
				$item['title'] = $key;
			}

			$item['items'] = $this->normalizeMenuItems($item['items']);
		});

		return $items;
	}

}
