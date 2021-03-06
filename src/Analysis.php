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

    public function __get(string $key): string
    {
        return $this->data[strtoupper($key)];
    }

    // TODO: make iterable somehow
}
