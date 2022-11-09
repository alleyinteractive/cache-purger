# cache-collector

Stable tag: 0.1.0

Requires at least: 5.9

Tested up to: 5.9

Requires PHP: 7.4

License: GPL v2 or later

Tags: alleyinteractive, cache-collector

Contributors: srtfisher

[![Coding Standards](https://github.com/alleyinteractive/cache-collector/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/alleyinteractive/cache-collector/actions/workflows/coding-standards.yml)
[![Testing Suite](https://github.com/alleyinteractive/cache-collector/actions/workflows/unit-test.yml/badge.svg)](https://github.com/alleyinteractive/cache-collector/actions/workflows/unit-test.yml)

Dynamic cache key collector for easy purging.

## Installation

You can install the package via composer:

```bash
composer require alleyinteractive/cache-collector
```

## Usage

Activate the plugin in WordPress and use it like so:

```php
$plugin = Cache_Collector\Cache_Collector\Cache_Collector();
$plugin->perform_magic();
```

## Testing

Run `npm run test` to run Jest tests against JavaScript files. Run
`npm run test:watch` to keep the test runner open and watching for changes.

Run `npm run lint` to run ESLint against all JavaScript files. Linting will also
happen when running development or production builds.

Run `composer test` to run tests against PHPUnit and the PHP code in the plugin.

## Updating Dependencies

To update `@wordpress` dependencies, simply execute:

```sh
npm run update-dependencies WPVERSION
```

Where `WPVERSION` is the version of WordPress you are targeting. The version
must include both the major and patch version (e.g., `5.9.3`). For example:

```sh
npm run update-dependencies 5.9.3
```

The versions are drawn from tags on
[wordpress-develop](https://github.com/WordPress/wordpress-develop/tags).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.co/careers/).

- [Sean Fisher](https://github.com/Sean Fisher)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.