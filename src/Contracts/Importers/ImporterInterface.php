<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Contracts\Importers;

use ContentReactor\Importer\Base\ImporterType;
use ContentReactor\Importer\Models\Settings;
use craft\models\{
	EntryType,
	Section,
};
use Illuminate\Support\Collection;

interface ImporterInterface
{
	/**
	 * Performs the central import logic. The `$item` is a member of `getData()` Collection
	 *
	 * @param mixed $item
	 * @param string $title
	 * @param string $slug
	 * @return void
	 * @see getData
	 */
	public function import(mixed $item): void;

	/**
	 * Provides the structured data that will be imported
	 *
	 * @template T
	 * @return Collection<array-key, T>
	 * @see import
	 */
	public function getData(): Collection;

	/**
	 * Returns the dot-separated array key in individual data items which will be used for the title of the saved element
	 */
	public function getTitleKey(): string;

	/**
	 * Returns the dot-separated array key in individual data items which will be used for the slug of the saved element
	 */
	public function getSlugKey(): string;

	/**
	 * Returns the attribute or field handle of the saved elements which is used to match the existing content
	 */
	public function getPrimaryKey(): string;

	/**
	 * Returns the dot-separated array key in individual data items which is used with `$primaryKey` to match the existing content
	 *
	 * @see getPrimaryKey
	 */
	public function getPrimaryKeyValue(): string;

	/**
	 * Returns the handle of the field where the contents are stored. If left empty, it uses the globally configured field
	 *
	 * @see Settings::$jsonField
	 */
	public function getFieldName(): string;

	/**
	 * Returns the instance of the `Section` `Model` where the stored content is being saved
	 */
	public function getSection(): Section;

	/**
	 * Returns the instance of the `EntryType` `Model` where the stored content is being saved
	 */
	public function getEntryType(): EntryType;

	/**
	 * Returns the type of source, ie, how is data sourced
	 *
	 * @return ImporterType
	 * @see ImporterType::IMPORTER_TYPE_FILE
	 * @see ImporterType::IMPORTER_TYPE_UPLOAD
	 * @see ImporterType::IMPORTER_TYPE_URL Default option
	 * @see getFilePath
	 */
	public function getType(): ImporterType;

	/**
	 * Returns the location of the imported file. The value must always be an absolute path
	 *
	 * Yii2/CraftCMS Aliases can be used
	 *
	 * @return string
	 * @see Craft::getAlias
	 * @see Craft::setAlias
	 */
	public function getFilePath(): string;

	/**
	 * Returns the MIME type of the imported file.
	 *
	 * @see Imports::FILE_JSON Default option
	 * @see Imports::FILE_XML
	 * @see Imports::FILE_CSV
	 * @see Imports::FILE_XLS
	 */
	public function getFileType(): string;
}
