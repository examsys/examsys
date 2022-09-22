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
 * Test campus class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class campustest extends unittestdatabase
{
    /** @var array Storage for campus data in tests. */
    private $campus;

    /** @var array Storage for campus data in tests. */
    private $campus2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('labs', 'core');
        $this->campus = $datagenerator->create_campus(array('name' => 'Test Campus', 'isdefault' => 1));
        $datagenerator->create_lab(array('name' => 'Test lab', 'building' => 'Test building', 'room' => 1, 'campus' => $this->campus['name']));
        $this->campus2 = $datagenerator->create_campus(array('name' => 'Test Campus 2', 'isdefault' => 0));
    }

    /**
     * Test get all campus details function
     * @group campus
     */
    public function test_get_all_campus_details()
    {
        $campus = new campus($this->db);
        // Test details found success.
        $campusarray = array();
        $campusarray[$this->campus['id']] = array('campusname' => $this->campus['name'], 'isdefault' => $this->campus['isdefault']);
        $campusarray[$this->campus2['id']] = array('campusname' => $this->campus2['name'], 'isdefault' => $this->campus2['isdefault']);
        $this->assertEquals($campusarray, $campus->get_all_campus_details());
        // Test details not found error.
        $this->teardown_dataset();
        $this->assertEmpty($campus->get_all_campus_details());
    }

    /**
     * Test get campus details function
     * @group campus
     */
    public function test_get_campus_details()
    {
        $campus = new campus($this->db);
        // Test details found success.
        $expected = array('campusid' => $this->campus['id'], 'campusname' => $this->campus['name'], 'isdefault' => $this->campus['isdefault']);
        $this->assertEquals($expected, $campus->get_campus_details($this->campus['id']));
        // Test details not found error.
        $expected = false;
        $this->assertEquals($expected, $campus->get_campus_details(0));
    }

    /**
     * Test check campus name in use function
     * @group campus
     */
    public function test_check_campus_name_inuse()
    {
        $campus = new campus($this->db);
        // Test campus name in use - true.
        $this->assertTrue($campus->check_campus_name_inuse($this->campus['name']));
        // Test campus name in use - false.
        $this->assertFalse($campus->check_campus_name_inuse('Another campus'));
    }

    /**
     * Test check campus id in use function
     * @group campus
     */
    public function test_check_campus_in_use()
    {
        $campus = new campus($this->db);
        // Test campus in use - true.
        $this->assertTrue($campus->check_campus_in_use($this->campus['id']));
        // Test campus in use - false.
        $this->assertFalse($campus->check_campus_in_use($this->campus2['id']));
    }
}
