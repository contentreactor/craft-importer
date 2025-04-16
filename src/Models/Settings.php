<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Models;

use Closure;
use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use craft\base\Model;

class Settings extends Model
{
	public string $jsonField = '';

	/** @var Closure(): ImporterInterface[] */
	public Closure $importers;
}
