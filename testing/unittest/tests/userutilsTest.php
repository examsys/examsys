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
 * Test userutils class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class userutilstest extends unittestdatabase
{
    /**
     * @var array Storage for a student in tests
     */
    private $student1;

    /**
     * @var array Storage for a student in tests
     */
    private $student2;

    /**
     * @var integer Storage for module id in tests
     */
    private $module2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->module2 = $this->get_module_id('SYSTEM');
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $datagenerator->create_enrolment(array('userid' => $this->student['id'], 'moduleid' => $this->module, 'calendar_year' => 2016));
        $datagenerator = $this->get_datagenerator('users', 'core');
        $this->student1 = $datagenerator->create_user(
            array('roles' => 'Student',
                'sid' => '24680',
                'surname' => 'Smith',
                'special_needs' => array(
                    'extra_time' => 10,
                    'break_time' => 5
                )
            )
        );
        $this->student2 = $datagenerator->create_user(
            array('roles' => 'Student',
                'sid' => '13579',
                'surname' => 'Johnson',
                'special_needs' => array(
                    'background' => '#000000',
                    'foreground' => '#FFFFFF',
                    'textsize' => 110,
                    'font' => 'Verdana',
                )
            )
        );
    }

    /**
     * Test getting enrolment id
     * @group user
     */
    public function testGetEnrolementId()
    {
        // Enrolment exists.
        $this->assertIsInt(UserUtils::get_enrolement_id($this->student['id'], $this->module, 2016, $this->db));
        // Enrolment does not exist.
        $this->assertEquals(false, UserUtils::get_enrolement_id($this->student['id'], $this->module2, 2016, $this->db));
    }

    /**
     * Test updating user
     * @group user
     */
    public function testUpdateUser()
    {
        // Student ID exists.
        $this->assertEquals(true, UserUtils::update_user($this->student['id'], 'student1', '12345678', 'Mr', 'Joe', 'Baxter', 'joseph.baxter@example.com', 'TEST', 'Male', 1, 'Student', '12345678', $this->db));
        // Check tables update as expected.
        $querytable = $this->query(array('columns' => array('username', 'grade', 'title', 'initials', 'surname', 'first_names', 'email', 'gender'),
            'table' => 'users', 'where' => array(array('column' => 'id', 'value' => $this->student['id']))));
        $expectedtable = array(
            0 => array(
                'username' => 'student1',
                'grade' => 'TEST',
                'title' => 'Mr',
                'initials' => 'J',
                'surname' => 'Baxter',
                'first_names' => 'Joe',
                'email' => 'joseph.baxter@example.com',
                'gender' => 'Male'
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
        $querytable = $this->query(array('columns' => array('roleid'), 'table' => 'user_roles', 'where' => array(array('column' => 'userid', 'value' => $this->student['id']))));
        $expectedtable = array(
                0 => array (
                        'roleid' => 4,
                ),
        );
        $this->assertEquals($expectedtable, $querytable);
        $querytable = $this->query(array('columns' => array('userID', 'student_id'), 'table' => 'sid', 'where' => array(array('column' => 'userID', 'value' => $this->student['id']))));
        $expectedtable = array(
            0 => array (
                'userID' => $this->student['id'],
                'student_id' => '12345678'
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test getting extra time data
     * @group user
     */
    public function testGetExtraTime()
    {
        // Has extra time.
        $expected = array(
            'extratime' => $this->student1['extra_time'],
            'breaktime' => $this->student1['break_time'],
        );
        $this->assertEquals($expected, UserUtils::getExtraTime($this->student1['id'], $this->db));
        // No extra time.
        $expected = array(
            'extratime' => 0,
            'breaktime' => 0,
        );
        $this->assertEquals($expected, UserUtils::getExtraTime($this->student2['id'], $this->db));
    }

    /**
     * Test getting user profile data
     * @group user
     */
    public function testgetUserProfile()
    {
        // No profile info.
        $expected = array(
            'background' => UserObject::BGCOLOUR,
            'foreground' => UserObject::FGCOLOUR,
            'textsize' => UserObject::TEXTSIZE,
            'marks' => UserObject::MARKSCOLOUR,
            'theme' => UserObject::THEMECOLOUR,
            'label' => UserObject::LABELCOLOUR,
            'font' => UserObject::FONT,
            'unanswered' => UserObject::UNANSWEREDCOLOUR,
            'dismiss' => UserObject::DISMISSCOLOUR,
            'globaltheme' => UserObject::GLOBALTHEMECOLOUR,
            'globalthemefontcolour' => UserObject::GLOBALTHEMEFONTCOLOUR,
            'highlight' => UserObject::HIGHLIGHTCOLOUR
        );
        $this->assertEquals($expected, UserUtils::getUserProfile($this->student1['id'], $this->db));

        // Profile info.
        $expected = array(
            'background' => '#000000',
            'foreground' => '#FFFFFF',
            'textsize' => 110,
            'marks' => UserObject::MARKSCOLOUR,
            'theme' => UserObject::THEMECOLOUR,
            'label' => UserObject::LABELCOLOUR,
            'font' => 'Verdana',
            'unanswered' => UserObject::UNANSWEREDCOLOUR,
            'dismiss' => UserObject::DISMISSCOLOUR,
            'globaltheme' => UserObject::GLOBALTHEMECOLOUR,
            'globalthemefontcolour' => UserObject::GLOBALTHEMEFONTCOLOUR,
            'highlight' => UserObject::HIGHLIGHTCOLOUR
        );
        $this->assertEquals($expected, UserUtils::getUserProfile($this->student2['id'], $this->db));
    }
}
