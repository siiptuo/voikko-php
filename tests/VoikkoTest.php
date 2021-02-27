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

    public function testAnalyzeLifetime(): void
    {
        $this->assertEquals("kissa", $this->voikko->analyzeWord("kissammeko")[0]->baseform);
    }

    public function testAnalyzeWord(): void
    {
        $analysis = $this->voikko->analyzeWord("olin");
        $this->assertEquals(null, $analysis[-1]);
        $this->assertEquals("olka", $analysis[0]->baseform);
        $this->assertEquals("olla", $analysis[1]->baseform);
        $this->assertEquals(null, $analysis[2]);
        $this->assertEquals(null, $analysis['0']);
        $this->assertEquals(null, $analysis['X']);
    }

    public function testMorAnalyzeArrayIndices(): void
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
