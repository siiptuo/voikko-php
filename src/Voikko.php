<?php
namespace Siiptuo\Voikko;

use \FFI;

/**
 * Main class of the library.
 */
class Voikko
{
    /** @internal */
    private static ?FFI $ffi = null;

    /** @internal */
    private FFI\CData $voikko;

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
        // XXX: $ffi will be cached even if $libraryPath is different
        if (self::$ffi == null) {
            self::$ffi = FFI::cdef(
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
                ",
                $libraryPath
            );
        }
        $error = FFI::new("char*");
        $handle = self::$ffi->voikkoInit(FFI::addr($error), $languageCode, $dictionaryPath);
        if (!FFI::isNull($error)) {
            throw new Exception(FFI::string($error));
        }
        $this->voikko = $handle;
    }

    public function __destruct()
    {
        self::$ffi->voikkoTerminate($this->voikko);
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
        $result = self::$ffi->voikkoSpellCstr($this->voikko, $word);
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
        $suggestions = self::$ffi->voikkoSuggestCstr($this->voikko, $word);
        if (is_null($suggestions) || FFI::isNull($suggestions[0])) {
            return $result;
        }
        $i = 0;
        while (!FFI::isNull($suggestions[$i])) {
            $result[] = FFI::string($suggestions[$i]);
            $i++;
        }
        self::$ffi->voikkoFreeCstrArray($suggestions);
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
        $pattern = self::$ffi->voikkoHyphenateCstr($this->voikko, $word);
        if (is_null($pattern)) {
            throw new Exception("Internal error");
        }
        $result = FFI::string($pattern);
        self::$ffi->voikkoFreeCstr($pattern);
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
        $analyses = self::$ffi->voikkoAnalyzeWordCstr($this->voikko, $word);
        if (is_null($analyses) || FFI::isNull($analyses[0])) {
            return $result;
        }
        $i = 0;
        while (!FFI::isNull($analyses[$i])) {
            $data = [];
            $keys = self::$ffi->voikko_mor_analysis_keys($analyses[$i]);
            $j = 0;
            while (!FFI::isNull($keys[$j])) {
                $value = self::$ffi->voikko_mor_analysis_value_cstr($analyses[$i], $keys[$j]);
                $data[FFI::string($keys[$j])] = FFI::string($value);
                self::$ffi->voikko_free_mor_analysis_value_cstr($value);
                $j++;
            }
            $result[] = new Analysis($data);
            $i++;
        }
        self::$ffi->voikko_free_mor_analysis($analyses);
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
            $type = self::$ffi->voikkoNextTokenCstr($this->voikko, $text, strlen($text), FFI::addr($tokenLength));
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
            $type = self::$ffi->voikkoNextSentenceStartCstr($this->voikko, $text, strlen($text), FFI::addr($sentenceLength));
            $sentence = mb_substr($text, 0, $sentenceLength->cdata, 'UTF-8');
            $text = substr($text, strlen($sentence));
            $sentences[] = new Sentence($type, $sentence);
        }
        return $sentences;
    }

    /**
     * Check the given text for grammar errors.
     *
     * @param string $text Text to check grammar errors in.
     * @param string $languageCode ISO language code for the language in which error descriptions should be returned
     * @return array<int, GrammarError> Array of grammar errors
     */
    public function grammarErrors(string $text, string $languageCode = 'en')
    {
        $this->validateInput($text);
        $errors = [];
        $i = 0;
        while (true) {
            $error = self::$ffi->voikkoNextGrammarErrorCstr($this->voikko, $text, strlen($text), 0, $i);
            if (is_null($error)) {
                break;
            }
            $errorCode = self::$ffi->voikkoGetGrammarErrorCode($error);
            $startPosition = self::$ffi->voikkoGetGrammarErrorStartPos($error);
            $errorLength = self::$ffi->voikkoGetGrammarErrorLength($error);
            $suggestions = [];
            $suggestionsPtr = self::$ffi->voikkoGetGrammarErrorSuggestions($error);
            $j = 0;
            while (!FFI::isNull($suggestionsPtr[$j])) {
                $suggestions[] = FFI::string($suggestionsPtr[$j]);
                $j++;
            }
            $shortDescription = self::$ffi->voikkoGetGrammarErrorShortDescription($error, $languageCode);
            $errors[] = new GrammarError($errorCode, $startPosition, $errorLength, $suggestions, FFI::string($shortDescription));
            self::$ffi->voikkoFreeErrorMessageCstr($shortDescription);
            self::$ffi->voikkoFreeGrammarError($error);
            $i++;
        }
        return $errors;
    }
}
