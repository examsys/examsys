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
 * Test abstract api functions
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class abstractmanagementtest extends unittestdatabase
{
    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('api', 'core');
        $client = $datagenerator->create_client(array('clientid' => 'test1', 'userid' => $this->admin['id'], 'secret' => 'test'));
        $datagenerator->create_external(array('clientid' => $client['clientid'], 'name' => 'test ExamSys api', 'type' => 'api'));
        $client = $datagenerator->create_client(array('clientid' => 'test2', 'userid' => $this->admin['id'], 'secret' => 'test2'));
        $datagenerator->create_external(array('name' => 'test ExamSys plugin', 'type' => 'plugin'));
    }

    /**
     * Test get external system api
     * @group api
     */
    public function test_get_external_system_api()
    {
        $faculty = new \api\facultymanagement($this->db, 'test1');
        $response = 'test ExamSys api';
        $this->assertEquals($response, $faculty->get_external_system('test ExamSys api'));
        $this->assertEquals($response, $faculty->get_external_system(null));
    }

    /**
     * Test get external system plugin
     * @group api
     */
    public function test_get_external_system_plugin()
    {
        $faculty = new \api\facultymanagement($this->db);
        $response = 'test ExamSys plugin';
        $this->assertEquals($response, $faculty->get_external_system('test ExamSys plugin'));
    }

    /**
     * Test get external system api super user
     * @group api
     */
    public function test_get_external_system_api_super()
    {
        $faculty = new \api\facultymanagement($this->db, 'test2');
        $response = 'test ExamSys api';
        $this->config->set_setting('api_allow_superuser', 1, 'boolean');
        $this->assertEquals($response, $faculty->get_external_system('test ExamSys api'));
        $this->config->set_setting('api_allow_superuser', 0, 'boolean');
        $response = null;
        $this->assertEquals($response, $faculty->get_external_system('test ExamSys api'));
    }
}
