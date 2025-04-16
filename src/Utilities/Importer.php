<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Utilities;

use Craft;
use craft\base\Utility;

/**
 * Importer utility
 */
class Importer extends Utility
{
	public static function displayName(): string
	{
		return Craft::t('craft-importer', 'Importer');
	}

	public static function id(): string
	{
		return 'importer';
	}

	public static function iconPath(): ?string
	{
		return Craft::getAlias('@importer/Icons/Upload.svg');
	}

	public static function contentHtml(): string
	{
		//Craft::$app->getView()->registerAssetBundle(ParserBundleAsset::class);
		return Craft::$app->getView()->renderTemplate('craft-importer/utilities/importer.twig');
	}
}
