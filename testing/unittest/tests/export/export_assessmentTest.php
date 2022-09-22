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

use export\export_assessment;
use testing\unittest\unittestdatabase;

/**
 * Test export csv assessment class
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 onwards The University of Nottingham
 * @package tests
 */
class export_assessmentTest extends unittestdatabase
{
    /** @var array Storage for paper data in tests. */
    private $pid1;

    /** @var array Storage for paper data in tests. */
    private $pid2;

    /** @var array Storage for an extended matching question data in tests. */
    private $question1;

    /** @var array Storage for a dichotomous question data in tests. */
    private $question2;

    /** @var array Storage for a ranking question data in tests. */
    private $question3;

    /** @var array Storage for a MCQ question data in tests. */
    private $question4;

    /** @var array Storage for a true/false question data in tests. */
    private $question5;

    /** @var array Storage for an enhanced calculation question data in tests. */
    private $question6;

    /** @var array Storage for a linked enhanced calculation question data in tests. */
    private $question7;

    /** @var array Storage for an area question data in tests. */
    private $question8;

    /** @var array Storage for a hotspot question data in tests. */
    private $question9;

    /** @var array Storage for a labelling question data in tests. */
    private $question10;

    /** @var array Storage for a sct question data in tests. */
    private $question11;

    /** @var array Storage for a textbox question data in tests. */
    private $question12;

    /** @var array Storage for a mrq question data in tests. */
    private $question13;

    /** @var array Storage for a fill in the blank question data in tests. */
    private $question14;

    /** @var array Storage for a matrix question data in tests. */
    private $question15;

    /** @var array Storage for a likert question data in tests. */
    private $question16;

    /** @var array Storage for a random question data in tests. */
    private $question17;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $datagenerator->create_academic_year(array('calendar_year' => 2018, 'academic_year' => '2018/19'));
        $datagenerator = $this->get_datagenerator('modules', 'core');
        $datagenerator->create_enrolment(array('userid' => $this->student['id'], 'moduleid' => 1, 'calendar_year' => 2018));
        $datagenerator = $this->get_datagenerator('papers', 'core');
        $this->pid1 = $datagenerator->create_paper(
            array(
                'papertitle' => 'Paper 1',
                'calendaryear' => 2018,
                'modulename' => 'Training Module',
                'paperowner' => 'admin',
                'papertype' => '2'
            )
        );
        $logdatagenerator = $this->get_datagenerator('log', 'core');
        $meta = $logdatagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => $this->pid1['id'], 'started' => '2018-01-01 09:00:00', 'year' => 1));
        $this->pid2 = $datagenerator->create_paper(
            array(
                'papertitle' => 'Paper 2',
                'calendaryear' => 2018,
                'modulename' => 'Training Module',
                'paperowner' => 'admin',
                'papertype' => '2'
            )
        );
        $meta2 = $logdatagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => $this->pid2['id'], 'started' => '2018-01-01 09:00:00', 'year' => 1));
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question1['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'extmatch',
                'theme' => 'Paris - Extended Matching',
                'leadin' => '<div>The capital of France was established in the 3rd century BC.</div>',
                'scenario' => "<div>It is now known as;</div>|<div>It's football team is called</div>|<div>It's most famous landmark is the</div>|||||||",
                'score_method' => 'Mark per Option',
                'q_option_order' => 'random',
                'settings' => '[]',
                'externalref' => '123456',
            )
        );
        $logdatagenerator->create_summative(array('q_id' => $this->question1['question']['id'], 'metadataID' => $meta['id'], 'screen' => 1, 'user_answer' => '1|3|2', 'mark' => 3, 'adjmark' => 3));
        $this->question1['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question1['question']['id'],
                'option_text' => 'Paris',
                'correct' => '1|3|2|||||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question1['question']['options2'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question1['question']['id'],
                'option_text' => 'Eifel Tower',
                'correct' => '1|3|2|||||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question1['question']['options3'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question1['question']['id'],
                'option_text' => 'Paris Saint-Germain F.C.',
                'correct' => '1|3|2|||||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question1['question']['options4'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question1['question']['id'],
                'option_text' => 'Derby',
                'correct' => '1|3|2|||||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question1['question']['options5'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question1['question']['id'],
                'option_text' => 'Intu Centre',
                'correct' => '1|3|2|||||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question1['question']['options6'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question1['question']['id'],
                'option_text' => 'Derby County FC',
                'correct' => '1|3|2|||||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $correct = explode('|', $this->question1['question']['options1']['correct']);
        $this->question1['question']['expected1'] = $correct[0];
        $this->question1['question']['expected2'] = $correct[1];
        $this->question1['question']['expected3'] = $correct[2];
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question1['question']['id'], 'screen' => 1, 'displaypos' => 1));
        $this->question2['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'dichotomous',
                'leadin' => "<p>1 mark per correct option,&nbsp;alphabetic order</p>\r\n<p>These are countries in Europe</p>",
                'score_method' => 'Mark per Option',
                'q_option_order' => 'alphabetic',
                'settings' => '[]'
            )
        );
        $logdatagenerator->create_summative(array('q_id' => $this->question2['question']['id'], 'metadataID' => $meta['id'], 'screen' => 2, 'user_answer' => 'ttf', 'mark' => 3, 'adjmark' => 3));
        $this->question2['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question2['question']['id'],
                'option_text' => 'Italy',
                'correct' => 't',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question2['question']['options2'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question2['question']['id'],
                'option_text' => 'France',
                'correct' => 't',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question2['question']['options3'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question2['question']['id'],
                'option_text' => 'Canada',
                'correct' => 'f',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question2['question']['id'], 'screen' => 2, 'displaypos' => 2));
        $this->question3['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'rank',
                'leadin' => "<p>2&nbsp;mark per correctly selected option</p>\r\n<p>Please rank the following&nbsp;numbers that start with the letter T starting with the&nbsp;highest first</p>",
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'setting`s' => '[]'
            )
        );
        $logdatagenerator->create_summative(array('q_id' => $this->question3['question']['id'], 'metadataID' => $meta['id'], 'screen' => 3, 'user_answer' => '0,2,1,0,0,0,0', 'mark' => 14, 'adjmark' => 14));
        $this->question3['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question3['question']['id'],
                'option_text' => 'One',
                'correct' => '0',
                'marks_correct' => 2,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question3['question']['options2'] = $datagenerator->add_options_to_question(array('question' => $this->question3['question']['id'],
            'option_text' => 'Two',
            'correct' => '2',
            'marks_correct' => 2,
            'marks_incorrect' => 0,
            'marks_partial' => 0));
        $this->question3['question']['options3'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question3['question']['id'],
                'option_text' => 'Three',
                'correct' => '1',
                'marks_correct' => 2,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question3['question']['options4'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question3['question']['id'],
                'option_text' => 'Four',
                'correct' => '0',
                'marks_correct' => 2,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question3['question']['options5'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question3['question']['id'],
                'option_text' => 'Five',
                'correct' => '0',
                'marks_correct' => 2,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question3['question']['options6'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question3['question']['id'],
                'option_text' => 'Six',
                'correct' => '0',
                'marks_correct' => 2,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question3['question']['options7'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question3['question']['id'],
                'option_text' => 'Seven',
                'correct' => '0',
                'marks_correct' => 2,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question3['question']['expected1']['numeric'] = 'N/A';
        $this->question3['question']['expected2']['numeric']  = '2';
        $this->question3['question']['expected3']['numeric']  = '1';
        $this->question3['question']['expected4']['numeric']  = 'N/A';
        $this->question3['question']['expected5']['numeric']  = 'N/A';
        $this->question3['question']['expected6']['numeric']  = 'N/A';
        $this->question3['question']['expected7']['numeric']  = 'N/A';
        $this->question3['question']['expected1']['text'] = 'N/A';
        $this->question3['question']['expected2']['text']  = '2nd';
        $this->question3['question']['expected3']['text']  = '1st';
        $this->question3['question']['expected4']['text']  = 'N/A';
        $this->question3['question']['expected5']['text']  = 'N/A';
        $this->question3['question']['expected6']['text']  = 'N/A';
        $this->question3['question']['expected7']['text']  = 'N/A';
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question3['question']['id'], 'screen' => 3, 'displaypos' => 3));
        $this->question4['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'mcq',
                'leadin' => '<p>25 December is usually known as Christmas Day in the United Kingdom.</p>',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'random',
                'settings' => '[]',
                'externalref' => 'abcdef',
            )
        );
        $logdatagenerator->create_summative(array('q_id' => $this->question4['question']['id'], 'metadataID' => $meta['id'], 'screen' => 4, 'user_answer' => '1', 'mark' => 1, 'adjmark' => 1));
        $this->question4['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question4['question']['id'],
                'option_text' => 'True',
                'correct' => '1',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question4['question']['options2'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question4['question']['id'],
                'option_text' => 'False',
                'correct' => '1',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question4['question']['options3'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question4['question']['id'],
                'option_text' => 'Maybe',
                'correct' => '1',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question4['question']['id'], 'screen' => 4, 'displaypos' => 4));
        $this->question5['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'true_false',
                'leadin' => '<div>Christmas Day falls on 24th December</div>',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '[]'
            )
        );
        $logdatagenerator->create_summative(array('q_id' => $this->question5['question']['id'], 'metadataID' => $meta['id'], 'screen' => 5, 'user_answer' => 'f', 'mark' => 1, 'adjmark' => 1));
        $this->question5['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question5['question']['id'],
                'option_text' => '',
                'correct' => '1',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question5['question']['options1']['expected'] = 'f';
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question5['question']['id'], 'screen' => 5, 'displaypos' => 5));
        $this->question6['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'leadin' => '<div>A square is $A cm by $B cm. What is the area correct to a whole number?</div>',
                'scenario' => '<div>Calculation scenario</div>',
                'display_method' => 'display order',
                'score_method' => 'Allow partial Marks',
                'settings' => '{"strictdisplay":true,"strictzeros":false,"dp":"0","tolerance_full":"0","fulltoltyp":"#","tolerance_partial":"0","parttoltyp":"#","marks_partial":0,"marks_incorrect":0,"marks_correct":1,"marks_unit":0,"show_units":false,"answers":[{"formula":"$A*$B","units":"cm"}],"vars":{"$A":{"min":"2","max":"10","inc":"1","dec":"0"},"$B":{"min":"5","max":"10","inc":"1","dec":"0"}}}'
            )
        );
        $this->question6['question']['options1']['expected']['formula'] = '$A*$B';
        $this->question6['question']['options1']['expected']['answer'] = 36;
        $logdatagenerator->create_summative(array('q_id' => $this->question6['question']['id'], 'metadataID' => $meta['id'], 'screen' => 6, 'user_answer' => '{"vars":{"$A":6,"$B":6},"uans":"36","uansunit":"","uansnumb":"36","ans":{"guessedunits":"","formula_used":"$A*$B","units_used":"cm","tolerance_full":0,"tolerance_fullans":36,"tolerance_fullansneg":36,"tolerance_partial":0,"tolerance_partialans":36,"tolerance_partialansneg":36},"status":{"units":false,"exact":false,"overall":0},"cans":36}', 'mark' => 0, 'adjmark' => 0));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question6['question']['id'], 'screen' => 6, 'displaypos' => 6));
        $this->question7['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'enhancedcalc',
                'theme' => 'Linked Calculation Question',
                'leadin' => "<div>Answer to previous question multiplied by 10</div>\r\n<div>\$A * \$B</div>",
                'display_method' => 'display order',
                'score_method' => 'Allow partial Marks',
                'settings' => '{"strictdisplay":true,"strictzeros":false,"dp":"0","tolerance_full":0,"fulltoltyp":"#","tolerance_partial":0,"parttoltyp":"#","marks_partial":0,"marks_incorrect":0,"marks_correct":1,"marks_unit":"N\\/A","show_units":true,"answers":[{"formula":"$A*$B","units":""}],"vars":{"$A":{"min":"ans6","max":"ans6","inc":"0","dec":"0"},"$B":{"min":"10","max":"10","inc":"0","dec":"0"}}}'
            )
        );
        $this->question7['question']['options1']['expected']['formula'] = '$A*$B';
        $this->question7['question']['options1']['expected']['answer'] = 36;
        $this->question7['question']['options1']['expected']['variables'] = '36,10';
        $this->question7['question']['options1']['expected']['a'] = '6,6';
        $this->question7['question']['options1']['expected']['correct'] = '360';
        $logdatagenerator->create_summative(array('q_id' => $this->question7['question']['id'], 'metadataID' => $meta['id'], 'screen' => 7, 'user_answer' => '{"vars":{"$A":"36","$B":"10"},"uans":"360","uansunit":"","uansnumb":"360","ans":{"guessedunits":"","formula_used":"$A*$B","units_used":"","tolerance_full":0,"tolerance_fullans":360,"tolerance_fullansneg":360,"tolerance_partial":0,"tolerance_partialans":360,"tolerance_partialansneg":360},"status":{"units":true,"exact":true,"tolerance_partial":true,"tolerance_full":true,"strictdp":true,"overall":1},"cans":360,"cans_dist":"0"}', 'mark' => 1, 'adjmark' => 1));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question7['question']['id'], 'screen' => 7, 'displaypos' => 7));
        $this->question8['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'area',
                'leadin' => '<div>Draw around the front windscreen.</div>',
                'score_method' => 'Allow partial Marks',
                'q_option_order' => 'display order',
                'settings' => '{"correct_full":"90","error_full":"10","correct_partial":"80","error_partial":"20"}'
            )
        );
        $useranswer = '43.3,80.1,23.4,5872,1712,1459;ca,72,db,6b,e9,61,f9,51,fe,47,108,44,12b,42,1a4,4b,19c,60,194,6c,182,71,de,70,ca,72, ';
        $logdatagenerator->create_summative(array('q_id' => $this->question8['question']['id'], 'metadataID' => $meta['id'], 'screen' => 8, 'user_answer' => $useranswer, 'mark' => 0, 'adjmark' => 0));
        $this->question8['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question8['question']['id'],
                'option_text' => '',
                'correct' => '19d,3c,10c,41,cf,6a,14d,6b,18b,68,19d,3c',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0.5
            )
        );
        $answer_parts = explode(';', $useranswer);
        $this->question8['question']['options1']['expected']['area'] = export_assessment::hex_to_dec($this->question8['question']['options1']['correct'], ',');
        $this->question8['question']['options1']['expected']['answer'] = export_assessment::hex_to_dec($answer_parts[1]);
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question8['question']['id'], 'screen' => 8, 'displaypos' => 8));
        $this->question9['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'hotspot',
                'leadin' => 'A) Chocolate calculator',
                'scenario' => "<div>Look at the contents of the student's briefcase. Can you spot the contraband chocolate calculator?</div>",
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '[]'
            )
        );
        $logdatagenerator->create_summative(array('q_id' => $this->question9['question']['id'], 'metadataID' => $meta['id'], 'screen' => 9, 'user_answer' => '1,330,995', 'mark' => 1, 'adjmark' => 1));
        $this->question9['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question9['question']['id'],
                'option_text' => '',
                'correct' => 'Chocolate calculator~16711680~polygon~16a,399,152,3c7,1a9,3ed,106,407,f9,3a6~0~',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question9['question']['options1']['expected']['answer'] = '330x995';
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question9['question']['id'], 'screen' => 9, 'displaypos' => 9));
        $this->question10['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'labelling',
                'leadin' => "<p>Complete the food chain diagram</p>\r\n<p>Image and text labels, 1 mark per option, -0.5 incorrect</p>",
                'theme' => '-ve marking',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '[]'
            )
        );
        $logdatagenerator->create_summative(array('q_id' => $this->question10['question']['id'], 'metadataID' => $meta['id'], 'screen' => 10, 'user_answer' => '4$4;370$148$beetle3.png$t$371$275$earwig3.png$t$537$78$spider$t$539$447$plants$t$', 'mark' => 4, 'adjmark' => 4));
        $this->question10['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question10['question']['id'],
                'option_text' => ',beetle3.png~80~75,earwig3.png~80~92,snail3.png~100~47,spider,ant,fruit,plants',
                'correct' => '4144959;1;16777215;10;0;100;19;100;92;single;label;0$0$370$173$beetle3.png~80~75|1$0$371$300$earwig3.png~80~92|2$0$5$54$snail3.png~100~47|3$0$537$103$spider|4$0$5$78$ant|5$0$110$78$fruit|6$0$539$472$plants|;',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0.5
            )
        );
        $this->question10['question']['options1']['expected']['option1'] = 'beetle3.png';
        $this->question10['question']['options1']['expected']['option2'] = 'earwig3.png';
        $this->question10['question']['options1']['expected']['option3'] = 'spider';
        $this->question10['question']['options1']['expected']['option4'] = 'plants';
        $this->question10['question']['options1']['expected']['numeric1'] = 1;
        $this->question10['question']['options1']['expected']['numeric2'] = 2;
        $this->question10['question']['options1']['expected']['numeric3'] = 4;
        $this->question10['question']['options1']['expected']['numeric4'] = '';
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question10['question']['id'], 'screen' => 10, 'displaypos' => 10));
        $this->question11['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'sct',
                'leadin' => '<div>Consider: There has been a power cut.</div>~<div>You notice all the other lights and electrical items are off too.</div>',
                'scenario' => '<div>You sit down on your sofa in front of the TV, you place the pizza that was delivered moments ago on the coffee table beside you. You switch on netflix, login into your account and select the first episode of the new series. This is going to be epic. Suddenly the screen does black?</div>',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '[]'
            )
        );
        $useranswer = '1';
        $logdatagenerator->create_summative(array('q_id' => $this->question11['question']['id'], 'metadataID' => $meta['id'], 'screen' => 11, 'user_answer' => $useranswer, 'mark' => 0, 'adjmark' => 0));
        $this->question11['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question11['question']['id'],
                'option_text' => 'very unlikely',
                'correct' => '0',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question11['question']['options2'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question11['question']['id'],
                'option_text' => 'unlikely',
                'correct' => '0',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question11['question']['options3'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question11['question']['id'],
                'option_text' => 'neither likely nor unlikely',
                'correct' => '0',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question11['question']['options4'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question11['question']['id'],
                'option_text' => 'more likely',
                'correct' => '0',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question11['question']['options5'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question11['question']['id'],
                'option_text' => 'very likely',
                'correct' => '1',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question11['question']['options1']['expected']['answer'] = 5;
        $this->question11['question']['options1']['expected']['useranswer'] = 'very unlikely';
        $this->question11['question']['options1']['expected']['useranswernumeric'] = $useranswer;
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question11['question']['id'], 'screen' => 11, 'displaypos' => 11));
        $this->question12['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'textbox',
                'leadin' => '<p>Explain how you came to your answer</p>',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '{"columns":"80","rows":"4","editor":"mathjax","terms":""}'
            )
        );
        $useranswer = 'test mathjax $$\\theta$$';
        $logdatagenerator->create_summative(array('q_id' => $this->question12['question'] ['id'], 'metadataID' => $meta['id'], 'screen' => 12, 'user_answer' => $useranswer));
        $this->question12['question']['options1']['expected']['useranswer'] = $useranswer;
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question12['question']['id'], 'screen' => 12, 'displaypos' => 12));
        $this->question13['question']  = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'mrq',
                'leadin' => '<div>Which of these wheels belong to a car.</div>',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '[]'
            )
        );
        $this->question13['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question13['question']['id'],
                'option_text' => 'Steering wheel',
                'correct' => 'y',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question13['question']['options2'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question13['question']['id'],
                'option_text' => 'Spare wheel',
                'correct' => 'y',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question13['question']['options3'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question13['question']['id'],
                'option_text' => 'Wheel of Fortune',
                'correct' => 'n',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question13['question']['options4'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question13['question']['id'],
                'option_text' => 'Yoga Wheel',
                'correct' => 'n',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question13['question']['options1']['expected']['yn'] = ',' . $this->question13['question']['options1']['correct'] . ','
            . $this->question13['question']['options2']['correct'] . ',' . $this->question13['question']['options3']['correct'] . ','
            . $this->question13['question']['options4']['correct'];
        $logdatagenerator->create_summative(array('q_id' => $this->question13['question']['id'], 'metadataID' => $meta['id'], 'screen' => 13, 'user_answer' => 'yynn', 'mark' => 2, 'adjmark' => 2));
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question13['question']['id'], 'screen' => 13, 'displaypos' => 13));
        $this->question14['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'blank',
                'theme' => 'capitals',
                'leadin' => '<div>London is the capital of?</div>',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '[]'
            )
        );
        $useranswer = 'England';
        $logdatagenerator->create_summative(array('q_id' => $this->question14['question']['id'], 'metadataID' => $meta['id'], 'screen' => 14, 'user_answer' => '["' . $useranswer . '"]', 'mark' => 1, 'adjmark' => 1));
        $this->question14['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question14['question']['id'],
                'option_text' => '<div>London is the capital of [blank]England,Scotland,Wales,Northern Ireland[/blank]</div>',
                'correct' => '',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question14['question']['options1']['expected']['answer'] = $useranswer;
        $this->question14['question']['options1']['expected']['useranswer'] = $useranswer;
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question14['question']['id'], 'screen' => 14, 'displaypos' => 14));
        $this->question15['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'matrix',
                'scenario' => 'Word|Excel|PowerPoint|Access5|Publisher|Data File||||',
                'leadin' => "<p>Match the file types to the appropriate extension.</p>\r\n<p>Alphabetic&nbsp;option order, 1 mark per option</p>",
                'score_method' => 'Mark per Option',
                'q_option_order' => 'alphabetic',
                'settings' => '[]'
            )
        );
        $logdatagenerator->create_summative(array('q_id' => $this->question15['question']['id'], 'metadataID' => $meta['id'], 'screen' => 15, 'user_answer' => '3|4|2|5|1|6', 'mark' => 6, 'adjmark' => 6));
        $option1 = '.DOC';
        $option2 = '.XLS';
        $option3 = '.PPT';
        $option4 = '.MDB';
        $option5 = '.PUB';
        $option6 = '.DAT';
        $this->question15['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question15['question']['id'],
                'option_text' => $option5,
                'correct' => '3|4|2|5|1|6||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question15['question']['options2'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question15['question']['id'],
                'option_text' => $option3,
                'correct' => '3|4|2|5|1|6||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question15['question']['options3'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question15['question']['id'],
                'option_text' => $option1,
                'correct' => '3|4|2|5|1|6||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question15['question']['options4'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question15['question']['id'],
                'option_text' => $option2,
                'correct' => '3|4|2|5|1|6||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question15['question']['options5'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question15['question']['id'],
                'option_text' => $option4,
                'correct' => '3|4|2|5|1|6||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question15['question']['options6'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question15['question']['id'],
                'option_text' => $option6,
                'correct' => '3|4|2|5|1|6||||',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question15['question']['options1']['expected']['option'] = $option1;
        $this->question15['question']['options2']['expected']['option'] = $option2;
        $this->question15['question']['options3']['expected']['option'] = $option3;
        $this->question15['question']['options4']['expected']['option'] = $option4;
        $this->question15['question']['options5']['expected']['option'] = $option5;
        $this->question15['question']['options6']['expected']['option'] = $option6;
        $this->question15['question']['options1']['expected']['numeric'] = 3;
        $this->question15['question']['options2']['expected']['numeric'] = 4;
        $this->question15['question']['options3']['expected']['numeric'] = 2;
        $this->question15['question']['options4']['expected']['numeric'] = 5;
        $this->question15['question']['options5']['expected']['numeric'] = 1;
        $this->question15['question']['options6']['expected']['numeric'] = 6;
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question15['question']['id'], 'screen' => 15, 'displaypos' => 15));
        $this->question16['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'likert',
                'theme' => 'GENERAL FEEDBACK',
                'leadin' => '<div>I experienced&nbsp;no difficulties registering at the University</div>',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '[]'
            )
        );
        $useranswer = '3';
        $answer = 'NULL';
        $logdatagenerator->create_summative(array('q_id' => $this->question16['question']['id'], 'metadataID' => $meta['id'], 'screen' => 16, 'user_answer' => $useranswer, 'mark' => 0, 'adjmark' => 0));
        $this->question16['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question16['question']['id'],
                'correct' => $answer,
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question16['question']['options1']['expected']['useranswer'] = $useranswer;
        $this->question16['question']['options1']['expected']['answer'] = $answer;
        $datagenerator->add_question_to_paper(array('paper' => $this->pid1['id'], 'question' => $this->question16['question']['id'], 'screen' => 16, 'displaypos' => 16));
        $this->question17['question'] = $datagenerator->create_question(
            array(
                'user' => 'admin',
                'type' => 'random',
                'leadin' => '<div>I experienced&nbsp;no difficulties registering at the University</div>',
                'score_method' => 'Mark per Option',
                'q_option_order' => 'display order',
                'settings' => '[]'
            )
        );
        $datagenerator->add_to_random_block(array('question' => $this->question2['question']['id'], 'block' => $this->question17['question']['id']));
        $datagenerator->add_to_random_block(array('question' => $this->question3['question']['id'], 'block' => $this->question17['question']['id']));
        $datagenerator->add_to_random_block(array('question' => $this->question5['question']['id'], 'block' => $this->question17['question']['id']));
        $datagenerator->add_to_random_block(array('question' => $this->question13['question']['id'], 'block' => $this->question17['question']['id']));
        $this->question17['question']['options1'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question17['question']['id'],
                'correct' => '',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question17['question']['options2'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question17['question']['id'],
                'correct' => '',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question17['question']['options3'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question17['question']['id'],
                'correct' => '',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question17['question']['options4'] = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question17['question']['id'],
                'correct' => '',
                'marks_correct' => 1,
                'marks_incorrect' => 0,
                'marks_partial' => 0
            )
        );
        $this->question17['question']['options5'] = $datagenerator->add_question_to_paper(array('paper' => $this->pid2['id'], 'question' => $this->question17['question']['id'], 'screen' => 1, 'displaypos' => 1));

        $logdatagenerator->create_summative(array('q_id' => $this->question17['question']['id'], 'metadataID' => $meta2['id'], 'screen' => 1, 'user_answer' => 'f', 'mark' => 1, 'adjmark' => 1));
    }

    /**
     * Test assessment dynamic header
     * @group export
     */
    public function test_create_dymanic_header()
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, '');
        $paper_buffer = $properties->get_paper_questions();
        $exclusions = new Exclusion($this->pid1['id'], $this->db);
        $exclusions->load();
        $file = 'test.csv';
        $csvhandler = new \csv\csv_handler($file);
        $export = new \export\export_assessment($csvhandler);
        $export->create_dynamic_header($paper_buffer, $exclusions);
        $expected = array(
            // Q1.
            'Q1i [' . $this->question1['question']['externalref'] . ']',
            'Q1ii [' . $this->question1['question']['externalref'] . ']',
            'Q1iii [' . $this->question1['question']['externalref'] . ']',
            // Q2.
            'Q2A',
            'Q2B',
            'Q2C',
            // Q3.
            'Q3A',
            'Q3B',
            'Q3C',
            'Q3D',
            'Q3E',
            'Q3F',
            'Q3G',
            // Q4.
            'Q4 [' . $this->question4['question']['externalref'] . ']',
            // Q5.
            'Q5A',
            // Q6.
            'Q6:user',
            'Q6:correct',
            'Q6:variables',
            // Q7.
            'Q7:user',
            'Q7:correct',
            'Q7:variables',
            // Q8.
            'Q8',
            // Q9.
            'Q9A',
            // Q10.
            'Q10A',
            'Q10B',
            'Q10C',
            'Q10D',
            // Q11.
            'Q11',
            // Q12.
            'Q12',
            // Q13.
            'Q13A',
            'Q13B',
            'Q13C',
            'Q13D',
            // Q14.
            'Q14A',
            // Q15.
            'Q15A',
            'Q15B',
            'Q15C',
            'Q15D',
            'Q15E',
            'Q15F',
            // Q16.
            'Q16',
        );
        $this->assertEquals($expected, $export->dynamic_headers);
    }

    /**
     * Test assessment correct answer
     * @group export
     */
    public function test_create_correct_answer()
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, '');
        $paper_buffer = $properties->get_paper_questions();
        $exclusions = new Exclusion($this->pid1['id'], $this->db);
        $exclusions->load();
        $file = 'test.csv';
        $csvhandler = new \csv\csv_handler($file);
        $export = new \export\export_assessment($csvhandler);
        $language = 'en';
        $string['correctanswers'] = 'Correct Answers: ';
        // Text export.
        $mode = 'text';
        $expected = array(
            0 => array(
                $string['correctanswers'],
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $this->question1['question']['options1']['option_text'],
                $this->question1['question']['options3']['option_text'],
                $this->question1['question']['options2']['option_text'],
                $this->question2['question']['options1']['correct'],
                $this->question2['question']['options2']['correct'],
                $this->question2['question']['options3']['correct'],
                $this->question3['question']['expected1']['text'],
                $this->question3['question']['expected2']['text'],
                $this->question3['question']['expected3']['text'],
                $this->question3['question']['expected4']['text'],
                $this->question3['question']['expected5']['text'],
                $this->question3['question']['expected6']['text'],
                $this->question3['question']['expected7']['text'],
                $this->question4['question']['options1']['option_text'],
                $this->question5['question']['options1']['correct'],
                '',
                $this->question6['question']['options1']['expected']['formula'],
                '',
                '',
                $this->question7['question']['options1']['expected']['formula'],
                '',
                $this->question8['question']['options1']['expected']['area'] ,
                '',
                $this->question10['question']['options1']['expected']['option1'] ,
                $this->question10['question']['options1']['expected']['option2'] ,
                $this->question10['question']['options1']['expected']['option3'] ,
                $this->question10['question']['options1']['expected']['option4'] ,
                $this->question11['question']['options5']['option_text'],
                '',
                $this->question13['question']['options1']['option_text'],
                $this->question13['question']['options2']['option_text'],
                '',
                '',
                $this->question14['question']['options1']['expected']['answer'],
                $this->question15['question']['options1']['expected']['option'],
                $this->question15['question']['options2']['expected']['option'],
                $this->question15['question']['options3']['expected']['option'],
                $this->question15['question']['options4']['expected']['option'],
                $this->question15['question']['options5']['expected']['option'],
                $this->question15['question']['options6']['expected']['option'],
                ''
            )
        );
        $this->assertEquals($expected, $export->create_correct_answer($paper_buffer, $exclusions, $mode, $string, $language));
        // Numeric export.
        $mode = 'numeric';
        $expected_part14 = ',' . $this->question3['question']['expected1']['numeric'] . ','
            . $this->question3['question']['expected2']['numeric'] . ','
            . $this->question3['question']['expected3']['numeric'] . ','
            . $this->question3['question']['expected4']['numeric'] . ','
            . $this->question3['question']['expected5']['numeric'] . ','
            . $this->question3['question']['expected6']['numeric'] . ','
            . $this->question3['question']['expected7']['numeric'];
        $expected = array(
            0 => array(
                $string['correctanswers'],
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $this->question1['question']['expected1'] ,
                $this->question1['question']['expected2'] ,
                $this->question1['question']['expected3'] ,
                $this->question2['question']['options1']['correct'],
                $this->question2['question']['options2']['correct'],
                $this->question2['question']['options3']['correct'],
                $expected_part14,
                $this->question4['question']['options1']['correct'],
                $this->question5['question']['options1']['correct'],
                '',
                $this->question6['question']['options1']['expected']['formula'],
                '',
                '',
                $this->question7['question']['options1']['expected']['formula'],
                '',
                $this->question8['question']['options1']['expected']['area'] ,
                '',
                $this->question10['question']['options1']['expected']['numeric1'] ,
                $this->question10['question']['options1']['expected']['numeric2'] ,
                $this->question10['question']['options1']['expected']['numeric3'] ,
                $this->question10['question']['options1']['expected']['numeric4'] ,
                $this->question11['question']['options1']['expected']['answer'],
                '',
                $this->question13['question']['options1']['expected']['yn'],
                $this->question14['question']['options1']['expected']['answer'],
                $this->question15['question']['options1']['expected']['numeric'],
                $this->question15['question']['options2']['expected']['numeric'],
                $this->question15['question']['options3']['expected']['numeric'],
                $this->question15['question']['options4']['expected']['numeric'],
                $this->question15['question']['options5']['expected']['numeric'],
                $this->question15['question']['options6']['expected']['numeric'],
                $this->question16['question']['options1']['expected']['answer']
            )
        );
        $this->assertEquals($expected, $export->create_correct_answer($paper_buffer, $exclusions, $mode, $string, $language));
    }

    /**
     * Test assessment create data
     * @group export
     */
    public function test_create_data()
    {
        $properties = PaperProperties::get_paper_properties_by_id($this->pid1['id'], $this->db, '');
        $paper_buffer = $properties->get_paper_questions();
        $course = 'TEST2';
        $startdate = '2018-01-01 00:00:00';
        $enddate = '2018-02-01 11:00:00';
        $studentonly = true;
        $demo = false;
        $modules = '';
        $percentile = 100;
        $students = $properties->get_user_list($startdate, $enddate, $percentile, $studentonly, $modules);
        $student_list = implode(',', $students);
        $log_array = $properties->get_paper_assessment_data($course, $startdate, $enddate, $student_list, $studentonly, $demo);
        $exclusions = new Exclusion($this->pid1['id'], $this->db);
        $exclusions->load();
        $file = 'test.csv';
        $csvhandler = new \csv\csv_handler($file);
        $export = new \export\export_assessment($csvhandler);
        $language = 'en';
        $string['error_random'] = 'error';
        // Text export.
        $mode = 'text';
        $expected = array(
            1 => array(
                null,
                'Dr',
                'User1',
                'A',
                '1234567890',
                'TEST2',
                1,
                '2018-01-01 09:00:00',
                $this->question1['question']['options1']['option_text'],
                $this->question1['question']['options3']['option_text'],
                $this->question1['question']['options2']['option_text'],
                $this->question2['question']['options1']['correct'],
                $this->question2['question']['options2']['correct'],
                $this->question2['question']['options3']['correct'],
                $this->question3['question']['expected1']['text'],
                $this->question3['question']['expected2']['text'],
                $this->question3['question']['expected3']['text'],
                $this->question3['question']['expected4']['text'],
                $this->question3['question']['expected5']['text'],
                $this->question3['question']['expected6']['text'],
                $this->question3['question']['expected7']['text'],
                $this->question4['question']['options1']['option_text'],
                $this->question5['question']['options1']['expected'],
                $this->question6['question']['options1']['expected']['answer'],
                $this->question7['question']['options1']['expected']['answer'],
                $this->question7['question']['options1']['expected']['a'],
                $this->question7['question']['options1']['expected']['correct'],
                $this->question7['question']['options1']['expected']['correct'],
                $this->question7['question']['options1']['expected']['variables'],
                $this->question8['question']['options1']['expected']['answer'],
                $this->question9['question']['options1']['expected']['answer'] ,
                $this->question10['question']['options1']['expected']['option1'] ,
                $this->question10['question']['options1']['expected']['option2'] ,
                $this->question10['question']['options1']['expected']['option3'] ,
                $this->question10['question']['options1']['expected']['option4'] ,
                $this->question11['question']['options1']['expected']['useranswer'],
                $this->question12['question']['options1']['expected']['useranswer'],
                $this->question13['question']['options1']['option_text'],
                $this->question13['question']['options2']['option_text'],
                '',
                '',
                $this->question14['question']['options1']['expected']['useranswer'],
                $this->question15['question']['options1']['expected']['option'],
                $this->question15['question']['options2']['expected']['option'],
                $this->question15['question']['options3']['expected']['option'],
                $this->question15['question']['options4']['expected']['option'],
                $this->question15['question']['options5']['expected']['option'],
                $this->question15['question']['options6']['expected']['option'],
                ''
            )
        );
        $this->assertEquals($expected, $export->create_data($log_array, $paper_buffer, $exclusions, $mode, $string, $language));
        // Numeric export.
        $mode = 'numeric';
        $expected = array(
            1 => array(
                null,
                'Dr',
                'User1',
                'A',
                '1234567890',
                'TEST2',
                1,
                '2018-01-01 09:00:00',
                $this->question1['question']['expected1'] ,
                $this->question1['question']['expected2'] ,
                $this->question1['question']['expected3'] ,
                $this->question2['question']['options1']['correct'],
                $this->question2['question']['options2']['correct'],
                $this->question2['question']['options3']['correct'],
                $this->question3['question']['expected1']['numeric'],
                $this->question3['question']['expected2']['numeric'],
                $this->question3['question']['expected3']['numeric'],
                $this->question3['question']['expected4']['numeric'],
                $this->question3['question']['expected5']['numeric'],
                $this->question3['question']['expected6']['numeric'],
                $this->question3['question']['expected7']['numeric'],
                $this->question4['question']['options1']['correct'],
                $this->question5['question']['options1']['expected'],
                $this->question6['question']['options1']['expected']['answer'],
                $this->question7['question']['options1']['expected']['answer'],
                $this->question7['question']['options1']['expected']['a'],
                $this->question7['question']['options1']['expected']['correct'],
                $this->question7['question']['options1']['expected']['correct'],
                $this->question7['question']['options1']['expected']['variables'],
                $this->question8['question']['options1']['expected']['answer'],
                $this->question9['question']['options1']['expected']['answer'],
                $this->question10['question']['options1']['expected']['numeric1'] ,
                $this->question10['question']['options1']['expected']['numeric2'] ,
                $this->question10['question']['options1']['expected']['numeric3'] ,
                $this->question10['question']['options1']['expected']['numeric4'] ,
                $this->question11['question']['options1']['expected']['useranswernumeric'],
                $this->question12['question']['options1']['expected']['useranswer'],
                $this->question13['question']['options1']['correct'],
                $this->question13['question']['options2']['correct'],
                $this->question13['question']['options3']['correct'],
                $this->question13['question']['options4']['correct'],
                $this->question14['question']['options1']['expected']['useranswer'],
                $this->question15['question']['options1']['expected']['numeric'],
                $this->question15['question']['options2']['expected']['numeric'],
                $this->question15['question']['options3']['expected']['numeric'],
                $this->question15['question']['options4']['expected']['numeric'],
                $this->question15['question']['options5']['expected']['numeric'],
                $this->question15['question']['options6']['expected']['numeric'],
                $this->question16['question']['options1']['expected']['useranswer']
            )
        );
        $this->assertEquals($expected, $export->create_data($log_array, $paper_buffer, $exclusions, $mode, $string, $language));
    }
}
