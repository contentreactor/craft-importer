<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Base;

use craft\base\Batchable;

class ImportBatcher implements Batchable
{
	/** @param array<array-key, mixed> $imports */
	public function __construct(
		private array $imports = [],
	) {
	}

	/** @return iterable<array-key, mixed> */
	public function getSlice(int $offset, int $limit): iterable
	{
		return array_slice($this->imports, $offset, $limit);
	}

	public function count(): int
	{
		return count($this->imports);
	}
}