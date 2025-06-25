<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Events;

use ContentReactor\Importer\Services\Imports;
use yii\base\Event;

/**
 * Allows for possibility to additionally modify the parsed file
 * @see Imports
 */
class ParsedContentEvent extends Event
{
	/** @var array<int, array<array-key, mixed>> */
	public array $content;
	public string $filePath;
	/** @var array<int, array<array-key, mixed>> */
	public array $options;
}