# Translate Spreadsheet

[![Latest Version on Packagist](https://img.shields.io/packagist/v/shishima/laravel-translate-spreadsheet.svg?style=flat-square)](https://packagist.org/packages/shishima/laravel-translate-spreadsheet)
[![Total Downloads](https://img.shields.io/packagist/dt/shishima/laravel-translate-spreadsheet.svg?style=flat-square)](https://packagist.org/packages/shishima/laravel-translate-spreadsheet)

This package serves the purpose of translating the contents within the spreadsheet file. It facilitates the transition from the original language to your preferred language

## Installation

You can install the package via composer:

```bash
composer require shishima/laravel-translate-spreadsheet
```

### Publish config

    php artisan vendor:publish --provider="Shishima\TranslateSpreadsheet\TranslateSpreadsheetServiceProvider"

After publishing the configuration file, you can edit the app/config/translate-spreadsheet.php file to customize the settings.

## Usage
### translate
The file can be a path to a file on the system
```php
use Shishima\TranslateSpreadsheet\Facades\TranslateSpreadsheet;

$fileInput = public_path('demo.xlsx');
TranslateSpreadsheet::translate($fileInput);
```
Or it could be a file retrieved from the `request` when using the `POST` method in a form submit
with `<input type="file">`.

```php
TranslateSpreadsheet::translate($request->file('file'));
```
### setTransTarget
To set the desired translation language target

```php
TranslateSpreadsheet::setTransTarget('en')->translate($file);
```

### setTransSource
To set the desired translation language source

```php
TranslateSpreadsheet::setTransSource('en')->translate($file);
```
__IMPORTANT!__ Pass in `null` if you want to use language detection


### setShouldRemoveSheet
Clear the current sheets after translation is complete. The parameter passed to the method is `true/false`.

```php
TranslateSpreadsheet::setShouldRemoveSheet(true)->translate($file);
```

### setOutputDir
Directory to store files after translation

```php
TranslateSpreadsheet::setOutputDir('translate/')->translate($file);
```
__IMPORTANT!__ The file will be stored in the `public` directory, not in the `storage` directory.

### setCloneSheetPosition
Position of the cloned sheets

The parameter passed in is enum `ClonePosition`. You can refer to it in the file Enumerations/ClonePosition.php for more detail.

```php
use Shishima\TranslateSpreadsheet\Enumerations\ClonePosition;

TranslateSpreadsheet::setCloneSheetPosition(ClonePosition::AppendLastSheet)->translate($file);
```

### highlightSheet
Sheets will be highlighted after export

```php

TranslateSpreadsheet::highlightSheet(true)->translate($file);
```

### translateSheetName
Sheets Name will be translated after export

```php

TranslateSpreadsheet::translateSheetName(true)->translate($file);
```

### enableDebug
For logging translated sentences into a log file for debugging purposes

```php

TranslateSpreadsheet::enableDebug()->translate($file);
```

To disable this feature, pass `false` as the argument.

## Translate Engine
The package supports two translation engines: Google Translate and Gemini

It can be installed through the `translate_engine` setting in the config file

Alternatively, you can use the setTranslateEngine function.
The parameter to be passed is an Enum TranslateEngine, which can be either `TranslateEngine::Gemini` or `TranslateEngine::Google`

```php

use Shishima\TranslateSpreadsheet\Enumerations\TranslateEngine;

TranslateSpreadsheet::setTranslateEngine(TranslateEngine::Gemini)->translate($file);
```

### Google Translate
No need for setting

### Gemini
This functionality has been developed from [google-gemini-php/laravel](https://github.com/google-gemini-php/laravel) library

Therefore, please install this package before use

### Testing

```bash
composer test
```

### Demo
Utilize the repository below as a reference for how to use it.

https://github.com/shishima123/laravel-translate-spreadsheet-demo

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email shishima21@gmail.com instead of using the issue tracker.

## Credits

-   [Phuoc Nguyen](https://github.com/shishima)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).

## Special Thanks
- [stichoza/google-translate-php](https://github.com/Stichoza/google-translate-php)
- [phpoffice/phpspreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)
