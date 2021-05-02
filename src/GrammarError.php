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
     * Start position of the error in characters.
     */
    public int $startPosition;

    /**
     * Length of the error in characters.
     */
    public int $errorLength;

    /**
     * Suggestions for correcting the grammar error.
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
