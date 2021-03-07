<?php

use PHPUnit\Framework\TestCase;
use Siiptuo\Voikko\Voikko;
use Siiptuo\Voikko\Exception;
use Siiptuo\Voikko\Token;
use Siiptuo\Voikko\Sentence;

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
        $this->assertEquals(
            [],
            $this->voikko->analyzeWord("xyz")
        );
    }

    public function testAnalyzeWord(): void
    {
        $this->assertEquals(
            "kissa",
            $this->voikko->analyzeWord("kissammeko")[0]->baseForm
        );
    }

    public function testAnalysisSet(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot set property baseForm. The object is immutable.");
        $analysis = $this->voikko->analyzeWord("kissammeko")[0];
        $analysis->baseForm = 'koira';
    }

    public function testAnalysisUnset(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Cannot unset property baseForm. The object is immutable.");
        $analysis = $this->voikko->analyzeWord("kissammeko")[0];
        unset($analysis->baseForm);
    }

    public function testAnalysisIsset(): void
    {
        $analysis = $this->voikko->analyzeWord("kissammeko")[0];
        $this->assertTrue(isset($analysis->baseForm));
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
