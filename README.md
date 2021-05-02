# voikko-php

Work-in-progress PHP bindings for [libvoikko](https://voikko.puimula.org/) based on [PHP FFI](https://www.php.net/manual/en/book.ffi.php).

## Requirements

- PHP 7.4 or newer with [FFI](https://www.php.net/manual/en/book.ffi.php) and [Multibyte String](https://www.php.net/manual/en/book.mbstring.php) extensions enabled
- libvoikko (`libvoikko1` package in Ubuntu)
- Voikko dictionary (`voikko-fi` package in Ubuntu)

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
