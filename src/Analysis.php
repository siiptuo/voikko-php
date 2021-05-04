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
 * Morphological analysis of a word.
 *
 * @see Voikko::analyzeWord()
 * @see https://github.com/voikko/corevoikko/blob/master/libvoikko/doc/morphological-analysis.txt
 *
 * @property ?string $structure
 *
 * This attribute describes morpheme boundaries, character case and hyphenation
 * restrictions for the word. The following characters are used in the values
 * of this attribute:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Character</th>
 *       <th>Description</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>=</td>
 *       <td>Start of a new morpheme. This must also be present at the start of a word.</td>
 *     </tr>
 *     <tr>
 *       <td>-</td>
 *       <td>Hyphen. Word can be split in text processors after this character without inserting an extra hyphen. If the hyphen is at morpheme boundary, the boundary symbol = must be placed after the hyphen.</td>
 *     </tr>
 *     <tr>
 *       <td>p</td>
 *       <td>Letter that is written in lower case in the standard form.</td>
 *     </tr>
 *     <tr>
 *       <td>q</td>
 *       <td>Letter that is written in lower case in the standard form. Hyphenation is forbidden before this letter.</td>
 *     </tr>
 *     <tr>
 *       <td>i</td>
 *       <td>Letter that is written in upper case in the standard form.</td>
 *     </tr>
 *     <tr>
 *       <td>j</td>
 *       <td>Letter that is written in upper case in the standard form. Hyphenation is forbidden before this letter.</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Structure</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>Matti-niminen</td>
 *       <td>=ipppp-=ppppppp</td>
 *     </tr>
 *     <tr>
 *       <td>DNA-näyte</td>
 *       <td>=jjj-=ppppp</td>
 *     </tr>
 *     <tr>
 *       <td>autokauppa</td>
 *       <td>=pppp=pppppp</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $fstOutput
 *
 * Analyzers that are implemented using finite state transducers can provide the raw transducer output using this attribute.
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>FST output</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>kissalla</td>
 *       <td>[Ln][Xp]kissa[X][Xs]505527[X]kissa[Sade][Ny]lla</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $baseForm
 *
 * Base form of the given word.
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Base form</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>kissalla</td>
 *       <td>kissa</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $number
 *
 * Grammatical number of the word. Suggested values for this attribute are
 * "singular", "dual", "trial" and "plural".
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Base form</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>kissa</td>
 *       <td>singular</td>
 *     </tr>
 *     <tr>
 *       <td>kissat</td>
 *       <td>plural</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $person
 *
 * For verbs in active voice this attribute represents the person (first,
 * second or third). The person for passive voice can be considered as the
 * fourth voice if appropriate for the language. Suggested values for this
 * attribute are "1", "2", "3" and "4".
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Person</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>juoksen</td>
 *       <td>1</td>
 *     </tr>
 *     <tr>
 *       <td>juokset</td>
 *       <td>2</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $mood
 *
 * Mood of a verb. Suggested values for this attribute are "indicative",
 * "conditional", "imperative" and "potential".
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Mood</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>juoksen</td>
 *       <td>indicative</td>
 *     </tr>
 *     <tr>
 *       <td>juoksisin</td>
 *       <td>conditional</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * Mainly due to structure of voikko-fi MOOD is also used to describe some
 * non-finite verb forms. For that purpose the following additional attribute
 * values are used:
 *
 * - A-infinitive (as in "juosta")
 * - E-infinitive (as in "juostessa")
 * - MA-infinitive (as in "juoksemassa", "juoksemasta", "juoksemaan" etc.)
 * - MINEN-infinitive (as in "juokseminen")
 * - MAINEN-infinitive (as in "juoksemaisillaan")
 *
 * @property ?string $tense
 *
 * Tense and aspect of a verb. Suggested values for this attribute are "past_imperfective", "present_simple", (add more as needed).
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Tense</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>juoksen</td>
 *       <td>present_simple</td>
 *     </tr>
 *     <tr>
 *       <td>juoksin</td>
 *       <td>past_imperfective</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $negative
 *
 * For all verbs this attribute indicates whether the verb is in a connegative
 * form. Suggested values: "false", "true", "both".
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Negative</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>sallitaan</td>
 *       <td>false</td>
 *     </tr>
 *     <tr>
 *       <td>sallita (as in "ei sallita")</td>
 *       <td>true</td>
 *     </tr>
 *     <tr>
 *       <td>maalaa (also "ei maalaa")</td>
 *       <td>both</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $participle
 *
 * Word is a participle of some sort. Suggested values for this attribute are
 * "present_active", "present_passive", "past_active", "past_passive", "agent"
 * and "negation" (add more as needed).
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Participle</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>juokseva</td>
 *       <td>present_active</td>
 *     </tr>
 *     <tr>
 *       <td>juostava</td>
 *       <td>present_passive</td>
 *     </tr>
 *     <tr>
 *       <td>juossut</td>
 *       <td>past_active</td>
 *     </tr>
 *     <tr>
 *       <td>juostu</td>
 *       <td>past_passive</td>
 *     </tr>
 *     <tr>
 *       <td>juoksema</td>
 *       <td>agent</td>
 *     </tr>
 *     <tr>
 *       <td>juoksematon</td>
 *       <td>negation</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $possessive
 *
 * Word contains information about possessor. For now this is used to indicate
 * the use of possessive suffix in Finnish nouns.
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Possessive</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>kissani</td>
 *       <td>1s</td>
 *     </tr>
 *     <tr>
 *       <td>kissasi</td>
 *       <td>2s</td>
 *     </tr>
 *     <tr>
 *       <td>kissamme</td>
 *       <td>1p</td>
 *     </tr>
 *     <tr>
 *       <td>kissanne</td>
 *       <td>2p</td>
 *     </tr>
 *     <tr>
 *       <td>kissansa</td>
 *       <td>3</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $comparison
 *
 * Word is comparable (adjective). Suggested values for this attribute are
 * "positive", "comparative" and "superlative".
 *
 * Examples:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Comparison</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>sininen</td>
 *       <td>positive</td>
 *     </tr>
 *     <tr>
 *       <td>sinisempi</td>
 *       <td>comparative</td>
 *     </tr>
 *     <tr>
 *       <td>sinisin</td>
 *       <td>superlative</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $class
 *
 * Sanan sanaluokka. Attribuutti on käytössä libvoikon sisällä.
 *
 * Attribuutin mahdolliset arvot ovat seuraavat:
 *
 * - nimisana (yleisnimi)
 * - laatusana
 * - nimisana_laatusana (sama kuin erilliset analyysit nimisanana ja laatusanana)
 * - teonsana
 * - seikkasana
 * - asemosana
 * - suhdesana
 * - huudahdussana
 * - sidesana
 * - etunimi
 * - sukunimi
 * - paikannimi
 * - nimi (muu erisnimi kuin etu-, suku- tai paikannimi)
 * - kieltosana
 * - lyhenne
 * - lukusana
 * - etuliite
 *
 * @property ?string $sijamuoto
 *
 * Nominin sijamuoto. Attribuutti on käytössä libvoikon sisällä.
 *
 * Attribuutin mahdolliset arvot ovat seuraavat:
 *
 * - nimento
 * - omanto
 * - osanto
 * - olento
 * - tulento
 * - kohdanto
 * - sisaolento
 * - sisaeronto
 * - sisatulento
 * - ulkoolento
 * - ulkoeronto
 * - ulkotulento
 * - vajanto
 * - seuranto
 * - keinonto
 * - kerrontosti (esim. "nopeasti")
 *
 * @property ?string $kysymysliite
 *
 * Sanaan liittyy kysymysliite -ko tai -kö. Attribuutin ainoa sallittu arvo on
 * "true". Jos sanaan ei liity kysymysliitettä, attribuuttia ei ole.
 *
 * @property ?string $focus
 *
 * Sanaan liittyy fokuspartikkeli -kin tai -kAAn.
 *
 * Esimerkkejä:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Focus</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>kissakin</td>
 *       <td>kin</td>
 *     </tr>
 *     <tr>
 *       <td>kissakaan</td>
 *       <td>kaan</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * @property ?string $wordBases
 *
 * Sanan osien perusmuodot. Attribuutti ei ole käytössä libvoikon sisällä.
 * Attribuutin arvona on sanan perusmuoto, jossa yhdyssanan osat ja päätteet on
 * erotettu toisistaan +-merkillä. Lisäksi kunkin yhdyssanan osan perusmuoto on
 * osan perässä suluissa. Mikäli yhdyssanan osat itsessään ovat jaettavissa
 * osiin, osat voidaan sulkujen sisällä olevassa perusmuodossa erotella
 * merkeillä = tai |.
 *
 * Esimerkkejä:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Word bases</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>köydenvetoa</td>
 *       <td>+köyde(köysi)+n+veto(veto)</td>
 *     </tr>
 *     <tr>
 *       <td>Alkio-opistossa</td>
 *       <td>+alkio(Alkio)+-+opisto(opisto)<br>+alkio(alkio)+-+opisto(opisto)</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 * Johdinpäätteiden perusmuodot ovat suluissa siten, että päätteen edessä on
 * +-merkki:
 *
 * <table style="text-align:left;border-collapse:separate;border-spacing:1rem 0">
 *   <thead>
 *     <tr>
 *       <th>Word</th>
 *       <th>Word bases</th>
 *     </tr>
 *   </thead>
 *   <tbody>
 *     <tr>
 *       <td>kansalliseepos</td>
 *       <td>+kansa(kansa)+llis(+llinen)+eepos(eepos)</td>
 *     </tr>
 *   </tbody>
 * </table>
 *
 */
class Analysis
{
    /**
     * @internal
     * @var array<string, string>
     */
    private array $data;

    /**
     * @internal
     * @param array<string, string> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $name
     * @param mixed $value
     * */
    public function __set(string $name, $value): void
    {
        throw new Exception("Cannot set property $name. The object is immutable.");
    }

    public function __get(string $name): ?string
    {
        return $this->data[strtoupper($name)] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[strtoupper($name)]);
    }

    public function __unset(string $name): void
    {
        throw new Exception("Cannot unset property $name. The object is immutable.");
    }

    // TODO: make iterable somehow
}
