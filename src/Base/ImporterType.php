<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Base;

use ContentReactor\Importer\Contracts\Importers\ImporterInterface;

enum ImporterType: string
{
	/**
	 * @see ImporterInterface::getFilePath() expects a local file path
	 */
	case IMPORTER_TYPE_FILE = 'fileImporter';
	/**
	 * This option is used in the background for dashboard uploads
	 * @see ImporterInterface::getFilePath() expects a remote url
	 */
	case IMPORTER_TYPE_UPLOAD = 'uploadImporter';
	/**
	 * @see ImporterInterface::getFilePath() expects a remote url. Default option
	 */
	case IMPORTER_TYPE_URL = 'urlImporter';

	public function requiresFile(): bool
	{
		return match ($this) {
			self::IMPORTER_TYPE_UPLOAD => false,
			default => true,
		};
	}
}