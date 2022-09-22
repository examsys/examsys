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
 * Test courseutils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class courseutilstest extends unittestdatabase
{
    /** @var array Storage for course data in tests. */
    private $course;

    /** @var array Storage for course data in tests. */
    private $course2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('course', 'core');
        $this->course = $datagenerator->create_course(array('name' => 'test', 'description' => 'a test', 'schoolid' => 1, 'externalid' => 'ABCD', 'externalsys' => 'external'));
        $this->course2 = $datagenerator->create_course(array('name' => 'test2', 'description' => 'a test 2', 'schoolid' => 1, 'externalid' => 'WXYZ', 'externalsys' => 'external'));
    }

    /**
     * Test comparing  courses with external list
     * @group courses
     */
    public function test_diff_external_courses_to_internal_courses()
    {
        $external = array($this->course['externalid'], 'EFGH', 'IJKL');
        $this->assertEquals(array($this->course2['externalid']), CourseUtils::diff_external_courses_to_internal_courses($external, 'external', $this->db));
    }

    /**
     * Test gettings course id  given external id
     * @group courses
     */
    public function test_get_courseid_from_externalid()
    {
        $this->assertEquals($this->course['id'], CourseUtils::get_courseid_from_externalid($this->course['externalid'], 'external', $this->db));
    }
}
