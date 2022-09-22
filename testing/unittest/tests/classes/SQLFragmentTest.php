<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tests the SQLFragment class.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 * @group core
 * @group search
 */
class SQLFragmentTest extends testing\unittest\UnitTest
{
    /**
     * Tests that adding parameters works.
     */
    public function testAddParameter()
    {
        $fragment = new SQLFragment();
        self::assertEmpty($fragment->params);
        self::assertEmpty($fragment->param_types);

        // Add a parameter.
        $fragment->addParameter('value', SQLFragment::TYPE_STRING);
        self::assertEquals(['value'], $fragment->params);
        self::assertEquals('s', $fragment->param_types);

        // Add a second parameter.
        $fragment->addParameter(1.0, SQLFragment::TYPE_DOUBLE);
        self::assertEquals(['value', 1.0], $fragment->params);
        self::assertEquals('sd', $fragment->param_types);

        // Add a third parameter.
        $fragment->addParameter(150, SQLFragment::TYPE_INTEGER);
        self::assertEquals(['value', 1.0, 150], $fragment->params);
        self::assertEquals('sdi', $fragment->param_types);
    }

    /**
     * Test that SQL fragments are combined correctly.
     */
    public function testCombine()
    {
        $fragment1 = new SQLFragment();
        $fragment1->sql = 'field = ?';
        $fragment1->addParameter('value', SQLFragment::TYPE_STRING);
        $fragment2 = new SQLFragment();
        $fragment2->sql = 'field2 = ?';
        $fragment2->addParameter(2, SQLFragment::TYPE_INTEGER);
        $emptyfragment = new SQLFragment();

        // Glue with AND.
        $combine1 = SQLFragment::combine(' AND ', $fragment1, $emptyfragment, $fragment2);
        self::assertEquals('field = ? AND field2 = ?', $combine1->sql);
        self::assertEquals(['value', 2], $combine1->params);
        self::assertEquals('si', $combine1->param_types);

        // Params reversed, with OR.
        $combine2 = SQLFragment::combine(' OR ', $fragment2, $emptyfragment, $fragment1);
        self::assertEquals('field2 = ? OR field = ?', $combine2->sql);
        self::assertEquals([2, 'value'], $combine2->params);
        self::assertEquals('is', $combine2->param_types);

        // Only one valid fragment.
        $combine3 = SQLFragment::combine(' AND ', $fragment1, $emptyfragment);
        self::assertEquals('field = ?', $combine3->sql);
        self::assertEquals(['value'], $combine3->params);
        self::assertEquals('s', $combine3->param_types);
    }

    /**
     * Test that an SQLFragment detects if it is valid correctly.
     *
     * @param string $sql A fragment of SQL.
     * @param array $params The values for placeholders in the fragment.
     * @param string $types The type string for the parameters.
     * @param bool $expected The expected validity of the fragment.
     *
     * @dataProvider dataValid
     */
    public function testValid(string $sql, array $params, string $types, bool $expected)
    {
        $fragment = new SQLFragment();
        $fragment->sql = $sql;
        $fragment->params = $params;
        $fragment->param_types = $types;
        self::assertEquals($expected, $fragment->valid());
    }

    /**
     * Data provider for testValid.
     *
     * @return array
     */
    public function dataValid(): array
    {
        return [
            'Valid' => ['field = ?', ['value'], 's', true],
            'Valid (no placeholders)' => ['feild = 2', [], '', true],
            'Empty' => ['', [], '', false],
            'Too many values' => ['field = ?', [1, 2], 'ii', false],
            'Too few values' => ['field = ?', [], '', false],
            'Values not all typed' => ['field = ?', ['value'], '', false],
            'Types without values' => ['field = ?', [], 's', false],
        ];
    }
}
