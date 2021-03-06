<?php

use PHPUnit\Framework\TestCase;
use Siiptuo\Voikko\{Voikko, Exception, Token, Sentence};

final class VoikkoTest extends TestCase
{
    private Voikko $voikko;

    protected function setUp(): void
    {
        $this->voikko = new Voikko();
    }

    public function testInitializationError(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Specified dictionary variant was not found");
        new Voikko("xy");
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
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("MorAnalyses is immutable");
        $analysis = $this->voikko->analyzeWord("kissammeko");
        $analysis[0] = "koira";
    }

    public function testMorAnalysesSetUnset(): void
    {
        $this->expectException(Exception::class);
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

    public function testAnalyzeWordKeys(): void
    {
        $this->assertEquals([
            'BASEFORM',
            'CLASS',
            'FSTOUTPUT',
            'KYSYMYSLIITE',
            'NUMBER',
            'POSSESSIVE',
            'SIJAMUOTO',
            'STRUCTURE',
            'WORDBASES',
        ], $this->voikko->analyzeWord("kissammeko")[0]->keys());
    }

    public function testAnalyzeWordToArray(): void
    {
        $this->assertEquals([
            "BASEFORM" => "kissa",
            "CLASS" => "nimisana",
            "FSTOUTPUT" => "[Ln][Xp]kissa[X]kiss[Sn][Ny]a[O1m]mme[Fko][Ef]ko",
            "NUMBER" => "singular",
            "POSSESSIVE" => "1p",
            "SIJAMUOTO" => "nimento",
            "STRUCTURE" => "=pppppppppp",
            "WORDBASES" => "+kissa(kissa)",
            'KYSYMYSLIITE' => 'true',
        ], $this->voikko->analyzeWord("kissammeko")[0]->toArray());
    }

    public function testTokens(): void
    {
        $this->assertEquals([
            new Token(Token::WORD, 'Tämä'),
            new Token(Token::WHITESPACE, ' '),
            new Token(Token::WORD, 'on'),
            new Token(Token::WHITESPACE, ' '),
            new Token(Token::WORD, 'testi'),
            new Token(Token::PUNCTUATION, '.'),
        ], $this->voikko->tokens('Tämä on testi.'));
    }

    public function testSentences(): void
    {
        $this->assertEquals([
            new Sentence(Sentence::PROBABLE, 'Tämä on ensimmäinen lause. '),
            new Sentence(Sentence::NONE, 'Tämä on toinen lause.'),
        ], $this->voikko->sentences('Tämä on ensimmäinen lause. Tämä on toinen lause.'));
    }
}
