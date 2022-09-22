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
 * Test schoolutils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class schoolutilstest extends unittestdatabase
{
    /**
     * @var integer id for default school in tests
     */
    private $school2;

    /**
     * @var array Storage for school in tests
     */
    private $school3;

    /**
     * @var integer Storage for faculty id in tests
     */
    private $faculty2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->school2 = $this->get_school_id('Training');
        $this->faculty2 = $this->get_faculty_id('Administrative and Support Units');
        $datagenerator = $this->get_datagenerator('school', 'core');
        $this->school3 = $datagenerator->create_school(array('school' => 'Test school 3', 'facultyID' => $this->faculty, 'externalid' => 'qwerty', 'externalsys' => 'external'));
        \schoolutils::delete_school($this->school3['id'], $this->db);
        $datagenerator = $this->get_datagenerator('course', 'core');
        $datagenerator->create_course(array('schoolid' => $this->school2));
        $course = $datagenerator->create_course(array('schoolid' => $this->school2));
        \CourseUtils::delete_course_by_id($course['id'], $this->db);
    }

    /**
     * Test updating a school
     * @group school
     */
    public function test_update_school()
    {
        // Check successful update.
        $this->assertTrue(SchoolUtils::update_school($this->school, $this->faculty, 'test update school', null, '123456', 'external', $this->db));
        // Check unsuccessful update - duplicate name.
        $this->assertFalse(SchoolUtils::update_school($this->school2, $this->faculty, 'test update school', null, '123456', null, $this->db));
        // Check unsuccessful update - no school supplied.
        $this->assertFalse(SchoolUtils::update_school($this->school2, $this->faculty, '', 'TST2', '678912', null, $this->db));
        // Check unsuccessful update - no faculty supplied.
        $this->assertFalse(SchoolUtils::update_school($this->school2, '', 'test update school 2', 'TST2', '678912', null, $this->db));
        // Check schools table update as expected.
        $querytable = $this->query(array('columns' => array('school', 'facultyID', 'code', 'externalid', 'externalsys'), 'table' => 'schools'));
        $expectedtable = array(
            0 => array(
                'school' => 'test update school',
                'facultyID' => $this->faculty,
                'code' => null,
                'externalid' => '123456',
                'externalsys' => 'external'),
            1 => array(
                'school' => 'Training',
                'facultyID' => $this->faculty2,
                'code' => null,
                'externalid' => 'berty',
                'externalsys' => 'external'),
            2 => array(
                'school' => $this->school3['school'],
                'facultyID' => $this->school3['facultyID'],
                'code' => $this->school3['code'],
                'externalid' => $this->school3['externalid'],
                'externalsys' => $this->school3['externalsys'])
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test checking is school in use
     * @group school
     */
    public function test_school_in_use()
    {
        // Check in use.
        $this->assertTrue(SchoolUtils::school_in_use($this->school2, $this->db));
        // Check not in use.
        $this->assertFalse(SchoolUtils::school_in_use($this->school, $this->db));
    }

    /**
     * Test getting school id from external id
     * @group school
     */
    public function test_get_schoolid_from_externalid()
    {
        $this->assertEquals($this->school, SchoolUtils::get_schoolid_from_externalid('ABC', 'external', $this->db));
    }

    /**
     * Test comparing  faculties with external list
     * @group school
     */
    public function test_diff_external_schools_to_internal_schools()
    {
        $external = array('ABC', 'berty', 'IJKL');
        $this->assertEquals(array('qwerty'), SchoolUtils::diff_external_schools_to_internal_schools($external, 'external', $this->db));
    }
}
