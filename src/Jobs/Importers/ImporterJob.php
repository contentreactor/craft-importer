<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Jobs\Importers;

use ContentReactor\Importer\Base\ImportBatcher;
use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use Craft;
use craft\base\Batchable;
use craft\queue\BaseBatchedJob;

class ImporterJob extends BaseBatchedJob
{
	/** @param array<array-key, mixed> $config */
	public function __construct(
		public ImporterInterface $importer,
		public array $config = [],
	) {
		parent::__construct($config);
	}

	protected function loadData(): Batchable
	{
		return new ImportBatcher($this->importer->getData()->all());
	}

	protected function processItem(mixed $item): void
	{
		$this->importer->import($item);
	}

	protected function defaultDescription(): ?string
	{
		return Craft::t('craft-importer', $this->importer->getSection()->name);
	}
}