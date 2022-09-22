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
 * Test mapping utils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2019 onwards The University of Nottingham
 * @package tests
 */
class mappingutilsstest extends unittestdatabase
{
    /**
     * @var integer Storage for session id in tests
     */
    private $sessid;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('objective', 'core');
        $session = $datagenerator->create_session(array('idMod' => $this->module, 'occurrence' => '2017-01-09 11:00:00'));
        $this->sessid = $session['identifier'];
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $this->sessid, 'objective' => 'a'));
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $this->sessid, 'objective' => 'b', 'sequence' => 2));
        $datagenerator->create_objective(array('idMod' => $this->module, 'identifier' => $this->sessid, 'objective' => 'c', 'sequence' => 3));
    }

    /**
     * Test get objectives start id
     * @group mapping
     */
    public function test_get_objectives_start()
    {
        $expected = 126;
        $this->assertEquals($expected, \mappingutils::get_objectives_start());
    }

    /**
     * Test get sessions start id
     * @group mapping
     */
    public function test_get_sessions_start()
    {
        $expected = $this->sessid + 1;
        $this->assertEquals($expected, \mappingutils::get_sessions_start());
    }
}
