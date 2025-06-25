# Vienna Importer


## Installation
```bash
# go to the project directory
cd /path/to/my/project

# tell Composer to load the plugin
composer require contentreactor/craft-importer

# tell Craft to install the required plugin
./craft plugin/install craft-importer
```

### DDEV Installation
If you're using [DDEV](https://ddev.com) for your local development, you can install the plugin like so:
```bash
cd /path/to/my/project

ddev composer require contentreactor/craft-importer

ddev craft plugin/install craft-importer
```

## Configuration
The Importer uses a `jsonField` setting to indicate where the content is being stored. Individual importers can also define their own fields. 
Add configurations to the `importers` callback of the craft-importer config file `config/craft-importer.php`. Here's an example:
```php
new \ContentReactor\Importer\Importers\BaseFileImporter(
	section: 'yourSectionHandle',
	importerType: \ContentReactor\Importer\Base\ImporterType::IMPORTER_TYPE_FILE,
	foundStrategy: \ContentReactor\Importer\Base\FoundStrategy::OVERWRITE,
	notFoundStrategy: \ContentReactor\Importer\Base\NotFoundStrategy::DELETE,
	filePath: '/full/system/path/to/your/file.xml', // <- the extension must match the fileType, or be compatible with the MIME type
	fileType: \ContentReactor\Importer\Services\Imports::FILE_XML,
	dataPath: 'comma.separated.nesting.is.supported',
	slugKey: 'slug-key',
	titleKey: 'titleKey',
	primaryKey: 'primary_key', // if 'slug', the `primaryKeyValue` will be tested as slugified
	primaryKeyValue: 'value used to look for existing elements', // relevant for `foundStrategy` and `notFoundStrategy`
),
```

The same configuration can be replaced by a more defined custom class. The class must implement the `\ContentReactor\Importer\Contracts\Importers\ImporterInterface`
```php
new \Your\Namespace\Importer(
	section: 'anotherSectionHandle',
	filePath: 'www.example.com/awesome-import-data.json',
	// If the constructor requires only two values, it means the rest of them are being pre-determined or have default fallback values
),
```

More details and information about each property used by the `ImporterInterface` can be found in the annotation of the constructor in `ContentReactor\Importer\Traits\Importer`.

Since the data is intended to be imported and stored in a content field, if it's supposed to be visible, but not editable by content editors, the configuration for NYStudio107's Code Editor Field offers a neat way to make the field readonly.
```json
{
    "readOnly": true
}
```