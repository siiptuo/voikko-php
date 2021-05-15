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

namespace Siiptuo\Voikko;

use \FFI;

/**
 * Main class of the library.
 */
class Voikko
{
    /** @internal */
    private static function getFFI(string $libraryPath): FFI
    {
        return FFI::cdef(
            "
            struct VoikkoHandle;
            struct VoikkoHandle * voikkoInit(const char ** error, const char * langcode,
                                             const char * path);
            void voikkoTerminate(struct VoikkoHandle * handle);
            char * voikkoHyphenateCstr(struct VoikkoHandle * handle, const char * word);
            struct voikko_mor_analysis;
            struct voikko_mor_analysis ** voikkoAnalyzeWordCstr(
                                          struct VoikkoHandle * handle, const char * word);
            void voikko_free_mor_analysis(struct voikko_mor_analysis ** analysis);
            const char ** voikko_mor_analysis_keys(const struct voikko_mor_analysis * analysis);
            char * voikko_mor_analysis_value_cstr(
                            const struct voikko_mor_analysis * analysis,
                            const char * key);
            void voikko_free_mor_analysis_value_cstr(char * analysis_value);
            enum voikko_token_type {TOKEN_NONE, TOKEN_WORD, TOKEN_PUNCTUATION, TOKEN_WHITESPACE, TOKEN_UNKNOWN};
            enum voikko_token_type voikkoNextTokenCstr(struct VoikkoHandle * handle, const char * text,
                                   size_t textlen, size_t * tokenlen);
            enum voikko_sentence_type {SENTENCE_NONE, SENTENCE_NO_START, SENTENCE_PROBABLE, SENTENCE_POSSIBLE};
            enum voikko_sentence_type voikkoNextSentenceStartCstr(struct VoikkoHandle * handle,
                                      const char * text, size_t textlen, size_t * sentencelen);
            char ** voikkoSuggestCstr(struct VoikkoHandle * handle, const char * word);
            int voikkoSpellCstr(struct VoikkoHandle * handle, const char * word);
            void voikkoFreeCstr(char * cstr);
            void voikkoFreeCstrArray(char ** cstrArray);
            struct VoikkoGrammarError;
            struct VoikkoGrammarError * voikkoNextGrammarErrorCstr(struct VoikkoHandle * handle,
                const char * text, size_t textlen, size_t startpos, int skiperrors);
            int voikkoGetGrammarErrorCode(const struct VoikkoGrammarError * error);
            size_t voikkoGetGrammarErrorStartPos(const struct VoikkoGrammarError * error);
            size_t voikkoGetGrammarErrorLength(const struct VoikkoGrammarError * error);
            const char ** voikkoGetGrammarErrorSuggestions(const struct VoikkoGrammarError * error);
            void voikkoFreeGrammarError(struct VoikkoGrammarError * error);
            char * voikkoGetGrammarErrorShortDescription(struct VoikkoGrammarError * error, const char * language);
            void voikkoFreeErrorMessageCstr(char * message);
            struct voikko_dict;
            struct voikko_dict ** voikko_list_dicts(const char * path);
            void voikko_free_dicts(struct voikko_dict ** dicts);
            const char * voikko_dict_language(const struct voikko_dict * dict);
            const char * voikko_dict_script(const struct voikko_dict * dict);
            const char * voikko_dict_variant(const struct voikko_dict * dict);
            const char * voikko_dict_description(const struct voikko_dict * dict);
            int voikkoSetBooleanOption(struct VoikkoHandle * handle, int option, int value);
            int voikkoSetIntegerOption(struct VoikkoHandle * handle, int option, int value);
            ",
            $libraryPath
        );
    }

    /** @internal */
    private FFI\CData $voikko;

    /** @internal */
    private FFI $ffi;

    /**
     * Initialises the library for use in the specified language, adding an extra directory to the standard dictionary search path.
     *
     * @param string $languageCode BCP 47 language tag for the language to be used. Private use subtags can be used to specify the dictionary variant.
     * @param string $dictionaryPath Path to a directory from which dictionary files should be searched first before looking into the standard dictionary locations. If null, no additional search path will be used.
     * @param string $libraryPath Path to libvoikko shared library.
     *
     * @throws Exception If initialization failed.
     */
    public function __construct(string $languageCode = 'fi', string $dictionaryPath = null, string $libraryPath = "libvoikko.so.1")
    {
        $this->ffi = self::getFFI($libraryPath);
        $error = FFI::new("char*");
        $handle = $this->ffi->voikkoInit(FFI::addr($error), $languageCode, $dictionaryPath);
        if (!FFI::isNull($error)) {
            throw new Exception(FFI::string($error));
        }
        $this->voikko = $handle;
    }

    public function __destruct()
    {
        $this->ffi->voikkoTerminate($this->voikko);
    }

    /** @internal */
    private function validateInput(string $input): void
    {
        if (strpos($input, "\0") !== false) {
            throw new Exception('Input must not contain null character');
        }
        if (!mb_check_encoding($input, 'UTF-8')) {
            throw new Exception('Input must be UTF-8 encoded');
        }
    }

    /**
     * Checks the spelling of the given word.
     *
     * @param string $word Word to check
     * @return bool Whether the spelling is correct or not
     *
     * @throws Exception on error
     */
    public function spell(string $word): bool
    {
        $this->validateInput($word);
        $result = $this->ffi->voikkoSpellCstr($this->voikko, $word);
        if ($result !== 0 && $result !== 1) {
            throw new Exception('Internal error');
        }
        return $result === 1;
    }

    /**
     * Finds suggested correct spellings for the given word.
     *
     * @param string $word Word to find suggestions for
     * @return array<int, string> Array of suggestions
     */
    public function suggest(string $word): array
    {
        $this->validateInput($word);
        $result = [];
        $suggestions = $this->ffi->voikkoSuggestCstr($this->voikko, $word);
        if (is_null($suggestions) || FFI::isNull($suggestions[0])) {
            return $result;
        }
        $i = 0;
        while (!FFI::isNull($suggestions[$i])) {
            $result[] = FFI::string($suggestions[$i]);
            $i++;
        }
        $this->ffi->voikkoFreeCstrArray($suggestions);
        return $result;
    }

    /**
     * Hyphenates the given word.
     *
     * @param string $word word to hyphenate
     * @param string $hyphen character string to insert at hyphenation positions
     * @param bool $allowContextChanges Whether hyphens may be inserted even if they alter the word in unhyphenated form.
     * @return string Hyphenated word
     */
    public function hyphenate(string $word, string $hyphen = '-', $allowContextChanges = true): string
    {
        $result = '';
        $pattern = $this->hyphenationPattern($word);
        for ($i = 0; $i < mb_strlen($word, 'UTF-8'); $i++) {
            if ($pattern[$i] == '-') {
                $result .= $hyphen;
                $result .= mb_substr($word, $i, 1, 'UTF-8');
            } elseif ($pattern[$i] == ' ' || !$allowContextChanges) {
                $result .= mb_substr($word, $i, 1, 'UTF-8');
            } elseif ($pattern[$i] == '=') {
                $result .= mb_substr($word, $i, 1, 'UTF-8') == '-' ? '-' : $hyphen;
            }
        }
        return $result;
    }

    /**
     * Return hyphenation pattern for the given word.
     *
     * The hyphenation pattern uses the following notation:
     *
     * ```
     * ' ' = no hyphenation at this character
     * '-' = hyphenation point (character at this position
     *       is preserved in the hyphenated form)
     * '=' = hyphenation point (character at this position
     *       is replaced by the hyphen)
     * ```
     *
     * @param string $word Word to hyphenate
     * @return string Hyphenation pattern
     */
    public function hyphenationPattern(string $word): string
    {
        $this->validateInput($word);
        $pattern = $this->ffi->voikkoHyphenateCstr($this->voikko, $word);
        if (is_null($pattern)) {
            throw new Exception("Internal error");
        }
        $result = FFI::string($pattern);
        $this->ffi->voikkoFreeCstr($pattern);
        return $result;
    }

    /**
     * Analyzes the morphology of given word.
     *
     * @param string $word Word to be analyzed.
     * @return array<int, Analysis> Array of analysis results. Empty array is returned for unknown words.
     */
    public function analyzeWord(string $word): array
    {
        $this->validateInput($word);
        $result = [];
        $analyses = $this->ffi->voikkoAnalyzeWordCstr($this->voikko, $word);
        if (is_null($analyses) || FFI::isNull($analyses[0])) {
            return $result;
        }
        $i = 0;
        while (!FFI::isNull($analyses[$i])) {
            $data = [];
            $keys = $this->ffi->voikko_mor_analysis_keys($analyses[$i]);
            $j = 0;
            while (!FFI::isNull($keys[$j])) {
                $value = $this->ffi->voikko_mor_analysis_value_cstr($analyses[$i], $keys[$j]);
                $data[FFI::string($keys[$j])] = FFI::string($value);
                $this->ffi->voikko_free_mor_analysis_value_cstr($value);
                $j++;
            }
            $result[] = new Analysis($data);
            $i++;
        }
        $this->ffi->voikko_free_mor_analysis($analyses);
        return $result;
    }

    /**
     * Split the given text into tokens.
     *
     * @param string $text Text to split into tokens.
     * @return array<int, Token> Array of tokens
     */
    public function tokens(string $text): array
    {
        $this->validateInput($text);
        $tokens = [];
        $tokenLength = FFI::new("size_t");
        while (strlen($text) > 0) {
            $type = $this->ffi->voikkoNextTokenCstr($this->voikko, $text, strlen($text), FFI::addr($tokenLength));
            $token = mb_substr($text, 0, $tokenLength->cdata, 'UTF-8');
            $text = substr($text, strlen($token));
            $tokens[] = new Token($type, $token);
        }
        return $tokens;
    }

    /**
     * Split the given text into sentences.
     *
     * @param string $text Text to split into sentences.
     * @return array<int, Sentence> Array of sentences
     */
    public function sentences(string $text): array
    {
        $this->validateInput($text);
        $sentences = [];
        $sentenceLength = FFI::new("size_t");
        while (strlen($text) > 0) {
            $type = $this->ffi->voikkoNextSentenceStartCstr($this->voikko, $text, strlen($text), FFI::addr($sentenceLength));
            $sentence = mb_substr($text, 0, $sentenceLength->cdata, 'UTF-8');
            $text = substr($text, strlen($sentence));
            $sentences[] = new Sentence($type, $sentence);
        }
        return $sentences;
    }

    /**
     * Check grammar errors in a paragraph or sentence.
     *
     * @param string $text A paragraph or sentence to check grammar errors in.
     * @param string $languageCode ISO language code for the language in which error descriptions should be returned
     * @return array<int, GrammarError> Array of grammar errors
     */
    public function grammarErrors(string $text, string $languageCode = 'en')
    {
        $this->validateInput($text);
        $errors = [];
        $i = 0;
        while (true) {
            $error = $this->ffi->voikkoNextGrammarErrorCstr($this->voikko, $text, strlen($text), 0, $i);
            if (is_null($error)) {
                break;
            }
            $errorCode = $this->ffi->voikkoGetGrammarErrorCode($error);
            $startPosition = $this->ffi->voikkoGetGrammarErrorStartPos($error);
            $errorLength = $this->ffi->voikkoGetGrammarErrorLength($error);
            $suggestions = [];
            $suggestionsPtr = $this->ffi->voikkoGetGrammarErrorSuggestions($error);
            $j = 0;
            while (!FFI::isNull($suggestionsPtr[$j])) {
                $suggestions[] = FFI::string($suggestionsPtr[$j]);
                $j++;
            }
            $shortDescription = $this->ffi->voikkoGetGrammarErrorShortDescription($error, $languageCode);
            $errors[] = new GrammarError($errorCode, $startPosition, $errorLength, $suggestions, FFI::string($shortDescription));
            $this->ffi->voikkoFreeErrorMessageCstr($shortDescription);
            $this->ffi->voikkoFreeGrammarError($error);
            $i++;
        }
        return $errors;
    }

    /**
     * Get a list of available dictionaries.
     *
     * @param string $dictionaryPath Path to a directory from which dictionary files should be searched first before looking into the standard dictionary locations.
     * @param string $libraryPath Path to libvoikko shared library.
     * @return array<int, Dictionary> Array of dictionaries
     */
    public static function dictionaries(string $dictionaryPath = null, string $libraryPath = "libvoikko.so.1")
    {
        $ffi = self::getFFI($libraryPath);
        $dicts = $ffi->voikko_list_dicts($dictionaryPath);
        $result = [];
        $i = 0;
        while (!FFI::isNull($dicts[$i])) {
            $result[] = new Dictionary(
                $ffi->voikko_dict_language($dicts[$i]),
                $ffi->voikko_dict_script($dicts[$i]),
                $ffi->voikko_dict_variant($dicts[$i]),
                $ffi->voikko_dict_description($dicts[$i])
            );
            $i++;
        }
        $ffi->voikko_free_dicts($dicts);
        return $result;
    }

    /**
     * Sets a boolean option.
     *
     * @param int $option option name
     * @param bool $value option value
     * @throws Exception if setting option failed
     * @internal
     */
    private function setBooleanOption(int $option, bool $value): void
    {
        if (!$this->ffi->voikkoSetBooleanOption($this->voikko, $option, $value)) {
            throw new Exception("Failed to set boolean option");
        }
    }

    /**
     * Sets an integer option.
     *
     * @param int $option option name
     * @param int $value option value
     * @throws Exception if setting option failed
     * @internal
     */
    private function setIntegerOption(int $option, int $value): void
    {
        if (!$this->ffi->voikkoSetIntegerOption($this->voikko, $option, $value)) {
            throw new Exception("Failed to set integer option");
        }
    }

    /**
     * Ignore dot at the end of the word (needed for use in some word
     * processors).
     *
     * If this option is set and input word ends with a dot, spell checking and
     * hyphenation functions try to analyze the word without the dot if no
     * results can be obtained for the original form. Also with this option,
     * string tokenizer will consider trailing dot of a word to be a part of
     * that word.
     *
     * Default: false
     */
    public function setIgnoreDot(bool $value): void
    {
        $this->setBooleanOption(0, $value);
    }

    /**
     * Ignore words containing numbers (spell checking only)
     *
     * Default: false
     */
    public function setIgnoreNumbers(bool $value): void
    {
        $this->setBooleanOption(1, $value);
    }

    /**
     * Accept words that are written completely in uppercase letters without
     * checking them at all.
     *
     * Default: false
     */
    public function setIgnoreUppercase(bool $value): void
    {
        $this->setBooleanOption(3, $value);
    }

    /**
     * Accept words even when the first letter is in uppercase (start of
     * sentence etc.)
     *
     * Default: true
     */
    public function setAcceptFirstUppercase(bool $value): void
    {
        $this->setBooleanOption(6, $value);
    }

    /**
     * Accept words even when all of the letters are in uppercase.
     *
     * Note that this is not the same as `setIgnoreUppercase`: with this option
     * the word is still checked, only case differences are ignored.
     *
     * Default: true
     */
    public function setAcceptAllUppercase(bool $value): void
    {
        $this->setBooleanOption(7, $value);
    }

    /**
     * Do not insert hyphenation positions that are considered to be ugly but
     * correct
     *
     * Default: false
     */
    public function setNoUglyHyphenation(bool $value): void
    {
        $this->setBooleanOption(4, $value);
    }

    /**
     * Use suggestions optimized for optical character recognition software.
     *
     * By default suggestions are optimized for typing errors.
     *
     * Default: false
     */
    public function setOcrSuggestions(bool $value): void
    {
        $this->setBooleanOption(8, $value);
    }

    /**
     * Ignore non-words such as URLs and email addresses (spell checking only)
     *
     * Default: true
     */
    public function setIgnoreNonwords(bool $value): void
    {
        $this->setBooleanOption(10, $value);
    }

    /**
     * Allow some extra hyphens in words (spell checking only)
     *
     * This option relaxes hyphen checking rules to work around some unresolved
     * issues in the underlying morphology, but it may cause some incorrect
     * words to be accepted. The exact behavior (if any) of this option is not
     * specified.
     *
     * Default: false
     */
    public function setAcceptExtraHyphens(bool $value): void
    {
        $this->setBooleanOption(11, $value);
    }

    /**
     * Accept missing hyphens at the start and end of the word (spell checking
     * only)
     *
     * Some application programs do not consider hyphens to be word characters.
     * This is reasonable assumption for many languages but not for Finnish. If
     * the application cannot be fixed to use proper tokenisation algorithm for
     * Finnish, this option may be used to tell libvoikko to work around this
     * defect.
     *
     * Default: false
     */
    public function setAcceptMissingHyphens(bool $value): void
    {
        $this->setBooleanOption(12, $value);
    }

    /**
     * Accept incomplete sentences that could occur in titles or headings
     * (grammar checking only)
     *
     * Set this option to true if your application is not able to differentiate
     * titles from normal text paragraphs, or if you know that you are checking
     * title text.
     *
     * Default: false
     */
    public function setAcceptTitlesInGc(bool $value): void
    {
        $this->setBooleanOption(13, $value);
    }

    /**
     * Accept incomplete sentences at the end of the paragraph (grammar
     * checking only)
     *
     * These may exist when text is still being written.
     *
     * Default: false
     */
    public function setAcceptUnfinishedParagraphsInGc(bool $value): void
    {
        $this->setBooleanOption(14, $value);
    }

    /**
     * Hyphenate unknown words (hyphenation only)
     *
     * Default: true
     */
    public function setHyphenateUnknownWords(bool $value): void
    {
        $this->setBooleanOption(15, $value);
    }

    /**
     * Accept paragraphs if they would be valid within bulleted lists (grammar
     * checking only)
     *
     * Default: false
     */
    public function setAcceptBulletedListsInGc(bool $value): void
    {
        $this->setBooleanOption(16, $value);
    }

    /**
     * Set the minimum length for words that may be hyphenated.
     *
     * This limit is also enforced on individual parts of compound words.
     *
     * Default: 2
     */
    public function setMinHyphenatedWordLength(int $value): void
    {
        $this->setIntegerOption(9, $value);
    }

    /**
     * Set the size of the spell checker cache.
     *
     * This can be -1 (no cache) or >= 0 (size in bytes = `2^cache_size * (6544*sizeof(wchar_t) + 1008)`).
     *
     * Default: 0
     */
    public function setSpellerCacheSize(int $value): void
    {
        $this->setIntegerOption(17, $value);
    }
}
