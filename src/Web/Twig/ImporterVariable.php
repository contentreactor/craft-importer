<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Web\Twig;

use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use ContentReactor\Importer\Plugin;
use ReflectionClass;

class ImporterVariable
{
	/**
	 * @return array<int, array{value: class-string<ImporterInterface>, label: string}>
	 */
	public function getImporterTypes(): array
	{
		return array_map(
			fn (string $className): array => [
				'value' => $className,
				'label' => (new ReflectionClass($className))->getShortName(),
			],
			Plugin::getInstance()->getImports()->getImporterTypes(),
		);
	}
}