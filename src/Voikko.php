<?php
namespace Siiptuo\Voikko;

use \FFI;
use \ArrayAccess;
use \Countable;
use \Iterator;

class MorAnalysisValue
{
    private FFI $ffi;
    private MorAnalysis $parent;
    private FFI\CData $value;

    public function __construct(FFI $ffi, MorAnalysis $parent, FFI\CData $value)
    {
        $this->ffi = $ffi;
        $this->parent = $parent;
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

    public function __debugInfo()
    {
        return [(string)$this];
    }
}

/**
 * @property ?MorAnalysisValue $baseform
 * TODO: add all properties
 */
class MorAnalysis
{
    private FFI $ffi;
    private MorAnalyses $parent;
    private FFI\CData $analysis;

    public function __construct(FFI $ffi, MorAnalyses $parent, FFI\CData $analysis)
    {
        $this->ffi = $ffi;
        $this->parent = $parent;
        $this->analysis = $analysis;
    }

    public function __get(string $key): ?MorAnalysisValue
    {
        $value = $this->ffi->voikko_mor_analysis_value_cstr($this->analysis, strtoupper($key));
        if ($value == null) {
            return null;
        }
        return new MorAnalysisValue($this->ffi, $this, $value);
    }

    public function keys(): array
    {
        $keys = [];
        $result = $this->ffi->voikko_mor_analysis_keys($this->analysis);
        while (!FFI::isNull($result[0])) {
            $keys[] = FFI::string($result[0]);
            $result++;
        }
        return $keys;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->keys() as $key) {
            $result[$key] = (string)$this->$key;
        }
        return $result;
    }

    public function __debugInfo()
    {
        return $this->toArray();
    }
}

/**
 * @implements ArrayAccess<int, MorAnalysis>
 * @implements Iterator<int, MorAnalysis>
 */
class MorAnalyses implements ArrayAccess, Countable, Iterator
{
    private FFI $ffi;
    private Voikko $parent;
    private FFI\CData $analyses;
    private int $size = 0;
    private int $position = 0;

    public function __construct(FFI $ffi, Voikko $parent, FFI\CData $analyses)
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

    public function current(): ?MorAnalysis
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

    public function toArray(): array
    {
        $result = [];
        for ($i = 0; $i < $this->size; $i++) {
            $result[$i] = $this[$i]->toArray();
        }
        return $result;
    }

    public function __debugInfo()
    {
        return $this->toArray();
    }
}

class Voikko
{
    private static ?FFI $ffi = null;
    private FFI\CData $voikko;

    public function __construct(string $lang, string $path = null, string $library_path = "libvoikko.so.1")
    {
        // XXX: $ffi will be cached even if $library_path is different
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
                ",
                $library_path
            );
        }
        $error = FFI::new("char*");
        $handle = self::$ffi->voikkoInit(FFI::addr($error), $lang, $path);
        if (!FFI::isNull($error)) {
            throw new Exception(FFI::string($error));
        }
        $this->voikko = $handle;
    }

    public function __destruct()
    {
        self::$ffi->voikkoTerminate($this->voikko);
    }

    public function hyphenate(string $word): string
    {
        return FFI::string(self::$ffi->voikkoHyphenateCstr($this->voikko, $word));
    }

    public function analyzeWord(string $word): ?MorAnalyses
    {
        $analyses = self::$ffi->voikkoAnalyzeWordCstr($this->voikko, $word);
        if (FFI::isNull($analyses) || FFI::isNull($analyses[0])) {
            return null;
        }
        return new MorAnalyses(self::$ffi, $this, $analyses);
    }
}
