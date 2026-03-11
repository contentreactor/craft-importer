<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Web\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Extension extends AbstractExtension
{
	public function getFunctions(): array
	{
		return [
			new TwigFunction('file_exists', file_exists(...)),
			new TwigFunction('file_get_contents', @file_get_contents(...)),
			new TwigFunction('file_put_contents', file_put_contents(...)),
		];
	}
}
