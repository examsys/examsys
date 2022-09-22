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
 * Test Killer Question class
 *
 * @author Naseem Sarwar <naseem.sarwar@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class Killer_Questiontest extends unittestdatabase
{
    /** @var array Storage for paper data in tests. */
    private $pid;

    /** @var array Storage for paper data in tests. */
    private $pid2;

    /* @var array Storage for question data in tests. */
    private $question;

    /* @var array Storage for question data in tests. */
    private $question2;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid = $datagenerator->create_paper(array('papertitle' => 'test osce',
            'startdate' => '2018-02-19 00:00:00',
            'enddate' => '2032-02-02 00:00:00',
            'duration' => 60,
            'paperowner' => 'admin',
            'labs' => '1',
            'papertype' => '4',
            'modulename' => 'Training Module'));
        $this->pid2 = $datagenerator->create_paper(array('papertitle' => 'test osce 2',
            'startdate' => '2018-02-19 00:00:00',
            'enddate' => '2032-02-02 00:00:00',
            'duration' => 60,
            'paperowner' => 'admin',
            'labs' => '1',
            'papertype' => '4',
            'modulename' => 'Training Module'));
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'true_false',
            'leadin' => 'Is the world round or flat?',
            'scenario' => 'This is a test'));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid['id'], 'question' => $this->question['id'], 'screen' => 1, 'displaypos' => 1));
        $this->question2 = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'true_false',
            'leadin' => 'Is the world round?',
            'scenario' => 'This is a test2'));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid['id'], 'question' => $this->question2['id'], 'screen' => 1, 'displaypos' => 1));
    }

    /**
     * Test checks a questions a killer
     * @group paper
     */
    public function test_is_a_killer_question()
    {
        // Checks a question is killer or not.
        $killer_question = new Killer_Question($this->pid['id'], $this->db);
        $this->assertFalse($killer_question->is_killer_question(1));
    }

    /**
     * Test sets a killer question
     * @group paper
     */
    public function test_set_question()
    {
        $killer_question = new Killer_Question($this->pid['id'], $this->db);
        $this->assertEquals(0, count($killer_question->get_questions()));
    }

    /**
     * Test counts killer questions by paper
     * @group paper
     */
    public function test_get_questions()
    {
        $killer_question = new Killer_Question($this->pid['id'], $this->db);
        $killer_question->set_question($this->question['id']);
        $killer_question->save();
        $this->assertEquals(1, count($killer_question->get_questions()));
    }

    /**
     * Test checks copy killer questions from one paper to the other
     * @group paper
     */
    public function test_copy_killer_questions()
    {
        $killer_question = new Killer_Question($this->pid['id'], $this->db);
        $killer_question->set_question($this->question['id']);
        $killer_question->save();
        $killer_question->copy_killer_questions($this->pid2['id']);
        $killer_question_new = new Killer_Question($this->pid2['id'], $this->db);
        $this->assertEquals(1, count($killer_question_new->get_questions()));
        $querytable = $this->query(array('columns' => array('paperID', 'q_id'), 'table' => 'killer_questions'));
        $expectedtable = array(
            0 => array(
                'paperID' => $this->pid['id'],
                'q_id' => $this->question['id']
            ),
            1 => array(
                'paperID' => $this->pid2['id'],
                'q_id' => $this->question['id']
            ),
        );
        $this->assertEquals($expectedtable, $querytable);
    }

    /**
     * Test unsets the killer question
     * @group paper
     */
    public function test_unset_question()
    {
        $killer_question = new Killer_Question($this->pid['id'], $this->db);
        $killer_question->unset_question($this->question['id']);
        $killer_question->save();
        $this->assertEquals(0, count($killer_question->get_questions()));
        $killer_question_new = new Killer_Question($this->pid2['id'], $this->db);
        $killer_question_new->unset_question($this->question['id']);
        $killer_question_new->save();
    }
}
