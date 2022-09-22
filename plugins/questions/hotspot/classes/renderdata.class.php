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

namespace plugins\questions\hotspot;

/**
 *
 * Class for hotspot rendering
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */
class renderdata extends \questiondata
{
    use \defaultgetmarks;

    /**
     * User answers
     * @var string
     */
    public $useranswer;

    /**
     * Screen submitted state
     * @var boolean
     */
    public $screensubmitted;

    /**
     * Temp correct answer
     * @var string
     */
    public $tmpcorrect;

    /**
     * Additional classes for rendering
     * @var string
     */
    public $cssclasses = '';

    /**
     * The labels for the hotspot
     * @var array
     */
    public $labels = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->questiontype = 'hotspot';
        $this->screensubmitted = false;
        $this->useranswer = '';
    }

    /**
     * Disable/Enable display of question header sections for template rendering
     */
    public function set_question_head()
    {
        $this->displaydefault = true;
        if ($this->notes != '') {
            $this->displaynotes = true;
        }
        if ($this->scenario != '') {
            $this->displayscenario = true;
        }
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
        $option = $this->get_opt($part_id);
        if ($useranswer == 'u' and  $screen_pre_submitted == 1) {
            $this->unanswered = true;
            $this->cssclasses = ' unans';
            $useranswer = mb_substr(str_repeat('u|', count(explode(\hotspot_helper::LAYER_SEPARATOR, $option['correct']))), 0, -1);
        } else {
            $this->unanswered = false;
        }
        // Adjust the height of the hotspot canvas based on the number of options available.
        $hotspot_no = mb_substr_count($option['correct'], \hotspot_helper::LAYER_SEPARATOR) + 1;
        $tmp_height = $this->mediaheight + 30;
        if ($tmp_height < (($hotspot_no * 36) + 25)) {
            $tmp_height = (($hotspot_no * 36) + 25);
        }
        $this->tmpcorrect = \hotspot_helper::get_instance()->correct_to_answer_mode(trim($option['correct']), $this->print);
        if ($this->print) {
            $this->labels = explode(\hotspot_helper::LAYER_SEPARATOR, $this->tmpcorrect);
        }
        $this->mediaheight = $tmp_height - 29;
        $mediadirectory = \rogo_directory::get_directory('media');
        $this->mediafile = $mediadirectory->url($this->mediafile);

        if (!is_null($useranswer)) {
            $this->useranswer = \hotspot_helper::get_instance()->answer_strip_correct_information(trim($useranswer));
            $this->screensubmitted = $screen_pre_submitted;
        }
        if ($useranswer == '' or in_array('u', explode(\hotspot_helper::LAYER_SEPARATOR, $useranswer)) !== false) {
            $this->unanswered = true;
        } else {
            $this->unanswered = false;
        }
        $marks = $this->marks;
        if ($this->scoremethod == 'Mark per Question') {
            $marks = $option['markscorrect'];
        } else {
            $marks = (mb_substr_count($option['correct'], \hotspot_helper::LAYER_SEPARATOR) + 1) * $option['markscorrect'];
        }
        $this->marks = $marks;
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
