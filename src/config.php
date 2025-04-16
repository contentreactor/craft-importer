<?php
declare(strict_types=1);

/**
 * Importer for Craft CMS
 *
 * @link      https://contentreactor.com
 * @copyright Copyright (c) 2025 ContentReactor
 */

/**
 * Importer config.php
 *
 * This file exists to store config settings for Importer. This file can
 * be used in place, or it can be put into @craft/config/ as `craft-importer.php`
 *
 * This file is multi-environment aware as well, so you can have different
 * settings groups for each environment, just as you do for `general.php`
 */

return [
	'jsonField' => '',
	'importers' => function (): array {
		return [
		];
	},
];
