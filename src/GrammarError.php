<?php
namespace Siiptuo\Voikko;

/**
 * Represents a grammar error.
 *
 * @see Voikko::grammarErrors()
 */
class GrammarError
{
    /**
     * Error code associated with the grammar error.
     */
    public int $errorCode;

    /**
     * The starting position of the error in the checked paragraph (in characters).
     */
    public int $startPosition;

    /**
     * The length of the error in the checked paragraph (in characters).
     */
    public int $errorLength;

    /**
     * Suggestions for correcting a grammar error.
     * @var array<int, string>
     */
    public array $suggestions;

    /**
     * Localized short description of the grammar error.
     */
    public string $shortDescription;

    /** @internal */
    public function __construct(int $errorCode, int $startPosition, int $errorLength, array $suggestions, string $shortDescription)
    {
        $this->errorCode = $errorCode;
        $this->startPosition = $startPosition;
        $this->errorLength = $errorLength;
        $this->suggestions = $suggestions;
        $this->shortDescription = $shortDescription;
    }
}
