<?php
namespace Siiptuo\Voikko;

/**
 * Represents a token.
 *
 * @see Voikko::tokens()
 */
class Token
{
    /**
     * End of text or error.
     */
    public const NONE = 0;

    /**
     * Word token.
     */
    public const WORD = 1;

    /**
     * Punctuation token.
     */
    public const PUNCTUATION = 2;

    /**
     * Whitespace token.
     */
    public const WHITESPACE = 3;

    /**
     * Character not used in any of the supported natural languages.
     */
    public const UNKNOWN = 4;

    /**
     * Token type.
     *
     * @see Token::NONE
     * @see Token::WORD
     * @see Token::PUNCTUATION
     * @see Token::WHITESPACE
     * @see Token::UNKNOWN
     */
    public int $type;

    /**
     * Token text.
     */
    public string $text;

    /** @internal */
    public function __construct($type, $text)
    {
        $this->type = $type;
        $this->text = $text;
    }
}
