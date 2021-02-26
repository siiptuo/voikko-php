# voikko-php

Work-in-progress PHP bindings for [libvoikko](https://voikko.puimula.org/) based on [PHP FFI](https://www.php.net/manual/en/book.ffi.php).

## Example

```php
$voikko = new \Siiptuo\Voikko\Voikko("fi");
$word = "kissammeko";
echo "       word: " . $word . PHP_EOL;
echo "hyphenation: " . $voikko->hyphenate($word) . PHP_EOL;
echo "   baseform: " . $voikko->analyzeWord($word)[0]->baseform . PHP_EOL;
```
