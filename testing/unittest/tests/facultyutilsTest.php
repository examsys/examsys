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
 * Test facultyutils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class facultyutilstest extends unittestdatabase
{
    /**
     * @var array Storage for faculty data in tests
     */
    private $faculty2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('faculty', 'core');
        $this->faculty2 = $datagenerator->create_faculty(array('externalid' => 'abcdef', 'externalsys' => 'external', 'code' => 'TEST'));
        \facultyutils::delete_faculty($this->faculty2['id'], $this->db);
    }

    /**
     * Test count schools in faculties
     * @group faculty
     */
    public function test_count_schools_in_faculty()
    {
        // Check count does not include deleted schools.
        $this->assertEquals(1, FacultyUtils::count_schools_in_faculty($this->faculty, $this->db));
    }

    /**
     * Test getting faculty name from external id
     * @group faculty
     */
    public function test_get_facultyid_from_externalid()
    {
        $this->assertEquals($this->faculty, FacultyUtils::get_facultyid_from_externalid('abcdefghi', 'external', $this->db));
    }

    /**
     * Test comparing  faculties with external list
     * @group faculty
     */
    public function test_diff_external_faculties_to_internal_faculties()
    {
        $external = array('abcdefghi', 'jklmnopq');
        $this->assertEquals(array($this->faculty2['externalid']), FacultyUtils::diff_external_faculties_to_internal_faculties($external, 'external', $this->db));
    }
}
