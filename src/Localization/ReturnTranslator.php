<?php

declare(strict_types=1);

namespace Contributte\MenuControl\Localization;

use Nette\Localization\ITranslator;

final class ReturnTranslator implements ITranslator
{

	public function translate($message, $count = null)
	{
		return $message;
	}

}
