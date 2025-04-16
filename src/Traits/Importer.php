<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Traits;

use ContentReactor\Importer\Base\{
	FoundStrategy,
	ImporterType,
	NotFoundStrategy,
};
use ContentReactor\Importer\Errors\FoundWhileSkippingException;
use ContentReactor\Importer\Plugin;
use ContentReactor\Importer\Services\Imports;
use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\models\{
	EntryType,
	Section,
};
use Illuminate\Support\Collection;
use yii\base\InvalidConfigException;

trait Importer
{
	/**
	 * @param string|Section $section  Section where the stored content is being saved
	 * @param string|EntryType $entryType  Entry Type within the where the stored content is being saved. If left empty, the first type of the Section is used
	 * @param string|ImporterType $importerType  Specifies the type of source. The available options are:
	 *
	 *  - `fileImporter` - The [[$filePath]] expects a local file path
	 *  - `urlImporter` - The [[$filePath]] expects a remote url. This is the default option
	 *  - `uploadImporter` - This option is used in the background for dashboard uploads
	 *
	 * Each of the listed options can be referenced by a case of ImporterType enum
	 * @param string $filePath A path/url of the imported file. The value must always be an absolute path. Aliases can be used
	 * @param string $fileType Valid MIME type of the imported file. Allowed types are:
	 *
	 *  - \ContentReactor\Importer\Services\Imports::FILE_JSON
	 *  - \ContentReactor\Importer\Services\Imports::FILE_XML
	 *  - \ContentReactor\Importer\Services\Imports::FILE_CSV
	 *  - \ContentReactor\Importer\Services\Imports::FILE_XLS
	 * @param string $dataPath Defines the dot-separated path to the content within the parsed data, eg. 'rss.channel.item', 'data.content'.
	 *
	 * If left empty, it assumes the content is in the root of the file
	 *
	 * @param string|FoundStrategy $foundStrategy Specifies the way to handle matched elements. The available options are:
	 *
	 *  - `overwrite` - If found, updates the contents of the element. This is the default option
	 *  - `skip` - If found, element is left untouched
	 *
	 *   Each of the listed options can be referenced by a case of FoundStrategy enum
	 * @param string|NotFoundStrategy $notFoundStrategy Specifies the type of source. The available options are:
	 *
	 *  - `ignore` - Doesn't check for missing elements. This is the default option
	 *  - `disable` - If an existing element doesn't match to any newly imported ones, it is disabled.
	 *  - `delete` - If an existing element doesn't match to any newly imported ones, it is deleted.
	 *
	 *   Each of the listed options can be referenced by a case of NotFoundStrategy enum
	 * @param string $primaryKey Defines the handle of the saved elements which is used to match the existing content
	 * @param string $primaryKeyValue Defines the dot-separated array key in individual data items which is used with [[$primaryKey]] to match the existing content
	 * @param string $slugKey Defines the dot-separated array key in individual data items which will be used for the slug of the saved element
	 * @param string $titleKey Defines the dot-separated array key in individual data items which will be used for the title of the saved element
	 * @param string $field Defines the handle of the field where the contents are stored. If left empty, it uses the globally configured field
	 * @throws InvalidConfigException
	 */
	public function __construct(
		private readonly string|Section          $section,
		private readonly string|EntryType        $entryType = '',
		private readonly string|ImporterType     $importerType = ImporterType::IMPORTER_TYPE_URL,
		private readonly string                  $filePath = '',
		private readonly string                  $fileType = Imports::FILE_JSON,
		private readonly string                  $dataPath = '',
		private readonly string|FoundStrategy    $foundStrategy = FoundStrategy::OVERWRITE,
		private readonly string|NotFoundStrategy $notFoundStrategy = NotFoundStrategy::IGNORE,
		private readonly string                  $primaryKey = 'slug',
		private readonly string                  $primaryKeyValue = 'slug',
		private readonly string                  $slugKey = 'slug',
		private readonly string                  $titleKey = 'title',
		private readonly string                  $field = '',
	)
	{
		if ($this->getType()->requiresFile() && (empty($this->filePath) || empty($this->fileType))) {
			throw new InvalidConfigException('Import path cannot be empty.');
		}

		$this->applyNotFoundStrategy();
	}

	public function applyNotFoundStrategy(): void
	{
		switch ($this->getNotFoundStrategy()) {
			case NotFoundStrategy::DISABLE:
				Plugin::getInstance()->getImports()->findMissing($this)
					->each(static function (Element $element): void {
						$element->setEnabledForSite(false);
						Craft::$app->getElements()->saveElement($element);
						Craft::info("disabled $element->title.", 'craft-importer::disable-element');
					});
				break;
			case NotFoundStrategy::DELETE:
				Plugin::getInstance()->getImports()->findMissing($this)
					->each(static function (Element $element): void {
						if (Craft::$app->getElements()->deleteElement($element)) {
							Craft::info("Removed $element->title.", 'craft-importer::delete-element');
						}
					});
				break;
			default:
				break;
		}
	}

	public function getSection(): Section
	{
		if ($this->section instanceof Section) {
			return $this->section;
		}

		return Craft::$app->getSections()
			->getSectionByHandle($this->section);
	}

	public function getEntryType(): EntryType
	{
		if ($this->entryType instanceof EntryType) {
			return $this->entryType;
		}

		$section = $this->getSection();
		$entryTypes = collect($section->getEntryTypes());
		$entryType = $entryTypes->first(function (EntryType $entryType): bool {
			return $entryType->handle === $this->entryType;
		});

		if (!$entryType) {
			$entryType = $entryTypes->first();
		}

		return $entryType;
	}

	public function getFieldName(): string
	{
		return $this->field ?: Plugin::getInstance()->getSettings()->jsonField;
	}

	public function getSlugKey(): string
	{
		return $this->slugKey;
	}

	public function getTitleKey(): string
	{
		return $this->titleKey;
	}

	public function getPrimaryKey(): string
	{
		return $this->primaryKey ?: 'slug';
	}

	public function getPrimaryKeyValue(): string
	{
		return $this->primaryKeyValue ?: 'slug';
	}

	public function getType(): ImporterType
	{
		$type = $this->importerType;
		if ($type instanceof ImporterType) return $type;

		return ImporterType::from($this->importerType);
	}

	public function getFoundStrategy(): FoundStrategy
	{
		$strategy = $this->foundStrategy;
		if ($strategy instanceof FoundStrategy) return $strategy;

		return FoundStrategy::from($this->foundStrategy);
	}

	public function getNotFoundStrategy(): NotFoundStrategy
	{
		$strategy = $this->notFoundStrategy;
		if ($strategy instanceof NotFoundStrategy) return $strategy;

		return NotFoundStrategy::from($this->notFoundStrategy);
	}

	public function getFilePath(): string
	{
		return $this->filePath;
	}

	public function getFileType(): string
	{
		return $this->fileType;
	}

	/**
	 * @return Collection<int, array<array-key, mixed>>
	 */
	public function getData(): Collection
	{
		$data = Plugin::getInstance()->getImports()
			->getParsedContent($this);

		if (!empty($this->dataPath)) {
			/** @var array<int, array<array-key, mixed>> $data */
			$data = data_get($data, $this->dataPath);
			return collect($data);
		}

		return $data;
	}

	/** @param array<array-key, mixed> $item */
	public function getElement(array $item): Entry
	{
		$slug = data_get($item, $this->getSlugKey());
		$title = data_get($item, $this->getTitleKey());
		$primaryKeyValue = Plugin::getInstance()->getImports()->getPrimaryKeyValue($this, data_get($item, $this->getPrimaryKeyValue()));
		$primaryKey = $this->getPrimaryKey();

		$element = Entry::find()
			->status(null)
			->section($this->getSection())
			->type($this->getEntryType())
			->$primaryKey($primaryKeyValue)
			->one();

		if (!$element) {
			$element = new Entry();
			$element->sectionId = $this->getSection()->id;
			$element->typeId = $this->getEntryType()->id;
		} else {
			if ($this->foundStrategy === FoundStrategy::SKIP) {
				throw new FoundWhileSkippingException("The entry $element->title already exists.");
			}
		}

		$element->title = $title;
		$element->slug = $slug;
		if ($primaryKey !== 'slug' && $primaryKey !== 'title') {
			$element->setFieldValue($primaryKey, $primaryKeyValue);
		}

		return $element;
	}
}