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

namespace testing\behat\steps\frontend;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use testing\behat\selectors;
use Exception;

/**
 * Paper creation and manipulation step definitions.
 *
 * @copyright Copyright (c) 2019 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait Paper
{
    /**
     * Click on an element on the page.
     *
     * @Given I navigate paper :name
     * @param string $type The type of navigation - Next, Previous, Finish
     * @throws Exception
     */
    public function iNavigatePaper(string $type): void
    {
        $selector = 'button';
        switch ($type) {
            case 'Previous':
                $content = 'previous';
                break;
            case 'Finish':
                $content = 'finish';
                break;
            default:
                $content = 'next';
        }
        $screen = $this->find('id_or_name', 'current_screen');
        $this->iWaitForElement($selector, $content);
        $element = $this->find($selector, $content);
        $element->click();
        if ($content === 'finish') {
            $this->i_click('OK', 'button');
        } else {
            $this->iWaitForScreen($screen->getValue());
        }
    }

    /**
     * Checks the mark for the paper
     *
     * @Given I should see your mark is :mark
     * @param string $mark the mark scored
     */
    public function iShouldSeeYourMarkIs(string $mark): void
    {
        $selector = 'summary_mark';
        $this->iWaitForElement($selector, $mark);
        $this->i_should_see($mark, $selector);
    }

    /**
     * Opens an exam preview.
     *
     * @Given I open the exam preview
     */
    public function iOpenTheExamPreview(): void
    {
        $this->i_click('Test & Preview', 'link');
        $this->i_focus_popup('ExamSys: Assessment');
    }

    /**
     * Opens an osce preview.
     *
     * @Given I open the osce preview
     */
    public function iOpenTheOscePreview(): void
    {
        $this->i_click('Test & Preview', 'link');
        $this->i_focus_popup('OSCE Form');
    }

    /**
     * Closes an osce preview.
     *
     * @Given I close the osce preview
     */
    public function iCloseTheOscePreview(): void
    {
        $this->i_close_popup_window('OSCE Form');
        $this->i_focus_main_window();
    }

    /**
     * Answer the overall classifcation
     * @Given I answer overall with :classification
     * @param string $classification the classification
     */
    public function iAnswerOverallWith(string $classification): void
    {
        $this->i_click($classification, 'classification_button');
    }

    /**
     * Enter feedback
     * @Given I enter the feedback :feedback
     * @param string $feedback the feedback
     */
    public function iEnterTheFeedback(string $feedback): void
    {
        $this->fillField('fback', $feedback);
    }

    /**
     * Select a radio field.
     * @Given I answer the questions:
     * @param TableNode $data The parameters used to answer the question
     */
    public function iAnswerTheQuestions(TableNode $data): void
    {
        foreach ($data->getHash() as $row) {
            switch ($row['type']) {
                case 'blank':
                case 'extmatch':
                case 'rank':
                    // Do not use fillDropdown as we want to select based on value not id.
                    $this->fillAnswer($row['type'], $row['position'], $row['answer']);
                    break;
                case 'sct':
                case 'dichotomous':
                case 'likert':
                case 'matrix':
                case 'mcq':
                case 'mrq':
                case 'true_false':
                    // Do not use selectRadio/selectCheckPoints as we want to select based on value not id.
                    $this->clickAnswer($row['type'], $row['position'], $row['answer']);
                    break;
                case 'textbox':
                    if ($row['answer'] != 'u') {
                        $this->fillTinyMCE('q' . $row['position'], $row['answer']);
                    }
                    break;
                case 'enhancedcalc':
                    if ($row['answer'] != 'u') {
                        $this->fillField('q' . $row['position'], $row['answer']);
                    }
                    break;
                default:
                    throw new PendingException('No handler for answering ' . $row['type'] . 'questions');
            }
        }
    }

    /**
     * Creates a new paper when the user is on a module page.
     *
     * @Given I create a new :type paper:
     * @param string $type The type of paper
     * @param TableNode $data The parameters used to create the paper
     */
    public function iCreateANewPaper(string $type, TableNode $data): void
    {
        $this->i_click('New Paper', 'link');
        $this->i_focus_popup('ExamSys: Create new Paper');
        switch ($type) {
            case 'formative':
                $this->createFormative($data);
                break;
            case 'offline':
                $this->createOffline($data);
                break;
            case 'osce':
                $this->createOsce($data);
                break;
            case 'peer review':
                $this->createPeerReview($data);
                break;
            case 'progress':
                $this->createProgress($data);
                break;
            case 'summative':
                $this->createSummative($data);
                break;
            case 'survey':
                $this->createSurvey($data);
                break;
            default:
                throw new PendingException("No handler for creating $type papers");
        }
        $this->i_click('Finish', 'button');
        $this->only_main_window();
        $this->i_focus_main_window();
    }

    /**
     * Creates a formative paper.
     *
     * @param TableNode $data
     */
    protected function createFormative(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->paperBasics('Formative Self-Assessment', $fields['name']);
        // Set the start date.
        $this->fillInDateTime('f', $fields['start']);
        // Set the end date.
        $this->fillInDateTime('t', $fields['end']);
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fillInTimezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fillInModules($fields['modules']);
        }
    }

    /**
     * Creates a offline paper.
     *
     * @param TableNode $data
     */
    protected function createOffline(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->paperBasics('Offline Paper', $fields['name']);
        // Set the start date.
        $this->fillInDateTime('f', $fields['start']);
        // Set the end date.
        $this->fillInDateTime('t', $fields['end']);
        if (isset($fields['session'])) {
            $this->fillInSession($fields['session']);
        }
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fillInTimezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fillInModules($fields['modules']);
        }
    }

    /**
     * Creates a osce paper.
     *
     * @param TableNode $data
     */
    protected function createOsce(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->paperBasics('OSCE Station', $fields['name']);
        // Set the start date.
        $this->fillInDateTime('f', $fields['start']);
        // Set the end date.
        $this->fillInDateTime('t', $fields['end']);
        if (isset($fields['session'])) {
            $this->fillInSession($fields['session']);
        }
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fillInTimezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fillInModules($fields['modules']);
        }
    }

    /**
     * Creates a peer review paper.
     *
     * @param TableNode $data
     */
    protected function createPeerReview(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->paperBasics('Peer Review', $fields['name']);
        // Set the start date.
        $this->fillInDateTime('f', $fields['start']);
        // Set the end date.
        $this->fillInDateTime('t', $fields['end']);
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fillInTimezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fillInModules($fields['modules']);
        }
    }

    /**
     * Creates a progress paper.
     *
     * @param TableNode $data
     */
    protected function createProgress(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->paperBasics('Progress Test', $fields['name']);
        // Set the start date.
        $this->fillInDateTime('f', $fields['start']);
        // Set the end date.
        $this->fillInDateTime('t', $fields['end']);
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fillInTimezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fillInModules($fields['modules']);
        }
    }

    /**
     * Creates a summative paper.
     *
     * @param TableNode $data
     */
    protected function createSummative(TableNode $data): void
    {
        $config = \Config::get_instance();
        $management = $config->get_setting('core', 'cfg_summative_mgmt');
        $fields = $data->getRowsHash();
        $this->paperBasics('Summative Exam', $fields['name']);
        if ($management) {
            $this->fillInSession($fields['session']);
            $this->fillField('barriers_needed', ($fields['barriers'] == 1));
            $this->fillInDuration($fields['duration']);
            $this->fillInDateRequired($fields['period']);
            $this->fillField('cohort_size', $fields['size']);
            $this->fillField('sittings', $fields['sittings']);
            $this->fillField('campus', $fields['campus']);
            if (isset($fields['notes'])) {
                $this->fillField('notes', $fields['notes']);
            }
        } else {
            // Set the start date.
            $this->fillInDateTime('f', $fields['start']);
            // Set the end date.
            $this->fillInDateTime('t', $fields['end']);
            // Set optional fields.
            if (isset($fields['session'])) {
                $this->fillInSession($fields['session']);
            }
            if (isset($fields['timezone'])) {
                $this->fillInTimezone($fields['timezone']);
            }
        }
        // This is present in both forms of summative creation.
        if (isset($fields['modules'])) {
            $this->fillInModules($fields['modules']);
        }
    }

    /**
     * Creates a survey.
     *
     * @param TableNode $data
     */
    protected function createSurvey(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        $this->paperBasics('Survey', $fields['name']);
        // Set the start date.
        $this->fillInDateTime('f', $fields['start']);
        // Set the end date.
        $this->fillInDateTime('t', $fields['end']);
        // Set optional fields.
        if (isset($fields['timezone'])) {
            $this->fillInTimezone($fields['timezone']);
        }
        if (isset($fields['modules'])) {
            $this->fillInModules($fields['modules']);
        }
    }

    /**
     * Fills in the Date required field.
     *
     * @param string $month English month name
     */
    protected function fillInDateRequired(string $month): void
    {
        $dates = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
        $this->fillField('period', array_search($month, $dates));
    }

    /**
     * Fills in a date time box.
     *
     * @param string $prefix The prefix for the datetime fields
     * @param string $datestring A string that will evaluate to a valid date time object
     */
    protected function fillInDateTime(string $prefix, string $datestring): void
    {
        $date = new \DateTime($datestring);
        $this->fillField("{$prefix}day", $date->format('d'));
        $this->fillField("{$prefix}month", $date->format('m'));
        $this->fillField("{$prefix}year", $date->format('Y'));
        $this->fillField("{$prefix}time", $date->format('His'));
    }

    /**
     * Fills in the duration fields.
     *
     * @param string $duration
     * @throws Exception
     */
    protected function fillInDuration(string $duration): void
    {
        $duration = explode(':', $duration);
        if (count($duration) !== 2) {
            throw new Exception('The duration must be formatted as hours:minutes');
        }
        $this->fillField('duration_hours', $duration[0]);
        $this->fillField('duration_mins', $duration[1]);
    }

    /**
     * Fills in the fields which define which modules the paper is attached to.
     *
     * @param string $modules_string Comma separated list of module codes.
     */
    protected function fillInModules(string $modules_string): void
    {
        // TODO: Refactor form so it will be possible to select the modules for a paper.
    }

    /**
     * Fills in a Academic session field.
     *
     * @param string $session
     */
    protected function fillInSession(string $session): void
    {
        $date = new \DateTime($session);
        $this->fillField('session', $date->format('Y'));
    }

    /**
     * Fills in a timezone field for the paper.
     *
     * @param string $timezone
     */
    protected function fillInTimezone(string $timezone): void
    {
        $this->fillField('timezone', $timezone);
    }

    /**
     * Fills in the first page of the paper creation dialogue.
     *
     * @param string $type The type of paper to be created.
     * @param string $name The name of the paper
     */
    protected function paperBasics(string $type, string $name): void
    {
        $this->i_click($type, 'paper_type');
        $this->fillField('paper_name', $name);
        $this->i_click('Next', 'button');
    }

    /**
     * Click on an answer
     * @param string $type question type
     * @param string $position the question position
     * @param string $answer the answer supplied
     */
    protected function clickAnswer(string $type, string $position, string $answer): void
    {
        if ($answer != 'u') {
            switch ($type) {
                case 'likert':
                    $select = $this->find('xpath', '//*[@id="c' . $position . '_' . $answer . '"]');
                    $select->click();
                    break;
                case 'mrq':
                    foreach (json_decode($answer) as $ans) {
                        $select = $this->find('xpath', '//label[contains(@class, "optiontext") and contains(normalize-space(.), "' . $ans . '")]');
                        $select->click();
                    }
                    break;
                case 'sct':
                case 'mcq':
                    $select = $this->find('xpath', '//label[contains(@class, "optiontext") and contains(normalize-space(.), "' . $answer . '")]');
                    $select->click();
                    break;
                case 'matrix':
                    foreach (json_decode($answer, true) as $idx => $ans) {
                        $opt = substr($ans, 6);
                        $select = $this->find('xpath', '//th[contains(text(),"' . $idx . '")]/following::td[' . $opt . ']/div/input[contains(@name, "q' . $position . '")]');
                        $select->click();
                    }
                    break;
                case 'dichotomous':
                    foreach (json_decode($answer, true) as $idx => $ans) {
                        $option = $idx + 1;
                        $select = $this->find('xpath', '//*[@name="q' . $position . '_' . $option . '" and @value="' . $ans . '"]');
                        $select->click();
                    }
                    break;
                default:
                    $select = $this->find('xpath', '//*[@name="q' . $position . '" and @value="' . $answer . '"]');
                    $select->click();
                    break;
            }
        }
    }

    /**
     * Fill an answer
     * @param string $type question type
     * @param string $position the question position
     * @param string $answer the answer supplied
     */
    protected function fillAnswer(string $type, string $position, string $answer): void
    {
        if ($answer != 'u') {
            switch ($type) {
                case 'blank':
                    foreach (json_decode($answer, true) as $idx => $ans) {
                        $option = $idx + 1;
                        $select = $this->find('xpath', '//*[@name="q' . $position . '_' . $option . '"]');
                        $select->selectOption($ans, true);
                    }
                    break;
                case 'extmatch':
                    foreach (json_decode($answer, true) as $idx => $ans) {
                        $select = $this->find('xpath', '//*[contains(text(),"' . $idx . '")]/following::select[1][contains(@name, "q' . $position . '")]');
                        $select->selectOption($ans, true);
                    }
                    break;
                default:
                    foreach (json_decode($answer, true) as $idx => $ans) {
                        $select = $this->find('xpath', '//div/span[contains(text(),"' . $idx . '")]/following::div[1]/select[contains(@class, "q' . $position . '")]');
                        $select->selectOption($ans, true);
                    }
                    break;
            }
        }
    }
}
