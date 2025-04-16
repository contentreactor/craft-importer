<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Importers;

use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use ContentReactor\Importer\Plugin;
use ContentReactor\Importer\Traits\Importer;
use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry;

class BaseFileImporter implements ImporterInterface
{
	use Importer;

	public function import(mixed $item, string $title = '', string $slug = ''): void
	{
		$slug = $slug ?: $item[$this->getSlugKey()];
		$title = $title ?: $item[$this->getTitleKey()];
		$element = $this->getElement($item);

		$element->title = $title;
		$element->slug = $slug;

		$fieldValue = json_encode($item, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
		$element->setFieldValue($this->field ?: Plugin::getInstance()->getSettings()->jsonField, $fieldValue);
		if (!Craft::$app->getElements()->saveElement($element, updateSearchIndex: false)) {
			Craft::info([
				$element->title,
				$element->getErrors(),
				$item,
			]);
		}
	}

	public function getElementType(): string
	{
		return Entry::class;
	}

	/**
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