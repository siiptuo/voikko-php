<?php
namespace Siiptuo\Voikko;
use \FFI;
use \Exception;
use \ArrayAccess;

class MorAnalysisValue
{
    private $ffi;
    private $analysis;
    private $value;

    function __construct($ffi, $analysis, $value)
    {
        $this->ffi = $ffi;
        $this->analysis = $analysis;
        $this->value = $value;
    }

    function __destruct()
    {
        $this->ffi->voikko_free_mor_analysis_value_cstr($this->value);
    }

    function __toString() {
        return FFI::string($this->value);
    }
}

class MorAnalysis
{
    private $ffi;
    private $analysis;

    function __construct($ffi, $analysis)
    {
        $this->ffi = $ffi;
        $this->analysis = $analysis;
    }

    function __get($key)
    {
        $value = $this->ffi->voikko_mor_analysis_value_cstr($this->analysis, strtoupper($key));
        if ($value == null) {
            return null;
        }
        return new MorAnalysisValue($this->ffi, $this->analysis, $value);
    }
}

class MorAnalysisArray implements ArrayAccess
{
    private $ffi;
    private $voikko;
    private $analysis;
    private $size = 0;

    function __construct($ffi, $voikko, $analysis)
    {
        $this->ffi = $ffi;
        $this->voikko = $voikko;
        $this->analysis = $analysis;
        while (!FFI::isNull($this->analysis[++$this->size]));
    }

    function __destruct()
    {
        $this->ffi->voikko_free_mor_analysis($this->analysis);
    }

    function offsetExists($offset)
    {
        return is_int($offset) && $offset >= 0 && $offset < $this->size;
    }

    function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? new MorAnalysis($this->ffi, $this->analysis[$offset]) : null;
    }

    function offsetSet($offset, $value)
    {
        throw VoikkoException('MorAnalysisArray is immutable');
    }

    function offsetUnset($offset)
    {
        throw VoikkoException('MorAnalysisArray is immutable');
    }
}

class VoikkoException extends Exception
{
}

class Voikko
{
    private static $ffi = null;

    function __construct($lang, $path = null)
    {
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
                char * voikko_mor_analysis_value_cstr(
                                const struct voikko_mor_analysis * analysis,
                                const char * key);
                void voikko_free_mor_analysis_value_cstr(char * analysis_value);
                ",
                "libvoikko.so.1"
            );
        }
        $error = FFI::new("char*");
        $this->voikko = self::$ffi->voikkoInit(FFI::addr($error), $lang, $path);
        if (!FFI::isNull($error)) {
            throw new VoikkoException(FFI::string($error));
        }
    }

    function __destruct()
    {
        self::$ffi->voikkoTerminate($this->voikko);
    }

    function hyphenate($word)
    {
        return FFI::string(self::$ffi->voikkoHyphenateCstr($this->voikko, $word));
    }

    function analyzeWord($word)
    {
        $analysis = self::$ffi->voikkoAnalyzeWordCstr($this->voikko, $word);
        if (FFI::isNull($analysis)) {
            return null;
        }
        return new MorAnalysisArray(self::$ffi, $this->voikko, $analysis);
    }
}
