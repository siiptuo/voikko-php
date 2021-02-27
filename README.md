# voikko-php

Work-in-progress PHP bindings for [libvoikko](https://voikko.puimula.org/) based on [PHP FFI](https://www.php.net/manual/en/book.ffi.php).

## Requirements

- PHP 7.4 or newer
- libvoikko (`libvoikko0` package in Ubuntu)
- Voikko dictionary (`voikko-fi` package in Ubuntu)

## Example

```php
$voikko = new \Siiptuo\Voikko\Voikko("fi");
$word = "kissammeko";
echo "       word: " . $word . PHP_EOL;
echo "hyphenation: " . $voikko->hyphenate($word) . PHP_EOL;
foreach ($voikko->analyzeWord($word) as $analysis) {
    echo "   baseform: " . $analysis->baseform . PHP_EOL;
}
```
