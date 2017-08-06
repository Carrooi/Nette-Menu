<?php

declare(strict_types=1);

namespace Carrooi\Menu\Loaders;

use Carrooi\Menu\IMenu;
use Carrooi\Menu\IMenuItem;
use Carrooi\Menu\IMenuItemsContainer;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class ArrayMenuLoader implements IMenuLoader
{


	/** @var array */
	private $items;


	public function __construct(array $items)
	{
		$this->items = $items;
	}


	public function load(IMenu $menu): void
	{
		$this->processItems($menu, $this->items);
	}


	private function processItems(IMenuItemsContainer $parent, array $items): void
	{
		foreach ($items as $name => $item) {
			$this->processItem($parent, $name, $item);
		}
	}


	private function processItem(IMenuItemsContainer $parent, string $name, array $config): void
	{
		$parent->addItem($name, $config['title'], function(IMenuItem $item) use ($config) {
			$item->setData($config['data']);

			$item->setMenuVisibility($config['visibility']['menu']);
			$item->setBreadcrumbsVisibility($config['visibility']['breadcrumbs']);
			$item->setSitemapVisibility($config['visibility']['sitemap']);

			if ($config['linkGenerator'] !== null) {
				$item->setLinkGenerator($config['linkGenerator']);
			}

			if ($config['action'] !== null) {
				if (is_array($config['action'])) {
					$item->setAction($config['action']['target'], $config['action']['parameters']);
				} else {
					$item->setAction($config['action']);
				}
			}

			if ($config['link'] !== null) {
				$item->setLink($config['link']);
			}

			$this->processItems($item, $config['items']);
		});
	}

}
