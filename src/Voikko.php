<?php
namespace Siiptuo\Voikko;

use \FFI;
use \ArrayAccess;
use \Countable;
use \Iterator;

class MorAnalysisValue
{
    private $ffi;
    private $analysis;
    private $value;

    public function __construct($ffi, $analysis, $value)
    {
        $this->ffi = $ffi;
        $this->analysis = $analysis;
        $this->value = $value;
    }

    public function __destruct()
    {
        $this->ffi->voikko_free_mor_analysis_value_cstr($this->value);
    }

    public function __toString()
    {
        return FFI::string($this->value);
    }
}

class MorAnalysis
{
    private $ffi;
    private $parent;
    private $analysis;

    public function __construct($ffi, $parent, $analysis)
    {
        $this->ffi = $ffi;
        $this->parent = $parent;
        $this->analysis = $analysis;
    }

    public function __get($key)
    {
        $value = $this->ffi->voikko_mor_analysis_value_cstr($this->analysis, strtoupper($key));
        if ($value == null) {
            return null;
        }
        return new MorAnalysisValue($this->ffi, $this->analysis, $value);
    }
}

class MorAnalyses implements ArrayAccess, Countable, Iterator
{
    private $ffi;
    private $parent;
    private $analyses;
    private $size = 0;
    private $position = 0;

    public function __construct($ffi, $parent, $analyses)
    {
        $this->ffi = $ffi;
        $this->parent = $parent;
        $this->analyses = $analyses;
        while (!FFI::isNull($this->analyses[$this->size])) {
            $this->size++;
        }
    }

    public function __destruct()
    {
        $this->ffi->voikko_free_mor_analysis($this->analyses);
    }

    public function offsetExists($offset)
    {
        return is_int($offset) && $offset >= 0 && $offset < $this->size;
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? new MorAnalysis($this->ffi, $this, $this->analyses[$offset]) : null;
    }

    public function offsetSet($offset, $value)
    {
        throw new Exception('MorAnalyses is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new Exception('MorAnalyses is immutable');
    }

    public function count()
    {
        return $this->size;
    }

    public function current()
    {
        return $this->offsetGet($this->position);
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return $this->offsetExists($this->position);
    }
}

class Voikko
{
    private static $ffi = null;

    public function __construct($lang, $path = null)
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
            throw new Exception(FFI::string($error));
        }
    }

    public function __destruct()
    {
        self::$ffi->voikkoTerminate($this->voikko);
    }

    public function hyphenate($word)
    {
        return FFI::string(self::$ffi->voikkoHyphenateCstr($this->voikko, $word));
    }

    public function analyzeWord($word)
    {
        $analyses = self::$ffi->voikkoAnalyzeWordCstr($this->voikko, $word);
        if (FFI::isNull($analyses) || FFI::isNull($analyses[0])) {
            return null;
        }
        return new MorAnalyses(self::$ffi, $this, $analyses);
    }
}
