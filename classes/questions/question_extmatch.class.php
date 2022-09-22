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
 *
 * Class for Extended Matching questions
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */
class QuestionEXTMATCH extends QuestionEdit
{
    /** @var string[] The stem text for the scenarios. */
    protected $stems = array();

    /**
     * An array of the names of the media files used in the question.
     *
     * The first entry is the leadin media, the rest of the entries are for the scenarios.
     *
     * @var string[]
     */
    protected $all_media_names = array();

    /**
     * An array of the heights of the media files used in the question.
     *
     * The first entry is the leadin media, the rest of the entries are for the scenarios.
     *
     * @var int[]
     */
    protected $all_media_heights = array();

    /**
     * An array of the widths of the media files used in the question.
     *
     * The first entry is the leadin media, the rest of the entries are for the scenarios.
     *
     * @var int[]
     */
    protected $all_media_widths = array();

    /**
     * An array of the alternate text for the media files used in the question.
     *
     * The first entry is the leadin media, the rest of the entries are for the scenarios.
     *
     * @var int[]
     */
    protected $all_media_alts = array();

    /**
     * An array of the owners for the media files used in the question.
     *
     * The first entry is the leadin media, the rest of the entries are for the scenarios.
     *
     * @var int[]
     */
    protected $all_media_owners = array();

    /**
     * An array of the number for the media files used in the question.
     *
     * The first entry is the leadin media, the rest of the entries are for the scenarios.
     *
     * @var int[]
     */
    protected $all_media_nums = array();

    /** @var string[] The feedback given when answered correctly for the scenarios. */
    protected $all_feedback = array();

    /** @var string[] The feedback given when answered incorrectly for the scenarios. */
    protected $_answer_negative = array();

    /** @var int The maximum number of options in the question. */
    public $max_options = 26;

    /** @var int The minimum number of options in the question. */
    protected $min_options = 3;

    /** @var int The maximum number of scenarios in the question. */
    public $max_stems = 10;

    protected $_fields_required = array('type', 'leadin', 'option_order', 'owner_id', 'status');
    protected $_fields_editable = array('theme', 'leadin', 'notes', 'score_method', 'option_order', 'bloom', 'status');
    protected $_fields_compound = array('stem', 'media', 'correct_fback');

    public function __construct($mysqli, $userObj, $lang_strings, $data = null)
    {
        parent::__construct($mysqli, $userObj, $lang_strings, $data);

        // 'correct' is not a unified field for Extmatch because it is compound
        $this->_fields_unified = array('marks_correct' => $this->_lang_strings['markscorrect'], 'marks_incorrect' => $this->_lang_strings['marksincorrect']);
    }

    // ACCESSORS

    /**
     * Get an array of stems for the compounded scenarios
     * @return multitype:
     */
    public function get_all_stems()
    {
        $this->get_scenario();
        return $this->stems;
    }

    /**
     * Compound the stems into a single string and set as the scenario
     */
    public function set_all_stems($value)
    {
        $this->stems = $value;
        $this->set_scenario('dummy');
    }

    /**
     * Get the question media as an array containing filename, width and height
     * @return array
     */
    public function get_all_media()
    {
        $this->get_media();
        return array('filenames' => $this->all_media_names, 'widths' => $this->all_media_widths, 'heights' => $this->all_media_heights, 'alts' => $this->all_media_alts, 'owners' => $this->all_media_owners, 'nums' => $this->all_media_nums);
    }

    /**
     * Get the question media as an array containing filename, width and height
     * @return array
     */
    public function set_all_media($value)
    {
        $this->set_all_medias($value['filenames']);
        $this->set_all_media_widths($value['widths']);
        $this->set_all_media_heights($value['heights']);
        $this->set_all_media_alts($value['alts']);
        $this->set_all_media_owners($value['owners']);
        $this->set_all_media_nums($value['nums']);
    }

    /**
     * Get the question media filenames as an array
     * @return array
     */
    public function get_all_medias()
    {
        $this->get_media();
        return $this->all_media_names;
    }

    /**
     * Compound the media filenames into a single string and set as the media
     */
    public function set_all_medias($value)
    {
        $this->all_media_names = $value;
        $this->set_media('dummy');
    }

    /**
     * Get the question media widths as an array
     * @return array
     */
    public function get_all_media_widths()
    {
        $this->get_media();
        return $this->all_media_widths;
    }

    /**
     * Compound the media widths into a single string and set as the media
     */
    public function set_all_media_widths($value)
    {
        $this->all_media_widths = $value;
        $this->set_media('dummy');
    }

    /**
     * Get the question media alt text as an array
     * @return array
     */
    public function get_all_media_alts()
    {
        $this->get_media();
        return $this->all_media_alts;
    }

    /**
     * Compound the media alt texts into a single string and set as the media
     */
    public function set_all_media_alts($value)
    {
        $this->all_media_alts = $value;
        $this->set_media('dummy');
    }

    /**
     * Get the question media owners as an array
     * @return array
     */
    public function get_all_media_owners()
    {
        $this->get_media();
        return $this->all_media_owners;
    }

    /**
     * Compound the media owners into a single string and set as the media
     */
    public function set_all_media_owners($value)
    {
        $this->all_media_owners = $value;
        $this->set_media('dummy');
    }

    /**
     * Get the question media numbers as an array
     * @return array
     */
    public function get_all_media_nums()
    {
        $this->get_media();
        return $this->all_media_nums;
    }

    /**
     * Compound the media numbers into a single string and set as the media
     */
    public function set_all_media_nums($value)
    {
        $this->all_media_nums = $value;
        $this->set_media('dummy');
    }

    /**
     * Get the question media heights as an array
     * @return array
     */
    public function get_all_media_heights()
    {
        $this->get_media();
        return $this->all_media_heights;
    }

    /**
     * Compound the media heights into a single string and set as the media
     */
    public function set_all_media_heights($value)
    {
        $this->all_media_heights = $value;
        $this->set_media('dummy');
    }

    /**
     * Get the question feedbacks as an array
     * @return array
     */
    public function get_all_correct_fbacks()
    {
        $this->get_correct_fback();
        return $this->all_feedback;
    }

    /**
     * Compound the question feedbacks into a single string and set as the correct feedback
     */
    public function set_all_correct_fbacks($value)
    {
        $this->all_feedback = $value;
        $this->set_correct_fback('dummy');
    }

    /**
     * Get the question media as an array containing filename, width and height
     * @return array
     */
    public function get_media()
    {
        if ($this->media_source != '') {
            $num = explode('|', $this->media_num);
            $source = explode('|', $this->media_source);
            $width = explode('|', $this->media_width);
            $height = explode('|', $this->media_height);
            $alt = explode('|', $this->media_alt);
            $owner = explode('|', $this->media_owner);
            // Ensure indexes match up with media number.
            for ($i = 0; $i < count($num); $i++) {
                $idx = $num[$i];
                $this->all_media_nums[$idx] = $idx;
                $this->all_media_names[$idx] = $source[$i];
                $this->all_media_widths[$idx] = $width[$i];
                $this->all_media_heights[$idx] = $height[$i];
                $this->all_media_alts[$idx] = $alt[$i];
                $this->all_media_owners[$idx] = $owner[$i];
            }
        } else {
            $this->all_media_names = $this->all_media_widths = $this->all_media_heights = $this->all_media_alts = $this->all_media_owners = $this->all_media_nums = array_fill(0, 11, '');
        }

        return $this->media_source;
    }

    /**
     * Set the question scenario
     * @param string $value
     */
    public function set_media($value)
    {
        $this->media_source = implode('|', $this->all_media_names);
        $this->media_width = implode('|', $this->all_media_widths);
        $this->media_height = implode('|', $this->all_media_heights);
        $this->media_alt = implode('|', $this->all_media_alts);
        $this->media_owner = implode('|', $this->all_media_owners);
        $this->media_num = implode('|', $this->all_media_nums);
    }

    /**
     * Get the question scenario
     * @return string
     */
    public function get_scenario()
    {
        if ($this->scenario != '') {
            $this->stems = explode('|', $this->scenario);
        }
        return $this->scenario;
    }

    /**
     * Set the question scenario
     * @param string $value
     */
    public function set_scenario($value)
    {
        $this->scenario = implode('|', $this->stems);
    }

    /**
     * Get the question correct feedback
     * @return string
     */
    public function get_correct_fback()
    {
        if ($this->correct_fback != '') {
            $this->all_feedback = explode('|', $this->correct_fback);
        }
        return $this->correct_fback;
    }

    /**
     * Set the question correct feedback
     * @param string $value
     */
    public function set_correct_fback($value)
    {
        $this->correct_fback = implode('|', $this->all_feedback);
    }

    /**
     * Validate the extended matching option is correct.
     *
     * @return string|bool
     */
    protected function validate()
    {
        $return = parent::validate();

        $media = 0; // Count of scenarios with valid media.
        $stems = 0; // Count of scenarios with a valid stem text.
        $used = 0; // Count of scenarios that have values.
        $errors = []; // Stores error messages.

        foreach ($this->stems as $key => $stem) {
            // Test if the stem is active.
            $inuse = !empty($stem) or !empty($this->all_media_names[$key]) or !empty($this->all_feedback[$key])
            or !empty($this->correct_fback[$key]);
            $no_text = (trim(param::clean($stem, param::TEXT)) === '');
            $no_media = false;
            if (!isset($this->all_media_names[$key]) or $this->all_media_names[$key] === '') {
                $no_media = true;
            }
            if ($inuse and $no_text and $no_media) {
                // The stem and media must not be empty.
                $scenario = $key + 1;
                $errors[] = sprintf($this->_lang_strings['stemerror'], $scenario);
            }
            if ($inuse) {
                $used++;
            }
            if (!$no_text) {
                $stems++;
            }
            if (!$no_media) {
                $media++;
            }
        }

        // The question requires that every scenario has either a stem or every scenario has media.
        // Some with one and some with another breaks ExamSys...
        if ($used !== $stems and $used !== $media) {
            // Make this the first error.
            array_unshift($errors, $this->_lang_strings['stemdisplayerror']);
        }

        // Combine the errors into a single string for output.
        if (!empty($errors)) {
            $errorstring = '<ul><li>' . implode('</li><li>', $errors) . '</l></ul>';
            if ($return === true) {
                $return = $errorstring;
            } else {
                $return .= "<br/>$errorstring";
            }
        }

        return $return;
    }
}
