<!--
SPDX-FileCopyrightText: 2021 Tuomas Siipola
SPDX-License-Identifier: CC0-1.0
-->

# voikko-php

PHP bindings for [libvoikko](https://voikko.puimula.org/) based on [PHP FFI](https://www.php.net/manual/en/book.ffi.php).

## Requirements

- PHP 7.4 or newer with [FFI](https://www.php.net/manual/en/book.ffi.php) and [Multibyte String](https://www.php.net/manual/en/book.mbstring.php) extensions enabled
- libvoikko (`libvoikko1` package in Ubuntu)
- Voikko dictionary (`voikko-fi` package in Ubuntu)

## Installation

Install via [Composer](https://getcomposer.org/):

```sh
composer require siiptuo/voikko
```

## Example

Running:

```php
$voikko = new \Siiptuo\Voikko\Voikko();
$word = "kissammeko";
echo "       word: " . $word . PHP_EOL;
echo "hyphenation: " . $voikko->hyphenate($word) . PHP_EOL;
foreach ($voikko->analyzeWord($word) as $analysis) {
    echo "  base form: " . $analysis->baseForm . PHP_EOL;
}
```

outputs:

```
       word: kissammeko
hyphenation: kis-sam-me-ko
  base form: kissa
```

Check out [API documentation](https://siiptuo.github.io/voikko-php/namespaces/siiptuo-voikko.html) for all available functionality.

## License

Like libvoikko, these bindings can be used under one of the following licenses:

- Mozilla Public License, version 1.1
- GNU General Public License, version 2 or later
- GNU Lesser General Public License, version 2.1 or later
