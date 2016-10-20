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

use testing\unittest\unittestdatabase;

/**
 * Test gradebook class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class gradebooktest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "gradebookTest" . DIRECTORY_SEPARATOR . "gradebook.yml");
    }
    /**
     * Get expected data set from yml
     * @param string $name fixture file name
     * @return dataset
     */
    public function get_expected_data_set($name) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "gradebookTest" . DIRECTORY_SEPARATOR . $name . ".yml");
    }
    /**
     * Test paper graded function
     * @group gradebook
     */
    public function test_paper_graded() {
        $gradebook = new gradebook($this->db);
        // Test check paper graded - true
        $this->assertTrue($gradebook->paper_graded(1));
        // Test check paper graded - false
        $this->assertFalse($gradebook->paper_graded(2));
    }
    /**
     * Test store grade function
     * @group gradebook
     */
    public function test_store_grade() {
        $gradebook = new gradebook($this->db);
        // Test successful grade recording.
        $gradebook->create_gradebook(2);
        $this->assertTrue($gradebook->store_grade(1000, 2, 30, 32, 'Fail'));
        $querytable = $this->getConnection()->createQueryTable('gradebook_user', 'SELECT * FROM gradebook_user');
        $expectedtable = $this->get_expected_data_set('creategradebook')->getTable("gradebook_user");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test create gradebook function
     * @group gradebook
     */
    public function test_create_gradebook() {
        $gradebook = new gradebook($this->db);
        // Test successful creation.
        $this->assertTrue($gradebook->create_gradebook(2));
        $querytable = $this->getConnection()->createQueryTable('gradebook_paper', 'SELECT paperid FROM gradebook_paper');
        $expectedtable = $this->get_expected_data_set('creategradebook')->getTable("gradebook_paper");  
        $this->assertTablesEqual($expectedtable, $querytable);
    }
    /**
     * Test get paper gradebook function
     * @group gradebook
     */
    public function test_get_paper_gradebook() {
        $gradebook = new gradebook($this->db);
        // Test get paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users[1000] = array('raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit');
        $expected[1] = $users;
        $this->assertEquals($expected, $gradebook->get_paper_gradebook('paper', 1));
        // Test get paper gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_paper_gradebook('paper', 2));
    }
    /**
     * Test get detailed paper gradebook function
     * @group gradebook
     */
    public function test_get_user_detailed_paper_gradebook() {
        $gradebook = new gradebook($this->db);
        // Test get paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users[1000] = array('student_id' => 12345678, 'raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit', 'surname' => 'test', 'first_names' => '');
        $expected[1] = $users;
        $this->assertEquals($expected, $gradebook->get_user_detailed_paper_gradebook(1));
        // Test get paper gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_user_detailed_paper_gradebook(2));
    }
     /**
     * Test get module gradebook function
     * @group gradebook
     */
    public function test_get_module_gradebook() {
        $gradebook = new gradebook($this->db);
        // Test get gradebook module - SUCCESS.
        $expected = array();
        $papers = array();
        $papers[1][1000] = array('raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit');
        $expected[1] = $papers;
        $this->assertEquals($expected, $gradebook->get_module_gradebook('module', 1));
        // Test get module gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_module_gradebook('module', 2));
    }
    /**
     * Test get paper gradebook function using paper external id
     * @group gradebook
     */
    public function test_get_paper_gradebook_ext() {
        $gradebook = new gradebook($this->db);
        // Test get paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users[12345678] = array('raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit');
        $expected["ass1234"] = $users;
        $this->assertEquals($expected, $gradebook->get_paper_gradebook('extpaper', "ass1234"));
        // Test get paper gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_paper_gradebook('extpaper', "ass5678"));
    }
     /**
     * Test get module gradebook function sing module external id
     * @group gradebook
     */
    public function test_get_module_gradebook_ext() {
        $gradebook = new gradebook($this->db);
        // Test get gradebook module - SUCCESS.
        $expected = array();
        $papers = array();
        $papers["ass1234"][12345678] = array('raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit');
        $expected["mod1234"] = $papers;
        $this->assertEquals($expected, $gradebook->get_module_gradebook('extmodule', "mod1234"));
        // Test get module gradebook - ERROR not found.
        $this->assertFalse($gradebook->get_module_gradebook('extmodule', "mod5678"));
    }
}
