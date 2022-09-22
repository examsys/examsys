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

/**
 * Tests the QuestionsMetadata class.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 onwards The University of Nottingham
 * @package tests
 */
class QuestionsMetadataTest extends \testing\unittest\unittestdatabase
{
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
        $datagenerator = $this->get_datagenerator('questions', 'core');
        $this->question = $datagenerator->create_question(
            array(
                'type' => 'mcq',
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
                'score_method' => 'Mark per Option',
                'externalref' => 'testvalue',
            )
        );
    }

    /**
     * Tests that the roles for the correct user are found.
     * @group questions
     */
    public function testSet(): void
    {
        // Insert.
        QuestionsMetadata::set('testtype', $this->question['id'], 'testvalue');
        $actual = $this->query(array('columns' => array('type', 'value', 'questionID'), 'table' => 'questions_metadata'));
        $expected = array(
            0 => array(
                'type' => 'externalref',
                'value' =>  $this->question['externalref'],
                'questionID' =>  $this->question['id'],
            ),
            1 => array(
                'type' => 'testtype',
                'value' =>  'testvalue',
                'questionID' =>  $this->question['id'],
            ),
        );
        $this->assertEquals($expected, $actual);
        // Update.
        QuestionsMetadata::set('externalref', $this->question['id'], '987654321');
        $actual = $this->query(array('columns' => array('type', 'value', 'questionID'), 'table' => 'questions_metadata'));
        $expected = array(
            0 => array(
                'type' => 'externalref',
                'value' => '987654321',
                'questionID' =>  $this->question['id'],
            ),
            1 => array(
                'type' => 'testtype',
                'value' =>  'testvalue',
                'questionID' =>  $this->question['id'],
            ),
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests that the roles for the correct user are found.
     * @group questions
     */
    public function testGet(): void
    {
        // Add another metadata so there is not just one in there.
        QuestionsMetadata::set('another testtype', $this->question['id'], 'another testvalue');
        // Confirm we get the right one.
        $actual = QuestionsMetadata::get('externalref', $this->question['id']);
        $expected = 'testvalue';
        $this->assertEquals($expected, $actual);
    }
}
