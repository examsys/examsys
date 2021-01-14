<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

namespace testing\behat\steps\frontend;

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Question creation and manipulation step definitions.
 *
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait Question
{
    /**
     * Select a question type from the module page.
     *
     * @Given I select a :type question type
     * @param string $type The type of question
     */
    public function iSelectAQuestionType(string $type): void
    {
        $this->i_click('New Question', 'link');
        $this->i_focus_popup('Rogō: New Question');
        $this->i_click($type, 'question_type');
        $this->only_main_window();
        $this->i_focus_main_window();
        $this->i_wait_for_page_to_load();
    }

    /**
     * Creates a new question of the specified type.
     *
     * @Given I create a new :type question:
     * @param string $type The type of question
     * @param TableNode $data The parameters used to create the question
     */
    public function iCreateANewQuestion($type, TableNode $data): void
    {
        switch ($type) {
            case 'area':
                $this->createArea($data);
                break;
            case 'enhancedcalc':
                $this->createEnhancedcalc($data);
                break;
            case 'dichotomous':
                $this->createDichotomous($data);
                break;
            case 'extmatch':
                $this->createExtmatch($data);
                break;
            case 'blank':
                $this->createBlank($data);
                break;
            case 'info':
                $this->createInfo($data);
                break;
            case 'matrix':
                $this->createMatrix($data);
                break;
            case 'hotspot':
                $this->createHotspot($data);
                break;
            case 'labelling':
                $this->createLabelling($data);
                break;
            case 'likert':
                $this->createLikert($data);
                break;
            case 'mcq':
                $this->createMcq($data);
                break;
            case 'mrq':
                $this->createMrq($data);
                break;
            case 'keyword_based':
                $this->createKeywordBased($data);
                break;
            case 'random':
                $this->createRandom($data);
                break;
            case 'rank':
                $this->createRank($data);
                break;
            case 'sct':
                $this->createSct($data);
                break;
            case 'textbox':
                $this->createTextbox($data);
                break;
            case 'true_false':
                $this->createTrueFalse($data);
                break;
            default:
                throw new PendingException('No handler for creating ' . $type . 'questions');
        }
    }

    /**
     * Add a question via a question bank popup
     * @Given I add the following questions via :bank:
     * @param string $name title of question bank popup
     * @param TableNode $data table of question leadins
     */
    public function iAddTheFollowingQuestionsVia(string $name, TableNode $data): void
    {
        $this->i_focus_popup($name);
        // Pop is using iframes so need to switch to it in order to be able to select the checkboxes.
        $this->iFocusOnIframe('iframeurl');
        $rows = $data->getRows();
        $questions = array();
        foreach ($rows as $row) {
            $question = array_shift($row);
            $leadin = $this->find('bank_question_leadin', $question);
            $questions[] = $this->getAttribute($leadin, 'data-qid');
        }
        $this->selectCheckPoints('q', $questions);
        $this->i_focus_popup($name);
        $this->i_click('Add Questions', 'button');
        $this->i_focus_main_window();
    }

    /**
     * Check questions are displayed in paper list.
     * @param TableNode $table table of question leadins
     * @Then /^I should see questions:$/
     * @throws Exception
     */
    public function iSeeQuestions(TableNode $table): void
    {
        $rows = $table->getRows();
        foreach ($rows as $row) {
            $question = array_shift($row);
            $found = $this->find('paper_question_leadin', $question);
            if (empty($found)) {
                throw new Exception('the question "' . $question . '" could not been found');
            }
        }
    }

    /**
     * Wait for the add question ajax to finish loading on the page
     * @Given I wait for questions to load
     */
    public function iWaitForQuestionsToLoad(): void
    {
        $this->iWaitForElement('id', 'link_1');
    }

    /**
     * Adds keywords to a question:
     * @Given I add keyword :keyword
     * @param string $keyword keyword to check
     */
    public function iAddKeywords(string $keyword): void
    {
        // Bottom Bar obscures page elements so need to scroll so we can draw.
        $this->scrollToElement('#keyword-select');
        $this->i_click($keyword, 'checkbox');
    }

    /**
     * Creates a area question.
     *
     * @param TableNode $data
     */
    protected function createArea(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->attachFileToField('q_media', $fields['file']);
        $this->pressButton('submit_media');
        $this->genericfields($fields);
        // Bottom Bar obscures page elements so need to scroll so we can draw.
        $this->scrollToElement('#canvas1');
        // Get coordinates and validate them.
        $coords = explode(',', $fields['coordinates']);
        if (!empty($coords) and count($coords) % 2 == 0) {
            $coordinates = array();
            $count = 0;
            for ($i = 0; $i < count($coords); $i++) {
                $coordinates[$count]['x'] = $coords[$i];
                $coordinates[$count]['y'] = $coords[$i + 1];
                $i++;
                $count++;
            }
            $this->drawAPolygon('canvas1', $coordinates);
        }
    }

    /**
     * Creates a enhancedcalc question.
     *
     * @param TableNode $data
     */
    protected function createEnhancedcalc(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->genericfields($fields);
        $this->fillField('option_min1', $fields['variable_min_1']);
        $this->fillField('option_max1', $fields['variable_max_1']);
        $this->fillField('option_decimals1', $fields['variable_decimal_1']);
        $this->fillField('option_increment1', $fields['variable_increment_1']);
        $this->fillField('option_formula1', $fields['formula_1']);
    }

    /**
     * Creates a dichotomous question.
     *
     * @param TableNode $data
     */
    protected function createDichotomous(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->genericfields($fields);
        $this->fillField('option_text1', $fields['stem_1']);
        if ($fields['stem_true_1'] == 1) {
            $select = 'option_correct1_t';
        } else {
            $select = 'option_correct1_f';
        }
        $this->selectRadio($select);
        $this->fillField('option_text2', $fields['stem_2']);
        if ($fields['stem_true_2'] == 1) {
            $select = 'option_correct2_t';
        } else {
            $select = 'option_correct2_f';
        }
        $this->selectRadio($select);
    }

    /**
     * Creates a extmatch question.
     *
     * @param TableNode $data
     */
    protected function createExtmatch(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('leadin', $fields['leadin']);
        $this->fillTinyMCE('question_stem1', $fields['stem_1']);
        $this->fillTinyMCE('question_stem2', $fields['stem_2']);
        $this->fillField('option_text1', $fields['option_1']);
        $this->fillField('option_text2', $fields['option_2']);
        $this->fillField('option_text3', $fields['option_3']);
        $this->fillDropDown('option_correct1', json_decode($fields['stem_select_1']));
        $this->fillDropDown('option_correct2', json_decode($fields['stem_select_2']));
    }

    /**
     * Creates a blank question.
     *
     * @param TableNode $data
     */
    protected function createBlank(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('leadin', $fields['leadin']);
        $this->fillTinyMCE('option_text', $fields['question']);
    }

    /**
     * Creates a info block.
     *
     * @param TableNode $data
     */
    protected function createInfo(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->fillField('theme', $fields['theme']);
        $this->fillTinyMCE('leadin', $fields['text']);
    }

    /**
     * Creates a matrix question.
     *
     * @param TableNode $data
     */
    protected function createMatrix(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('leadin', $fields['leadin']);
        $this->fillField('question_stem1', $fields['row_1']);
        $this->fillField('question_stem2', $fields['row_2']);
        $this->fillField('option_text1', $fields['column_1']);
        $this->fillField('option_text2', $fields['column_2']);
        $this->selectRadio('option_correct1_' . $fields['select_1']);
        $this->selectRadio('option_correct2_' . $fields['select_2']);
    }

    /**
     * Creates a hotspot question.
     *
     * @param TableNode $data
     */
    protected function createHotspot(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->attachFileToField('q_media', $fields['file']);
        $this->pressButton('submit_media');
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('scenario', $fields['scenario']);
        // Draw Hotspots.
        $hotspots = array($fields['hotspot_1'], $fields['hotspot_2'], $fields['hotspot_3']);
        $layer = 0;
        $last = count($hotspots) - 1;
        foreach ($hotspots as $h) {
            // Add label.
            $key = $this->find('xpath', '//*[@id="question1-layer-' . $layer . '"]//*[@class="mainarea"]//*[@class="textarea"]');
            $keynum = $layer + 1;
            $key->setValue('key' . $keynum);
            // Draw hotspot.
            $hotspot = explode(',', $h);
            $shape = $hotspot[0];
            $coords = array_slice($hotspot, 1);
            // Get coordinates and validate them.
            if (!empty($coords) and count($coords) % 2 == 0) {
                $coordinates = array();
                $count = 0;
                for ($i = 0; $i < count($coords); $i++) {
                    $coordinates[$count]['x'] = $coords[$i];
                    $coordinates[$count]['y'] = $coords[$i + 1];
                    $i++;
                    $count++;
                }
                $this->addAHotspot($shape, $coordinates);
            }
            if ($layer < $last) {
                // Add another layer.
                $addlayer = $this->find('id', 'question1-add_layer');
                $addlayer->click();
            }
            $layer++;
        }
    }

    /**
     * Creates a labelling question.
     *
     * @param TableNode $data
     */
    protected function createLabelling(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->attachFileToField('q_media', $fields['file']);
        $this->pressButton('submit_media');
        $this->genericfields($fields);
        // Bottom Bar obscures page elements so need to scroll so we can draw.
        $this->scrollToElement('#leadin_ifr');
        // Get coordinates and validate them.
        $coords = explode(',', $fields['coordinates']);
        if (!empty($coords) and count($coords) % 2 == 0) {
            $coordinates = array();
            $count = 0;
            for ($i = 0; $i < count($coords); $i++) {
                $coordinates[$count]['x'] = $coords[$i];
                $coordinates[$count]['y'] = $coords[$i + 1];
                $i++;
                $count++;
            }
            $this->addALabel($coordinates);
        }
    }

    /**
     * Creates a likert question.
     *
     * @param TableNode $data
     */
    protected function createLikert(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->genericfields($fields);
        $this->fillDropDown('scale_type', array($fields['scale']));
    }

    /**
     * Creates a mcq question.
     *
     * @param TableNode $data
     */
    protected function createMcq(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->genericfields($fields);
        $this->fillTinyMCE('option_text1', $fields['option_1']);
        $this->fillTinyMCE('option_text2', $fields['option_2']);
        $this->fillTinyMCE('option_text3', $fields['option_3']);
        // Bottom Bar obscures page elements so need to scroll so we can click.
        $this->scrollToElement('#option_correct_fback1');
        $this->selectRadio('option_correct' . $fields['correct']);
    }

    /**
     * Creates a mrq question.
     *
     * @param TableNode $data
     */
    protected function createMrq(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->genericfields($fields);
        $this->fillTinyMCE('option_text1', $fields['option_1']);
        $this->fillTinyMCE('option_text2', $fields['option_2']);
        $this->fillTinyMCE('option_text3', $fields['option_3']);
        // Bottom Bar obscures page elements so need to scroll so we can click.
        $this->scrollToElement('#option_correct_fback1');
        $this->selectCheckPoints('option_correct', json_decode($fields['correct']));
    }

    /**
     * Creates a keyword_based question.
     *
     * @param TableNode $data
     */
    protected function createKeywordBased(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->fillField('leadin', $fields['description']);
        $this->fillDropDown('option_text1', array($fields['keyword']));
    }

    /**
     * Creates a random question.
     *
     * @param TableNode $data
     */
    protected function createRandom(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->fillField('leadin', $fields['description']);
        $this->i_click('Add Questions(s)', 'button');
        $questions = json_decode($fields['questions']);
        $questionarray = array();
        foreach ($questions as $q) {
            $questionarray[] = array($q);
        }
        $questionstable = new TableNode($questionarray);
        $this->iAddTheFollowingQuestionsVia('Questions Bank', $questionstable);
    }

    /**
     * Creates a rank question.
     *
     * @param TableNode $data
     */
    protected function createRank(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->genericfields($fields);
        $this->fillField('option_text1', $fields['option_1']);
        $this->fillField('option_text2', $fields['option_2']);
        $this->fillField('option_text3', $fields['option_3']);
        $this->fillDropDown('option_correct1', array($fields['rank_1']));
        $this->fillDropDown('option_correct2', array($fields['rank_2']));
        $this->fillDropDown('option_correct3', array($fields['rank_3']));
    }

    /**
     * Creates a sct question.
     *
     * @param TableNode $data
     */
    protected function createSct(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('scenario', $fields['clinical vignette']);
        $this->fillTinyMCE('hypothesis', $fields['hypothesis']);
        $this->fillDropDown('option_correct1', array($fields['experts_1']));
        $this->fillDropDown('option_correct2', array($fields['experts_2']));
        $this->fillDropDown('option_correct3', array($fields['experts_3']));
        $this->fillDropDown('option_correct4', array($fields['experts_4']));
        $this->fillDropDown('option_correct5', array($fields['experts_5']));
    }

    /**
     * Creates a textbox question.
     *
     * @param TableNode $data
     */
    protected function createTextbox(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->genericfields($fields);
        $this->uploadQuestionMedia($fields['file']);
    }

    /**
     * Creates a true_false question.
     *
     * @param TableNode $data
     */
    protected function createTrueFalse(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->genericfields($fields);
        $this->uploadQuestionMedia($fields['file']);
        // Bottom Bar obscures page elements so need to scroll so we can click.
        $this->scrollToElement('#leadin_ifr');
        if ($fields['true'] == 1) {
            $select = 'option_correct1_t';
        } else {
            $select = 'option_correct1_f';
        }
        $this->selectRadio($select);
    }

    /**
     * Fill generic data.
     * @param array $fields field data
     */
    protected function genericfields(array $fields): void
    {
        $this->fillField('theme', $fields['theme']);
        $this->fillField('notes', $fields['notes']);
        $this->fillTinyMCE('scenario', $fields['scenario']);
        $this->fillTinyMCE('leadin', $fields['leadin']);
    }

    /**
     * Upload a media file to the question
     * @param string $fileinfo json string containing filename and alternate text
     */
    protected function uploadQuestionMedia(string $fileinfo): void
    {
        $this->i_click('Upload Media', 'button');
        $this->fillField('agreement_q_media', true);
        $file = json_decode($fileinfo, true);
        if (empty($file['alt'])) {
            $this->fillField('dec_q_media', true);
        } else {
            $this->fillField('alt_q_media', $file['alt']);
        }
        $this->i_click('Upload Media', 'button');
        $this->attachFileToField('q_media', $file['filename']);
    }
}
