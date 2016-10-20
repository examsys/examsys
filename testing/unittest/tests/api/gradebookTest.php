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
 * Test gradebook api class
 * 
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class gradebookttest extends unittestdatabase {
    /**
     * Get init data set from yml
     * @return dataset
     */
    public function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet($this->get_base_fixture_directory() . "api" . DIRECTORY_SEPARATOR . "gradebookTest" . DIRECTORY_SEPARATOR . "gradebook.yml");
    }
    /**
     * Test gradebook paper
     * @group api
     */
    public function test_gradebook_paper() {
        $gradebook = new \api\gradebook($this->db);
        // Test paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users[1000] = array('raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit');
        $expected[1] = $users;
        $this->assertEquals(array('OK', $expected), $gradebook->get('paper', 1));
        // Test paper gradebook - ERROR not found.
        $this->assertEquals(array('BAD', array('Gradebook not found for paper 2')), $gradebook->get('paper', 2));
    }
    /**
     * Test gradebook module
     * @group api
     */
    public function test_gradebook_module() {
        $gradebook = new \api\gradebook($this->db);
        // Test module gradebook - SUCCESS.
        $expected = array();
        $papers = array();
        $papers[1][1000] = array('raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit');
        $expected[1] = $papers;
        $this->assertEquals(array('OK', $expected), $gradebook->get('module', 1));
         // Test module gradebook - ERROR not found.
        $this->assertEquals(array('BAD', array('Gradebook not found for module 2')), $gradebook->get('module', 2));
    }
    /**
     * Test gradebook paper using external ids
     * @group api
     */
    public function test_gradebook_paper_ext() {
        $gradebook = new \api\gradebook($this->db);
        // Test paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users["12345678"] = array('raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit');
        $expected["xyz987uvw"] = $users;
        $this->assertEquals(array('OK', $expected), $gradebook->get('extpaper', "xyz987uvw"));
        // Test paper gradebook - ERROR not found.
        $this->assertEquals(array('BAD', array('Gradebook not found for extpaper xyz123uvw')), $gradebook->get('extpaper', "xyz123uvw"));
    }
    /**
     * Test gradebook module using external ids
     * @group api
     */
    public function test_gradebook_module_ext() {
        $gradebook = new \api\gradebook($this->db);
        // Test module gradebook - SUCCESS.
        $expected = array();
        $papers = array();
        $papers["xyz987uvw"]["12345678"] = array('raw_grade' => 60, 'adjusted_grade' => 62,
                    'classification' => 'Pass', 'username' => 'unit');
        $expected["abc123def"] = $papers;
        $this->assertEquals(array('OK', $expected), $gradebook->get('extmodule', "abc123def"));
         // Test module gradebook - ERROR not found.
        $this->assertEquals(array('BAD', array('Gradebook not found for extmodule abc789def')), $gradebook->get('extmodule', "abc789def"));
    }
}
