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
                void voikkoFreeCstrArray(char ** cstrArray);
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
        $result = self::$ffi->voikkoSpellCstr($this->voikko, $word);
        if ($result === 2) {
            throw new Exception('Internal error');
        }
        if ($result === 3) {
            throw new Exception('Character set conversion failed');
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
        $result = [];
        $suggestions = self::$ffi->voikkoSuggestCstr($this->voikko, $word);
        if (FFI::isNull($suggestions) || FFI::isNull($suggestions[0])) {
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
     * @return string Hyphenated word
     */
    public function hyphenate(string $word, string $hyphen = '-'): string
    {
        $result = '';
        $pattern = $this->hyphenationPattern($word);
        for ($i = 0; $i < mb_strlen($word); $i++) {
            if ($pattern[$i] == ' ') {
                $result .= mb_substr($word, $i, 1);
            } elseif ($pattern[$i] == '-') {
                $result .= $hyphen;
                $result .= mb_substr($word, $i, 1);
            } elseif ($pattern[$i] == '=') {
                $result .= mb_substr($word, $i, 1) == '-' ? '-' : $hyphen;
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
        // TODO: error handling
        return FFI::string(self::$ffi->voikkoHyphenateCstr($this->voikko, $word));
    }

    /**
     * Analyzes the morphology of given word.
     *
     * @param string $word Word to be analyzed.
     * @return array<int, Analysis> Array of analysis results. Empty array is returned for unknown words.
     */
    public function analyzeWord(string $word): array
    {
        $result = [];
        $analyses = self::$ffi->voikkoAnalyzeWordCstr($this->voikko, $word);
        if (FFI::isNull($analyses) || FFI::isNull($analyses[0])) {
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
        $tokens = [];
        $tokenLength = FFI::new("size_t");
        while (strlen($text) > 0) {
            $type = self::$ffi->voikkoNextTokenCstr($this->voikko, $text, strlen($text), FFI::addr($tokenLength));
            $token = mb_substr($text, 0, $tokenLength->cdata);
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
        $sentences = [];
        $sentenceLength = FFI::new("size_t");
        while (strlen($text) > 0) {
            $type = self::$ffi->voikkoNextSentenceStartCstr($this->voikko, $text, strlen($text), FFI::addr($sentenceLength));
            $sentence = mb_substr($text, 0, $sentenceLength->cdata);
            $text = substr($text, strlen($sentence));
            $sentences[] = new Sentence($type, $sentence);
        }
        return $sentences;
    }
}
