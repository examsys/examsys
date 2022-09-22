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
 * Test media handler class
 *
 * @author Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 onwards The University of Nottingham
 * @package tests
 */
class MediaHandlerTest extends unittestdatabase
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
     * Generate common data for test.
     * @throws \testing\datagenerator\no_database
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
                'q_option_order' => 'random',
                'display_method' => 'vertical',
                'score_method' => 'Mark per Option'
            )
        );
        $this->options1 = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question['id'],
                'option_text' => 'true',
                'correct' => 1,
                'marks_correct' => 2,
                'marks_incorrect' => -2,
                'marks_partial' => 0
            )
        );
        $this->options2 = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question['id'],
                'option_text' => 'false',
                'correct' => 1,
                'marks_correct' => 2,
                'marks_incorrect' => -2,
                'marks_partial' => 0
            )
        );
        $this->options3 = $datagenerator->add_options_to_question(
            array(
                'question' => $this->question['id'],
                'option_text' => 'maybe',
                'correct' => 1,
                'marks_correct' => 2,
                'marks_incorrect' => -2,
                'marks_partial' => 0
            )
        );
    }

    /**
     * Test inserting media.
     * @group media
     */
    public function testInsertMedia(): void
    {
        $source = 'test.png';
        $width = 100;
        $height = 100;
        $alt = 'test text';
        $owner = $this->admin['id'];
        $id = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        // Media.
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'source',
                    'width',
                    'height',
                    'alt',
                    'ownerid'
                ),
                'table' => 'media',
                'where' => array(
                    array(
                        'column' => 'id',
                        'value' => $id
                    )
                )
            )
        );
        $expectedTable = array(
            0 => array (
                'source' => $source,
                'width' => $width,
                'height' => $height,
                'alt' => $alt,
                'ownerid' => $owner
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
        //  Questions Media Link.
        \media_handler::linkQuestionToMedia(
            $id,
            $this->question['id'],
            0
        );
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'mediaid',
                    'qid',
                    'num',
                ),
                'table' => 'questions_media',
                'where' => array(
                    array(
                        'column' => 'qid',
                        'value' => $this->question['id']
                    )
                )
            )
        );
        $expectedTable = array(
            0 => array (
                'mediaid' => $id,
                'qid' => $this->question['id'],
                'num' => 0,
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test updating media.
     * @group media
     */
    public function testUpdateMedia(): void
    {
        $source = 'test.png';
        $width = 100;
        $height = 100;
        $alt = 'test text';
        $owner = $this->admin['id'];
        $id = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        $updatesource = 'test2.png';
        \media_handler::updateMedia(
            $id,
            $updatesource,
            $width,
            $height,
            $alt,
            $owner
        );
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'source',
                    'width',
                    'height',
                    'alt',
                    'ownerid'
                ),
                'table' => 'media',
                'where' => array(
                    array(
                        'column' => 'id',
                        'value' => $id
                    )
                )
            )
        );
        $expectedTable = array(
            0 => array (
                'source' => $updatesource,
                'width' => $width,
                'height' => $height,
                'alt' => $alt,
                'ownerid' => $owner
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test updating media with null values
     * @group media
     */
    public function testUpdateMediaNullValues(): void
    {
        $source = 'test.png';
        $width = 100;
        $height = 100;
        $alt = 'test text';
        $owner = $this->admin['id'];
        $id = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        $updatealt = null;
        $updatewidth = null;
        $updateheight = null;
        \media_handler::updateMedia(
            $id,
            $source,
            $updatewidth,
            $updateheight,
            $updatealt,
            $owner
        );
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'source',
                    'width',
                    'height',
                    'alt',
                    'ownerid'
                ),
                'table' => 'media',
                'where' => array(
                    array(
                        'column' => 'id',
                        'value' => $id
                    )
                )
            )
        );
        $expectedTable = array(
            0 => array (
                'source' => $source,
                'width' => $updatewidth,
                'height' => $updateheight,
                'alt' => $updatealt,
                'ownerid' => $owner
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test updating media alt text.
     * @group media
     */
    public function testUpdateMediaAltText(): void
    {
        $source = 'test.png';
        $width = 100;
        $height = 100;
        $alt = 'test text';
        $owner = $this->admin['id'];
        $id = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        $updatealt = 'I change this';
        \media_handler::updateMediaAltText(
            $id,
            $updatealt
        );
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'source',
                    'width',
                    'height',
                    'alt',
                    'ownerid'
                ),
                'table' => 'media',
                'where' => array(
                    array(
                        'column' => 'id',
                        'value' => $id
                    )
                )
            )
        );
        $expectedTable = array(
            0 => array (
                'source' => $source,
                'width' => $width,
                'height' => $height,
                'alt' => $updatealt,
                'ownerid' => $owner
            )
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test removing media.
     * @group media
     */
    public function testRemoveMedia(): void
    {
        $source = 'test.png';
        $width = 100;
        $height = 100;
        $alt = 'test text';
        $owner = $this->admin['id'];
        $id = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        \media_handler::linkQuestionToMedia(
            $id,
            $this->question['id'],
            0
        );
        //  Questions Media Link.
        \media_handler::unlinkQuestionFromMedia(
            $id,
            $this->question['id']
        );
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'mediaid',
                    'qid',
                    'num',
                ),
                'table' => 'questions_media',
                'where' => array(
                    array(
                        'column' => 'qid',
                        'value' => $this->question['id']
                    )
                )
            )
        );
        $expectedTable = array();
        $this->assertEquals($expectedTable, $queryTable);
        // Media.
        \media_handler::removeMedia(
            $id
        );
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'source',
                    'width',
                    'height',
                    'alt',
                    'ownerid'
                ),
                'table' => 'media',
                'where' => array(
                    array(
                        'column' => 'id',
                        'value' => $id
                    )
                )
            )
        );
        $expectedTable = array();
        $this->assertEquals($expectedTable, $queryTable);
    }


    /**
     * Test inserting options media.
     * @group media
     */
    public function testInsertOptionsMedia(): void
    {
        $source = 'test.png';
        $width = 100;
        $height = 100;
        $alt = 'test text';
        $owner = $this->admin['id'];
        $id = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        $id2 = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        //  Questions Media Link.
        \media_handler::linkOptionToMedia(
            $id2,
            $this->options1['id_num']
        );
        \media_handler::linkOptionToMedia(
            $id,
            $this->options2['id_num']
        );
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'mediaid',
                    'oid',
                ),
                'table' => 'options_media'
            )
        );
        $expectedTable = array(
            0 => array (
                'mediaid' => $id,
                'oid' => $this->options2['id_num']
            ),
            1 => array (
                'mediaid' => $id2,
                'oid' => $this->options1['id_num']
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }

    /**
     * Test removing options media.
     * @group media
     */
    public function testRemoveOptionsMedia(): void
    {
        $source = 'test.png';
        $width = 100;
        $height = 100;
        $alt = 'test text';
        $owner = $this->admin['id'];
        $id = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        $id2 = \media_handler::insertMedia(
            $source,
            $width,
            $height,
            $alt,
            $owner
        );
        //  Questions Media Link.
        \media_handler::linkOptionToMedia(
            $id2,
            $this->options1['id_num']
        );
        \media_handler::linkOptionToMedia(
            $id,
            $this->options2['id_num']
        );
        //  Questions Media Link.
        \media_handler::unlinkOptionFromMedia(
            $id,
            $this->options2['id_num']
        );
        $queryTable = $this->query(
            array(
                'columns' => array(
                    'mediaid',
                    'oid',
                ),
                'table' => 'options_media',
            )
        );
        $expectedTable = array(
            0 => array (
                'mediaid' => $id2,
                'oid' => $this->options1['id_num']
            ),
        );
        $this->assertEquals($expectedTable, $queryTable);
    }
}
