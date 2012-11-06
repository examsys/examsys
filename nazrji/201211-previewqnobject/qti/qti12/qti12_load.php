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

class IE_qti12_Load extends IE_Main {
  var $likert_values = array();
  var $dich_values = array();
  var $abstainvalues = array();
  var $result;

  function __construct() {
    $this->ll = array();
    for ($i = 1; $i < 27; $i++) {
      $varletter = chr(ord('A') + $i - 1);
      $this->ll[$i] = $varletter;
    }

    $this->BuildMatchArrays();

    $this->result = new stdClass();
  }

  function BuildMatchArrays() {
    global $string;

    // some values for abstaining and not applicable
    $this->abstainvalues = explode("|", strtolower($string['na_abstain']));

    // values for likert scales

    // from touchstone
    ExplodeToArray($this->likert_values, $string['failpass3']);
    ExplodeToArray($this->likert_values, $string['disagre3']);

    ExplodeToArray($this->likert_values, $string['disagre4']);

    ExplodeToArray($this->likert_values, $string['disagre4']);
    ExplodeToArray($this->likert_values, $string['disagre5a']);
    ExplodeToArray($this->likert_values, $string['disagre5b']);
    ExplodeToArray($this->likert_values, $string['disagre5c']);

    // from qmp
    ExplodeToArray($this->likert_values, $string['qmpagree3']);
    ExplodeToArray($this->likert_values, $string['qmplike3']);
    ExplodeToArray($this->likert_values, $string['qmplikeme3']);
    ExplodeToArray($this->likert_values, $string['qmpsatisfied3']);

    ExplodeToArray($this->likert_values, $string['qmpagree4']);
    ExplodeToArray($this->likert_values, $string['qmplike4']);
    ExplodeToArray($this->likert_values, $string['qmplikeme4']);
    ExplodeToArray($this->likert_values, $string['qmpsatisfied4']);

    ExplodeToArray($this->likert_values, $string['qmpagree5']);
    ExplodeToArray($this->likert_values, $string['qmplike5']);
    ExplodeToArray($this->likert_values, $string['qmplikeme5']);
    ExplodeToArray($this->likert_values, $string['qmpsatisfied5']);

    // values for dichotomous
    ExplodeToArray($this->dich_values, $string['qmptf']);
    ExplodeToArray($this->dich_values, $string['qmpyn']);
  }

  function Load($params) {
    global $string;

    $file = $params->sourcefile;
    $this->params = $params;

    $xmlStr = file_get_contents($file);
    $xmlStr = str_replace('webct:localizable_mattext','webct_localizable_mattext',$xmlStr,$count);
    $xmlStr = str_replace(array('‘','’'),array("'","'"),$xmlStr,$count);
    $xml = @simplexml_load_string($xmlStr);

    if (!$xml) {
      $this->AddError(sprintf($string['invalidxml'], $file));
      return;
    }
$rt="";

    // detector for jumbled sentance
    $pos=strpos($xmlStr,"wct_questiontype");


$numb=0;


    // single assessment object possible
    if ($xml->assessment) $this->LoadAssessment($xml->assessment);

    // multiple xml section objects possible
    if ($xml->section) {
      foreach ($xml->section as $section) $this->LoadSection($section);
    }

    // multiple xml item objects possible
    if ($xml->item) {
      foreach ($xml->item as $item) $this->LoadItem($item);
    }

    //print_p($this->result,false);

    return $this->result;
  }

  function LoadAssessment($xml) {
    $paper = new ST_Paper();
    $paper->load_id = (string) $xml->attributes()->ident;
    $paper->title = (string) $xml->attributes()->title;

    $wct=0;
    if(isset($xml->qtimetadata)) {
      foreach($xml->qtimetadata as $each1) {
        if(isset($each1->qtimetadatafield)) {
          foreach($each1->qtimetadatafield as $each2) {
            if(substr($each2->fieldlabel,0,3)=="wct") {
              $wct=1;
            }
          }
        }
      }
    }

    $this->result->papers[] = $paper;
    $paper->screens[0] = "";
    foreach ($xml->section as $section) {
      $screen = $this->LoadSection($section);
    }
    if($wct==1) {
      unset($this->result->papers);
      unset($this->result->screens);
    }
    unset($paper->screens[0]);
  }

  function LoadSection($xml) {
    global $string;

    $screen = new ST_Paper_Screen();

    echo "{$string['loadingsection']}<br>";
    foreach ($this->result->papers as & $p) {
      $paper = $p;
      break;
    }

    $screen = new ST_Paper_Screen();
    $this->result->screens[] =& $screen;

    foreach ($xml->item as $item) {
      $id = $this->LoadItem($item);
      $screen->question_ids[$paper->GetNextQuestionID()] = $id;
    }

    $paper->screens[$paper->GetNextScreenID()] = $screen;
  }

  function LoadItem($item) {
    global $q_warnings, $q_errors, $string;

    $q_warnings = array();
    $q_errors = array();

    $q_imp = $this->LoadQuestion($item);
    $type = $this->DetermineQType($q_imp);

    $oiii = print_r($q_imp,TRUE);
    $t = 8;
    $question = '';
    $marks = '';

    if ($type == "blank") $question = $this->LoadBlank($q_imp);
    elseif ($type == "calculation") $question = $this->LoadCalculation($q_imp);
    elseif ($type == "truefalse") $question = $this->LoadTrueFalse($q_imp);
    elseif ($type == "true_false") $question = $this->LoadTrueFalse($q_imp);
    elseif ($type == "dichotomous") $question = $this->LoadDichotomous($q_imp);
    elseif ($type == "extmatch") $question = $this->LoadExtmatch($q_imp);
    elseif ($type == "flash") $question = $this->LoadFlash($q_imp);
    elseif ($type == "hotspot") $question = $this->LoadHotspot($q_imp);
    elseif ($type == "info") $question = $this->LoadInfo($q_imp);
    elseif ($type == "info") $question = $this->LoadInfo($q_imp);
    elseif ($type == "labelling") $question = $this->LoadLabelling($q_imp);
    elseif ($type == "likert") $question = $this->LoadLikert($q_imp);
    elseif ($type == "matrix") $question = $this->LoadMatrix($q_imp);
    elseif ($type == "mcq") $question = $this->LoadMCQ($q_imp);
    elseif ($type == "mrq") $question = $this->LoadMRQ($q_imp);
    elseif ($type == "rank") $question = $this->LoadRank($q_imp);
    elseif ($type == "textbox") $question = $this->LoadTextbox($q_imp);
    elseif ($type == "timedate") $question = $this->LoadTimedate($q_imp);
    elseif ($type == "error") $question = $this->LoadError($q_imp);
    else {
      $question = $this->LoadUnknown($q_imp);
      $this->AddError(sprintf($string['qunsupported'], $type), $q_imp->load_id);
    }
    // DEBUGDEBUG add if WCTJUMB SENTANCE DETECT AND OVERWRITE $question->$question

    if($q_imp->wct_questiontype=="WCT_JumbledSentence") {
      if($q_imp->options2["%BLANK_1%"][0]==NULL) {
        foreach ($q_imp->respconditions as & $respconditions) {
          if(count($respconditions->sortedout)>0) {
            foreach($respconditions->sortedout as $par3 => $child3) {
              $string=sprintf("%%BLANK_%d%%",$par3);
              $lk2=$respconditions->sortedout[$par3];
              $options[$string][]=$q_imp->optionslk2[$lk2];
              foreach($q_imp->optionslk2 as $par4 => $child4) {
                if($par4!=$lk2) {
                  $options[$string][]=$q_imp->optionslk2[$par4];
                }
              }
            }
          }
        }
        $q_imp->options2=$options;
      }


      unset($question->question);
      unset($question->options);
      $question->question=$q_imp->question2;
      foreach($q_imp->options2 as $parent => $child) {
        foreach($child as $parent1 => $child1) {
          $blank = new STQ_Blank_Option();
          $blank->display = $child1;
          $blank->correct = 1;
          $blankoptions[] =$blank;
        }
        $question->options[$parent]=$blankoptions;
        unset($blankoptions);
      }
      $question->q_option_order="display order";

      if(strlen($question->leadin)<1) {
        $question->leadin=$item->attributes()->title;
      }
    }

    $oiii = print_r($question,TRUE);
    $t = 9;
    $t = 8;

    if (!empty($q_imp->material->media)) {
      $question->media = $q_imp->material->media;
      $question->media_width = $q_imp->material->media_width;
      $question->media_height = $q_imp->material->media_height;
    }

    // load taxinomy and keywords
    if (array_key_exists('BLOOMS', $q_imp->params)) {
      $question->bloom = $q_imp->params['BLOOMS'];
    }

    foreach ($q_imp->params['KEYWORD'] as $keyword) {
      $question->keywords[] = $keyword;
    }

    foreach ($q_warnings as $warn) $this->AddWarning($warn, $q_imp->load_id);

    foreach ($q_errors as $error) $this->AddError($error, $q_imp->load_id);

    if ($question) {
      $this->result->questions[$question->load_id] = $question;
    } else {
      $this->result->questions[] = '';
    }

    // any warnings that have been put under question 99999 should be moved to proper question id

    unset($q_imp->raw_xml);
    unset($q_imp->presentation);

    return $question->load_id;
  }

  function LoadQuestion(&$item) {
    $q = new ST_QTI12_Question($item);
    $q->CountStuff();
    if (!isset($q->q_option_order)) {
      $q->q_option_order='display order';
    }
    return $q;
  }

  function DetermineQType(&$question) {
    global $string;

    // no input stuff so this is an info question
    if (empty($question->counts['response'])) return "info";

    if (isset($question->counts['hotspot']) && $question->counts['hotspot'] > 0) {
      if ($question->cardinality == "Multi") return "labelling";
      return "hotspot";
    }


    if($question->wct_questiontype =="WCT_Matching") {
      print "WebCT Matching Question Detected<br>";
      return "extmatch";
    }

    if (isset($question->counts['grp']) && $question->counts['grp'] > 0) {
      $this->AddError($string['noresponsegroups'], $question->load_id);
      return "error";
    }

    if (isset($question->counts['extension']) && $question->counts['extension'] > 0 && $question->wct_questiontype == "WCT_JumbledSentence") {
      print "WebCT Jumbled Sentence Detected<br>";

      return "blank";
    }



    if (isset($question->counts['extension']) && $question->counts['extension'] > 0) {
      $this->AddError($string['norenderextensions'], $question->load_id);
      return "error";
    }

    if ($question->qmd_itemtype == 'Multiple Response - 1 mark per True Option with Other') return "mrq";

    // do we have differing labels for each part of the question?? if so only thing we can possibly import as is 'blank'
    if (!$question->labelsets) {
      // if we dont have single cardinality, then we cant import a blank with dropdowns
      if ($question->cardinality != 'Single') {
        $this->AddError($string['nomultiplecard'], $question->load_id);
        return 'error';
      }

      return 'blank';
    }

    // if we have multiple materials in our question, things are all odd, so process seperatly, 'blank' only i think
    // if a blank question has single material followed by single lid then will get imported as something else
    /*if ($question->counts['material'] > 1)
     return "blank";*/

    // single field entry
    if ($question->counts['fib'] == 1) {
      $rows = 0;
      $responses = $this->GetResponses($question, '', 'fib');
      foreach ($responses as $response) {
        if ($response->fibtype == 'String') {
          $rows = $response->rows;
          break;
        }
      }

      if ($rows > 1) return "textbox";

      // check for any correct responses, if 1 then we are a fill in the blank
      if ($question->counts['num'] == 1) return "calculation";

      list($positive, $zero, $negative) = $this->GetRespConditionMarkCounts($question);

      return "blank";
    }

    if ($question->counts['slider'] == 1) return "calculation";

    // single material in question, good for all qmp exported things except 'blank'

    // single response lid, possible are mrq, dichotomous, likert, mcq
    if ($question->counts['lid'] == 1) {
      // check cardinality
      // cant be varies as only single lid
      if ($question->cardinality == "Multi") return "mrq";

      // either dichotomous, likert or mcq
      // need to match options to one of our know lists	

      $response_list = $this->GetResponseLabelList($question);

      if($question->qmd_itemtype =="Multiple Choice") return "mcq";
      if($question->qmd_itemtype =="True False") return "truefalse";
      if($question->wct_questiontype =="WCT_TrueFalse") return "truefalse";

      if (MatchArraySet($this->dich_values, $response_list, $this->abstainvalues)) return "dichotomous";

      if (MatchArraySet($this->likert_values, $response_list, $this->abstainvalues)) return "likert";

      // hack to ensure touchstone fill in the blanks are imported as such
      if ($question->qmd_itemtype == "Select a Blank" || $question->qmd_itemtype == "Select a Blank") return "blank";

      // hack to ensure touchstone likert are imported as such
      if ($question->qmd_itemtype == "Likert Scale") return "likert";

      return "mcq";
    }

    // multiple lid, possible are extmatching, ranking, matrix, dichotomous
    if ($question->counts['lid'] > 1) {
      // should be checking to see if each of the sets of answers are the same
      if (!$question->labelsets) {
        // label sets are not the same	
        $this->AddError($string['labelsetserror'], $question->load_id);
        return "blank";
      }

      // check cardinality
      // if single then ranking or matrix
      
      if ($question->cardinality == "Single") {
        $response_list = $this->GetResponseLabelList($question);

        //print_p($response_list);
        // need to check values to see if dichotomous
        if (MatchArraySet($this->dich_values, $response_list, $this->abstainvalues)) return "dichotomous";

        // need to check values to see if ranking	
        if ($this->IsRankingQuestion($response_list)) return "rank";

        if ($question->qmd_itemtype == "Pull-down list") return "extmatch";
        
        if ($question->qmd_itemtype == "Extended Matching") return "extmatch";

        return "matrix";
      }

      // more than one response, and cardinality not single
      return "extmatch";
    }

    // single num calculation,
    if ($question->counts['num'] == 1) return "calculation";

    // multiple numeric, not supported
    if ($question->counts['num'] > 1) {
      $this->AddError($string['nomultiinputs'], $question->load_id);
      return "error";
    }

    if ($question->counts['str'] > 0) {
      return "blank";
    }
    // multiple string, but only 1 material, could this be a blank question??
    if ($question->counts['str'] > 1) {
      return "blank";
    }

    // hotspot, labelling
    if ($question->counts['xy'] > 0) {
      // either hotspot or labelling, not sure how to pick yet
      }

    return "unknown";
  }

  function IsRankingQuestion(&$response_list) {
    if (count($response_list) < 3) return false;

    // check for all but 2 of the items matching 1st/2nd/3rd etc
    $match_str = array();
    $match_num = array();
    for ($i = 1; $i < count($response_list) - 1; $i++) {
      $item = OrderToStr($i);
      $match_num[$i] = 0;
      $match_str[$item] = 0;
    }

    foreach ($response_list as $item) {
      if (array_key_exists($item, $match_str)) $match_str[$item] = 1;
      if (array_key_exists($item, $match_num)) $match_num[$item] = 1;
    }

    $str_ok = true;
    foreach ($match_str as $isok) if ($isok == 0) $str_ok = false;

    if ($str_ok) {
      echo "Found ranking based on 1st, 2nd, 3rd etc<br>";
      return true;
    }

    $num_ok = true;
    foreach ($match_num as $isok) if ($isok == 0) $num_ok = false;

    return false;
  }

  function GenerateQuestionInfo(&$question, $material, $title, $responsemat = '') {
    $notes = '';
    foreach ($material->chunks as & $chunk) {
      if ($chunk->label == "theme") {
        $question->theme .= strip_tags($chunk->GetHTML());
      } else if ($chunk->label == "notes") {
        $notes .= strip_tags($chunk->GetHTML());
        $question->notes .= trim(str_ireplace("note:", "", $notes));
      } else if ($chunk->label == "scenario") {
        $question->scenario .= $chunk->GetHTML();
      } else { // anything else or leadin
        $question->leadin .= $chunk->GetHTML();
      }
    }

    if ($responsemat) {
      $question->leadin .= $responsemat->GetHTML();
    }

    $question->leadin = RemoveLoneP($question->leadin);
  }

  function ReplaceTitle(&$title, $scn) {
    $scn = NX_ChangePreSetCharsToRaw($scn);

    // parse scenario into xml chunks
    $bits = explode("<", $scn);

    $to_remove = array();
    // for each chunk
    $newtitle = "";
    $removed_count = 0;
    foreach ($bits as $bit) {
      $bit = trim($bit);
      if ($bit == "") continue;
      if (substr($bit, strlen($bit) - 1, 1) == ">") continue;

      if (strpos(" ".$bit, ">") > 0) $bit = substr($bit, strpos($bit, ">") + 1);

      // if it fits into title and is longer then X character
      if (strlen($bit) < 5) continue;

      //echo "CHUNK : " . htmlentities($bit) . "<br>";

      if (stripos(" ".$title, $bit) > 0) {
        $to_remove[] = $bit;
        $removed_count += strlen($bit);
        $newtitle .= $bit." ";
        if ($removed_count > 10) {
          $title = trim($newtitle);
          //echo "Breaking as chunk !$bit! due to more than 10 characters<br>";
          break;
        }
      } else if (strlen($bit) > 3) {
        // force title to be regenerated if we found a chunk that isnt in it
        $title = trim($newtitle);
        break;
      }
    }

    foreach ($to_remove as $remove) {
      // bodge to limit title to around 50 characters. Once we have removed 50 characters worth of cunks, replace the title
      $scn = str_ireplace($remove, "", $scn);
    }

    $scn = trim(RemoveEmptyHTMLTags($scn));
    $scn = trim(MakeValidHTML($scn));

    if (strtolower($scn) == "<br>") $scn = "";
    if (strtolower($scn) == "<br/>") $scn = "";
    if (strtolower($scn) == "<br />") $scn = "";

    if (strlen($scn) < 3) $scn = "";

    return $scn;
  }

  //////////////////////////////////
  // SPECIFIC QUESTION TYPE LOADS //
  //////////////////////////////////

  function LoadBlank(&$source) {
    global $string;

    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Blank();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "blank";

    $this->GenerateQuestionInfo($dest, $source->material, $source->title);
    $dest->scenario = "";
    $dest->leadin = "";
    foreach ($source->material->chunks as & $chunk) {
      if ($chunk->label == "leadin") {
        $dest->leadin .= $chunk->GetHTML();
      }
    }

    // determine if strings or if dropdowns and call appropriate function
    if ($source->counts['lid'] > 0) {
      $this->BlankDropdowns($dest, $source);
    } else if ($source->counts['fib'] > 0) {
      $this->BlankString($dest, $source);
    } else {
      $this->AddError($string['blanktypeerror'], $source->load_id);
    }
    
    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
    $dest->marks_correct = $marks_correct;
    $dest->marks_incorrect = $marks_incorrect;
    $dest->marks_partial = $marks_partial;

    $fb = $this->GetAllFeedbacks($source);
    $dest->feedback = $this->GetFeedbackFromArray($source, $fb);

    foreach ($dest->question as & $chunk) {
      if (substr($chunk, 0, 1) != "%") $chunk = " ".$chunk." ";
    }

    return $dest;
  }

  function BlankString(&$dest, &$source) {
    global $string;

    echo "{$string['loadingblank']}<br>";
    $qtext = array();
    $optionid = 1;
    foreach ($source->presentation->children() as $child) {
      $this->ProcessBlankChild($dest, $source, $qtext, $optionid, $child, false);
    }

    $dest->question = $qtext;
    $dest->displaymode = "textboxes";
  }

  function ProcessBlankChild(&$dest, &$source, &$qtext, &$optionid, &$child, $inflow) {
    global $string;

    $name = $child->getName();
    echo "Name : $name<br>";
    if ($name == "flow") {
      $qtext[] = "<div>";
      echo "Found flow node, processing its children<br>";
      foreach ($child->children() as $subchild) {
        $this->ProcessBlankChild($dest, $source, $qtext, $optionid, $subchild, true);
      }
      $qtext[] = "</div>";
    }

    if ($name == 'material') {
      if ($child->attributes()->label != "") return;
      $material = new ST_QTI12_Material();
      $material->add($child);
      if ($dest->leadin == "") {
        $dest->leadin = $material->GetHTML();
      } else {
        if (!$inflow) $qtext[] = "<p>";
        $qtext[] = $material->GetHTML();
        if (!$inflow) $qtext[] = "</p>";
      }
    }

    if ($name == 'response_str') {
      $qtext_i = array();

      $response = new ST_QTI12_Response('str', $child);

      foreach ($child->children() as $chunk) {
        $name = $chunk->getName();

        if ($name == "material") {
          $material = new ST_QTI12_Material();
          $material->add($chunk);
          $qtext_i[] = $material->GetHTML();
          if (!$inflow) $qtext_i[] = "<br />";
        } else if ($name == "render_fib") {
          if (count($chunk->children()) == 0) {
            echo $string['addingsub'] . '<br>';
            // no children in the fib, just add as a blank
            $blankid = "%".sprintf("BLANK_%d", $optionid)."%";

            $blankoptions = array();

            // get all conditions that are related to this response
            $conds = $this->GetRespConditions($source, 1, $response->id);

            foreach ($conds as $cond) {
              foreach ($cond->conditions as $opt) {
                $blank = new STQ_Blank_Option();
                $blank->display = strip_tags($opt->value);
                $blank->correct = 1;
                $blankoptions[] = $blank;
              }

              // need to work out if this label is correct or not
              }

            $dest->options[$blankid] = $blankoptions;
            $qtext_i[] = " ";
            $qtext_i[] = $blankid;
            $qtext_i[] = " ";
            $optionid++;
          } else {
            $index = 1;
            echo "Adding sub item - render_fib<br>";
            foreach ($chunk->children() as $subchunk) {
              $subname = $subchunk->getName();
              //echo "Sub chunk name : $subname<br>";
              if ($subname == "material") {
                $material = new ST_QTI12_Material();
                $material->add($subchunk);
                $text = $material->GetHTML();
                $qtext_i[] = $text;
              } else if ($subname == "response_label") {
                // create a new blank for the label

                $blankid = "%".sprintf("BLANK_%d", $optionid)."%";

                $blankoptions = array();

                foreach ($source->respconditions as $respcondition) {
                  if ($respcondition->mark < 1) continue;
                  foreach ($respcondition->conditions as $condition) {
                    if ($condition->not) continue;
                    if ($condition->index && $condition->index != $index) continue;
                    if ($condition->respident != $response->id) continue;

                    $blank = new STQ_Blank_Option();
                    $blank->display = strip_tags($condition->value);
                    $blank->correct = 1;
                    $blankoptions[] = $blank;
                  }
                }

                $dest->options[$blankid] = $blankoptions;
                $qtext_i[] = " ";
                $qtext_i[] = $blankid;
                $qtext_i[] = " ";
                $optionid++;
                $index++;
              }
            }
          }
        } else {
          echo "Unknown Element : $name<br>";
        }
      }

      if (count($qtext_i) > 0) {
        foreach ($qtext_i as $text) $qtext[] = $text;
      }
    }
  }
  function BlankDropdowns(&$dest, &$source) {
    global $string;

    echo "{$string['loadingblankdrop']}<br>";
    $qtext = array();
    $optionid = 1;
    foreach ($source->presentation->children() as $child) {
      $name = $child->getName();

      if ($name == 'material') {
        if ($child->attributes()->label != "") continue;
        $material = new ST_QTI12_Material();
        $material->add($child);
        $qtext[] = $material->GetHTML();
      }

      if ($name == 'response_lid') {
        $response = new ST_QTI12_Response('lid', $child);

        $blankid = "%".sprintf("BLANK_%d", $optionid)."%";

        $blankoptions = array();

        // get all conditions that are related to this response
        $conds = $this->GetRespConditions($source, 1, $response->id);

        foreach ($response->labels as $id => $label) {
          $blank = new STQ_Blank_Option();
          $blank->display = $label->material->GetText();
          $blank->id = $id;
          $blankoptions[] = $blank;

          //echo "Checking for blank " . $id . " - ";
          // need to work out if this label is correct or not
          foreach ($conds as $cond) {
            foreach ($cond->conditions as $condvar) {
              if ($condvar->value != $blank->id) continue;
              if ($condvar->respident != $response->id) continue;
              if ($condvar->not) continue;

              $blank->correct = 1;
            }
          }
        }

        $dest->options[$blankid] = $blankoptions;
        $qtext[] = $blankid;
        $optionid++;
      }

      $dest->question = $qtext;
    }
    $dest->displaymode = "dropdown";
  }

  // DONE
  function LoadCalculation(&$source) {

    $dest = new ST_Question_Calculation();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "calculation";

    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);
    
    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
    $dest->marks_correct = $marks_correct;
    $dest->marks_incorrect = $marks_incorrect;
    $dest->marks_partial = $marks_partial;
    
    if($marks_partial > 0) {
      $dest->score_method = 'Allow partial Marks';
    } else {
      $dest->score_method = 'Mark per Question';
    }
    
    // get positive marked outcomes
    $respconds = $this->GetRespConditions($source, 5);
    $lessthan = '';
    $morethan = '';

    foreach ($respconds as $respcond) {
      foreach ($respcond->conditions as $cond) {        
        if ($cond->type == "varequal") {
          $dest->formula = $cond->value;
          break;
        } else if ($cond->type == "vargte" || $cond->type == "vargt") {
          $morethan = $cond->value;
        } else if ($cond->type == "varlte" || $cond->type == "varlt") {
          $lessthan = $cond->value;
        }
      }

      //if ($dest->formula) break;
    }
    if (!$dest->formula) {
      if ($lessthan != '' && $morethan != '') {
        $dest->formula = ($lessthan + $morethan) / 2;
      }
    }

    $feedback = $this->GetAllFeedbacks($source);
    $dest->feedback = $this->GetFeedbackFromArray($source, $feedback);

    // if we have question metadata containing variables and formula, then load em
    if (array_key_exists("VARIABLE", $source->params) && array_key_exists("FORMULA", $source->params) && array_key_exists("QUESTION", $source->params)) {
      echo "Calculation importing variables<br>";
      foreach ($source->params['VARIABLE'] as $var) {
        $varc = new STQ_Calc_Vars();
        list($var_id, $varc->min, $varc->max, $varc->dec, $varc->inc) = explode("|", $var);
        $dest->variables[$var_id] = $varc;
      }

      $dest->leadin = $source->params['QUESTION'];
      $dest->formula = $source->params['FORMULA'];

      if (array_key_exists('UNITS', $source->params)) $dest->units = $source->params['UNITS'];
      if (array_key_exists('DECIMALS', $source->params)) $dest->decimals = $source->params['DECIMALS'];
      if (array_key_exists('TOLERANCE', $source->params)) $dest->tolerance = $source->params['TOLERANCE'];
    }
    return $dest;
  }

  function LoadDichotomous(&$source) {
    global $string;

    // easy to do, no feedback in Rogo so goes the way of the dinosar
    $dest = new ST_Question_Dichotomous();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "dichotomous";
    if ($source->responses[1]->shuffle == 1) {
      $dest->q_option_order = 'random';
    }

    $this->GenerateQuestionInfo($dest, $source->material, $source->title);

    $responses_clean = $this->GetResponseLabelList($source);

    $mark_tf = false;
    $mark_abstain = false;
    $mark_negative = false;

    // do we have a N/A value?
    foreach ($responses_clean as $rid => $resp) {
      if (ItemInArray($this->abstainvalues, $resp)) {
        unset($responses_clean[$rid]);
        $mark_abstain = true;
      }
    }

    // work out true/false or yes/no
    $scale = implode("|", $responses_clean);
    if ($scale == "true|false") $mark_tf = true;

    // count up response conditions
    list($positive, $zero, $negative) = $this->GetRespConditionMarkCounts($source);
    if ($negative > 0) $mark_negative = true;
    
    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
    
    // set the display type
    if ($mark_tf) {
      if (!$mark_abstain) {
        $dest->display_method = "TF_Positive";
      } else {
        $dest->display_method = "TF_NegativeAbstain";
      }
    } else {
      if (!$mark_abstain) {
        $dest->display_method = "YN_Positive";
      } else {
        $dest->display_method = "YN_NegativeAbstain";
      } 
    }
    
    //set the score method
    $dest->score_method = "Mark per Option";
    
    if ($mark_abstain) {
      if (array_key_exists('MARKING', $source->params)) $dest->score_method = $source->params['MARKING'];
    }
    // load all options into the dest
    $optionid = 1;
    foreach ($source->responses as $response) {
      $option = new STQ_Dic_Options();
      $option->text = strip_tags($response->material->GetHTML(),"<div><span>");
      $option->response_id = $response->id;
      $option->iscorrect = 0;
      
      $option->marks_correct = $marks_correct;
      $option->marks_incorrect = $marks_incorrect;

      foreach ($response->labels as $id => $label) {
        $text = strtolower($label->material->GetText());
        if ($text == "yes" || $text == "true") $option->value_true = $id;
        if ($text == "no" || $text == "false") $option->value_false = $id;
      }

      if (!empty($response->material->media)) {
        $option->media = $response->material->media;
        $option->media_width = $response->material->media_width;
        $option->media_height = $response->material->media_height;
      }

      $dest->options[$optionid] = $option;
      $optionid++;
    }

    // attempt to work out the correct answers
    $conds = $this->GetRespConditions($source, 1);
    foreach ($conds as $condition) {
      if (count($condition->conditions) == 0) {
        $this->AddWarning($string['posnocond'], $source->load_id);
      } else {
        if (count($condition->conditions) > 1) $this->AddWarning($string['multiplepos'], $source->load_id);

        $correctvalue = $condition->conditions[0]->value;
        $id = $condition->conditions[0]->respident;

        //echo "Response $id - correct value is $correctvalue<br>";
        foreach ($dest->options as & $option) {
          if ($option->response_id != $id) continue;

          if ($option->value_true == $correctvalue) $option->iscorrect = 1;
        }
      }
    }

    // sort out feedback

    $generalfb = array();

    // for each answer
    foreach ($dest->options as & $option) {
      // get list of feedbacks for when correct
      $correctfb1 = $this->GetFeedbacks($source, $option->response_id, $option->value_true, $option->iscorrect);
      $incorrectfb1 = $this->GetFeedbacks($source, $option->response_id, $option->value_true, !$option->iscorrect);

      // get list of feedbacks for when incorrect
      $correctfb2 = $this->GetFeedbacks($source, $option->response_id, $option->value_false, !$option->iscorrect);
      $incorrectfb2 = $this->GetFeedbacks($source, $option->response_id, $option->value_false, $option->iscorrect);

      // merge arrays
      $incorrectfb = array_merge($incorrectfb1, $incorrectfb2);
      $correctfb = array_merge($correctfb1, $correctfb2);

      // get list of feedbacks common to both outcomes and add to general feedback array
      // remove common ones from the list
      RemoveCommonInArray($correctfb, $incorrectfb, $generalfb);

      $option->fb_correct = $this->GetFeedbackFromArray($source, $correctfb);
      $option->fb_incorrect = $this->GetFeedbackFromArray($source, $incorrectfb);

    }

    $dest->feedback = $this->GetFeedbackFromArray($source, $generalfb);

    return $dest;
  }

  // DONE
  function LoadExtmatch(&$source) {
    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Extmatch();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "extmatch";
    $shuf=0;
    foreach($source->responses as $respeach) {
      if ($respeach->shuffle == 1) {
        $shuf=1;
      }
    }
  if ($shuf == 1) {
    //  if ($source->responses[1]->shuffle == 1) {
      $dest->q_option_order = 'random';
    }

    $this->GenerateQuestionInfo($dest, $source->material, $source->title);

    // load option list

    $optionlist = $this->GetResponseLabelList($source, false,$lablk,$lablkd);
    $optid = 1;

    foreach ($optionlist as $id => $option) {
      $opt = new STQ_Extm_Option();
      $opt->option = $option;
      $opt->id = $id;

      $dest->optionlist[$optid] = $opt;
      $optid++;
    }
    $optid=1;


    // load all stems
    $stemid = 1;
    $respcond = $this->GetRespConditions($source, 1);
    print_p($respcond);

    $usedfb = array();

    $dest->marks = 0;

    foreach ($source->responses as $rid => $response) {

      $stem = new STQ_Extm_Scenario();
      $stem->stem = MakeValidHTML(RemoveLoneP($response->material->GetHTML()));
      $stem->base_response_id = $rid;

      if (!empty($response->material->media)) {
        $stem->media = $response->material->media;
        $stem->media_width = $response->material->media_width;
        $stem->media_height = $response->material->media_height;
      }

      $dest->scenarios[$stemid++] = $stem;

      $correct = array();
      foreach ($respcond as $cond) {
        foreach ($cond->conditions as $condvar) {
          // 1 mark per correct answer so skip ones that have a diff respident and mark correct rest
          if ($condvar->respident != $rid) continue;
          if ($condvar->not) continue;
          $correct[] = $condvar->value;
        }
      }
      
      list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
      $dest->marks_correct = $marks_correct;
      $dest->marks_incorrect = $marks_incorrect;
      $dest->marks_partial = $marks_partial;
    
      // work out correct answers for this stem

      $correct_mapped = array();
      foreach ($correct as $answer) {
        foreach ($dest->optionlist as $oid => $option) {
          if ($option->id == $answer) {
           $stem->correctans[] = $oid;
          }

        }
        foreach ($lablkd as $lablkk => $lablkv) {
          if($lablkk!="")
          {
            if($lablk[$rid][$answer] == $lablkk)
            {
              $stem->correctans[]=$lablkv;
            }
          }
        }
      }

      // get feedback for this stem
      $fb = $this->GetFeedbacks($source, $rid);

      $stem->feedback = $this->GetFeedbackFromArray($source, $fb);
    }

    return $dest;
  }

  // NEW
  function LoadFlash(&$source) {
    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Flash();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "flash";

    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    return $dest;
  }

  // NEW
  function LoadHotspot(&$source) {
    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Hotspot();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "hotspot";
    $dest->score_method = 'Mark per Option';
    
    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
    $dest->marks_correct = $marks_correct;
    $dest->marks_incorrect = $marks_incorrect;
    $dest->marks_partial = $marks_partial;

    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    if (!empty($response->material->media)) {
      $dest->media = $response->material->media;
      $dest->media_width = $response->material->media_width;
      $dest->media_height = $response->material->media_height;
    }

    // need to find all coordinates that are in the results
    // get back the single positive response for the question
    $dest->hotspots[0] = 1;
    $conds = $this->GetRespConditions($source, 1);
    foreach ($conds as & $conds) {
      foreach ($conds->conditions as $condition) {
        if ($condition->not) continue;
        $coords = str_replace(" ", ",", $condition->value);
        $type = strtolower($condition->areatype);
        if ($type == "rectangle" || $type == "ellipse") {
          $hs = new STQ_Hotspot_Spot();
          $hs->type = $type;
          $hs->coords = explode(",", $coords);
          $dest->hotspots[] = $hs;
        }
      }
    }
    unset($dest->hotspots[0]);

    // load in any raw option data when a touchstone export
    if (array_key_exists("RAW_HOTSPOT", $source->params)) {
      $dest->hotspots = array();
      $dest->raw_option = $source->params['RAW_HOTSPOT'];
    }

    // get feedback
    $fb = $this->GetAllFeedbacks($source);
    $dest->feedback = $this->GetFeedbackFromArray($source, $fb);

    return $dest;
  }

  // DONE
  function LoadInfo(&$source) {
    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Info();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "info";

    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    return $dest;
  }

  function LoadLabelling(&$source) {
    global $string;

    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Labelling();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "labelling";
    $dest->score_method = 'Mark per Option';
    
    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
    $dest->marks_correct = $marks_correct;
    $dest->marks_incorrect = $marks_incorrect;
    $dest->marks_partial = $marks_partial;
    
    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    $labels = array();
    $max_width = 0;
    $max_height = 0;

    // get main image for question
    if (!empty($response->material->media)) {
      $dest->media = $response->material->media;
      $dest->media_width = $response->material->media_width;
      $dest->media_height = $response->material->media_height;
    }

    // build a list of all the labels and their coordinates
    foreach ($source->responses as $response) {
      foreach ($response->labels as $label) {
        $mylabel = new stdClass();
        $mylabel->id = $label->id;
        if (!empty($label->material->media)) {
          $mylabel->media = $label->material->media;
          $mylabel->media_width = $label->material->media_width;
          $mylabel->media_height = $label->material->media_height;

          if ($max_height < $mylabel->media_height) $max_height = $mylabel->media_height;

          if ($max_width < $mylabel->media_width) $max_width = $mylabel->media_width;
        }

        $mylabel->text = $label->material->GetText();

        foreach ($source->respconditions as $respcondition) {
          foreach ($respcondition->conditions as $condition) {
            if ($condition->respident != $mylabel->id) continue;
            if ($condition->not) continue;
            $mylabel->coords = str_replace(" ", ",", $condition->value);
          }
        }

        if (trim($mylabel->text) == "") {
          $mylabel->text = $mylabel->media;
        }
        $labels[] = $mylabel;
      }
    }

    // work out box size
    if ($max_height == 0) $max_height = 35;
    if ($max_width == 0) $max_width = 90;
    $top_offset = $max_height / 2;
    $left_offset = $max_width / 2;

    $dest->width = round($max_width * $response->material->x_scale);
    $dest->height = round($max_height * $response->material->y_scale);

    $label_match = 0;
    // remap coordinates to top left
    foreach ($labels as & $label) {
      if (empty($label->coords)) {
        $label->left = -1;
        $label->top = -1;
      } else {
        $coords = explode(",", $label->coords);
        $left = (($coords[0] + $coords[2]) / 2 - $left_offset) * $response->material->x_scale;
        $top = (($coords[1] + $coords[3]) / 2 - $top_offset) * $response->material->y_scale;
        $label->left = round($left);
        $label->top = round($top);
        $label_match++;
      }

      $destlabel = new STQ_Labelling_Label();
      $destlabel->tag = $label->text;
      $destlabel->left = $label->left;
      $destlabel->top = $label->top;

      $dest->labels[] = $destlabel;
    }

    // load in any raw option data when a touchstone export
    if (array_key_exists("RAW_LABELLING", $source->params)) {
      unset($dest->labels);
      $dest->raw_option = $source->params['RAW_LABELLING'];
    }

    // get feedback
    $fb = $this->GetAllFeedbacks($source);
    $dest->feedback = $this->GetFeedbackFromArray($source, $fb);

    if ($label_match == 0) {
      $this->AddError($string['nomatchinglabel'], $source->load_id);
      $dest->type = "error";
    }

    return $dest;
  }

  function LoadLikert(&$source) {
    global $string;

    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Likert();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "likert";

    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    $responses_html = $this->GetResponseLabelList($source, false);
    $responses_clean = $this->GetResponseLabelList($source);

    if (count($source->itemfeedback) > 0) $this->AddWarning($string['nolikertfeedback'], $source->load_id);

    // do we have a N/A value?
    foreach ($responses_clean as $key => $isna) {
      if (ItemInArray($this->abstainvalues, $isna)) {
        $dest->hasna = 1;
        unset($responses_html[$key]);
      }
    }

    // build up scale
    foreach ($responses_html as $response) {
      $dest->scale[] = $response;
    }

    $dest->marks = '';

    return $dest;
  }

  // DONE
  function LoadMatrix(&$source) {

    $dest = new ST_Question_Matrix();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "matrix";
    if ($source->responses[1]->shuffle == 1) {
      $dest->q_option_order = 'random';
    }

    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);
    
    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
    $dest->marks_correct = $marks_correct;
    $dest->marks_incorrect = $marks_incorrect;
    $dest->marks_partial = $marks_partial;
    
    $optionmapping = array();

    // build option list

    $oid = 1;
    foreach ($source->responses as $response) {
      foreach ($response->labels as $id => $label) {
        $dest->options[$oid] = $label->material->GetText();
        $optionmapping[$id] = $oid;
        $oid++;
      }
      break;
    }

    // build stem list
    $scnid = 1;
    foreach ($source->responses as $response) {
      $qr = new STQ_Matrix_Scenario();
      $qr->scenario = $response->material->GetText();

      // work out correct answers
      foreach ($source->respconditions as $condition) {
        if (count($condition->conditions) < 1) continue;
        foreach ($condition->conditions as $cond) {
          if ($cond->respident != $response->id) continue;
          if ($cond->not) continue;

          $qr->answer = $optionmapping[$cond->value];
        }
      }

      $dest->scenarios[$scnid] = $qr;
      $scnid++;
    }

    // get feedback
    $fb = $this->GetAllFeedbacks($source);
    $dest->feedback = $this->GetFeedbackFromArray($source, $fb);
    $dest->marks = count($dest->scenarios);
    return $dest;
  }

  function LoadMCQ(&$source) {
    global $string;

    $dest = new ST_Question_Mcq();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->presentation = 'vertical';
    $dest->type = 'mcq';
    reset($source->responses);
    $key=key($source->responses);
    if ($source->responses[$key]->shuffle == 1) {
      $dest->q_option_order = 'random';
    }

    // should only be 1 response, so get it
    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
 
    $choiceno = 1;
    foreach ($response->labels as $label) {
      $choice = new STQ_Mcq_Option();
      $choice->stem = $label->material->GetText();
      $choice->base_id = $label->id;

      if (!empty($label->material->media)) {
        $choice->media = $label->material->media;
        $choice->media_width = $label->material->media_width;
        $choice->media_height = $label->material->media_height;
      }
      
      $choice->marks_correct = $marks_correct;
      $choice->marks_incorrect = $marks_incorrect;

      $dest->options[$choiceno] = $choice;
      $choiceno++;
    }

    // count up response conditions
    list($positive, $zero, $negative) = $this->GetRespConditionMarkCounts($source);
    
    if ($positive == 0) {
      $this->AddWarning($string['nocorrect'], $source->load_id);
    } else if ($positive > 1) {
      $this->AddWarning($string['multipleconds'], $source->load_id);
    }

    // get back the single positive response for the question
    $conds = $this->GetRespConditions($source, 1);

    // get first and only response condition
    $conds = reset($conds);

    // find correct answer (first and only value (hopefully)
    $corid = '';
    if (count($conds->conditions) > 0) $corid = reset($conds->conditions)->value;

    foreach ($dest->options as $id => $option) {
      if ($option->base_id == $corid) {
        $dest->correct = $id;
        break;
      }
    }

    // SW amendment 16/11/2010
    foreach ($dest->options as & $option) {
      $correctfb = $this->GetFeedbacks($source, 1, $option->base_id, 1);
      $incorrectfb = explode('<br />', $this->GetFeedbackFromArray($source, $correctfb));

      // get list of feedbacks common to both outcomes and add to general feedback array
      // remove common ones from the list
      RemoveCommonInArray($correctfb, $incorrectfb, $generalfb);

      $option->fb_correct = $this->GetFeedbackFromArray($source, $correctfb);
      $option->fb_incorrect = $this->GetFeedbackFromArray($source, $incorrectfb);
    }
    // SW amendment

    //$dest->feedback = $this->GetFeedbackFromArray($source,$generalfb);

    // get list of feedbacks for when correct
    $correctfb = $this->GetFeedbacks($source, 1, $corid, 1);

    // get list of feedbacks for when incorrect
    $incorrectfb = $this->GetFeedbacks($source, 1, $corid, 0);

    // get list of feedbacks common to both outcomes and add to general feedback array
    // remove common ones from the list
    RemoveCommonInArray($correctfb,$incorrectfb,$generalfb);

    if($this->GetFeedbackFromArray($source, $correctfb) == $this->GetFeedbackFromArray($source, $incorrectfb) )
    {
      $generalfb=$correctfb;
      unset($correctfb);
      $correctfb=array();

      unset($incorrectfb);
      $incorrectfb=array();
    }

    $dest->feedback = $this->GetFeedbackFromArray($source, $generalfb);

    $dest->fb_correct = $this->GetFeedbackFromArray($source, $correctfb);
    $dest->fb_incorrect = $this->GetFeedbackFromArray($source, $incorrectfb);

    // load presentation type from comments field if it was specified
    if (array_key_exists('DISPLAY', $source->params)) $dest->presentation = $source->params['DISPLAY'];
    
    return $dest;
  }

  function LoadTrueFalse(&$source) {
    global $string;

    $dest = new ST_Question_Mcq();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->presentation = 'vertical';
    $dest->type = 'true_false';
    reset($source->responses);
    $key=key($source->responses);
    if ($source->responses[$key]->shuffle == 1) {
      $dest->q_option_order = 'random';
    }

    // should only be 1 response, so get it
    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);

    $conds = $this->GetRespConditions($source, 1);
    foreach ($conds as $condition) {
      if (count($condition->conditions) == 0) {
        $this->AddWarning($string['posnocond'], $source->load_id);
      } else {
        if (count($condition->conditions) > 1) $this->AddWarning($string['multiplepos'], $source->load_id);

        $correctvalue = $condition->conditions[0]->value;
        $id = $condition->conditions[0]->respident;
        $answer= $condition->conditions[0]->value;
      }
    }
    $dest->answer=strtolower($answer);

    $choiceno = 1;
    foreach ($response->labels as $label) {
      $choice = new STQ_Mcq_Option();
      $choice->stem = $label->material->GetText();
      $choice->base_id = $label->id;

      if (!empty($label->material->media)) {
        $choice->media = $label->material->media;
        $choice->media_width = $label->material->media_width;
        $choice->media_height = $label->material->media_height;
      }

      $choice->marks_correct = $marks_correct;
      $choice->marks_incorrect = $marks_incorrect;

      $dest->options[$choiceno] = $choice;
      $choiceno++;
    }

    // count up response conditions
    list($positive, $zero, $negative) = $this->GetRespConditionMarkCounts($source);

    if ($positive == 0) {
      $this->AddWarning($string['nocorrect'], $source->load_id);
    } else if ($positive > 1) {
      $this->AddWarning($string['multipleconds'], $source->load_id);
    }

    // get back the single positive response for the question
    $conds = $this->GetRespConditions($source, 1);

    // get first and only response condition
    $conds = reset($conds);

    // find correct answer (first and only value (hopefully)
    $corid = '';
    if (count($conds->conditions) > 0) $corid = reset($conds->conditions)->value;

    foreach ($dest->options as $id => $option) {
      if ($option->base_id == $corid) {
        $dest->correct = $id;
        break;
      }
    }

    // SW amendment 16/11/2010
    foreach ($dest->options as & $option) {
      $correctfb = $this->GetFeedbacks($source, 1, $option->base_id, 1);
      $incorrectfb = explode('<br />', $this->GetFeedbackFromArray($source, $correctfb));

      // get list of feedbacks common to both outcomes and add to general feedback array
      // remove common ones from the list
      RemoveCommonInArray($correctfb, $incorrectfb, $generalfb);

      $option->fb_correct = $this->GetFeedbackFromArray($source, $correctfb);
      $option->fb_incorrect = $this->GetFeedbackFromArray($source, $incorrectfb);
    }
    // SW amendment

    //$dest->feedback = $this->GetFeedbackFromArray($source,$generalfb);

    // get list of feedbacks for when correct
    $correctfb = $this->GetFeedbacks($source, 1, $corid, 1);

    // get list of feedbacks for when incorrect
    $incorrectfb = $this->GetFeedbacks($source, 1, $corid, 0);

    // get list of feedbacks common to both outcomes and add to general feedback array
    // remove common ones from the list
    //RemoveCommonInArray($correctfb,$incorrectfb,$generalfb);

    $dest->fb_correct = $this->GetFeedbackFromArray($source, $correctfb);
    $dest->fb_incorrect = $this->GetFeedbackFromArray($source, $incorrectfb);

    $dest->options2=$dest->options;


    unset($dest->options);
    unset($choices);
    foreach($dest->options2 as $opts) {
      if($opts->base_id==$dest->answer) {
        $choice = new STQ_Mcq_Option();
        $choice=$opts;
        $as=strtolower(substr($opts->stem,0,1));
        $dest->options[$as]=$choice;
        $dest->correct=$as;
      }
    }

    // load presentation type from comments field if it was specified
    if (array_key_exists('DISPLAY', $source->params)) $dest->presentation = $source->params['DISPLAY'];

    return $dest;
  }

  function LoadMRQ(&$source) {
    global $string;

    // count up response conditions    
    list($positive, $zero, $negative) = $this->GetRespConditionMarkCounts($source);
    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
 
    $dest = new ST_Question_Mrq();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->presentation = "vertical";
    $dest->type = "mrq";
    if ($source->responses[1]->shuffle == 1) {
      $dest->q_option_order = 'random';
    }

    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    // load option list into dest
    // should only be 1
    $choiceno = 1;
    foreach ($source->responses as $response) {
      if (!$response->ismulti) {
        $this->AddWarning($string['mrqnoismulti'], $source->load_id);
      }

      foreach ($response->labels as $label) {
        $choice = new STQ_Mrq_Option();
        $choice->stem = $label->material->GetText();
        $choice->base_id = $label->id;
        $choice->marks_correct = $marks_correct;
        $choice->marks_incorrect = $marks_incorrect;
        if (!empty($label->material->media)) {
          $choice->media = $label->material->media;
          $choice->media_width = $label->material->media_width;
          $choice->media_height = $label->material->media_height;
        }

        $dest->options[$choiceno] = $choice;
        $choiceno++;
      }
    }

    // work out marking type, and which items are correct
    // allnegative / selectedpositive / allitemscorrect
    // single positive answer		
    if ($positive == 1) {
      $this->MRQ_GetCorrect_allitemscorrect($dest, $source);
    } else { // multiple positive answers, no negatives, so assume 1 mark per correct option
      $this->MRQ_GetCorrect_selectedpositive($dest, $source);
    }

    //////////////////////////
    // work out all feedbacks
    //////////////////////////

    $generalfb = array();

    // for each answer
    foreach ($dest->options as & $option) {
      // get list of feedbacks for when correct
      $correctfb = $this->GetFeedbacks($source, 1, $option->base_id, $option->is_correct);

      // get list of feedbacks for when incorrect
      $incorrectfb = $this->GetFeedbacks($source, 1, $option->base_id, !$option->is_correct);

      // get list of feedbacks common to both outcomes and add to general feedback array
      // remove common ones from the list
      RemoveCommonInArray($correctfb, $incorrectfb, $generalfb);

      $option->fb_correct = $this->GetFeedbackFromArray($source, $correctfb);
      $option->fb_incorrect = $this->GetFeedbackFromArray($source, $incorrectfb);

    }
    // store list of general feedbacks into general feedback

    $dest->feedback = $this->GetFeedbackFromArray($source, $generalfb);

    return $dest;
  }

  function MRQ_GetCorrect_allnegative(&$dest, &$source) {
    global $string;

    echo "{$string['someneg']}<br>";
    $dest->score_method = 'AllNegative';

    $conds = $this->GetRespConditions($source, 1);

    foreach ($conds as & $cond) {
      if (count($cond->conditions) > 1) {
        $this->AddWarning($string['multiposmultiopt'], $source->load_id);
      }

      if (count($cond->conditions) == 0) {
        $this->AddWarning($string['posnocond'], $source->load_id);
        continue;
      }

      $value = $cond->conditions[0]->value;

      // skip not values as they arent correct
      if ($cond->conditions[0]->not == 1) continue;

      // find the option and mark it as correct
      foreach ($dest->options as & $option) {
        if ($option->base_id == $value) {
          $option->is_correct = true;
        }
      }
    }

    $dest->marks = count($dest->options);
  }

  // DONE
  function MRQ_GetCorrect_selectedpositive(&$dest, &$source) {
    global $string;

    echo  "{$string['noneg']}<br>";
    $dest->score_method = 'SelectedPositive';

    $conds = $this->GetRespConditions($source, 1);

    $dest->marks = 0;

    foreach ($conds as & $cond) {
      if (count($cond->conditions) > 1) {
        $this->AddWarning($string['multiposmultiopt'], $source->load_id);
      }

      if (count($cond->conditions) == 0) {
        $this->AddWarning($string['posnocond'], $source->load_id);
        continue;
      }

      $value = $cond->conditions[0]->value;

      // find the option and mark it as correct
      foreach ($dest->options as & $option) {
        if ($option->base_id == $value) {
          $option->is_correct = true;
          $dest->marks++;
        }
      }
    }

  }

  // DONE
  function MRQ_GetCorrect_allitemscorrect(&$dest, &$source) {
    $dest->score_method = 'AllItemsCorrect';
    $conds = $this->GetRespConditions($source, 1);
    $conds = $conds[0];
    // can work out the correct answers from $resps;

    foreach ($conds->conditions as $cond) {
      // if condition not is 1, this item should be unchecked to be correct
      if ($cond->not == 0) { // item should be checked to be correct
        $value = $cond->value;

        // find the option and mark it as correct
        foreach ($dest->options as & $option) {
          if ($option->base_id == $value) {
            $option->is_correct = true;
          }
        }
      }
    }
  }

  // DONE
  function LoadRank(&$source) {
    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Rank();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "rank";

    if ($source->responses[1]->shuffle == 1) {
      $dest->q_option_order = 'random';
    }

    $response = reset($source->responses);
    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
    $dest->marks_correct = $marks_correct;
    $dest->marks_incorrect = $marks_incorrect;
    $dest->marks_partial = $marks_partial;
    
    // build option list
    $optionmapping = array();

    $oid = 1;
    foreach ($source->responses as $response) {
      foreach ($response->labels as $id => $label) {
        $optionmapping[$id] = $label->material->GetText();
        $oid++;
      }
      break;
    }

    $optid = 1;
    foreach ($source->responses as $response) {
      $rankopt = new STQ_Rank_Options();
      $rankopt->stem = $response->material->GetText();

      $value = '';
      // work out correct answers
      foreach ($source->respconditions as $condition) {
        if (count($condition->conditions) < 1) continue;
        foreach ($condition->conditions as $cond) {
          if ($cond->respident != $response->id) continue;
          if ($cond->not) continue;

          $rankopt->order = RemoveStNdRd($optionmapping[$cond->value]);
          ;
        }
      }

      $dest->options[$optid++] = $rankopt;
    }

    // dig out feedbacks
    foreach ($source->respconditions as $condition) {
      if (count($condition->conditions) > 1) {
        if ($condition->conditions[0]->not) {
          $dest->fb_incorrect = $source->itemfeedback[$condition->feedback]->material->GetHTML();
        } else {
          $dest->fb_correct = $source->itemfeedback[$condition->feedback]->material->GetHTML();
        }
      }
    }

    // decide if 1 per correct or 1 for all
    list($positive, $zero, $negative) = $this->GetRespConditionMarkCounts($source);

    if ($positive == 1) {
      $dest->score_method = 'Mark per Option';
    } else {
      $dest->score_method = 'Mark per Question';
    }

    return $dest;
  }

  // DONE	
  function LoadTextbox(&$source) {
    global $string;

    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question_Textbox();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "textbox";

    // there should only be a single response fib available
    $response = $this->GetResponses($source, '', 'fib');
    $response = reset($response);

    $dest->rows = $response->rows;
    $dest->columns = $response->cols;

    $this->GenerateQuestionInfo($dest, $source->material, $source->title, $response->material);

    // get any positive marks that are terms to match
    $conditions = $this->GetRespConditions($source, 1);

    if (count($conditions) > 0) $this->AddWarning($string['importingtext'], $source->load_id);

    $dest->marks = 0;
    foreach ($conditions as $condition) {
      foreach ($condition->conditions as $value) {
        $dest->terms[] = strip_tags($value->value);
      }
      $dest->marks += $condition->mark;
    }

    list($marks_incorrect,$marks_partial, $marks_correct) = $this->getMarksFromRespConditions($source);
    $dest->marks_correct = $marks_correct;
    $dest->marks_incorrect = $marks_incorrect;
    
    // sort out feedback
    $fb = $this->GetAllFeedbacks($source);
    $dest->feedback = $this->GetFeedbackFromArray($source, $fb);

    // load taxinomy and keywords
    if (array_key_exists('EDITOR', $source->params)) {
      $dest->editor = $source->params['EDITOR'];
    }

    return $dest;
  }

  function LoadUnknown(&$source) {
    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "unknown";

    $this->GenerateQuestionInfo($dest, $source->material, $source->title);

    return $dest;
  }

  function LoadError(&$source) {
    // easy to do, no feedback in touchstone so goes the way of the dinosar
    $dest = new ST_Question();

    $dest->load_id = $source->load_id;
    $dest->status = $source->qmd_status;
    $dest->type = "error";

    $this->GenerateQuestionInfo($dest, $source->material, $source->title);

    return $dest;
  }

  ///////////////////////////////////////////////
  // GENERAL FUNCTIONS SPECIFIC TO QTI LOADING //
  ///////////////////////////////////////////////

  // gets a list of the possible responses in the question and returns it as an array
  function GetResponseLabelList(&$question, $clean = true,&$lablk=array(),&$lablkd=array()) {
    $resplist = array();
$numbb=1;
    $loop=0;
    foreach ($question->responses as $rid => $response) {
      foreach ($response->labels as $label) {
        if ($clean) {
          $value = strtolower($label->material->GetText());
        } else {
          $value = $label->material->GetHTML();
        }
        $labl=$label->material->GetLabel();
        $labl2=$label->id;
        $lablk[$rid][$labl2]=$labl;
        if(!isset($lablkd[$labl])) {
          $lablkd[$labl]=$numbb;
        }
        if($question->wct_questiontype =="WCT_Matching") {
          $label->id="MATCH" . $numbb++;
        }
        if($loop==0) {
          $resplist[$label->id] = $value;
        }

      }
  
      $loop=1;
    }

    return $resplist;
  }

  // gets a list of the possible responses in the question and returns it as an array based on response id
  function GetResponseLabelListByID(&$question, $rid, $clean = true) {
    $resplist = array();

    foreach ($question->responses as $response) {
      if ($response->id != $rid) return;

      foreach ($response->labels as $label) {
        if ($clean) {
          $value = strtolower($label->material->GetText());
        } else {
          $value = $label->material->GetHTML();
        }
        $resplist[$label->id] = $value;
      }
      break;
    }

    return $resplist;
  }

  // get counts of each type of response ident
  // return array($positive,$zero,$negative)
  function GetRespConditionMarkCounts(&$data) {
    $positive = 0;
    $zero = 0;
    $negative = 0;

    foreach ($data->respconditions as & $respconditions) {
      if ($respconditions->mark == 0) $zero++;
      else if ($respconditions->mark > 0) $positive++;
      else $negative++;
    }

    return array($positive, $zero, $negative);
  }
  
  // returns the Max and Min mark for a question
  //
  //  as an array (min,max)
  //
  function getMarksFromRespConditions(&$data) {
    $max = 0;
    $part = 0;
    $min = 0;

    foreach ($data->respconditions as & $respconditions) {      
      if(isset($respconditions->conditions[0]) and ($respconditions->conditions[0]->type == 'vargte' or $respconditions->conditions[0]->type == 'varlte') ) {
        if ($respconditions->mark > $part) $part = $respconditions->mark;
      } else {    
        if ($respconditions->mark > $max) $max = $respconditions->mark;
        else if ($respconditions->mark < $min) $min = $respconditions->mark;
      }
    }

    // Fix for webCT output where it gives output as a percentage and as this is upto 100 and and doesnt include the
    // question mark and rogo doesnt support above 20 fix it to 1 to allow user editing

    $max=round($max);
    $min=round($min);
    $part=round($part);

    if($max>20) $max=1;
    if($min>20) $min=1;
    if($part>20) $part=1;
    return array($min,$part,$max);  
  }

  // return array of conditions based on mark
  // mark = 0 returns all with mark as 0
  // mark = 1 returns all with positive marks
  // mark = -1 return all with negatvie marks
  function GetRespConditions(&$data, $mark = '', $respident = '') {
    //echo "<strong>Processing respconditions for $mark and $respident</strong><br>";
    $resps = array();
    foreach ($data->respconditions as & $condition) {
      //print_p($condition);
      if ($mark == 0 && $condition->mark != 0) {
        //echo "Skipping Respcondition - MARK NOT 0 : " . $condition->__toString() . "<br>";
        continue;
      }

      if ($mark == 1 && $condition->mark < 1) {
        //echo "Skipping Respcondition - MARK < 1 : " . $condition->__toString() . "<br>";
        continue;
      }

      if ($mark == - 1 && $condition->mark > -1) {
        //echo "Skipping Respcondition - MARK > -1 : " . $condition->__toString() . "<br>";
        continue;
      }
      // check respident for - to get responses only with no sub ids
      if ($respident == '-') {
        $valid = true;
        foreach ($condition->conditions as $cond) if ($cond->respident != '') $valid = false;

        if (!$valid) continue;
      } else if ($respident) {
        $valid = false;
        foreach ($condition->conditions as $cond) if ($cond->respident == $respident) $valid = true;

        if (!$valid) {
          continue;
        }
      }

      $resps[] = $condition;
    }
    return $resps;
  }

  // return array of responses based on type
  // type returns conditions with specific type (lid / str / num / xy)
  // render returns conditions with sepecific render type (choice / hotspot / slider / fib )
  function GetResponses(&$data, $type = '', $render = '') {
    $resps = array();
    foreach ($data->responses as & $response) {
      if ($type && $response->type != $type) continue;
      if ($render && $response->render != $render) continue;

      $resps[] = $response;
    }
    return $resps;
  }

  // return array of feedbacks depending on conditions
  // respident = id of the input
  // value = value to calculate for
  // match - if 0, then will only match items with <not>
  function GetFeedbacks(&$source, $respident, $value = '', $match = 1) {
    //echo "Getting feedback list for $respident - $value - $match<br>";

    $feedbacks = array();

    foreach ($source->respconditions as & $respcondition) {
      if (count($respcondition->conditions) > 0 && $respcondition->conditions[0]->respident != $respident) continue;

      //print_p($respcondition);
      // no value provided so just output feedback
      if ($value == '') {
        if ($respcondition->feedback) $feedbacks[$respcondition->feedback] = $respcondition->feedback;
        continue;
      }

      $is_match = true;

      // if its an empty condition, output the feedback
      if (count($respcondition->conditions) == 0) {
        //echo "Count is 0<br>";
        if ($respcondition->feedback) $feedbacks[$respcondition->feedback] = $respcondition->feedback;
      }

      foreach ($respcondition->conditions as $cond) {
        if ($cond->value != $value) $is_match = false;

        if ($cond->not == $match) $is_match = false;
      }

      // do we have a matching condition?
      if ($is_match) {
        if ($respcondition->feedback) $feedbacks[$respcondition->feedback] = $respcondition->feedback;

        if ($respcondition->continue == 0) break;
      }
    }

    return $feedbacks;
  }

  // return array of all used feedbacks
  function GetAllFeedbacks(&$source) {
    $feedbacks = array();

    foreach ($source->itemfeedback as $key => & $feedback) {
      $feedbacks[$key] = $key;
    }

    return $feedbacks;
  }

  // returns html feedback based on an array of feedback ids passed in
  function GetFeedbackFromArray(&$source, &$feedbacks) {
    $output = array();
    foreach ($feedbacks as $feedback) {
      if (array_key_exists($feedback, $source->itemfeedback)) {
        $fbtext = trim($source->itemfeedback[$feedback]->material->GetHTML());
        if ($fbtext) $output[] = $fbtext;
      }
    }

    return implode("<br />", $output);
  }
}
