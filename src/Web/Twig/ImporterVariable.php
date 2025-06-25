<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Web\Twig;

use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use ContentReactor\Importer\Plugin;
use craft\helpers\FileHelper;
use ReflectionClass;
use ReflectionException;

class ImporterVariable
{
	/**
	 * Provides registered importer types in Twig
	 * 
	 * @return array<int, array{value: class-string<ImporterInterface>, label: string}>
	 * @throws ReflectionException
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

	public function isWritable(string $path): bool
	{
		return FileHelper::isWritable($path);
	}
}