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
 * Tests for the QuestionUtils class
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package tests
 */
class QuestionUtilsTest extends unittestdatabase
{
    /**
     * @var array Storage for question data in tests
     */
    private $question;

    /**
     * @var array Storage for options data in tests
     */
    private $options1;

    /**
     * @var array Storage for options data in tests
     */
    private $options2;

    /**
     * @var array Storage for options data in tests
     */
    private $options3;

    /**
     * Generate data for test.
     * @throws \testing\datagenerator\not_found
     */
    public function datageneration(): void
    {
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question = $datagenerator->create_question(array('type' => 'mcq',
            'user' => 'admin',
            'status' => 1,
            'theme' => 'test theme',
            'scenario' => 'test scenario',
            'leadin' => 'test leadin',
            'notes' => 'test notes',
            'q_media' => '1517406311.png',
            'q_media_width' => 480,
            'q_media_height' => 105,
            'q_media_alt' => 'question image',
            'q_option_order' => 'random',
            'display_method' => 'vertical',
            'score_method' => 'Mark per Option'));
        $this->options1 = $datagenerator->add_options_to_question(array('question' => $this->question['id'],
            'option_text' => 'true',
            'correct' => 1,
            'o_media' => '1517409282.jpg',
            'o_media_width' => 951,
            'o_media_height' => 121,
            'o_media_alt' => 'option image',
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $this->options2 = $datagenerator->add_options_to_question(array('question' => $this->question['id'],
            'option_text' => 'false',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $this->options3 = $datagenerator->add_options_to_question(array('question' => $this->question['id'],
            'option_text' => 'maybe',
            'correct' => 1,
            'marks_correct' => 2,
            'marks_incorrect' => -2,
            'marks_partial' => 0));
        $datagenerator = $this->get_datagenerator('log', 'core');
        $meta = $datagenerator->create_metadata(array('userID' => $this->admin['id'], 'paperID' => 1, 'started' => '2017-01-01 00:00:00', 'completed' => '2017-01-02 00:00:00'));
        $datagenerator->create_summative(array('q_id' => 88, 'metadataID' => $meta['id']));
        $meta = $datagenerator->create_metadata(array('userID' => $this->student['id'], 'paperID' => 2, 'started' => '2017-01-01 00:00:00', 'completed' => '2017-01-02 00:00:00'));
        $datagenerator->create_summative(array('q_id' => 33, 'metadataID' => $meta['id']));
    }

    /**
     * Test that we can detect if a summative question has been answered by a student.
     *
     * @group questions
     */
    public function testQuestionAnsweredInSummative()
    {
        // Answered by student.
        $this->assertTrue(QuestionUtils::question_answered_in_summative(33, $this->db));
        // Answered by non student.
        $this->assertFalse(QuestionUtils::question_answered_in_summative(88, $this->db));
        // Not answered.
        $this->assertFalse(QuestionUtils::question_answered_in_summative(69, $this->db));
    }

    /**
     * Test get question details
     * @group questions
     */
    public function testGetCorrectAnswer()
    {
        $question = array();
        $expected['ID'] = $this->question['id'];
        $expected['type'] = $this->question['q_type'];
        $expected['score_method'] = $this->question['score_method'];
        $expected['correct'] = ',' . $this->options3['correct'];
        $expected['option_text'] = $this->options3['option_text'];
        $expected['correct_text'] = "\t" . $this->options1['option_text'] . "\t" . $this->options2['option_text'] . "\t" . $this->options3['option_text'];
        $this->assertEquals($expected, QuestionUtils::get_correct_answer($question, $this->question['id'], $this->db));
    }

    /**
     * Test fix correct (fill in the blank)
     * @group questions
     */
    public function testFixCorrect()
    {
        $expected = ',a';
        $q_type = 'blank';
        $correct = '';
        $old_correct = '';
        $option_text = '<div>test [blank]a,b,c[/blank]</div> ';
        $this->assertEquals($expected, QuestionUtils::fix_correct($q_type, $correct, $old_correct, $option_text));
    }

    /**
     * Test get question media
     * @group questions
     */
    public function testGetMedia()
    {
        $expected = array(
            new \MediaObject(
                $this->question['q_media_id'],
                $this->question['q_media'],
                $this->question['q_media_width'],
                $this->question['q_media_height'],
                $this->question['q_media_alt'],
                $this->question['q_media_owner'],
                $this->question['q_media_num'],
            )
        );
        $this->assertEquals($expected, QuestionUtils::getMedia($this->question['id']));
    }

    /**
     * Test set media object with no alt text
     * @group questions
     */
    public function testGetMediaNoAlt()
    {
        $media = new \MediaObject(
            $this->question['q_media_id'],
            $this->question['q_media'],
            $this->question['q_media_width'],
            $this->question['q_media_height'],
            null,
            $this->question['q_media_owner'],
            $this->question['q_media_num'],
        );
        $this->assertInstanceOf('MediaObject', $media);
    }

    /**
     * Test get question media
     * @group questions
     */
    public function testGetMediaAsString()
    {
        $expected = array(
            'id' => $this->question['q_media_id'],
            'source' => $this->question['q_media'],
            'width' => $this->question['q_media_width'],
            'height' => $this->question['q_media_height'],
            'alt' => $this->question['q_media_alt'],
            'owner' => $this->question['q_media_owner'],
            'num' => $this->question['q_media_num'],
        );
        $this->assertEquals($expected, QuestionUtils::getMediaAsString($this->question['id']));
    }

    /**
     * Test get question media
     * @group questions
     */
    public function testGetOptionMedia()
    {
        $expected = new \MediaObject(
            $this->options1['o_media_id'],
            $this->options1['o_media'],
            $this->options1['o_media_width'],
            $this->options1['o_media_height'],
            $this->options1['o_media_alt'],
            $this->options1['o_media_owner'],
            0,
        );
        $this->assertEquals($expected, QuestionUtils::getOptionMedia($this->options1['id_num']));
        $this->assertFalse(QuestionUtils::getOptionMedia($this->options2['id_num']));
    }
}
