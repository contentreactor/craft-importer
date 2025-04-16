<?php
declare(strict_types=1);

namespace ContentReactor\Importer\Base;

enum ImporterType: string
{
	case IMPORTER_TYPE_UPLOAD = 'uploadImporter';
	case IMPORTER_TYPE_URL = 'urlImporter';
	case IMPORTER_TYPE_FILE = 'fileImporter';

	public function requiresFile(): bool
	{
		return match ($this) {
			self::IMPORTER_TYPE_UPLOAD => false,
			default => true,
		};
	}
}