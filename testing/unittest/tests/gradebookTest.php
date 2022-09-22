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
 * Test gradebook class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class gradebooktest extends unittestdatabase
{

    /**
     * @var array Storage for paper data in tests
     */
    private $pid1;

    /**
     * @var array Storage for paper data in tests
     */
    private $pid2;

    /**
     * @var integer Storage for module id in tests
     */
    private $module2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->module2 = $this->get_module_id('SYSTEM');
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid1 = $datagenerator->create_paper(array('papertitle' => 'Test create summativ',
            'startdate' => '2016-01-25 09:00:00',
            'enddate' => '2016-01-25 10:00:00',
            'duration' => 60,
            'calendaryear' => 2016,
            'timezone' => 'Europe/London',
            'paperowner' => 'admin',
            'labs' => '1',
            'papertype' => '2',
            'modulename' => 'Training Module',
            'externalid' => 'ass1234',
            'externalsys' => 'test ExamSys api'));
        $this->pid2 = $datagenerator->create_paper(array('papertitle' => 'Test create summativ 2',
            'startdate' => '2016-01-25 09:00:00',
            'enddate' => '2016-01-25 10:00:00',
            'duration' => 60,
            'calendaryear' => 2016,
            'timezone' => 'Europe/London',
            'paperowner' => 'admin',
            'labs' => '1',
            'papertype' => '2',
            'modulename' => 'Training Module',
            'externalid' => 'erwer346547',
            'externalsys' => 'test ExamSys api'));
        $datagenerator = $this->get_datagenerator('gradebook', 'core');
        $datagenerator->create_paper(array('paperid' => $this->pid1['id'], 'timestamp' => '2016-01-25 10:10:00'));
        $datagenerator->create_user(array('paperid' => $this->pid1['id'], 'userid' => $this->student['id'], 'grade' => 60, 'adjustedgrade' => 62, 'classification' => 'Pass'));
        $datagenerator = $this->get_datagenerator('api', 'core');
        $client = $datagenerator->create_client(array('clientid' => 'test1', 'userid' => $this->admin['id'], 'secret' => 'test'));
        $datagenerator->create_external(array('clientid' => $client['clientid'], 'name' => 'test ExamSys api', 'type' => 'api'));
    }

    /**
     * Test paper graded function
     * @group gradebook
     */
    public function test_paper_graded()
    {
        $gradebook = new gradebook($this->db);
        // Test check paper graded - true
        $this->assertTrue($gradebook->paper_graded($this->pid1['id']));
        // Test check paper graded - false
        $this->assertFalse($gradebook->paper_graded($this->pid2['id']));
    }

    /**
     * Test store grade function
     * @group gradebook
     */
    public function test_store_grade()
    {
        $gradebook = new gradebook($this->db);
        // Test successful grade recording.
        $gradebook->create_gradebook($this->pid2['id']);
        $this->assertTrue($gradebook->store_grade($this->student['id'], $this->pid2['id'], 30, 32, 'Fail'));
        $querytable = $this->query(array('table' => 'gradebook_user'));
        $expectedtable = array(
            0 => array(
                'paperid' => $this->pid1['id'],
                'userid' => $this->student['id'],
                'raw_grade' => 60,
                'adjusted_grade' => 62,
                'classification' => 'Pass',
                ),
            1 => array (
                'paperid' => $this->pid2['id'],
                'userid' => $this->student['id'],
                'raw_grade' => 30,
                'adjusted_grade' => 32,
                'classification' => 'Fail',
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test create gradebook function
     * @group gradebook
     */
    public function test_create_gradebook()
    {
        $gradebook = new gradebook($this->db);
        // Test successful creation.
        $this->assertTrue($gradebook->create_gradebook($this->pid2['id']));
        $querytable = $this->query(array('columns' => array('paperid'), 'table' => 'gradebook_paper'));
        $expectedtable = array(
            0 => array(
                'paperid' => $this->pid1['id'],
            ),
            1 => array (
                'paperid' => $this->pid2['id'],
            )
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test get paper gradebook function
     * @group gradebook
     */
    public function test_get_paper_gradebook()
    {
        $gradebook = new gradebook($this->db);
        // Test get paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users[$this->student['id']] = array('raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1');
        $expected[$this->pid1['id']] = $users;
        $this->assertEquals($expected, $gradebook->get_paper_gradebook('paper', $this->pid1['id']));
        // Test get paper gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_paper_gradebook('paper', $this->pid2['id']));
    }

    /**
     * Test get detailed paper gradebook function
     * @group gradebook
     */
    public function test_get_user_detailed_paper_gradebook()
    {
        $gradebook = new gradebook($this->db);
        // Test get paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users[$this->student['id']] = array('student_id' => '1234567890', 'raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1', 'surname' => 'User1', 'first_names' => 'A');
        $expected[$this->pid1['id']] = $users;
        $this->assertEquals($expected, $gradebook->get_user_detailed_paper_gradebook($this->pid1['id']));
        // Test get paper gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_user_detailed_paper_gradebook($this->pid2['id']));
    }

    /**
     * Test get module gradebook function
     * @group gradebook
     */
    public function test_get_module_gradebook()
    {
        $gradebook = new gradebook($this->db);
        // Test get gradebook module - SUCCESS.
        $expected = array();
        $papers = array();
        $papers[$this->pid1['id']][$this->student['id']] = array('raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1');
        $expected[1] = $papers;
        $this->assertEquals($expected, $gradebook->get_module_gradebook('module', $this->module));
        // Test get module gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_module_gradebook('module', $this->module2));
    }

    /**
     * Test get paper gradebook function using paper external id
     * @group gradebook
     */
    public function test_get_paper_gradebook_ext()
    {
        $gradebook = new gradebook($this->db);
        // Test get paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users[1234567890] = array('raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1');
        $expected['ass1234'] = $users;
        $this->assertEquals($expected, $gradebook->get_paper_gradebook('extpaper', 'ass1234', 'test ExamSys api'));
        // Test get paper gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_paper_gradebook('extpaper', 'ass5678'));
    }

    /**
     * Test get module gradebook function sing module external id
     * @group gradebook
     */
    public function test_get_module_gradebook_ext()
    {
        $gradebook = new gradebook($this->db);
        // Test get gradebook module - SUCCESS.
        $expected = array();
        $papers = array();
        $papers['ass1234'][1234567890] = array('raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1');
        $expected['abc123def'] = $papers;
        $this->assertEquals($expected, $gradebook->get_module_gradebook('extmodule', 'abc123def', 'test ExamSys api'));
        // Test get module gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_module_gradebook('extmodule', 'mod5678'));
    }
}
