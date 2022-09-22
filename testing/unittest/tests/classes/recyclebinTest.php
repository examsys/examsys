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
 * Test recyclebin class
 *
 * @author Mr Naseem Sarwar <naseem.sarwar@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class recyclebinTest extends unittestdatabase
{

    /**
     * @var integer Storage for school id in tests
     */
    private $school2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $this->school2 = $this->get_school_id('Training');
        $datagenerator = $this->get_datagenerator('faculty', 'core');
        $faculty = $datagenerator->create_faculty(array('code' => 'a100'));
        \FacultyUtils::delete_faculty($faculty['id'], $this->db);
        $datagenerator = $this->get_datagenerator('school', 'core');
        $school = $datagenerator->create_school(array('facultyID' => $faculty['id']));
        \SchoolUtils::delete_school($school['id'], $this->db);
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $module = $datagenerator->create_module(array('fullname' => 'Module test', 'moduleid' => 'RECYLE1'));
        \module_utils::delete_module($module['id'], $this->db);
        $datagenerator = $this->get_datagenerator('course', 'core');
        $course = $datagenerator->create_course(array('name' => 'computer Science', 'description' => 'a computer Science course', 'schoolid' => $this->school2));
        \CourseUtils::delete_course_by_id($course['id'], $this->db);
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $paper = $datagenerator->create_paper(array('papertitle' => 'test formative',
            'bidirectional' => '1',
            'fullscreen' => '0',
            'paperowner' => 'admin',
            'papertype' => '0',
            'modulename' => 'Training Module'));
        \Paper_utils::delete_paper($paper['id'], $this->admin['id'], $this->db);
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $datagenerator->create_question(array('user' => 'admin',
            'type' => 'blank',
            'leadin' => 'Test question',
            'deleted' => true));
        $datagenerator = $this->get_datagenerator('folder', 'core');
        $datagenerator->create_folder(array('ownerID' => $this->admin['id'], 'deleted' => true));
    }

    /**
     * Test count deleted from questions, papers, folders, schools, courses, modules and faculties
     * @group recyclebin
     */
    public function test_count_get_recyclebin_contents()
    {
        $this->set_active_user($this->admin['id']);
        $recyclebin = new recyclebin();
        $this->assertEquals(7, count($recyclebin->get_recyclebin_contents()));
    }

    /**
     * Test count deleted papers
     * @group recyclebin
     */
    public function test_count_get_papers_recyclebin_contents()
    {
        $this->set_active_user($this->admin['id']);
        $recyclebin = new recyclebin();
        $this->assertEquals(1, count($recyclebin->get_papers_recyclebin_contents()));
    }

    /**
     * Test count deleted questions
     * @group recyclebin
     */
    public function test_count_get_questions_recyclebin_contents()
    {
        $this->set_active_user($this->admin['id']);
        $recyclebin = new recyclebin();
        $this->assertEquals(1, count($recyclebin->get_questions_recyclebin_contents()));
    }

    /**
     * Test count deleted folders
     * @group recyclebin
     */
    public function test_count_get_folders_recyclebin_contents()
    {
        $this->set_active_user($this->admin['id']);
        $recyclebin = new recyclebin();
        $this->assertEquals(1, count($recyclebin->get_folders_recyclebin_contents()));
    }

    /**
     * Test count deleted schools
     * @group recyclebin
     */
    public function test_count_get_schools_recyclebin_contents()
    {
        $recyclebin = new recyclebin();
        $this->assertEquals(1, count($recyclebin->get_schools_recyclebin_contents()));
    }

    /**
     * Test count deleted
     * @group recyclebin
     */
    public function test_count_get_courses_recyclebin_contents()
    {
        $recyclebin = new recyclebin();
        $this->assertEquals(1, count($recyclebin->get_courses_recyclebin_contents()));
    }

    /**
     * Test count deleted modules
     * @group recyclebin
     */
    public function test_count_get_moduels_recyclebin_contents()
    {
        $recyclebin = new recyclebin();
        $this->assertEquals(1, count($recyclebin->get_modules_recyclebin_contents()));
    }

    /**
     * Test count deleted faculties in faculty table
     * @group recyclebin
     */
    public function test_count_get_faculties_recyclebin_contents()
    {
        $recyclebin = new recyclebin();
        $this->assertEquals(1, count($recyclebin->get_faculties_recyclebin_contents()));
    }
}
