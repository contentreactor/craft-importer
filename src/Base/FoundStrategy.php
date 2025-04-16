<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Base;

enum FoundStrategy: string
{
	case OVERWRITE = 'overwrite';
	case SKIP = 'skip';
}