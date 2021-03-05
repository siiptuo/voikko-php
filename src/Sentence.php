<?php
namespace Siiptuo\Voikko;

class Sentence
{
    public const NONE = 0;
    public const NO_START = 1;
    public const PROBABLE = 2;
    public const POSSIBLE = 3;

    public int $type;
    public string $text;

    public function __construct($type, $text)
    {
        $this->type = $type;
        $this->text = $text;
    }
}
