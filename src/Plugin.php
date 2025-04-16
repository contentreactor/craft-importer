<?php
declare(strict_types=1);

namespace ContentReactor\Importer;

use ContentReactor\Importer\Models\Settings;
use ContentReactor\Importer\Services\{
	Imports as ImportsService,
	Spreadsheets as SpreadsheetsService,
};
use ContentReactor\Importer\Traits\Services;
use ContentReactor\Importer\Utilities\Importer;
use ContentReactor\Importer\Web\Twig\ImporterVariable;
use Craft;
use craft\base\{
	Model,
	Plugin as BasePlugin,
};
use craft\events\{
	RegisterComponentTypesEvent,
	RegisterTemplateRootsEvent,
};
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use yii\base\Event;

/**
 * Importer Imports plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @method ImportsService getImports()
 * @method SpreadsheetsService getSpreadsheets()
 * @author ContentReactor <support@contentreactor.com>
 * @copyright ContentReactor
 * @license MIT
 */
class Plugin extends BasePlugin
{
	use Services;

	//public bool $hasCpSettings = true;

	/**
	 * @return array{
	 *     components: array<string, class-string>
	 * }
	 */
	public static function config(): array
	{
		return [
			'components' => [
				'imports' => ImportsService::class,
				'spreadsheets' => SpreadsheetsService::class,
			],
		];
	}

	public function init(): void
	{
		parent::init();

		if (Craft::$app->getRequest()->getIsConsoleRequest()) {
			$this->controllerNamespace = __NAMESPACE__ . '\\Console\\Controllers';
			Craft::$app->getConfig()->getGeneral()
				->generateTransformsBeforePageLoad();
		} else {
			$this->controllerNamespace = __NAMESPACE__ . '\\Controllers';
		}

		Craft::$app->onInit(function () {
			Craft::setAlias('@importer', __DIR__);
		});

		$this->attachEventHandlers();
	}

	protected function createSettingsModel(): ?Model
	{
		return Craft::createObject(Settings::class);
	}

	protected function settingsHtml(): ?string
	{
		return Craft::$app->getView()->renderTemplate('craft-importer/cp/_settings.twig', [
			'plugin' => $this,
			'settings' => $this->getSettings(),
		]);
	}

	protected function attachEventHandlers(): void
	{
		Event::on(
			Utilities::class,
			Utilities::EVENT_REGISTER_UTILITY_TYPES,
			static function (RegisterComponentTypesEvent $event): void {
				$event->types[] = Importer::class;
			}
		);

		Event::on(
			View::class,
			View::EVENT_REGISTER_CP_TEMPLATE_ROOTS,
			static function (RegisterTemplateRootsEvent $event): void {
				$event->roots['craft-importer'] = __DIR__ . '/Templates';
			}
		);

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			function (Event $event): void {
				$event->sender->set('importer', ImporterVariable::class);
			}
		);
	}
}
