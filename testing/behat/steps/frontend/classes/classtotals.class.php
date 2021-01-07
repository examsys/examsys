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

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use testing\behat\selectors;
use Exception;

/**
 * Class totals report manipulation step definitions.
 *
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait ClassTotals
{
    /**
     * Add a paper note to a student on the class totals report
     *
     * @Given I add a note :note
     * @param string $note the note
     * @throws Exception
     */
    public function iAddANote(string $note): void
    {
        $this->i_focus_popup('Note');
        $this->fillField('note', $note);
        $element = $this->find('button', 'Save');
        $element->click();
        $this->i_focus_main_window();
        $this->i_wait_for_page_to_load();
    }

    /**
     * View a students paper note on the class totals report
     *
     * @Given I view a students :student note
     * @param string $student the student
     * @throws Exception
     */
    public function iViewAStudentsNote(string $student): void
    {
        $element = $this->find('class_totals_student', $student);
        $element->click();
        $element = $this->find('class_totals_menu_item', 'New Note...');
        $element->click();
        $this->i_focus_popup('Note');
    }
}
