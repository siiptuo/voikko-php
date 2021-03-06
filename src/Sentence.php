<?php
namespace Siiptuo\Voikko;

/**
 * Represents a sentence.
 *
 * @see Voikko::sentences()
 */
class Sentence
{
    /**
     * End of text reached or error.
     */
    public const NONE = 0;

    /**
     * This is not a start of a new sentence.
     */
    public const NO_START = 1;

    /**
     * This is a probable start of a new sentence.
     */
    public const PROBABLE = 2;

    /**
     * This may be a start of a new sentence.
     */
    public const POSSIBLE = 3;

    /**
     * Sentence type.
     *
     * @see Sentence::NONE
     * @see Sentence::NO_START
     * @see Sentence::PROBABLE
     * @see Sentence::POSSIBLE
     */
    public int $type;

    /**
     * Sentence text.
     */
    public string $text;

    /** @internal */
    public function __construct(int $type, string $text)
    {
        $this->type = $type;
        $this->text = $text;
    }
}
