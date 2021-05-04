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
 * Represents a dictionary.
 *
 * @see Voikko::dictionaries()
 */
class Dictionary
{
    /**
     * Language tag of the dictionary.
     */
    public string $language;

    /**
     * Script of the dictionary.
     */
    public string $script;

    /**
     * Variant identifier of the dictionary.
     */
    public string $variant;

    /**
     * Human readable description for the dictionary.
     */
    public string $description;

    /** @internal */
    public function __construct(string $language, string $script, string $variant, string $description)
    {
        $this->language = $language;
        $this->script = $script;
        $this->variant = $variant;
        $this->description = $description;
    }
}
