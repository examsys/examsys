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
 * User manipulation step definitions.
 *
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait User
{
    /**
     * Add a paper note to a student on the user profile
     *
     * @Given I add a note :note to the paper :paper
     * @param string $note the note
     * @param string $paper the paper
     * @throws Exception
     */
    public function iAddANoteToThePaper(string $note, string $paper): void
    {
        $element = $this->find('id_or_name', 'createname');
        $element->click();
        $this->i_focus_popup('Note');
        $this->fillDropDown('paperID', array($paper));
        $this->fillField('note', $note);
        $element = $this->find('button', 'Save');
        $element->click();
        $this->only_main_window();
        $this->i_focus_main_window();
    }

    /**
     * Set user accessibility settings
     *
     * @Given I set the accessibility settings:
     * @param TableNode $data
     */
    public function iSetTheAccessiblitySettings(TableNode $data): void
    {
        $fields = $data->getRowsHash();
        if (isset($fields['extratime'])) {
            $this->fillField('extra_time', $fields['extratime']);
        }
        if (isset($fields['fontsize'])) {
            $this->fillField('textsize', $fields['fontsize']);
        }
        if (isset($fields['typeface'])) {
            $this->fillField('font', $fields['typeface']);
        }
        if (isset($fields['background'])) {
            $this->fillColourPicker('span_background', $fields['background']);
        }
        if (isset($fields['foreground'])) {
            $this->fillColourPicker('span_foreground', $fields['foreground']);
        }
        if (isset($fields['marks'])) {
            $this->fillColourPicker('span_marks_color', $fields['marks']);
        }
        if (isset($fields['heading'])) {
            $this->fillColourPicker('span_themecolor', $fields['heading']);
        }
        if (isset($fields['label'])) {
            $this->fillColourPicker('span_labelcolor', $fields['label']);
        }
        if (isset($fields['unanswered'])) {
            $this->fillColourPicker('span_unansweredcolor', $fields['unanswered']);
        }
        if (isset($fields['dismiss'])) {
            $this->fillColourPicker('span_dismisscolor', $fields['dismiss']);
        }
        if (isset($fields['highlight'])) {
            $this->fillColourPicker('span_highlightcolour', $fields['highlight']);
        }
        if (isset($fields['globaltheme'])) {
            $this->fillColourPicker('span_globalthemecolour', $fields['globaltheme']);
        }
        if (isset($fields['globalfont'])) {
            $this->fillColourPicker('span_globalthemefontcolour', $fields['globalfont']);
        }
        if (isset($fields['medical'])) {
            $this->fillField('medical', $fields['medical']);
        }
        if (isset($fields['breaks'])) {
            $this->fillField('breaks', $fields['breaks']);
        }
        if (isset($fields['breaktime'])) {
            $this->fillField('break_time', $fields['breaktime']);
        }
    }
}
