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

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Keyword creation and manipulation step definitions.
 *
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @package testing
 * @subpackage behat
 */
trait Keyword
{
    /**
     * Creates a new keyword when the user is on the manage keywords page.
     *
     * @Given I create a new keyword :keyword
     * @param string $keyword The type of question
     */
    public function iCreateANewKeyword($keyword): void
    {
        $this->i_click('Create new Keyword', 'link');
        $this->i_focus_popup('New Keyword');
        $this->fillField('new_keyword', $keyword);
        $this->i_click('OK', 'button');
        $this->i_focus_main_window();
    }
}
