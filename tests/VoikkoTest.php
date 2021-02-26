<?php

use PHPUnit\Framework\TestCase;
use Siiptuo\Voikko\Voikko;

final class VoikkoTest extends TestCase
{
    protected function setUp(): void
    {
        $this->voikko = new Voikko("fi");
    }

    public function testHyphenate(): void
    {
        $this->assertEquals(
            "   -  - - ",
            $this->voikko->hyphenate("kissammeko")
        );
    }

    public function testAnalyzeWord(): void
    {
        $this->assertEquals(
            "kissa",
            $this->voikko->analyzeWord("kissammeko")->baseform
        );
    }
}
