<?php

declare(strict_types=1);

namespace Contributte\MenuControl;

use Contributte\MenuControl\LinkGenerator\ILinkGenerator;
use Contributte\MenuControl\Loaders\IMenuLoader;
use Contributte\MenuControl\Security\IAuthorizator;
use Nette\Application\UI\Presenter;
use Nette\Http\Request;
use Nette\Localization\ITranslator;

/**
 * @author David Kudera <kudera.d@gmail.com>
 */
final class Menu extends AbstractMenuItemsContainer implements IMenu
{


	/** @var IMenuLoader */
	private $loader;

	/** @var string */
	private $name;

	/** @var string[] */
	private $templates = [
		'menu' => null,
		'breadcrumbs' => null,
		'sitemap' => null,
	];

	/** @var Presenter */
	private $activePresenter;


	public function __construct(ILinkGenerator $linkGenerator, ITranslator $translator, IAuthorizator $authorizator, Request $httpRequest, IMenuItemFactory $menuItemFactory, IMenuLoader $loader, string $name, string $menuTemplate, string $breadcrumbsTemplate, string $sitemapTemplate)
	{
		parent::__construct($this, $linkGenerator, $translator, $authorizator, $httpRequest, $menuItemFactory);

		$this->loader = $loader;
		$this->name = $name;
		$this->templates['menu'] = $menuTemplate;
		$this->templates['breadcrumbs'] = $breadcrumbsTemplate;
		$this->templates['sitemap'] = $sitemapTemplate;
	}


	public function init(): void
	{
		$this->loader->load($this);
	}


	public function getName(): string
	{
		return $this->name;
	}


	public function getMenuTemplate(): string
	{
		return $this->templates['menu'];
	}


	public function getBreadcrumbsTemplate(): string
	{
		return $this->templates['breadcrumbs'];
	}


	public function getSitemapTemplate(): string
	{
		return $this->templates['sitemap'];
	}


	public function getPath(): array
	{
		$path = [];
		$parent = $this;

		while ($parent) {
			$item = $parent->findActiveItem();

			if (!$item) {
				break;
			}

			$parent = $path[] = $item;
		}

		return $path;
	}


	public function getActivePresenter(): ?Presenter
	{
		return $this->activePresenter;
	}


	public function setActivePresenter(?Presenter $presenter): void
	{
		$this->activePresenter = $presenter;
	}

}
