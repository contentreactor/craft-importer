<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Models;

use Closure;
use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use craft\base\Model;

class Settings extends Model
{
	/**
	 * @var string The default, system-wide field handle where the imported data is being stored.
	 *
	 * Individual importers can define their overrides for different fields
	 * @see ImporterInterface::getFieldName
	 */
	public string $jsonField = '';

	/**
	 * @var Closure(): array<ImporterInterface>
	 */
	public Closure $importers;
}
