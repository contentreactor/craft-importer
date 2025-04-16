<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Contracts\Importers;

use ContentReactor\Importer\Base\ImporterType;
use craft\models\{
	EntryType,
	Section,
};
use Illuminate\Support\Collection;

interface ImporterInterface
{
	public function import(mixed $item): void;

	/** @return Collection<int, array<array-key, mixed>> */
	public function getData(): Collection;

	public function getTitleKey(): string;

	public function getSlugKey(): string;

	public function getPrimaryKey(): string;

	public function getPrimaryKeyValue(): string;

	public function getFieldName(): string;

	public function getSection(): Section;

	public function getEntryType(): EntryType;

	public function getType(): ImporterType;

	public function getFilePath(): string;

	public function getFileType(): string;
}
