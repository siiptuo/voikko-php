<?php
namespace Siiptuo\Voikko;

/**
 * Morphological analysis of a word.
 *
 * @property ?string $baseForm
 * @property ?string $class
 * @property ?string $fstOutput
 * @property ?string $number
 * @property ?string $sijamuoto
 * @property ?string $structure
 * @property ?string $wordBases
 * @property ?string $mood
 * @property ?string $negative
 * @property ?string $person
 * @property ?string $tense
 * @property ?string $comparison
 *
 * @see Voikko::analyzeWord()
 */
class Analysis
{
    /** @internal */
    private array $data;

    /** @internal */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function __set(string $name, $value): void
    {
        throw new Exception("Cannot set property $name. The object is immutable.");
    }

    public function __get(string $name): ?string
    {
        return $this->data[strtoupper($name)] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[strtoupper($name)]);
    }

    public function __unset(string $name): void
    {
        throw new Exception("Cannot unset property $name. The object is immutable.");
    }

    // TODO: make iterable somehow
}
