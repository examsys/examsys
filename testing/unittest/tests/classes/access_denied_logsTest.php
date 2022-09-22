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
 * Test access denied logs class
 *
 * @author Naseem Sarwar <naseem.sarwar@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class access_denied_logsTest extends unittestdatabase
{
    /* @var array Storage for denied log data in tests. */
    private $denied1;

    /* @var array Storage for denied log data in tests. */
    private $denied2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('incident', 'core');
        $this->denied1 = $datagenerator->create_denied(array('userid' => $this->admin['id'], 'page' => 'localhost'));
        $this->denied2 = $datagenerator->create_denied(array('userid' => $this->admin['id'], 'page' => 'index.php'));
    }

    /**
     * Test get all the logs from access denied record
     * @group log
     */
    public function test_get_access_denied_logs()
    {
        $log_obj = new access_denied_logs($this->db);
        $this->assertEquals(2, count($log_obj->get_access_denied_logs()));
    }

    /**
     * Test deleting a access denied log record
     * @group log
     */
    public function test_delete_a_access_denied_log()
    {
        $log_obj = new access_denied_logs($this->db);
        $this->assertTrue($log_obj->delete_a_access_denied_log($this->denied1['id']));
    }

    /**
     * Test deleting all the access denied logs records
     * @group log
     */
    public function test_delete_access_denied_logs()
    {
        $log_obj = new access_denied_logs($this->db);
        $this->assertTrue($log_obj->delete_access_denied_logs());
    }
}
