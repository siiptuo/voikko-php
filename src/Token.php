<?php
namespace Siiptuo\Voikko;

class Token
{
    public const NONE = 0;
    public const WORD = 1;
    public const PUNCTUATION = 2;
    public const WHITESPACE = 3;
    public const UNKNOWN = 4;

    public int $type;
    public string $text;

    public function __construct($type, $text)
    {
        $this->type = $type;
        $this->text = $text;
    }
}
