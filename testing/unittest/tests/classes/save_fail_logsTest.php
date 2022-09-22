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
 * Test save fail logs class
 *
 * @author Naseem Sarwar <naseem.sarwar@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class save_fail_logsTest extends unittestdatabase
{

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $pid1 = $datagenerator->create_paper(array('papertitle' => 'test formative',
            'bidirectional' => '1',
            'fullscreen' => '1',
            'paperowner' => 'admin',
            'papertype' => '0',
            'modulename' => 'Training Module'));
        $pid2 = $datagenerator->create_paper(array('papertitle' => 'test formative 2',
            'bidirectional' => '1',
            'fullscreen' => '1',
            'paperowner' => 'admin',
            'papertype' => '0',
            'modulename' => 'Training Module'));
        $datagenerator = $this->get_datagenerator('incident', 'core');
        $datagenerator->create_fail(array('userid' => $this->admin['id'], 'paperid' => $pid1['id']));
        $datagenerator->create_fail(array('userid' => $this->admin['id'], 'paperid' => $pid2['id']));
    }

    /**
     * Test get all the logs from fail logs record
     * @group log
     */
    public function test_get_save_fail_logs()
    {
        $log_obj = new save_fail_logs($this->db);
        $this->assertEquals(2, count($log_obj->get_save_fail_logs()));
    }

    /**
     * Test deleting a save fail log record
     * @group log
     */
    public function test_delete_a_save_fail_log()
    {
        $log_obj = new save_fail_logs($this->db);
        $log_obj->delete_a_save_fail_log('1');
        $this->assertTrue($log_obj->delete_a_save_fail_log(1));
    }

    /**
     * Test deleting all the save fail logs records
     * @group log
     */
    public function test_delete_save_fail_logs()
    {
        $log_obj = new save_fail_logs($this->db);
        $this->assertTrue($log_obj->delete_save_fail_logs());
    }
}
