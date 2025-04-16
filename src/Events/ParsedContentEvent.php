<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Events;

use yii\base\Event;

class ParsedContentEvent extends Event
{
	/** @var array<int, array<array-key, mixed>> */
	public array $content;
	public string $filePath;
	/** @var array<int, array<array-key, mixed>> */
	public array $options;
}