<?php
require 'voikko.php';
$voikko = new Voikko("fi");
$word = "kissammeko";
echo "       word: " . $word . PHP_EOL;
echo "hyphenation: " . $voikko->hyphenate($word) . PHP_EOL;
echo "   baseform: " . $voikko->analyzeWord($word)->baseform . PHP_EOL;
