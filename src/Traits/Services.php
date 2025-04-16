<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Traits;

use ContentReactor\Importer\Plugin;
use ContentReactor\Importer\Services\{
	Imports,
	Spreadsheets,
};

/**
 * @mixin Plugin
 */
trait Services
{
	public function getImports(): Imports
	{
		return $this->get('imports');
	}

	public function getSpreadsheets(): Spreadsheets
	{
		return $this->get('spreadsheets');
	}
}