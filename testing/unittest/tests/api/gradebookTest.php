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
 * Test gradebook api class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class gradebookttest extends unittestdatabase
{

    /**
     * @var array Storage for paper data in tests
     */
    private $pid1;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid1 = $datagenerator->create_paper(array('papertitle' => 'Test create summative',
            'startdate' => '2016-01-25 09:00:00',
            'enddate' => '2016-01-25 10:00:00',
            'duration' => 60,
            'calendaryear' => 2016,
            'paperowner' => 'admin',
            'labs' => '1',
            'papertype' => '2',
            'externalid' => 'xyz987uvw',
            'externalsys' => 'test ExamSys api',
            'modulename' => 'Training Module'));
        $datagenerator = $this->get_datagenerator('gradebook', 'core');
        $datagenerator->create_paper(array('paperid' => $this->pid1['id'], 'timestamp' => '2016-01-25 10:10:00'));
        $datagenerator->create_user(array('paperid' => $this->pid1['id'], 'userid' => $this->student['id'], 'grade' => 60, 'adjustedgrade' => 62, 'classification' => 'Pass'));
        $datagenerator = $this->get_datagenerator('api', 'core');
        $client = $datagenerator->create_client(array('clientid' => 'test1', 'userid' => $this->admin['id'], 'secret' => 'test'));
        $datagenerator->create_external(array('clientid' => $client['clientid'], 'name' => 'test ExamSys api', 'type' => 'api'));
    }

    /**
     * Test gradebook paper
     * @group api
     */
    public function test_gradebook_paper()
    {
        $gradebook = new \api\gradebook($this->db);
        // Test paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users[$this->student['id']] = array('raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1');
        $expected[$this->pid1['id']] = $users;
        $this->assertEquals(array('OK', $expected), $gradebook->get('paper', $this->pid1['id']));
        // Test paper gradebook - ERROR not found.
        $this->assertEquals(array('BAD', array('Gradebook not found for paper 0')), $gradebook->get('paper', 0));
    }

    /**
     * Test gradebook module
     * @group api
     */
    public function test_gradebook_module()
    {
        $gradebook = new \api\gradebook($this->db);
        // Test module gradebook - SUCCESS.
        $expected = array();
        $papers = array();
        $papers[$this->pid1['id']][$this->student['id']] = array('raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1');
        $expected[$this->module] = $papers;
        $this->assertEquals(array('OK', $expected), $gradebook->get('module', $this->module));
        // Test module gradebook - ERROR not found.
        $this->assertEquals(array('BAD', array('Gradebook not found for module 0')), $gradebook->get('module', 0));
    }

    /**
     * Test gradebook paper using external ids
     * @group api
     */
    public function test_gradebook_paper_ext()
    {
        $gradebook = new \api\gradebook($this->db);
        // Test paper gradebook - SUCCESS.
        $expected = array();
        $users = array();
        $users['1234567890'] = array('raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1');
        $expected['xyz987uvw'] = $users;
        $this->assertEquals(array('OK', $expected), $gradebook->get('extpaper', 'xyz987uvw', 'test ExamSys api'));
        // Test paper gradebook - ERROR not found.
        $this->assertEquals(array('BAD', array('Gradebook not found for extpaper xyz123uvw')), $gradebook->get('extpaper', 'xyz123uvw', 'test ExamSys api'));
    }

    /**
     * Test gradebook module using external ids
     * @group api
     */
    public function test_gradebook_module_ext()
    {
        $gradebook = new \api\gradebook($this->db);
        // Test module gradebook - SUCCESS.
        $expected = array();
        $papers = array();
        $papers['xyz987uvw']['1234567890'] = array('raw_grade' => 60, 'adjusted_grade' => 62.0,
            'classification' => 'Pass', 'username' => 'test1');
        $expected['abc123def'] = $papers;
        $this->assertEquals(array('OK', $expected), $gradebook->get('extmodule', 'abc123def', 'test ExamSys api'));
        // Test module gradebook - ERROR not found.
        $this->assertEquals(array('BAD', array('Gradebook not found for extmodule abc789def')), $gradebook->get('extmodule', 'abc789def', 'test ExamSys api'));
    }
}
