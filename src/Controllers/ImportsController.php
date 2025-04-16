<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Controllers;

use ContentReactor\Importer\Base\ImporterType;
use ContentReactor\Importer\Contracts\Importers\ImporterInterface;
use ContentReactor\Importer\Jobs\Importers\ImporterJob;
use ContentReactor\Importer\Plugin;
use craft\helpers\Queue;
use craft\web\Controller;
use yii\web\Response;

class ImportsController extends Controller
{
	protected array|int|bool $allowAnonymous = true;

	public function actionTypeUpload(): Response
	{
		$count = Plugin::getInstance()->getImports()->getImporters()
			->filter(fn (ImporterInterface $importer): bool => $importer->getType() === ImporterType::IMPORTER_TYPE_URL)
			->each(function (ImporterInterface $importer): void {
				Queue::push(new ImporterJob($importer));
			})
			->count();
		;

		return $this->asJson([
			'queuedJobs' => $count,
		]);
	}
}