<?php

/*
 * This file is part of the libvoikko PHP bindings.
 *
 * SPDX-FileCopyrightText: 2021 Tuomas Siipola
 *
 * API and documentation are based on libvoikko and its Java bindings.
 *
 * SPDX-FileCopyrightText: 2006-2010 Harri Pitkänen
 * SPDX-License-Identifier: MPL-1.1 OR GPL-2.0-or-later OR LGPL-2.1-or-later
 */

use PHPUnit\Framework\TestCase;
use Siiptuo\Voikko\Voikko;
use Siiptuo\Voikko\Exception;
use Siiptuo\Voikko\Token;
use Siiptuo\Voikko\Sentence;
use Siiptuo\Voikko\GrammarError;
use Siiptuo\Voikko\Dictionary;

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

    public function testSpell(): void
    {
        $this->assertFalse($this->voikko->spell("sydämmeeni"));
        $this->assertTrue($this->voikko->spell("sydämeeni"));
    }

    public function testSuggest(): void
    {
        $this->assertEquals(
            ['sydämeeni', 'sydänmeemi'],
            $this->voikko->suggest("sydämmeeni")
        );
    }

    public function testHyphenateDefault(): void
    {
        $this->assertEquals(
            "lin-ja-au-tom-me-ko",
            $this->voikko->hyphenate("linja-autommeko")
        );
        $this->assertEquals(
            "tä-mä-kin-kö",
            $this->voikko->hyphenate("tämäkinkö")
        );
    }

    public function testHyphenateHtml(): void
    {
        $this->assertEquals(
            "lin&shy;ja-au&shy;tom&shy;me&shy;ko",
            $this->voikko->hyphenate("linja-autommeko", "&shy;")
        );
        $this->assertEquals(
            "tä&shy;mä&shy;kin&shy;kö",
            $this->voikko->hyphenate("tämäkinkö", "&shy;")
        );
    }

    public function testHyphenationPattern(): void
    {
        $this->assertEquals(
            "   - =  -  - - ",
            $this->voikko->hyphenationPattern("linja-autommeko")
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

    public function testAnalysisNonExisting(): void
    {
        $analysis = $this->voikko->analyzeWord("kissammeko")[0];
        $this->assertNull($analysis->baseFormi);
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

    public function testNonUtf8(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Input must be UTF-8 encoded");
        $this->voikko->analyzeWord(utf8_decode('ööö'));
    }

    public function testLong(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Internal error");
        $this->voikko->hyphenate(str_repeat('ö', 1000));
    }

    public function testGrammarErrors(): void
    {
        $this->assertEquals(
            [new GrammarError(8, 5, 5, ["on"], "Remove duplicate word.")],
            $this->voikko->grammarErrors('Tämä on on testi.')
        );
    }

    public function testDictionaries(): void
    {
        $this->assertEquals(
            [new Dictionary("fi", "", "standard", "suomi (perussanasto)")],
            Voikko::dictionaries()
        );
    }

    public function testMinHyphenateSetting(): void
    {
        $this->assertEquals(
            'kis-sa',
            $this->voikko->hyphenate('kissa')
        );
        $this->voikko->setMinHyphenatedWordLength(6);
        $this->assertEquals(
            'kissa',
            $this->voikko->hyphenate('kissa')
        );
    }

    public function testUnknownHyphenateSetting(): void
    {
        $this->assertEquals(
            'ree-na-pu-la-ri',
            $this->voikko->hyphenate('reenapulari')
        );
        $this->voikko->setHyphenateUnknownWords(false);
        $this->assertEquals(
            'reenapulari',
            $this->voikko->hyphenate('reenapulari')
        );
    }
}
