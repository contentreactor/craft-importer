<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Importers;

use ContentReactor\Importer\Base\FoundStrategy;
use ContentReactor\Importer\Base\ImporterType;
use ContentReactor\Importer\Base\NotFoundStrategy;
use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use ContentReactor\Importer\Plugin;
use ContentReactor\Importer\Traits\Importer;
use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry;
use craft\errors\ElementException;
use craft\errors\ElementNotFoundException;
use craft\helpers\App;
use craft\models\EntryType;
use craft\models\Section;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Base implementation of the `ImporterInterface`
 * @throws ElementNotFoundException
 * @throws Exception
 * @throws InvalidConfigException
 * @throws Throwable
 * @see Importer
 *
 * @property string|Section $section Section where the stored content is being saved
 * @property string|EntryType $entryType Entry Type where the stored content is being saved. If left empty, the first type of the Section is used
 * @property string|ImporterType $importerType Specifies the type of source. The available options are:
 *
 *  - `fileImporter` - The [[$filePath]] expects a local file path
 *  - `urlImporter` - The [[$filePath]] expects a remote url. This is the default option
 *  - `uploadImporter` - This option is used in the background for dashboard uploads
 *
 * Each of the listed options can be referenced by a case of ImporterType enum
 * @property string $filePath A path/url of the imported file. The value must always be an absolute path. Aliases can be used
 * @property string $fileType Valid MIME type of the imported file. Allowed types are:
 *
 *  - \ContentReactor\Importer\Services\Imports::FILE_JSON
 *  - \ContentReactor\Importer\Services\Imports::FILE_XML
 *  - \ContentReactor\Importer\Services\Imports::FILE_CSV
 *  - \ContentReactor\Importer\Services\Imports::FILE_XLS
 * @property string $dataPath Defines the dot-separated path to the content within the parsed data, eg. 'rss.channel.item', 'data.content'.
 *
 * If left empty, it assumes the content is in the root of the file
 *
 * @property string|FoundStrategy $foundStrategy Specifies the way to handle matched elements. The available options are:
 *
 *  - `overwrite` - If found, updates the contents of the element. This is the default option
 *  - `skip` - If found, element is left untouched
 *
 *   Each of the listed options can be referenced by a case of FoundStrategy enum
 * @property string|NotFoundStrategy $notFoundStrategy Specifies how to handle the elements not found in the imported data. The available options are:
 *
 *  - `ignore` - Doesn't check for missing elements. This is the default option
 *  - `disable` - If an existing element doesn't match to any newly imported ones, it is disabled.
 *  - `delete` - If an existing element doesn't match to any newly imported ones, it is deleted.
 *
 *   Each of the listed options can be referenced by a case of NotFoundStrategy enum
 * @property string $primaryKey Defines the attribute or field handle of the saved elements which is used to match the existing content
 * @property string $primaryKeyValue Defines the dot-separated array key in individual data items which is used with [[$primaryKey]] to match the existing content
 * @property string $slugKey Defines the dot-separated array key in individual data items which will be used for the slug of the saved element
 * @property string $titleKey Defines the dot-separated array key in individual data items which will be used for the title of the saved element
 * @property string $field Defines the handle of the field where the contents are stored. If left empty, it uses the globally configured field
 */
class BaseFileImporter implements ImporterInterface
{
	use Importer;

	public function import(mixed $item): void
	{
		$element = $this->getElement($item);

		$fieldValue = json_encode($item, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
		$element->setFieldValue($this->field ?: Plugin::getInstance()->getSettings()->jsonField, $fieldValue);
		if (!Craft::$app->getElements()->saveElement($element, updateSearchIndex: false)) {
			if (App::devMode()) {
				Craft::error([
					$element->title,
					$element->getErrors(),
					$item,
				]);
			}
			throw new ElementException($element, 'Could not save element');
		}
	}

	/**
	 * Currently not used
	 *
	 * @return string
	 */
	public function getElementType(): string
	{
		return Entry::class;
	}

	/**
	 * Currently not used
	 *
	 * @param string $primaryKeyValue
	 * @return ElementInterface
	 */
	private function matchElement(string $primaryKeyValue): ElementInterface
	{
		$existingFeature = Entry::find()
			->status(null)
			->{$this->getPrimaryKey()}($primaryKeyValue)
			->one();

		if ($existingFeature) {
			return $existingFeature;
		}

		$element = new Entry();
		$element->sectionId = $this->getSection()->id;
		$element->typeId = $this->getEntryType()->id;
		$element->{$this->getPrimaryKey()} = $primaryKeyValue;

		return $element;
	}
}