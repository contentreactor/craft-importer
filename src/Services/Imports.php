<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Services;

use AXP\FileParser\FileParser;
use Box\Spout\Common\Entity\{
	Cell,
	Row,
};
use Box\Spout\Reader\XLSX\Sheet;
use Cake\Utility\Xml as XmlParser;
use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use ContentReactor\Importer\Events\ParsedContentEvent;
use ContentReactor\Importer\Importers\BaseFileImporter;
use ContentReactor\Importer\Plugin;
use craft\elements\Entry;
use craft\errors\FileException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\{
	ElementHelper,
	StringHelper,
};
use Illuminate\Support\Collection;
use League\Csv\{
	Reader,
	Statement,
};
use yii\base\Event;

class Imports
{
	public const EVENT_REGISTER_IMPORTER_TYPES = 'registerImporterTypes';
	public const EVENT_AFTER_PARSING_JSON_FILE = 'afterParsingJsonFile';
	public const EVENT_AFTER_PARSING_CSV_FILE = 'afterParsingCsvFile';
	public const EVENT_AFTER_PARSING_XML_FILE = 'afterParsingXmlFile';
	public const EVENT_AFTER_PARSING_XLS_FILE = 'afterParsingXlsFile';

	public const FILE_JSON = 'application/json';
	public const FILE_XML = 'application/xml';
	public const FILE_CSV = 'text/csv';
	public const FILE_XLS = 'application/vnd.ms-excel';

	/**
	 * @return Collection<array-key, ImporterInterface>
	 */
	public function getImporters(): Collection
	{
		/**
		 * @TKey array-key
		 * @TValue ImporterInterface
		 */
		return collect(
			(Plugin::getInstance()
				->getSettings()
				->importers)(),
		);
	}

	/** @return class-string<ImporterInterface>[] */
	public function getImporterTypes(): array
	{
		$event = new RegisterComponentTypesEvent([
			'types' => [
				BaseFileImporter::class,
			],
		]);

		Event::trigger(self::class, self::EVENT_REGISTER_IMPORTER_TYPES, $event);

		return $event->types;
	}

	/**
	 * @param ImporterInterface $importer
	 * @param array<array-key, mixed> $options
	 * @return Collection<int, array<array-key, mixed>>
	 */
	public function getParsedContent(ImporterInterface $importer, array $options = []): Collection
	{
		$content = match ($importer->getFileType()) {
			self::FILE_JSON => $this->parseJson($importer->getFilePath(), $options),
			self::FILE_CSV => $this->parseCsv($importer->getFilePath(), $options),
			self::FILE_XML => $this->parseXml($importer->getFilePath(), $options),
			self::FILE_XLS => $this->parseXls($importer->getFilePath(), $options),
			default => throw new FileException('Invalid file type provided.'),
		};

		return collect($content);
	}

	/**
	 * @param string $jsonPath
	 * @param array<array-key, mixed> $options
	 * @return array<int, array<array-key, mixed>>
	 */
	protected function parseJson(string $jsonPath, array $options = []): array
	{
		$content = FileParser::json(file_get_contents($jsonPath));
		$event = new ParsedContentEvent([
			'content' => $content,
			'filePath' => $jsonPath,
			'options' => $options,
		]);
		Event::trigger(self::class, self::EVENT_AFTER_PARSING_JSON_FILE, $event);

		return $event->content;
	}

	/**
	 * @param string $csvPath
	 * @param array<array-key, mixed> $options
	 * @return array<int, array<array-key, mixed>>
	 */
	protected function parseCsv(string $csvPath, array $options = []): array
	{
		$delimiter = ',';
		if (array_key_exists('delimiter', $options)) {
			$delimiter = $options['delimiter'];
		}
		$data = file_get_contents($csvPath);

		if (!ini_get('auto_detect_line_endings')) {
			ini_set('auto_detect_line_endings', '1');
		}

		$data = StringHelper::convertToUtf8($data);

		$csv = Reader::createFromString($data);
		$csv->setDelimiter($delimiter);
		$csv->setHeaderOffset(0);

		$records = (new Statement)->process($csv);
		$content = array_map(fn($record) => $record, iterator_to_array($records));

		$event = new ParsedContentEvent([
			'content' => $content,
			'filePath' => $csvPath,
			'options' => $options,
		]);
		Event::trigger(self::class, self::EVENT_AFTER_PARSING_CSV_FILE, $event);

		return $event->content;
	}

	/**
	 * @param string $xmlPath
	 * @param array<array-key, mixed> $options
	 * @return array<int, array<array-key, mixed>>
	 */
	protected function parseXml(string $xmlPath, array $options = []): array
	{
		libxml_use_internal_errors(true);

		$array = XmlParser::build(file_get_contents($xmlPath));
		$array = XmlParser::toArray($array);
		$xml = json_encode($array);
		$xml = json_decode($xml, true);
		$event = new ParsedContentEvent([
			'content' => $xml,
			'filePath' => $xmlPath,
			'options' => $options,
		]);
		Event::trigger(self::class, self::EVENT_AFTER_PARSING_XML_FILE, $event);
		//$xml = str_ireplace(['ogdwien:', 'gml:'], '', $xml);
		return $event->content;
	}

	/**
	 * @param string $xlsPath
	 * @param array<array-key, mixed> $options
	 * @return array<int, array<array-key, mixed>>
	 */
	protected function parseXls(string $xlsPath, array $options = []): array
	{
		$reader = Plugin::getInstance()->getSpreadsheets()->read($xlsPath);
		$sheet = collect(iterator_to_array($reader->getSheetIterator()))
			->map(fn(Sheet $sheet): array => iterator_to_array($sheet->getRowIterator()))
			->flatten(1)
			->map(fn(Row $row) => array_map(fn (Cell $cell): string => trim($cell->getValue()), $row->getCells()));
		$header = $sheet->first();

		$content = $sheet->reject(fn(array $item): bool => $item[0] === $header[0] || count($header) !== count($item))
			->map(fn(array $item): array => array_combine($header, $item))
			->all();

		$event = new ParsedContentEvent([
			'content' => $content,
			'filePath' => $xlsPath,
			'options' => $options,
		]);
		Event::trigger(self::class, self::EVENT_AFTER_PARSING_XLS_FILE, $event);

		return $event->content;
	}

	public function getPrimaryKeyValue(ImporterInterface $importer, string $value): string
	{
		return $importer->getPrimaryKey() === 'slug' ? ElementHelper::generateSlug($value) : $value;
	}

	/**
	 * @param ImporterInterface $importer
	 * @return Collection<int, Entry>
	 */
	public function findMissing(ImporterInterface $importer): Collection
	{
		$primaryKeyValues = $importer->getData()->pluck($importer->getPrimaryKeyValue())
			->map(fn(string $value): string => $this->getPrimaryKeyValue($importer, $value))
			->all();
		$primaryKey = $importer->getPrimaryKey();

		return Entry::find()
			->status(null)
			->section($importer->getSection())
			->type($importer->getEntryType())
			->$primaryKey(['not', ...$primaryKeyValues])
			->collect();
	}
}