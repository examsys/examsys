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

/* touchstone Load params list
 *
 * q_ids = list of question ids if type is batch_q or question
 * p_ids = list of paper types if type is batch_q or paper
 *
 */

// TODO : if neg feedback is empty use positive one for both question and options
// TODO : Status is a field in the database, stick it in the struct

require_once '../config/config.inc.php';

class IE_Local_Load extends IE_Main {
  var $type = "";
  var $q_ids = array();
  var $p_ids = array();
  var $params;

  function QuickInfo($params) {
    /*$result['impexp_type'] = $params->type;
    		
     $db = new Database();
     $db->SetTable('questions');
     $db->AddField(array('q_id', 'q_type', 'theme', 'leadin'));
     $db->AddWhere('q_id',$params->q_ids,'i','IN');
     $db->AddOrder('q_id');
     $rows = $db->GetMultiRow();

     foreach ($rows as $row)
     {
     echo $row['q_id'] . " - " . $row['q_type'] . " - " . $row['theme'] . " - " . $row['leadin'] . "<br>";
     }

     return $result;*/
  }

  function Load($params) {
    echo "<h4>Params</h4>";
    print_p($params);
    echo "<h4>Other Debug</h4>";

    $this->params = $params;
    // store params in class
    $this->type = $params->type;
    $this->ids = $params->ids;

    // create storage to put loaded data
    $result = new ST_Main();
    if ($this->type == "question") {
      // for each q in list, load the question
      foreach ($this->ids as $q_id) {
        $question = $this->LoadQuestion($q_id);
        $result->questions[] = $question;
      }
    } else {
      $questions = array();
      foreach ($this->ids as $p_id) {
        $paper = $this->LoadPaper($p_id);

        foreach ($paper->screens as & $screen) {
          foreach ($screen->question_ids as $q_id) {
            $questions[$q_id] = $q_id;
          }
        }

        $result->papers[$paper->load_id] = $paper;
      }

      foreach ($questions as $q_id) {
        $question = $this->LoadQuestion($q_id);
        $result->questions[] = $question;
      }

      //print_p($questions);
      }

    //print_p($result);

    return $result;
  }

  function LoadPaper($p_id) {
    $paper_row = array();
    $prop_rows = array();

    // retrieve question row from database	
    $db = new Database();
    $db->SetTable('properties');
    $db->AddField('*');
    $db->AddWhere('property_id', $p_id, 'i');
    $paper_row = $db->GetSingleRow();

    // retrieve array of options from database
    $db = new Database();
    $db->SetTable('papers');
    $db->AddField('*');
    $db->AddWhere('paper', $p_id, 'i');
    $db->AddOrder('display_pos');
    $prop_rows = $db->GetMultiRow();

    $paper = new ST_Paper();
    $paper->load_id = $p_id;
    $paper->paper_title = $paper_row['paper_title'];
    $paper->rubric = $paper_row['rubric'];

    foreach ($prop_rows as $row) {
      $screen = $row['screen'];
      $q_id = $row['question'];
      $displypos = $row['display_pos'];

      if (!array_key_exists($screen, $paper->screens)) {
        $paper->screens[$screen] = new ST_Paper_Screen();
      }

      $paper->screens[$screen]->question_ids[$displypos] = $q_id;
    }

    return $paper;
  }

  function LoadQuestion($q_id) {
    global $userID, $show_debug;
    //echo "<div>Loading question $q_id</div>";

    // storage for question data
    $q_row = array();
    $o_rows = array();

    // retrieve question row from database	
    $db = new Database();
    $db->SetTable('questions');
    $db->AddField('*');
    $db->AddWhere('q_id', $q_id, 'i');
    $q_row = $db->GetSingleRow();

    // retrieve array of options from database
    $db = new Database();
    $db->SetTable('options');
    $db->AddField('*');
    $db->AddWhere('o_id', $q_id, 'i');
    $db->AddOrder('id_num');
    $o_rows = $db->GetMultiRow();

    // determine q type and create a storage class for correct type
    $q_type = $q_row['q_type'];
    $q_storage = "ST_Question_".$q_type;

    //echo "Question type : $q_type, storage class : $q_storage\n";

    $store = new $q_storage;
    $store->type = $q_type;

    // populate base storage fields
    $this->LoadQuestionBase($store, $q_row, $o_rows);

    // populate class specific storage fields
    $funcname = 'LoadQuestion'.$q_type;
    call_user_func(array($this, $funcname), $store, $q_row, $o_rows);
    //call_user_func(array('IE_local_Load',$funcname),$store,$q_row,$o_rows);

    // display some debug data
    print_p($q_row);
    print_p($o_rows,true,100);

    // insert track changes record
    if($show_debug != true) {
      $track = array();
      $track['type'] = "QTI Export";
      $track['typeID'] = $q_row['q_id'];
      $track['editor'] = $userID;
      $track['new'] = "Exported to QTI file";
      $track['part'] = "all";
      $track['changed'] = date("Y-m-d H:i:s");

      $db->InsertRow("track_changes", "id", $track);
    }
    // return question	
    return $store;
  }

  function LoadQuestionBase($store, $q_row, $o_rows) {
    // store id that we loaded as so can be referenced later
    $store->load_id = $q_row['q_id'];

    // load in things common to all questions
    $store->leadin = $q_row['leadin'];
    $store->theme = $q_row['theme'];
    $store->notes = $q_row['notes'];
    $store->q_group = $q_row['q_group'];
    $store->bloom = $q_row['bloom'];
    $store->score_method = $q_row['score_method'];

    if ($q_row['ownerID'] > 0) {
      $store->author = GetAuthorName($q_row['ownerID']);
    }

    // Get keywords for question
    $db = new Database();
    $db->SetTable('keywords_user', 'ku');
    $db->AddField('keyword');
    $db->AddInnerJoin('keywords_question', 'kq', 'id', 'keywordID');
    $db->AddWhere('kq.q_id', $q_row['q_id'], 'i');
    $o_rows = $db->GetMultiRow();

    $keywords = array();
    if (count($o_rows) > 0) {
      $keywords[] = $o_rows[0]['keyword'];
      for ($i = 1; $i < count($o_rows); $i++) {
        $keywords[] = $o_rows[$i]['keyword'];
      }
    }

    $store->keywords = $keywords;

    // standard media, gets cleared for extmatch
    $this->AddMedia($store, $q_row['q_media'], $q_row['q_media_width'], $q_row['q_media_height']);

    $store->status = $q_row['status'];
  }

  function LoadQuestionBlank($store, $q_row, $o_rows) {
    // basic things
    $store->displaymode = $q_row['score_method'];
    $store->feedback = $q_row['correct_fback'];

    // load option text
    $q = $o_rows[0]['option_text'];

    $question = array();

    // parse question into some more meaningful format
    $blankno = 1;
    while (stripos($q, '[blank]') > 0) {
      // create new indentifier for the blank option
      $blankid = '%BLANK_'.$blankno.'%';

      // locate [blank][/blank] segment
      $offset = stripos($q, '[blank]');
      $endoffset = stripos($q, '[/blank]');

      // pull out list of options and replace segment with blankid created arlier
      $midpart = substr($q, $offset + 7, $endoffset - $offset - 7);
      //echo "MidPart : $midpart<br>";
      $question[] = substr($q, 0, $offset);
      $question[] = $blankid;
      $q = substr($q, $endoffset + 8);
      //echo "New Question = $q<Br>";

      // process the options
      $optlist = explode(",", $midpart);

      // array to store resulting STQ_Blank_Option classes
      $options = array();

      $optionno = 1;
      foreach ($optlist as $opt) {
        $opt = trim($opt);
        if ($opt) {
          // create new option
          $curopt = new STQ_Blank_Option();
          $curopt->display = $opt;
          
          $curopt->marks_correct = $o_rows[0]['marks_correct'];
          $curopt->marks_incorrect = $o_rows[0]['marks_incorrect'];
          $curopt->marks_partial = $o_rows[0]['marks_partial'];
 

          // if display mode is dropdown, only first is correct answer
          if ($store->displaymode == "dropdown") {
            $curopt->correct = $optionno == 1 ? 1 : 0;

            // if textboxes, each item is a correct answer
            } else if ($store->displaymode == "textboxes") {
            $curopt->correct = 1;
          }
          $options[] = $curopt;
          $optionno++;
        }
      }
      $store->options[$blankid] = array();
      foreach ($options as $option) $store->options[$blankid][] = $option;

      $blankno++;
    }

    if ($q) $question[] = $q;

    $store->question = $question;

  }

  function LoadQuestionCalculation($store, $q_row, $o_rows) {
    // fiarly sure this is ok
    $store->scenario = $q_row['scenario'];
    $store->feedback = $q_row['correct_fback'];
    $store->formula = $o_rows[0]['correct'];
    
    list($store->decimals, $store->tolerance, $store->units) = explode(",", $q_row['display_method']);

    $calcvar = 0;
    foreach ($o_rows as $o_row) {
      $calcvarletter = chr(ord('A') + $calcvar);
      $var = new STQ_Calc_Vars();
      list($var->min, $var->max, $var->inc, $var->dec) = explode(",", $o_row['option_text']);
      $store->variables[$calcvarletter] = $var; 
      
      $store->marks_correct = $o_row['marks_correct'];
      $store->marks_incorrect = $o_row['marks_incorrect'];
      $store->marks_partial = $o_row['marks_partial'];
      
   
      $calcvar++;
    }
  }

  function LoadQuestionDichotomous($store, $q_row, $o_rows) {
    // basic stuff
    $store->scenario = $q_row['scenario'];
    $store->feedback = $q_row['correct_fback'];
    $store->score_method = $q_row['score_method'];
    $store->display_method = $q_row['display_method'];

    // for each options, create a STQ_Dic_Options
    $optionno = 1;
    foreach ($o_rows as $o_row) {
      $options = new STQ_Dic_Options();
      $options->text = $o_row['option_text'];
      $options->iscorrect = strtolower($o_row['correct']) == 't' ? 1 : 0;
      $options->fb_correct = $o_row['feedback_right'];
      $options->fb_incorrect = $o_row['feedback_wrong'];
      if (!$options->fb_incorrect) $options->fb_incorrect = $options->fb_correct;

      $this->AddMedia($options, $o_row['o_media'], $o_row['o_media_width'], $o_row['o_media_height']);
      
      $options->marks_correct = $o_row['marks_correct'];
      $options->marks_incorrect = $o_row['marks_incorrect'];
     
      $store->options[$optionno] = $options;
      $optionno++;
    }
  }

  function LoadQuestionExtmatch($store, $q_row, $o_rows) {
    // no question media for this qtype
    $store->media = '';
    $store->media_width = 0;
    $store->media_height = 0;

    // get list of possible answers
    $optno = 1;
    foreach ($o_rows as $o_row) {
      $store->optionlist[$optno] = $o_row['option_text'];
      $optno++;
    }

    // split all stuff from q_row into arrays for processing
    $feedbacks = explode("|", $q_row['correct_fback']);
    $medias = explode("|", $q_row['q_media']);
    $media_widths = explode("|", $q_row['q_media_width']);
    $media_heights = explode("|", $q_row['q_media_height']);
    $tmp_scenarios = explode("|", $q_row['scenario']);
    $scenarios = array();
    foreach($tmp_scenarios as $s) {
      if($s != '') {
        $scenarios[] = $s;
      }
    }
    
    $correct = explode("|", $o_rows[0]['correct']);

    // for all the arrays made, create scenarios
    $scenariono = 1;
    for ($i = 0; $i < count($scenarios); $i++) {
      $ems = new STQ_Extm_Scenario();
      $ems->stem = $scenarios[$i];
      
      $this->AddMedia($ems, $medias[$i + 1], $media_widths[$i + 1], $media_heights[$i + 1]);
      
      $ems->marks_correct = $o_rows[0]['marks_correct'];
      $ems->marks_incorrect = $o_rows[0]['marks_incorrect'];
      $ems->marks_partial = $o_rows[0]['marks_partial'];
      
      $ems->feedback = (empty($feedbacks[$i])) ? '' : $feedbacks[$i];
      $ems->correctans = explode("$", $correct[$i]);

      $store->scenarios[$scenariono] = $ems;
      $scenariono++;
    }

    $this->AddMedia($store, $medias[0], $media_widths[0], $media_heights[0]);
  }

  function LoadQuestionFlash($store, $q_row, $o_rows) {
    // no question media for this qtype
    $store->media = '';
    $store->media_width = 0;
    $store->media_height = 0;

    $store->question_swf = $q_row['q_media'];
    $store->question_swf_width = $q_row['q_media_width'];
    $store->question_swf_height = $q_row['q_media_height'];

    $store->feedback_swf = $o_rows[0]['o_media'];
    $store->feedback_swf_width = $o_rows[0]['o_media_width'];
    $store->feedback_swf_height = $o_rows[0]['o_media_height'];

    $store->marks = $o_rows[0]['marks'];
  }

  // TODO - Does this deal with multi-layered hotspot questions?
  function LoadQuestionHotspot($store, $q_row, $o_rows) {

    $store->scenario = $q_row['scenario'];
    $store->feedback = $q_row['correct_fback'];

    $hotspots = $o_rows[0]['correct'];

    $store->raw_option = $hotspots;
    $hotspots = explode("|", $hotspots);
    $spotcount = 0;
    foreach ($hotspots as $hotspot) {
      $parts = explode("~", $hotspot);
      array_shift($parts);
      array_shift($parts);
      array_pop($parts);
      $num=0;
      for ($i = 0; $i < count($parts) -1; $i += 3) {
        $type = $parts[$i];
        $coords = $parts[$i + 1];
        $coords = explode(",", $coords);
        $hotspot = new STQ_Hotspot_Spot();
        $hotspot->type = $type;
        foreach ($coords as $coord) {
          $coord = hexdec($coord);
          $hotspot->coords[] = $coord;
        }
        $hotspot->marks_correct = $o_rows[0]['marks_correct'];
        $hotspot->marks_incorrect = $o_rows[0]['marks_incorrect'];
        $hotspot->marks_partial = $o_rows[0]['marks_partial'];
        if ($hotspot->type != "") {
          $store->hotspots[$spotcount][$num] = $hotspot;
        }
        $num++;
        $spotcount++;
      }
    }
  }

  function LoadQuestionInfo($store, $q_row, $o_rows) {
    // Info type question has no notes!
    // main info stored in leadin
  }


  function LoadQuestionLabelling($store, $q_row, $o_rows) {
    // 1 - 3/4 pt
    // 2 - 1 pt
    // 3 - 1 1/4 pt
    // 4 - 2 1/4 pt
    // 5 - 3 pt
    // 6 - 4 1/2 pt
    // 7 - 6 pt
    $line_thicknesses = array();
    $line_thicknesses[1] = 0.75;
    $line_thicknesses[2] = 1;
    $line_thicknesses[3] = 1.25;
    $line_thicknesses[4] = 2.25;
    $line_thicknesses[5] = 3;
    $line_thicknesses[6] = 4.5;
    $line_thicknesses[7] = 6;

    $store->scenario = $q_row['scenario'];
    $store->feedback = $q_row['correct_fback'];
    
    $store->marks_correct = $o_rows[0]['marks_correct'];
    $store->marks_incorrect = $o_rows[0]['marks_incorrect'];
    $store->marks_partial = $o_rows[0]['marks_partial'];
    
    $data = $o_rows[0]['correct'];
    $store->raw_option = $data;

    $data = explode(";", $data);
    
    $store->line_color = $data[0];
    $store->line_thickness = $line_thicknesses[$data[1]];
    $store->box_color = $data[2];
    $store->font_size = $data[3];
    $store->font_color = $data[4];
    $store->width = $data[5];
    $store->height = $data[6];
    $store->label_type = $data[7];

    //print_p($data);

    for ($i = 11; $i < count($data); $i++) {
      if (empty($data[$i])) continue;
      
      $chunk = $data[$i];
      $data3 = explode("$", $data[$i]);
      $arrow = new STQ_Labelling_Arrow();
      $arrow->type = $data3[1];
      $arrow->coords[] = $data3[2];
      $arrow->coords[] = $data3[3];
      $arrow->coords[] = $data3[4];
      $arrow->coords[] = $data3[5];

      $store->arrows[] = $arrow;
    }

    $data2 = explode("|", $data[11]);
    //print_p($data2);
    foreach ($data2 as $label) {
      $label = explode("$", $label);

      if (empty($label[4])) continue;
      //echo $label[0] . " - " .$label[1] . " - " .$label[2] . " - " .$label[3] . " - " .$label[4] . "<br>";
      $lc = new STQ_Labelling_Label();
      
      $tag = explode('~',$label[4]);
 
      if(count($tag) == 1) {
        $lc->tag = $tag[0];
        $lc->type = 'text';
      } else {
        $lc->tag = $tag[0];
        $lc->width = $tag[1];
        $lc->height = $tag[2];
        $lc->type = 'img';
      }
      $lc->left = $label[2] - 220;
      $lc->top = $label[3] - 25;

      if ($lc->left < 0) {
        $lc->left = -1;
        $lc->top = -1;
      }


      $store->labels[$label[0]] = $lc;
      
    }
  }

  function LoadQuestionLikert($store, $q_row, $o_rows) {
    $store->scenario = $q_row['scenario'];

    // options for likert in score method, along with has n/a
    $sm = $q_row['score_method'];

    // extract the last part of the score method and if true has n/a
    $store->hasna = strtolower(substr($sm, strrpos($sm, "|") + 1)) == "true" ? 1 : 0;

    // trim off the last scoremethod as this stored has n/a
    $sm = substr($sm, 0, strrpos($sm, "|"));

    // store rest of the options in scale
    $opts = explode("|", $sm);
    $i = 1;
    $store->scale = array();
    foreach ($opts as $opt) {
      $store->scale[$i++] = $opt;
    }
  }

  function LoadQuestionMatrix($store, $q_row, $o_rows) {
    // get list of correct values for each of the questions
    $correctvalues = explode("|", $o_rows[0]['correct']);

    // build a list of the top row options
    $topvalueno = 1;
    foreach ($o_rows as $o_row) {
      $option = new STQ_Mcq_Option();
      $option->stem = $o_row['option_text'];
      $option->marks_correct = $o_row['marks_correct'];
      $option->marks_incorrect = $o_row['marks_incorrect'];
      $option->marks_partial = $o_row['marks_partial'];
      $store->options[$topvalueno] = $option;
      $topvalueno++;
    }

    // for all questions down left, create a STQ_Matrix_Scenario
    $scenno = 1;
    $leftvalue = explode("|", $q_row['scenario']);
    foreach ($leftvalue as $left) {
      if($left != '') {
        $scenario = new STQ_Matrix_Scenario();
        $scenario->scenario = $left;
        // lookup the correct top row option id and store it as the answer
        $scenario->answer = $correctvalues[$scenno - 1];

        $store->scenarios[$scenno] = $scenario;
        $scenno++;
      }
    }
  }

  function LoadQuestionMcq($store, $q_row, $o_rows) {
    // basic stuff
    $store->scenario = $q_row['scenario'];
    $store->fb_correct = $q_row['correct_fback'];
    $store->fb_incorrect = $q_row['incorrect_fback'];
    if (!$store->fb_incorrect) $store->fb_incorrect = $store->fb_correct;
    $store->correct = $o_rows[0]['correct'];
    
    // for each of the options create an STQ_Mcq_Option
    $optionno = 1;
    foreach ($o_rows as $o_row) {
      $option = new STQ_Mcq_Option();
      $option->stem = $o_row['option_text'];
      $option->marks_correct = $o_row['marks_correct'];
      $option->marks_incorrect = $o_row['marks_incorrect'];
      $option->marks_partial = $o_row['marks_partial'];
      $this->AddMedia($option, $o_row['o_media'], $o_row['o_media_width'], $o_row['o_media_height']);

      $store->options[$optionno] = $option;
      $optionno++;
    }
  }

  function LoadQuestionMrq($store, $q_row, $o_rows) {
    // basic stuff
    $store->scenario = $q_row['scenario'];
    // score method oddness, if type is other, the include other then
    // score method gets set to "1 Mark per True Option"
    $store->score_method = $q_row['score_method'];
    if ($store->score_method == "other") {
      $store->include_other = true;
    }
    $store->feedback = $q_row['correct_fback'];

    // get a list of all the options
    $optionno = 1;

    foreach ($o_rows as $o_row) {
      $option = new STQ_Mrq_Option();
      $option->stem = $o_row['option_text'];
      // check correct and map to 1/0
      $option->is_correct = strtolower($o_row['correct']) == 'y' ? 1 : 0;
      $option->fb_correct = $o_row['feedback_right'];
      $option->fb_incorrect = $o_row['feedback_wrong'];
      if ($option->fb_incorrect == "") $option->fb_incorrect = $option->fb_correct;
      
      $option->marks_correct = $o_row['marks_correct'];
      $option->marks_incorrect = $o_row['marks_incorrect'];
      $option->marks_partial = $o_row['marks_partial'];
      
      $this->AddMedia($option, $o_row['o_media'], $o_row['o_media_width'], $o_row['o_media_height']);
      $store->options[$optionno] = $option;
      $optionno++;
    }

  }

  function LoadQuestionRank($store, $q_row, $o_rows) {
    // basic stuff
    $store->scenario = $q_row['scenario'];
    $store->score_method = $q_row['score_method'];
    $store->fb_correct = $q_row['correct_fback'];
    $store->fb_incorrect = $q_row['incorrect_fback'];
    if (!$store->fb_incorrect) $store->fb_incorrect = $store->fb_correct;

    // get a list of options and create a STQ_Rank_Options for em
    $optionno = 1;
    foreach ($o_rows as $o_row) {
      //if ($o_row['correct'] > 0 && $o_row['correct'] < 9990)
      {
        $ranking = new STQ_Rank_Options();
        $ranking->order = $o_row['correct'];
        $ranking->stem = $o_row['option_text'];
        $ranking->marks_correct = $o_row['marks_correct'];
        $ranking->marks_incorrect = $o_row['marks_incorrect'];
        $ranking->marks_partial = $o_row['marks_partial'];
        $store->options[$optionno] = $ranking;
        $optionno++;
      }
    }
  }

  function LoadQuestionTextbox($store, $q_row, $o_rows) {
    // basic stuff
    $store->scenario = $q_row['scenario'];

    // size of text box stored as 100x30 in sm
    list($store->columns, $store->rows) = explode("x", $q_row['display_method']);

    $store->editor = $o_rows[0]['option_text'];
    $store->marks_correct = $o_rows[0]['marks_correct'];
    $store->marks_incorrect = $o_rows[0]['marks_incorrect'];
    $store->feedback = $q_row['correct_fback'];

    // create a list of ; separated terms, stripping out any empty ones?
    // TODO: Should this happen? maybe they want to leave blank ones in?
    $store->terms = explode_no_empty(";", $o_rows[0]['correct']);
  }

  function LoadQuestionRandom($store, $q_row, $o_rows) {
    return "Error : Random questions cant be exported<br>";
  }

  function LoadQuestionSct($store, $q_row, $o_rows) {
    return "Error : SCT questions cant be exported<br>";
  }

  function AddMedia(&$question, $media, $width = 0, $height = 0) {
    if ($media == "") return;

    //echo "Getting media $media<br>";

    $question->media = $media;
    $question->media_width = $width;
    $question->media_height = $height;
    $question->media_type = GenerateMediaType($question->media);
    $this->GetMedia($question->media);
  }

  function GetMedia($filename) {
    global $cfg_web_root;
    if (file_exists($cfg_web_root.'media/'.$filename)) {
      //echo "Copied /var/www/media/" . $filename . " to " . $this->params->base_dir . $this->params->dir."/".$filename . "<br>";
      copy($cfg_web_root . 'media/'. $filename, $this->params->base_dir . $this->params->dir . "/" . $filename);
    } else {
      //echo "File $filename doesnt exist in media directory<br>";	
      }
  }
}
?>