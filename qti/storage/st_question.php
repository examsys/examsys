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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

class ST_Question
{
    public $type;
    public $load_id;
    public $save_id;

    public $theme = '';
    public $notes = '';
    public $leadin = '';
    public $media = '';
    public $media_width = 0;
    public $media_height = 0;
    public $media_alt = '';
    public $media_type = '';

    public $status;

    public $author = '';
    public $q_group = '';

    public $bloom = '';
    public $keywords = array();
    public $q_option_order = 'display order'; //stem/option randomisation

    public $display_method = '';

    public $score_method; //
}

class STQ_Blank_Option
{
    public $display = '';
    public $correct = 0;

    public $marks_correct;
    public $marks_incorrect;
    public $marks_partial;

    public function __toString()
    {
        return $this->display . '=' . ($this->correct ? 'True' : 'False');
    }
}

class ST_Question_Blank extends ST_Question
{
    public $displaymode = 0;
    public $question = '';
    public $feedback = '';
    public $options = array(); // array of STQ_Blank_Option, key as blank id in text ($BLANK_1$ etc)
}

class STQ_Calc_Vars
{
    public $min = 0;
    public $max = 0;
    public $dec = 0;
    public $inc = 1;

    public function __toString()
    {
        return $this->min . ',' . $this->max . ',' . $this->dec . ',' . $this->inc;
    }
}

class ST_Question_Calculation extends ST_Question
{
    public $scenario = '';
    public $variables = array(); // array of STQ_Calc_Vars, key as variable stored as (A-H)
    public $formula;
    public $units;
    public $decimals = 0;
    public $tolerance = 0;
    public $feedback;
    public $settings = array();
}

class ST_Question_enhancedcalc extends ST_Question_Calculation
{

}

class STQ_Dic_Options
{
    public $text;
    public $iscorrect;
    public $media;
    public $media_width;
    public $media_height;
    public $media_alt;
    public $media_type;
    public $fb_correct;
    public $fb_incorrect;

    public $marks_correct;
    public $marks_incorrect;
    public $marks_partial;

    public function __toString()
    {
        return $this->text . '=' . ($this->iscorrect ? 'True' : 'False');
    }
}

class ST_Question_Dichotomous extends ST_Question
{
    public $scenario = '';
    public $feedback = '';
    public $score_method = 0;
    public $options = array();
}

class STQ_Extm_Scenario
{
    public $stem = '';
    public $media;
    public $media_width;
    public $media_height;
    public $media_alt;
    public $media_type;
    public $feedback;
    public $correctans = array(); // array of Keys for correct answers based on optionlist

    public $marks_correct;
    public $marks_incorrect;
    public $marks_partial;

    public function __toString()
    {
        return $this->stem . '=' . implode('|', $this->correctans);
    }
}

class STQ_Extm_Option
{
    public $option;
    public $id;

    public $marks_correct;
    public $marks_incorrect;
    public $marks_partial;

    public function __toString()
    {
        return $this->id . '=' . $this->option;
    }
}

class ST_Question_Extmatch extends ST_Question
{
    public $optionlist = array(); // string array of STQ_Extm_Option options by Key (A-Z)
    public $scenarios = array(); // array of STQ_Extm_Scenario, key as scenarion no
}

class STQ_Hotspot_Spot
{
    public $type;
    public $coords = array();

    public function __toString()
    {
        return $this->type . '=' . implode(',', $this->coords);
    }
}

class ST_Question_Hotspot extends ST_Question
{
    public $scenario = '';
    public $feedback = '';
    public $hotspots = array(); // array of STQ_Hotspot_Spot
    // raw labeling option text for rogo->qti->rogo
    public $raw_option = '';
}

class ST_Question_Info extends ST_Question
{
    // nothing in this question type
}

class STQ_Labelling_Label
{
    public $tag;
    public $left;
    public $top;
    public $type;
    public $width;
    public $height;

    public function __toString()
    {
        return $this->tag . '=' . $this->left . ',' . $this->top . '=' . count($this->matches);
    }
}

class STQ_Labelling_Arrow
{
    public $type;
    public $coords = array();

    public function __toString()
    {
        return $this->type . '=' . implode(',', $this->coords);
    }
}

class ST_Question_Labelling extends ST_Question
{
    public $scenario = '';
    public $feedback = '';

    public $line_color = '0x000000';
    public $line_thickness = 0.75;
    // 1 - 3/4 pt
    // 2 - 1 pt
    // 3 - 1 1/4 pt
    // 4 - 2 1/4 pt
    // 5 - 3 pt
    // 6 - 4 1/2 pt
    // 7 - 6 pt
    public $box_color = '0xc6c6c6';
    public $font_size = 10;
    public $font_color = '0x000000';
    public $width = 90;
    public $height = 35;
    public $label_type = 'single';

    public $arrows = array(); // array of STQ_Labelling_Arrow
    public $labels = array();

    // raw labeling option text for rogo->qti->rogo
    public $raw_option = '';
    // STORE LABELING INFO IN HERE!!!!
}

class ST_Question_Likert extends ST_Question
{
    public $scenario = '';
    public $scale = array(); // string array of poss values
    public $hasna = 0;
}

class STQ_Matrix_Scenario
{
    public $scenario;
    public $answer;

    public function __toString()
    {
        return $this->scenario . '=' . $this->answer;
    }
}

class ST_Question_Matrix extends ST_Question
{
    public $options = array();
    // Store matrix as $matrix[TOP][LEFT] = T/F
    public $scenarios = array(); // array of STQ_Matrix_Scenario, key as row
}

class STQ_Mcq_Option
{
    public $stem = '';

    public $media = '';
    public $media_width = 0;
    public $media_height = 0;
    public $media_alt = '';
    public $media_type;

    public $marks_correct;
    public $marks_incorrect;
    public $marks_partial;

    public function __toString()
    {
        return $this->stem;
    }
}

class ST_Question_Mcq extends ST_Question
{
    public $scenario = '';
    public $correct = 0;

    public $options = array(); // array of STQ_Mcq_Option, key as option no

    public $fb_correct;
    public $fb_incorrect;
    public $answer;
}

class ST_Question_TrueFalse extends ST_Question
{
    public $scenario = '';
    public $correct = 0;

    public $options = array(); // array of STQ_Mcq_Option, key as option no

    public $fb_correct;
    public $fb_incorrect;
    public $answer;
}

class STQ_Mrq_Option
{
    public $stem = '';
    public $is_correct = 0;
    public $media = '';
    public $media_width = 0;
    public $media_height = 0;
    public $media_alt = '';
    public $media_type;
    public $fb_correct;
    public $fb_incorrect;

    public $marks_correct;
    public $marks_incorrect;
    public $marks_partial;

    public function __toString()
    {
        return $this->stem . '=' . ($this->is_correct ? 'True' : 'False');
    }
}

class ST_Question_Mrq extends ST_Question
{
    public $scenario = '';
    public $score_method = 0;
    public $include_other = 0;
    public $options = array(); // array of STQ_Mrq_Options, key as option no
    public $feedback;
}

class STQ_Rank_Options
{
    public $stem = '';
    public $order = 9990;

    public $marks_correct;
    public $marks_incorrect;
    public $marks_partial;

    // order - 1-15, or 0 as blank, and 9990 as N/A

    public function __toString()
    {
        return $this->stem . '=' . $this->order;
    }
}

class ST_Question_Rank extends ST_Question
{
    public $scenario = '';
    public $score_method = 0;
    public $options = array(); // array of STQ_Rank_Options, key as option no
    public $fb_correct;
    public $fb_incorrect;
}

class ST_Question_Textbox extends ST_Question
{
    public $scenario = '';
    public $columns = 100;
    public $rows = 3;
    public $editor = 'WYSIWYG';
    public $marks = 1;
    public $feedback = '';
    public $terms = array(); // array of strings
}

class ST_Question_Sct extends ST_Question
{
    // NO EXTENSIONS
}

class ST_Question_Random extends ST_Question
{
    // NO EXTENSIONS
}

class ST_Question_keyword_based extends ST_Question
{
    // NO EXTENSIONS
}

class ST_Question_true_false extends ST_Question
{
    public $scenario = '';
    public $correct = 0;

    public $options = array(); // array of STQ_Mcq_Option, key as option no

    public $fb_correct;
    public $fb_incorrect;
}
