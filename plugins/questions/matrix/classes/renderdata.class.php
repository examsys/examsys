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

namespace plugins\questions\matrix;

/**
 *
 * Class for matrix rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */
class renderdata extends \questiondata
{
    use \defaultgetmarks;

    /**
     * Matching scenarios
     * @var array
     */
    public $scenarios;

    /**
     * Matching user answers
     * @var array
     */
    public $usersanswers;

    /**
     * Matching options
     * @var array
     */
    public $matchoptions;

    /**
     * Matching scenarios
     * @var integer
     */
    public $matchscenarios;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->questiontype = 'matrix';
    }

    /**
     * Disable/Enable display of question header sections for template rendering
     */
    public function set_question_head()
    {
        $this->displaydefault = true;
        if ($this->qmedia != '') {
            $this->displaymedia = true;
        }
        if ($this->notes != '') {
            $this->displaynotes = true;
        }
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
        $this->scenarios = explode('|', $this->scenario);
        if (!is_null($useranswer)) {
            $this->usersanswers = explode('|', $useranswer);
        } else {
            $this->usersanswers = array();
        }
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
        $option = $this->get_opt($part_id);
        $this->matchoptions[]['option'] = $option['optiontext'];
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
        $part_id = 1;
        $option = $this->get_opt($part_id);
        $matchscenario = array();
        $matching_users_answers = $this->usersanswers;
        $option_order = explode(',', $this->optionorder);
        foreach ($this->scenarios as $single_scenario) {
            if (trim($single_scenario) != '') {
                if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == '' and $screen_pre_submitted == 1) {
                    $matchscenario[$part_id - 1]['unanswered'] = true;
                    $this->unanswered = true;
                } else {
                    $matchscenario[$part_id - 1]['unanswered'] = false;
                }
                $matchscenario[$part_id - 1]['id'] = chr(64 + $part_id);
                $matchscenario[$part_id - 1]['value'] = $single_scenario;
                for ($i = 0; $i < count($this->matchoptions); $i++) {
                    $tmp_part_id = $option_order[$i] + 1;
                    $this->matchoptions[$i]['value'] = $tmp_part_id;
                    if (isset($matching_users_answers[$part_id - 1]) and $matching_users_answers[$part_id - 1] == $tmp_part_id) {
                        $this->matchoptions[$i]['selected'][$part_id] = true;
                    } else {
                        $this->matchoptions[$i]['selected'][$part_id] = false;
                    }
                }
                $part_id++;
            }
        }
        $this->matchscenarios = $matchscenario;
        if ($this->scoremethod == 'Mark per Question') {
            $this->marks = $option['markscorrect'];
        } else {
            $this->marks = ($part_id - 1) * $option['markscorrect'];
        }
    }
}
