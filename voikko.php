<?php
$ffi = FFI::cdef(
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
    char * voikko_mor_analysis_value_cstr(
                    const struct voikko_mor_analysis * analysis,
                    const char * key);
    void voikko_free_mor_analysis_value_cstr(char * analysis_value);
    ",
    "libvoikko.so");

class MorAnalysisValue
{
    function __construct(private $analysis, private $value)
    {
    }

    function __destruct()
    {
        global $ffi;
        $ffi->voikko_free_mor_analysis_value_cstr($this->value);
    }

    function __toString() {
        return FFI::string($this->value);
    }
}

class MorAnalysis
{
    function __construct(private $voikko, private $analysis)
    {
    }

    function __destruct()
    {
        global $ffi;
        $ffi->voikko_free_mor_analysis($this->analysis);
    }

    function __get($key)
    {
        global $ffi;
        $value = $ffi->voikko_mor_analysis_value_cstr($this->analysis[0], strtoupper($key));
        if ($value == null) {
            return null;
        }
        return new MorAnalysisValue($this->analysis, $value);
    }
}

class VoikkoException extends Exception
{
}

class Voikko
{
    function __construct($lang, $path = null)
    {
        global $ffi;
        $error = FFI::new("char*");
        $this->voikko = $ffi->voikkoInit(FFI::addr($error), $lang, $path);
        if (!FFI::isNull($error)) {
            throw new VoikkoException(FFI::string($error));
        }
    }

    function __destruct()
    {
        global $ffi;
        $ffi->voikkoTerminate($this->voikko);
    }

    function hyphenate($word)
    {
        global $ffi;
        return FFI::string($ffi->voikkoHyphenateCstr($this->voikko, $word));
    }

    function analyzeWord($word)
    {
        global $ffi;
        $analysis = $ffi->voikkoAnalyzeWordCstr($this->voikko, $word);
        if (FFI::isNull($analysis)) {
            return null;
        }
        return new MorAnalysis($this->voikko, $analysis);
    }
}
