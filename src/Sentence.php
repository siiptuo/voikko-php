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
