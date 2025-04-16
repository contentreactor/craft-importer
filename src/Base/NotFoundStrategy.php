<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Base;

enum NotFoundStrategy: string
{
	case IGNORE = 'ignore';
	case DISABLE = 'disable';
	case DELETE = 'delete';
}