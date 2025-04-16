<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Console\Controllers;

use ContentReactor\Importer\Base\ImporterType;
use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use ContentReactor\Importer\Jobs\Importers\ImporterJob;
use ContentReactor\Importer\Plugin;
use craft\console\Controller;
use craft\helpers\Queue;
use yii\console\ExitCode;

class ImportsController extends Controller
{
	public function actionTypeFile(): int
	{
		Plugin::getInstance()->getImports()->getImporters()
			->filter(fn (ImporterInterface $importer): bool => $importer->getType() === ImporterType::IMPORTER_TYPE_FILE)
			->each(function (ImporterInterface $importer): void {
				Queue::push(new ImporterJob($importer));
			});

		return ExitCode::OK;
	}

	public function actionTypeUrl(): int
	{
		Plugin::getInstance()->getImports()->getImporters()
			->filter(fn (ImporterInterface $importer): bool => $importer->getType() === ImporterType::IMPORTER_TYPE_URL)
			->each(function (ImporterInterface $importer): void {
				Queue::push(new ImporterJob($importer));
			})
		;

		return ExitCode::OK;
	}
}
