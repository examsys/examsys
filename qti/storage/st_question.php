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

/**
 *
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2011 The University of Nottingham
 * @package
 */

class ST_Question {
  var $type;
  var $load_id;
  var $save_id;

  var $theme = '';
  var $notes = '';
  var $leadin = '';
  var $media = '';
  var $media_width = 0;
  var $media_height = 0;
  var $media_type = '';

  var $status;
  
  var $author = '';
  var $q_group = '';

  var $bloom = '';
  var $keywords = array();
  var $q_option_order = 'display order'; //stem/option randomisation
  
  var $display_method = '';
  
  var $score_method; //
}

class STQ_Blank_Option {
  var $display = '';
  var $correct = 0;
  
  var $marks_correct;
  var $marks_incorrect;
  var $marks_partial;
  
  function __toString() {
    return $this->display."=".($this->correct ? "True" : "False");
  }
}

class ST_Question_Blank extends ST_Question {
  var $displaymode = 0;
  var $question = '';
  var $feedback = '';
  var $options = array(); // array of STQ_Blank_Option, key as blank id in text ($BLANK_1$ etc)
}

class STQ_Calc_Vars {
  var $min = 0;
  var $max = 0;
  var $dec = 0;
  var $inc = 1;

  function __toString() {
    return $this->min.",".$this->max.",".$this->dec.",".$this->inc;
  }
}

class ST_Question_Calculation extends ST_Question {
  var $scenario = '';
  var $variables = array(); // array of STQ_Calc_Vars, key as variable stored as (A-H)
  var $formula;
  var $units;
  var $decimals = 0;
  var $tolerance = 0; 
  var $feedback;
}

class STQ_Dic_Options {
  var $text;
  var $iscorrect;
  var $media;
  var $media_width;
  var $media_height;
  var $media_type;
  var $fb_correct;
  var $fb_incorrect;
  
  var $marks_correct;
  var $marks_incorrect;
  var $marks_partial;
  
  function __toString() {
    return $this->text."=".($this->iscorrect ? "True" : "False");
  }
}

class ST_Question_Dichotomous extends ST_Question {
  var $scenario = '';
  var $feedback = '';
  var $score_method = 0;
  var $options = array();
}

class STQ_Extm_Scenario {
  var $stem = '';
  var $media;
  var $media_width;
  var $media_height;
  var $media_type;
  var $feedback;
  var $correctans = array(); // array of Keys for correct answers based on optionlist
  
  var $marks_correct;
  var $marks_incorrect;
  var $marks_partial;
  
  function __toString() {
    return $this->stem."=".implode("|", $this->correctans);
  }
}

class STQ_Extm_Option {
  var $option;
  var $id;
  
  var $marks_correct;
  var $marks_incorrect;
  var $marks_partial;
  
  function __toString() {
    return $this->id."=".$this->option;
  }
}

class ST_Question_Extmatch extends ST_Question {
  var $optionlist = array(); // string array of STQ_Extm_Option options by Key (A-Z)
  var $scenarios = array(); // array of STQ_Extm_Scenario, key as scenarion no
}

class ST_Question_Flash extends ST_Question {
  // NO SCENARIO
  var $question_swf = '';
  var $question_swf_width = '';
  var $question_swf_height = '';
  var $feedback_swf = '';
  var $feedback_swf_width = '';
  var $feedback_swf_height = '';
  var $marks = 1;
}

class STQ_Hotspot_Spot {
  var $type;
  var $coords = array();

  function __toString() {
    return $this->type."=".implode(",", $this->coords);
  }
}

class ST_Question_Hotspot extends ST_Question {
  var $scenario = '';
  var $feedback = '';
  var $hotspots = array(); // array of STQ_Hotspot_Spot
  // raw labeling option text for touchstone->qti->touchstone
  var $raw_option = '';
}

class ST_Question_Info extends ST_Question {
  // nothing in this question type
}

class STQ_Labelling_Label {
  var $tag;
  var $left;
  var $top;
  var $type;
  var $width;
  var $height;

  function __toString() {
    return $this->tag."=".$this->left.",".$this->top."=".count($this->matches);
  }
}

class STQ_Labelling_Arrow {
  var $type;
  var $coords = array();

  function __toString() {
    return $this->type."=".implode(",", $this->coords);
  }
}

class ST_Question_Labelling extends ST_Question {
  var $scenario = '';
  var $feedback = '';

  var $line_color = '0x000000';
  var $line_thickness = 0.75;
  // 1 - 3/4 pt
  // 2 - 1 pt
  // 3 - 1 1/4 pt
  // 4 - 2 1/4 pt
  // 5 - 3 pt
  // 6 - 4 1/2 pt
  // 7 - 6 pt
  var $box_color = '0xc6c6c6';
  var $font_size = 10;
  var $font_color = '0x000000';
  var $width = 90;
  var $height = 35;
  var $label_type = 'single';

  var $arrows = array(); // array of STQ_Labelling_Arrow
  var $labels = array();

  // raw labeling option text for touchstone->qti->touchstone
  var $raw_option = '';
  // STORE LABELING INFO IN HERE!!!!
}

class ST_Question_Likert extends ST_Question {
  var $scenario = '';
  var $scale = array(); // string array of poss values
  var $hasna = 0;
}

class STQ_Matrix_Scenario {
  var $scenario;
  var $answer;

  function __toString() {
    return $this->scenario."=".$this->answer;
  }
}

class ST_Question_Matrix extends ST_Question {
  var $options = array();
  // Store matrix as $matrix[TOP][LEFT] = T/F
  var $scenarios = array(); // array of STQ_Matrix_Scenario, key as row
}

class STQ_Mcq_Option {
  var $stem = '';

  var $media = '';
  var $media_width = 0;
  var $media_height = 0;
  var $media_type;
  
  var $marks_correct;
  var $marks_incorrect;
  var $marks_partial;
  
  function __toString() {
    return $this->stem;
  }
}

class ST_Question_Mcq extends ST_Question {
  var $scenario = '';
  var $correct = 0;

  var $options = array(); // array of STQ_Mcq_Option, key as option no

  var $fb_correct;
  var $fb_incorrect;
}

class STQ_Mrq_Option {
  var $stem = '';
  var $is_correct = 0;
  var $media = '';
  var $media_width = 0;
  var $media_height = 0;
  var $media_type;
  var $fb_correct;
  var $fb_incorrect;
  
  var $marks_correct;
  var $marks_incorrect;
  var $marks_partial;
  
  function __toString() {
    return $this->stem."=".($this->is_correct ? "True" : "False");
  }
}

class ST_Question_Mrq extends ST_Question {
  var $scenario = '';
  var $score_method = 0;
  var $include_other = 0;
  var $options = array(); // array of STQ_Mrq_Options, key as option no
  var $feedback;
}

class STQ_Rank_Options {
  var $stem = '';
  var $order = 9990;
    
  var $marks_correct;
  var $marks_incorrect;
  var $marks_partial;
  
  // order - 1-15, or 0 as blank, and 9990 as N/A

  function __toString() {
    return $this->stem."=".$this->order;
  }
}

class ST_Question_Rank extends ST_Question {
  var $scenario = '';
  var $score_method = 0;
  var $options = array(); // array of STQ_Rank_Options, key as option no
  var $fb_correct;
  var $fb_incorrect;
}

class ST_Question_Textbox extends ST_Question {
  var $scenario = '';
  var $columns = 100;
  var $rows = 3;
  var $editor = 'WYSIWYG';
  var $marks = 1;
  var $feedback = '';
  var $terms = array(); // array of strings
}

class ST_Question_Sct extends ST_Question {
  // NO EXTENSIONS
}

class ST_Question_Random extends ST_Question {
  // NO EXTENSIONS
}

