# Kafka Bus Outbox for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/micromus/kafka-bus-outbox.svg?style=flat-square)](https://packagist.org/packages/micromus/kafka-bus-outbox)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/micromus/kafka-bus-outbox/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/micromus/kafka-bus-outbox/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style](https://img.shields.io/github/actions/workflow/status/micromus/kafka-bus-outbox/php-code-style.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/micromus/kafka-bus-outbox/actions?query=workflow%3A"PHP+Code+Style"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/micromus/kafka-bus-outbox.svg?style=flat-square)](https://packagist.org/packages/micromus/kafka-bus-outbox)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require micromus/kafka-bus-outbox
```

## Usage

```php
$kafkaBus = new Micromus\KafkaBus();
echo $kafkaBus->echoPhrase('Hello, Micromus!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [kEERill](https://github.com/kEERill)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
