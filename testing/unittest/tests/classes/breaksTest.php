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

use testing\unittest\unittestdatabase;

/**
 * Test breaks class
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 */
class BreaksTest extends unittestdatabase
{
    /**
     * @var array Storage for paper data in tests
     */
    private $pid1;

    /**
     * @var array Storage for student data in tests
     */
    private $student1;

    /**
     * @var array Storage for student data in tests
     */
    private $student2;

    /**
     * @var array Storage for student data in tests
     */
    private $student3;

    /**
     * Generate common data for test.
     *
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $usergen = $this->get_datagenerator('users', 'core');
        $this->student1 = $usergen->create_user(['roles' => 'Student', 'sid' => '24680', 'surname' => 'Smith']);
        $this->student2 = $usergen->create_user(['roles' => 'Student', 'sid' => '13579', 'surname' => 'Johnson']);
        $this->student3 = $usergen->create_user(['roles' => 'Student', 'sid' => '65478', 'surname' => 'Peterson']);
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid1 = $datagenerator->create_paper(
            array(
                'papertitle' => 'Test summative',
                'papertype' => '2',
                'paperowner' => 'admin',
                'modulename' => 'Training Module'
            )
        );
    }

    /**
     * Test adding a break
     * @group paper
     */
    public function testAddBreak(): void
    {
        $this->assertIsInt(Breaks::addBreak($this->student1['id'], $this->pid1['id']));
        $querytable = $this->query(
            array(
                'columns' => array(
                    'userID',
                    'paperID'
                ),
                'table' => 'breaks'
            )
        );
        $expectedtable = array(
            0 => array(
                'userID' => $this->student1['id'],
                'paperID' => $this->pid1['id']
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test getting break datetime
     * @group paper
     */
    public function testGetBreak(): void
    {
        $bid = Breaks::addBreak($this->student1['id'], $this->pid1['id']);
        Breaks::addBreak($this->student1['id'], $this->pid1['id']);
        Breaks::addBreak($this->student3['id'], $this->pid1['id']);
        $expectedtable = $this->query(
            array(
                'columns' => array(
                    'break_taken',
                ),
                'table' => 'breaks',
                'where' => array(
                    array(
                        'column' => 'id',
                        'value' => $bid
                    )
                )
            )
        );
        $tmp_cfg_long_date_time = str_replace('%', '', $this->config->get('cfg_long_date_time'));
        $expected = date_format(date_create($expectedtable[0]['break_taken']), $tmp_cfg_long_date_time);
        $this->assertEquals($expected, Breaks::getBreak($bid));
    }

    /**
     * Test getting all breaks
     * @group paper
     */
    public function testGetAllBreaks(): void
    {
        $bid1 = Breaks::addBreak($this->student1['id'], $this->pid1['id']);
        $bid2 = Breaks::addBreak($this->student3['id'], $this->pid1['id']);
        $bid3 = Breaks::addBreak($this->student2['id'], $this->pid1['id']);
        $bid4 = Breaks::addBreak($this->student1['id'], $this->pid1['id']);
        $expected[$this->student1['id']][0] = $bid1;
        $expected[$this->student3['id']][0] = $bid2;
        $expected[$this->student2['id']][0] = $bid3;
        $expected[$this->student1['id']][1] = $bid4;
        $this->assertEquals($expected, Breaks::getAllBreaks($this->pid1['id']));
        $this->assertEquals(array(), Breaks::getAllBreaks(99));
    }
}
