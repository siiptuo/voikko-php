<?php

/*
 * This file is part of the libvoikko PHP bindings.
 *
 * SPDX-FileCopyrightText: 2021 Tuomas Siipola
 *
 * API and documentation are based on libvoikko and its Java bindings.
 *
 * SPDX-FileCopyrightText: 2006-2010 Harri Pitkänen
 * SPDX-License-Identifier: MPL-1.1 OR GPL-2.0-or-later OR LGPL-2.1-or-later
 */

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
    public function __construct(int $type, string $text)
    {
        $this->type = $type;
        $this->text = $text;
    }
}
