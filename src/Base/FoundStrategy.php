<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Base;

enum FoundStrategy: string
{
	/**
	 * If found, updates the contents of the element. Default option
	 */
	case OVERWRITE = 'overwrite';
	/**
	 * If found, element is left untouched
	 */
	case SKIP = 'skip';
}