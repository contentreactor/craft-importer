<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Base;

enum NotFoundStrategy: string
{
	/**
	 * Doesn't check for missing elements. This is the default option
	 */
	case IGNORE = 'ignore';
	/**
	 * If an existing element doesn't match to any newly imported ones, it is disabled.
	 */
	case DISABLE = 'disable';
	/**
	 * If an existing element doesn't match to any newly imported ones, it is deleted.
	 */
	case DELETE = 'delete';
}