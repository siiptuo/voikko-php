<?php

use PHPUnit\Framework\TestCase;
use Siiptuo\Voikko;

final class VoikkoTest extends TestCase
{
    protected function setUp(): void
    {
        $this->voikko = new Voikko\Voikko("fi");
    }

    public function testInitializationError(): void
    {
        $this->expectException(Voikko\Exception::class);
        $this->expectExceptionMessage("Specified dictionary variant was not found");
        new Voikko\Voikko("xy");
    }

    public function testHyphenate(): void
    {
        $this->assertEquals(
            "   -  - - ",
            $this->voikko->hyphenate("kissammeko")
        );
    }

    public function testAnalyzeNotFound(): void
    {
        $this->assertNull($this->voikko->analyzeWord("xyz"));
    }

    public function testAnalyzeLifetime(): void
    {
        $this->assertEquals("kissa", $this->voikko->analyzeWord("kissammeko")[0]->baseform);
    }

    public function testMorAnalysesSetOffset(): void
    {
        $this->expectException(Voikko\Exception::class);
        $this->expectExceptionMessage("MorAnalyses is immutable");
        $analysis = $this->voikko->analyzeWord("kissammeko");
        $analysis[0] = "koira";
    }

    public function testMorAnalysesSetUnset(): void
    {
        $this->expectException(Voikko\Exception::class);
        $this->expectExceptionMessage("MorAnalyses is immutable");
        $analysis = $this->voikko->analyzeWord("kissammeko");
        unset($analysis[0]);
    }

    public function testMorAnalysesGetOffset(): void
    {
        $analysis = $this->voikko->analyzeWord("olin");
        $this->assertEquals(null, $analysis[-1]);
        $this->assertEquals("olka", $analysis[0]->baseform);
        $this->assertEquals("olla", $analysis[1]->baseform);
        $this->assertEquals(null, $analysis[2]);
        $this->assertEquals(null, $analysis['0']);
        $this->assertEquals(null, $analysis['X']);
    }

    public function testMorAnalysesOffsetExists(): void
    {
        $analysis = $this->voikko->analyzeWord("olin");
        $this->assertFalse(isset($analysis[-1]), "index -1");
        $this->assertTrue(isset($analysis[0]), "index 0");
        $this->assertTrue(isset($analysis[1]), "index 1");
        $this->assertFalse(isset($analysis[2]), "index 2");
        $this->assertFalse(isset($analysis['0']), "index '0'");
        $this->assertFalse(isset($analysis['X']), "index 'X'");
    }

    public function testMorAnalysesCountable(): void
    {
        $this->assertEquals(2, count($this->voikko->analyzeWord("olin")));
    }

    public function testMorAnalysesIterator(): void
    {
        $it = $this->voikko->analyzeWord("olin");
        for ($i = 0; $i < 2; $i++) {
            $this->assertTrue($it->valid());
            $this->assertEquals(0, $it->key());
            $this->assertEquals("olka", $it->current()->baseform);
            $it->next();
            $this->assertTrue($it->valid());
            $this->assertEquals(1, $it->key());
            $this->assertEquals("olla", $it->current()->baseform);
            $it->next();
            $this->assertFalse($it->valid());
            $this->assertEquals(2, $it->key());
            $this->assertEquals(null, $it->current());
            $it->rewind();
        }
    }
}
