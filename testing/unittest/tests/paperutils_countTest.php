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
 * Test paperutils class count methods.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 */
class paperutils_counttest extends unittestdatabase
{

    /**
     * @var integer Storage for user id in tests
     */
    private $uid3;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2015, 'academic_year' => '2015/16'));
        $datagenerator->create_academic_year(array('calendar_year' => 2016, 'academic_year' => '2016/17'));
        $datagenerator = $this->get_datagenerator('users', 'core');
        $user = $datagenerator->create_user(array('surname' => 'staff', 'username' => 'staff1', 'grade' => 'University Lecturer', 'initials' => 'a',
            'title' => 'Dr', 'email' => 'staff1@example.com', 'gender' => 'Male', 'first_names' => 'a', 'yearofstudy' => null, 'roles' => 'Staff'));
        $this->uid3 = $user['id'];
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $pid1 = $datagenerator->create_paper(array('papertitle' => 'Test progressive',
            'calendaryear' => 2015,
            'modulename' => 'Training Module',
            'paperowner' => 'staff1',
            'papertype' => '1'));
        $pid2 = $datagenerator->create_paper(array('papertitle' => 'Test summative 1',
            'calendaryear' => 2016,
            'modulename' => 'Training Module',
            'paperowner' => 'staff1',
            'papertype' => '2'));
        $pid3 = $datagenerator->create_paper(array('papertitle' => 'Test survey',
            'calendaryear' => 2015,
            'modulename' => 'Online Help',
            'paperowner' => 'staff1',
            'papertype' => '3'));
        $pid4 = $datagenerator->create_paper(array('papertitle' => 'Test osce 1',
            'calendaryear' => 2016,
            'modulename' => 'Training Module',
            'paperowner' => 'staff1',
            'papertype' => '4'));
        $pid5 = $datagenerator->create_paper(array('papertitle' => 'Test osce 2',
            'calendaryear' => 2016,
            'modulename' => 'Training Module',
            'paperowner' => 'admin',
            'papertype' => '4'));
        $pid6 = $datagenerator->create_paper(array('papertitle' => 'Test summative 2',
            'calendaryear' => 2016,
            'modulename' => 'Training Module',
            'paperowner' => 'admin',
            'papertype' => '2'));
        \Paper_utils::delete_paper($pid6['id'], $this->admin['id'], $this->db);
        $this->set_active_user($this->admin['id']);
        \Paper_utils::remove_modules(array(), $pid2['id'], $this->db, $this->userobject, 'all');
        \Paper_utils::remove_modules(array(), $pid4['id'], $this->db, $this->userobject, 'all');
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $question = $datagenerator->create_question(array('user' => 'staff1',
            'leadin' => 'this is a test 1',
            'type' => 'enhancedcalc'));
        $datagenerator->add_to_module(array('question' => $question['id'], 'module' => 1));
        $datagenerator->add_question_to_paper(array('paper' => $pid1['id'], 'question' => $question['id'], 'screen' => 1, 'displaypos' => 1));
        $question = $datagenerator->create_question(array('user' => 'staff1',
            'leadin' => 'this is a test 2',
            'type' => 'enhancedcalc'));
        $datagenerator->add_question_to_paper(array('paper' => $pid4['id'], 'question' => $question['id'], 'screen' => 1, 'displaypos' => 1));
        $datagenerator->create_question(array('user' => 'staff1',
            'leadin' => 'this is a test 3',
            'type' => 'enhancedcalc'));
        $datagenerator->add_to_module(array('question' => $question['id'], 'module' => 2));
        $datagenerator->create_question(array('user' => 'staff1',
            'leadin' => 'this is a test 4',
            'type' => 'enhancedcalc',
            'deleted' => true));
    }

    /**
     * Test that the count unassigned papers method counts the number of papers
     * owned by a user that are not assigned to a module.
     *
     * @group PaperUtils
     */
    public function test_count_unassigned_papers()
    {
        // Get the ExamSys database connection.
        $db = $this->config->db;
        $paperutils = new PaperUtils();
        // Test a user who owns papers, where all are assigned or deleted.
        $this->assertEquals(0, $paperutils->count_unassigned_papers($this->admin['id'], $db));
        // Test a user who owns papers, and has some, but not all assigned.
        $this->assertEquals(2, $paperutils->count_unassigned_papers($this->uid3, $db));
        // Test a user who owns no papers.
        $this->assertEquals(0, $paperutils->count_unassigned_papers($this->student['id'], $db));
    }

    /**
     * Test that the count unnasigned questions method counts the number of
     * questions owned by a user that are not assigned to a module.
     *
     * @group PaperUtils
     */
    public function test_count_unassigned_questions()
    {
        // Get the ExamSys database connection.
        $db = $this->config->db;
        $paperutils = new PaperUtils();
        // Test a user who owns questions, where all are assigned or deleted.
        $this->assertEquals(0, $paperutils->count_unassigned_questions($this->admin['id'], $db));
        // Test a user who owns questions, and has some, but not all assigned.
        $this->assertEquals(1, $paperutils->count_unassigned_questions($this->uid3, $db));
        // Test a user who owns no questions.
        $this->assertEquals(0, $paperutils->count_unassigned_questions($this->student['id'], $db));
    }
}
