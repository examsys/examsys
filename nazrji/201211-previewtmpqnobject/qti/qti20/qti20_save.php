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

// TODO: Status should be from struct
// TODO: Heading and notes should be stuck in the question materia
// TODO: MRQ : Feedback is not correct - sort out

class IE_qti20_Save extends IE_Main {
  var $data;
  var $params;

  // main save function
  function Save($params, &$data) {
    global $string;

    echo "<h4>{$string['params']}</h4>";
    print_p($params);
    echo "<h4>{$string['generaldebuginfo']}</h4>";

    global $userID;
    $data->ownerID = $userID;

    $this->data =& $data;
    $this->params =& $params;

    $this->outputfiles = array();

    $this->ll = array();
    for ($i = 1; $i < 27; $i++) {
      $varletter = chr(ord('A') + $i - 1);
      $this->ll[$i] = $varletter;
    }

    // output all questions as xml files ready for packaging
    foreach ($data->questions as $question) {
      $this->output = "";
      $this->OutputQuestion($question);
      $file = "question-".$question->load_id.".xml";
      $filename = $params->base_dir.$params->dir."/".$file;
      file_put_contents($filename, utf8_encode($this->output));
      $data->files[] = new ST_File($file, trim(strip_tags($question->leadin)), $params->dir, 'question', $question->load_id);

      $this->outputfiles[$file] = $this->output;
    }

    // should we export a test file?
    if (count($data->papers) == 1) {

      foreach ($data->papers as $paper) {
        // create manifest for questions with no associated paper
        $ob = new OB();
        $ob->ClearAndSave();
        include "qti20/tmpl/imsmanifest-paper.php";
        $manifest = $ob->GetContent();
        $ob->Restore();

        $file = "imsmanifest.xml";
        $filename = $params->base_dir.$params->dir."/".$file;
        file_put_contents($filename, utf8_encode($manifest));
        $data->files[] = new ST_File($file, "IMS Manifest", $params->dir, 'manifest', $question->load_id);
        $this->outputfiles[$file] = $manifest;

        // create manifest for questions with no associated paper
        $ob = new OB();
        $ob->ClearAndSave();
        include "qti20/tmpl/test-paper.php";
        $manifest = $ob->GetContent();
        $ob->Restore();

        $file = "test.xml";
        $filename = $params->base_dir.$params->dir."/".$file;
        file_put_contents($filename, utf8_encode($manifest));
        $data->files[] = new ST_File($file, "Test File", $params->dir, 'test', $question->load_id);
        $this->outputfiles[$file] = $manifest;
      }

    } else {

      // create manifest for questions with no associated paper
      $ob = new OB();
      $ob->ClearAndSave();
      include "qti20/tmpl/imsmanifest-question.php";
      $manifest = $ob->GetContent();
      $ob->Restore();

      $file = "imsmanifest.xml";
      $filename = $params->base_dir.$params->dir."/".$file;
      file_put_contents($filename, utf8_encode($manifest));
      $data->files[] = new ST_File($file, "IMS Manifest", $params->dir, 'manifest', $question->load_id);
      $this->outputfiles[$file] = $manifest;

      // create manifest for questions with no associated paper
      $ob = new OB();
      $ob->ClearAndSave();
      include "qti20/tmpl/test-question.php";
      $manifest = $ob->GetContent();
      $ob->Restore();

      $file = "test.xml";
      $filename = $params->base_dir.$params->dir."/".$file;
      file_put_contents($filename, utf8_encode($manifest));
      $data->files[] = new ST_File($file, "Test File", $params->dir, 'test', $question->load_id);
      $this->outputfiles[$file] = $manifest;
    }

    foreach ($this->outputfiles as $file => $text) {
      echo "<h4>QTI File : $file</h4>";
      echo "<pre>";
      echo htmlentities($text);
      echo "</pre>";
    }
  }

  function OutputQuestion(&$question) {
    if ($question->media) {
      $this->data->files[] = new ST_File($question->media, $question->media, $this->params->dir, 'image', $question->load_id);
    }

    if ($question->type == "blank") $this->SaveBlank($question);
    elseif ($question->type == "calculation") $this->SaveCalculation($question);
    elseif ($question->type == "dichotomous") $this->SaveDichotomous($question);
    elseif ($question->type == "extmatch") $this->SaveExtMatch($question);
    elseif ($question->type == "flash") $this->SaveFlash($question);
    elseif ($question->type == "hotspot") $this->SaveHotspot($question);
    elseif ($question->type == "info") $this->SaveInfo($question);
    elseif ($question->type == "labelling") $this->SaveLabelling($question);
    elseif ($question->type == "likert") $this->SaveLikert($question);
    elseif ($question->type == "matrix") $this->SaveMatrix($question);
    elseif ($question->type == "mcq") $this->SaveMcq($question);
    elseif ($question->type == "mrq") $this->SaveMrq($question);
    elseif ($question->type == "rank") $this->SaveRank($question);
    elseif ($question->type == "textbox") $this->SaveTextbox($question);
    else $this->AddError("Question type ".$question->type." not yet supported", $question->load_id);

  }

  function MakeQuestionHeader(&$question) {
    $output = '';

    // do we have a theme? if so use it as title
    if ($question->theme) {
      $title = $question->theme;
    } else {
      // no title, use leadin for title
      $title = $question->leadin;
    }

    if ($question->notes) $output .= "<p>".htmlentities($question->notes)."</p>\n";

    if ($question->scenario) $output .= MakeNiceXHTML($question->scenario)."\n";

    if ($question->leadin) $output .= MakeNiceXHTML($question->leadin)."\n";

    if ($output == "") $output = "<p></p>";

    $title = StripForTitle($title);

    return array($output, $title);
  }

  function MakeQuestionHeaderBlank(&$question) {
    $output = "";

    // do we have a theme? if so use it as title
    if ($question->theme) {
      $title = $question->theme;
    } else {
      // no title, use leadin for title
      $title = $question->leadin;
    }

    if ($question->notes) $output .= "<p>".htmlentities($question->notes)."</p>\n";

    if ($question->leadin) $output .= MakeNiceXHTML($question->leadin)."\n";

    if ($output == "") $output = "<p></p>";

    $title = StripForTitle($title);

    return array($output, $title);
  }

  // DONE
  function SaveBlank(&$question) {
    // format the text for the question
    list($headertext, $title) = $this->MakeQuestionHeaderBlank($question);

    //echo "Doing SaveBlank - " . $question->displaymode . "<br>";
    $ob = new OB();
    $ob->ClearAndSave();
    if (strtolower($question->displaymode) == "dropdown") include "qti20/tmpl/blank-dropdown.php";
    else include "qti20/tmpl/blank-textentry.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();
  }

  // TODO
  function SaveCalculation(&$question) {
    // TODO : NO template
    // format the text for the question

    foreach ($question->variables as $var => $vairable) {
      $$var = MathsUtils::gen_random_no(checkVariables($vairable->min), checkVariables($vairable->max), $vairable->inc, $vairable->dec);
    }

    eval("\$answer = ".$question->formula.";");

    $q_text = $question->leadin;
    $q_text = str_ireplace("\$A", $A, $q_text);
    $q_text = str_ireplace("\$B", $B, $q_text);
    $q_text = str_ireplace("\$C", $C, $q_text);
    $q_text = str_ireplace("\$D", $D, $q_text);
    $q_text = str_ireplace("\$E", $E, $q_text);
    $q_text = str_ireplace("\$F", $F, $q_text);
    $q_text = str_ireplace("\$G", $G, $q_text);
    $q_text = str_ireplace("\$H", $H, $q_text);

    //echo $q_text."<BR>";

    $question->leadin = $q_text;

    $headertext = $this->MakeQuestionHeader($question);
    $title = StripForTitle($question->leadin);

    $ob = new OB();
    $ob->ClearAndSave();
    include "qti20/tmpl/calculation.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();
  }

  // DONE
  function SaveDichotomous(&$question) {
    // list of score methods:
    /*
     TF_NegativeAbstain - True/False/Abstain (Negative Marking -1)
     TF_NegativeAbstainHalf - True/False/Abstain (Negative Marking -0.5)
     TF_Positive - True/False
     YN_NegativeAbstain - Yes/No/Abstain (Negative Marking -1)
     YN_Positive - Yes/No
     */

    // generate the marking and text to be used
    $hasab = false;
    $negmark = 0;
    $true = "True";
    $false = "False";

    if ($question->score_method == "TF_NegativeAbstain") {
      $hasab = 1;
      $negmark = -1;
    } else if ($question->score_method == "TF_NegativeAbstainHalf") {
      $hasab = 1;
      $negmark = -1;
      $this->AddWarning("Negative half marks not supported with QTI, using negative 1 instead", $question->load_id);
    } else if ($question->score_method == "TF_Positive") {
      // default
      } else if ($question->score_method == "YN_NegativeAbstain") {
      $true = "Yes";
      $false = "No";
      $hasab = 1;
      $negmark = -1;
    } else if ($question->score_method == "YN_Positive") {
      $true = "Yes";
      $false = "No";
    }

    // header stuff
    list($headertext, $title) = $this->MakeQuestionHeader($question);

    $ob = new OB();
    $ob->ClearAndSave();
    include "qti20/tmpl/dichotomous.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();

    foreach ($question->options as $option) {
      if ($option->media) {
        $this->data->files[] = new ST_File($option->media, $option->media, $option->params->dir, 'image', $question->load_id);
      }
    }
  }

  // DONE
  function SaveExtMatch(&$question) {
    // format the text for the question
    $this->AddWarning("QMP Cannot import extended matching questions correctly, it cannot handle the multiple select stuff", $question->load_id);

    list($headertext, $title) = $this->MakeQuestionHeader($question);

    $ob = new OB();
    $ob->ClearAndSave();
    include "qti20/tmpl/extmatch.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();

    foreach ($question->scenarios as $scenarios) {
      if ($scenarios->media) {
        $this->data->files[] = new ST_File($scenarios->media, $scenarios->media, $scenarios->params->dir, 'image');
      }
    }
  }

  // TODO
  function SaveFlash(&$question) {
    //$this->AddError("Question type " . $question->type . " not yet supported",$question->load_id);
    }

  // TODO
  function SaveHotspot(&$question) {
    //$this->AddError("Question type " . $question->type . " not yet supported",$question->load_id);
    }

  // DONE
  function SaveInfo(&$question) {
    // format the text for the question
    list($headertext, $title) = $this->MakeQuestionHeader($question);

    $ob = new OB();
    $ob->ClearAndSave();
    include "qti20/tmpl/info.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();
  }

  // TODO
  function SaveLabelling(&$question) {

  }

  // DONE
  function SaveLikert(&$question) {
    // format the text for the question
    list($headertext, $title) = $this->MakeQuestionHeader($question);

    $ob = new OB();
    $ob->ClearAndSave();
    include "qti20/tmpl/likert.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();
  }

  // DONE
  function SaveMatrix(&$question) {
    // NO FEEDBACK ON MATRIX!!!

    // format the text for the question
    list($headertext, $title) = $this->MakeQuestionHeader($question);
    $max_score = count($question->scenarios);

    $ob = new OB();
    $ob->ClearAndSave();
    include "qti20/tmpl/matrix.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();
  }

  // DONE
  function SaveMcq(&$question) {
    // fairly sure this is exporting correctly, feedback for pos + neg ok,
    // all options listed ok

    // format the text for the question
    list($headertext, $title) = $this->MakeQuestionHeader($question);

    foreach ($question->options as $oid => $option) {
      if ($question->correct != $oid) continue;
      $correctid = $this->ll[$oid];
    }

    $ob = new OB();
    $ob->ClearAndSave();
    include "qti20/tmpl/mcq.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();

    foreach ($question->options as $option) {
      if ($option->media) {
        $this->data->files[] = new ST_File($option->media, $option->media, $option->params->dir, 'image');
      }
    }
  }

  // DONE
  function SaveMrq(&$question) {
    // QMP doesnt pay attention
    // to the maxnumber field in render_choice so allows all options to be checked
    // spits out valid QTI format, but QMP doesnt read it correctly (or export it
    // correctly for that matter)

    list($headertext, $title) = $this->MakeQuestionHeader($question);

    // work out how many correct answers we have
    $maxanswers = 0;
    foreach ($question->options as $option) {
      if ($option->is_correct) $maxanswers++;
    }

    // use different template depending on marking type
    // current marking types - allnegative, selectedpositive, allitemscorrect
    // marking type other not currently supported

    $ob = new OB();
    $ob->ClearAndSave();
    $hasother = 0;

    // 1 mark per correct answer, negative for wrong ones. 4 item question
    // with 2 correct answers will result in following
    // 2 correct answers - 4 marks
    // 2 incorrect answers - -4 marks
    if (strtolower($question->score_method) == "allnegative") {
      $wrongmark = -1;
      include "qti20/tmpl/mrq-selectedpositive.php";
    }

    // multiple marks for question - 1 mark per positive, should only be able to
    // select same no of options as correct answers but not in QMP as its broken
    if (strtolower($question->score_method) == "selectedpositive") {
      $wrongmark = 0;
      include "qti20/tmpl/mrq-selectedpositive.php";
    }

    // results and feedback for 1 mark for all items correcte, should only be able to
    // select same no of options as correct answers but not in QMP as its broken
    if (strtolower($question->score_method) == "allitemscorrect") include "qti20/tmpl/mrq-allitemscorrect.php";

    // other - 1 mark per correct, no maximum number of items, and other box.
    // NOT WORKING
    if (strtolower($question->score_method) == "other") {
      $this->AddError("Other text entry not supported for this question type", $question->load_id);
      $wrongmark = 0;
      $hasother = 1;
      include "qti20/tmpl/mrq-selectedpositive.php";
    }
    $this->output .= $ob->GetContent();
    $ob->Restore();

    foreach ($question->options as $option) {
      if ($option->media) {
        $this->data->files[] = new ST_File($option->media, $option->media, $option->params->dir, 'image');
      }
    }
  }

  // TODO
  function SaveRank(&$question) {
    // format the text for the question

    list($headertext, $title) = $this->MakeQuestionHeader($question);

    //build list of options
    $optlist = array();
    $optlist[0] = "-";
    foreach ($question->options as $option) {
      if ($option->order == "") $option->order = 0;
      $optlist[$option->order] = OrderToStr($option->order);
    }
    $optlist[9990] = "N/A";

    $question->optlist = $optlist;

    // different template for each type of marking
    $ob = new OB();
    $ob->ClearAndSave();

    if (strtolower($question->score_method) == "strictorder") include "qti20/tmpl/rank-strictorder.php";
    if (strtolower($question->score_method) == "allitemscorrect") include "qti20/tmpl/rank-allitemscorrect.php";
    if (strtolower($question->score_method) == "orderneighbours") {
      $this->AddWarning("'Strict order plus half marks for neighbours' is not a supported marking type, using 'Strict order (mark per option)' instead", $question->load_id);
      include "qti20/tmpl/rank-strictorder.php";
      //include "qti12/tmpl/rank-orderneighbours.php";
      }
    if (strtolower($question->score_method) == "bonusmark") {
      $this->AddWarning("'Correct items with bonus for overall order' is not a supported marking type, using 'Strict order (mark per option)' instead", $question->load_id);
      include "qti20/tmpl/rank-strictorder.php";
      //include "qti12/tmpl/rank-bonusmark.php";
      }
    $this->output .= $ob->GetContent();

    $ob->Restore();
  }

  // DONE
  function SaveTextBox(&$question) {
    // format the text for the question
    list($headertext, $title) = $this->MakeQuestionHeader($question);

    $this->AddWarning("Terms are not exported to QTI with this question type", $question->load_id);

    $ob = new OB();
    $ob->ClearAndSave();
    include "qti20/tmpl/textbox.php";
    $this->output .= $ob->GetContent();
    $ob->Restore();
  }

  function GetTDSet($type, $correct, &$question = '') {
    $res = new ST_Question_Timedate_set();
    $res->correct = $correct;
    $res->values[0] = "";
    if ($type == "dd") {
      for ($i = 1; $i <= 31; $i++) $res->values[] = sprintf("%02d", $i);
    } else if ($type == "MM") {
      for ($i = 1; $i <= 12; $i++) $res->values[] = sprintf("%02d", $i);
    } else if ($type == "yyyy") {
      for ($i = $question->startyear ; $i <= $question->endyear ; $i++) $res->values[] = sprintf("%02d", $i);
    } else if ($type == "hh") {
      for ($i = 0; $i < 24; $i++) $res->values[] = sprintf("%02d", $i);
    } else if ($type == "mm") {
      for ($i = 0; $i < 60; $i++) $res->values[] = sprintf("%02d", $i);
    } else if ($type == "ss") {
      for ($i = 0; $i < 60; $i++) $res->values[] = sprintf("%02d", $i);
    } else if ($type == "MMMM") {
      $res->values[1] = "Jan";
      $res->values[2] = "Feb";
      $res->values[3] = "Mar";
      $res->values[4] = "Apr";
      $res->values[5] = "May";
      $res->values[6] = "Jun";
      $res->values[7] = "Jul";
      $res->values[8] = "Aug";
      $res->values[9] = "Sep";
      $res->values[10] = "Oct";
      $res->values[11] = "Nov";
      $res->values[12] = "Dec";

      $res->correct = $res->values[(int) $res->correct];
    }

    unset($res->values[0]);
    foreach ($res->values as $id => $value) {
      if ($res->correct == $value) {
        $res->correct = $id;
        break;
      }
    }
    return $res;
  }

  function DoHeader() {
    /*$output = "<?xml version='1.0' standalone='no'?>\n";
     $output .= "<!DOCTYPE questestinterop SYSTEM 'ims_qtiasiv1p2.dtd'>\n\n";
     $output .= "<questestinterop>\n";
    		
     return $output;	*/
  }

}
