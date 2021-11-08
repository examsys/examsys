<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Testcase for class textbox_marking_utils.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 * @group textbox
 * @group question
 */
class TextboxMarkingUtilsTest extends \testing\unittest\UnitTest{
    /**
     * Tests that terms will be highlighted correctly.
     *
     * @param array $terms An array of terms to search for in the text
     * @param string $answer The text to be searched
     * @param string $expected The result we expect
     *
     * @dataProvider dataHighlightTerms
     */
    public function testHighlightTerms(array $terms, string $answer, string $expected) {
        $settings = [
            'terms' => json_encode($terms),
        ];

        $this->assertEquals($expected, textbox_marking_utils::higlightterms($settings, $answer));
    }

    /**
     * Data for testHighlightTerms
     *
     * @return array
     */
    public function dataHighlightTerms(): array {
        return [
            'no matches' => [
                ['test'],
                'This is some text',
                'This is some text',
            ],
            'simple' => [
                ['test'],
                'This is some test text',
                'This is some <span class="highlight">test</span> text',
            ],
            'multiple' => [
                ['some', 'test'],
                'This is some test text',
                'This is <span class="highlight">some</span> <span class="highlight">test</span> text',
            ],
            'avoids html' => [
                ['class', 'span'],
                'This is some <span class="highlight">test</span> text, with a class',
                'This is some <span class="highlight">test</span> text, with a <span class="highlight">class</span>',
            ],
        ];
    }
}
