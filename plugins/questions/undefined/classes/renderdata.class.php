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

namespace plugins\questions\undefined;

/**
 *
 * Class for undefined question type
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */
class renderdata extends \questiondata
{
    use \defaultgetmarks;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->questiontype = 'undefined';
    }

    /**
     * Disable/Enable display of question header sections for template rendering
     */
    public function set_question_head()
    {
        $this->displayscenario = false;
        $this->displaymedia = false;
        $this->displaydefault = true;
        $this->displaynotes = false;
        $this->displayleadin = true;
    }

    /**
     * Question level settings for template rendering
     * @param boolean $screen_pre_submitted has the user submitted and answer previously
     * @param mixed $useranswer user answer
     * @param string $userdismissed list of enable/disable flag for options the user has dismissed
     */
    public function set_question($screen_pre_submitted, $useranswer, $userdismissed)
    {
        // Nothing to do.
    }

    /**
     * Option level settings for template rendering
     * @param integer $part_id part loop id
     * @param mixed $useranswer user answer
     * @param string $userdismissed list of enable/disable flag for options the user has dismissed
     * @param boolean $screen_pre_submitted has the user submitted and answer previously
     */
    public function set_option_answer($part_id, $useranswer, $userdismissed, $screen_pre_submitted)
    {
        // Nothing to do.
    }

    /**
     * Additional option level settings for template rendering
     * @param integer $part_id part loop id
     * @param mixed $useranswer user answer
     * @param string $userdismissed list of enable/disable flag for options the user has dismissed
     * @param boolean $screen_pre_submitted has the user submitted and answer previously
     */
    public function process_options($part_id, $useranswer, $userdismissed, $screen_pre_submitted)
    {
        // Nothing to do.
    }
}
