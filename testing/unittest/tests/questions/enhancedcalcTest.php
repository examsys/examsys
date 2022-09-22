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
 * Test fill in the enhancedcalc question class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class enhancedcalctest extends unittestdatabase
{
    /**
     * @var array Storage for paper data in tests
     */
    private $pid1;

    /**
     * @var array Storage for question data in tests
     */
    private $question;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2018, 'academic_year' => '2018/19'));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid1 = $datagenerator->create_paper(array('papertitle' => 'test formative',
            'startdate' => '2018-02-19 00:00:00',
            'enddate' => '2032-02-02 00:00:00',
            'duration' => 60,
            'calendaryear' => 2018,
            'timezone' => 'Europe/London',
            'paperowner' => 'admin',
            'labs' => '1',
            'papertype' => '0',
            'modulename' => 'Training Module'));
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question = $datagenerator->create_question(array('user' => 'admin',
            'type' => 'enhancedcalc',
            'leadin' => '<div>A square is $A cm by $B cm. What is the area correct to a whole number?</div>',
            'scenario' => 'Calculation scenario',
            'display_method' => '',
            'score_method' => 'Allow partial Marks',
            'notes' => 'Calculation student notes',
            'settings' => '{"strictdisplay":true,"strictzeros":false,"dp":"0","tolerance_full":"0","fulltoltyp":"#","tolerance_partial":"0","parttoltyp":"#","marks_partial":0,"marks_incorrect":0,"marks_correct":3,"marks_unit":0,"show_units":true,"answers":[{"formula":"$A*$B","units":"cm"}],"vars":{"$A":{"min":"2","max":"10","inc":"1","dec":"0"},"$B":{"min":"5","max":"10","inc":"1","dec":"0"}}}'));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question['id'], 'screen' => 1, 'displaypos' => 1));
    }

    /**
     * Test question header setter
     * @group question
     */
    public function test_set_question_head()
    {
        $data = questiondata::get_datastore('enhancedcalc');
        $data->set_question_head();
        $this->assertTrue($data->displaydefault);
        $this->assertFalse($data->displaynotes);
        $data->notes = 'test';
        $data->set_question_head();
        $this->assertTrue($data->displaynotes);
    }

    /**
     * Test question question setter
     * @group question
     */
    public function test_set_question()
    {
        $data = questiondata::get_datastore('enhancedcalc');
        $cfg_web_root = $this->config->get('cfg_web_root');
        require_once $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';
        $question['object'] = new \EnhancedCalc($this->config);
        $data->question = $question;
        $useranswer = '{"vars":{"$A":2,"$B":8},"uans":""}';
        $data->set_question(1, $useranswer, '');
        $this->assertEquals($data->useranswers, $question['object']->alluseranswers);
    }

    /**
     * Test question option setter
     * @group question
     */
    public function test_set_option_answer()
    {
        ob_start(); // Start output buffering
        $data = questiondata::get_datastore('enhancedcalc');
        $data->marks = 0;
        $data->questionno = 1;
        $cfg_web_root = $this->config->get('cfg_web_root');
        require_once $cfg_web_root . 'plugins/questions/enhancedcalc/enhancedcalc.class.php';
        $propertyObj = \PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, array(), true);
        $questions = $propertyObj->build_paper(true, $this->question['id'], 1);
        $questions[1]['object'] = new \EnhancedCalc($this->config);
        $questions[1]['object']->load($questions[1]);
        $data->question = $questions[1];
        $useranswer = '{"vars":{"$A":2,"$B":8},"uans":""}';
        $data->set_option_answer(1, $useranswer, '', 1);
        $this->assertEquals(3, $data->marks);
        $output = ob_get_contents(); // Store buffer in variable
        ob_end_clean(); // End buffering and clean up
    }
}
