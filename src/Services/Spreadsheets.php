<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Services;

use Box\Spout\Common\Type;
use Box\Spout\Reader\Common\Creator\ReaderFactory;
use Box\Spout\Reader\ReaderInterface;
use yii\base\Component;

class Spreadsheets extends Component
{
	protected ReaderInterface $reader;

	public function getReader(string $readerType = 'xlsx'): ReaderInterface
	{
		if (!isset($this->reader)) {
			$this->reader = $this->createReaderByType($readerType);
		}
		return $this->reader;
	}

	private function createReaderByType(string $readerType): ReaderInterface
	{
		return match ($readerType) {
			Type::CSV => ReaderFactory::createFromType(Type::CSV),
			Type::ODS => ReaderFactory::createFromType(Type::ODS),
			default => ReaderFactory::createFromType(Type::XLSX),
		};
	}

	public function read(string $filePath, string $readerType = 'xlsx'): ReaderInterface
	{
		$this->getReader($readerType)->open($filePath);
		return $this->getReader($readerType);
	}
}
