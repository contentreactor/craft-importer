<?php
declare(strict_types=1);

use Developion\CodingStandards\SetList;
use PhpCsFixer\Finder;

require 'vendor/autoload.php';

$finder = (new Finder())
	->in([
		__DIR__ . '/src',
	])
	->ignoreDotFiles(false)
	->ignoreVCSIgnored(true)
;

$config = require SetList::PHP_CS_FIXER;

return $config($finder);