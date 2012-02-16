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

require_once '../include/media.inc';
require_once '../config/config.inc.php';

// main question object
class ST_QTI12_Question // <item
  {
  var $raw_xml;
  var $title; // <item title=
  var $load_id; // <item ident=

  // itemmetadata stuff
  var $qmd_itemtype; // <itemmetadata><qmd_itemtype>
  var $qmd_status = 'Normal'; // <itemmetadata><qmd_status>
  var $qmd_toolvendor; // <itemmetadata><qmd_toolvendor>

  // counted stuff
  var $counts = array(
    'lid' => 0,
    'str' => 0,
    'num' => 0,
    'xy' => 0,
    'grp' => 0,

    'choice' => 0,
    'hotspot' => 0,
    'slider' => 0,
    'fib' => 0,

    'response' => 0,
    'material' => 0
  );

  // calculate cardinaltiy, if all of lid are ismulti then Multi, if some are ismulti then varies (for extmatch i think)
  var $cardinality = 'Single';

  // label sets - do all sets of labels for each of the responses have the same values in or not??
  // this needs to be 1 for most question type, the only question type that allows multiple sets of answers is blank
  var $labelsets = 1;

  // <presentation>
  // do we have flow data?
  var $hasflow = 0;

  // general question material
  // <material>
  // array of QT_QTI12_Material, as some questions have multiple material sections
  // $questionmats - OBSOLETE!!! REPLACED BY $material
  var $material;

  // <response_lid>
  // array of ST_QTI12_Response
  // key on <response_lid ident=
  var $responses = array();

  var $presentation;
  // </presentation>

  // <resprocessing>

  // <respcondition>
  // array of ST_QTI12_RespCondition
  // key by numbe
  var $respconditions = array();

  // <resprocessing>

  // <itemfeedback>
  // array of ST_QTI12_Itemfeedback
  // key by <itemfeedback ident=
  var $itemfeedback = array();

  var $comments = array();
  var $params = array();

  function __construct($xml) {
    $this->params['KEYWORD'] = array();
    $this->params['VARIABLE'] = array();
    $this->raw_xml = $xml;

    $this->title = (string) $xml->attributes()->title;
    $this->load_id = (string) $xml->attributes()->ident;

    //echo $this->load_id;
    //exit;

    $this->material = new ST_QTI12_Material();

    // load item meta data
    if ($xml->itemmetadata) {
      if ($xml->itemmetadata->qmd_itemtype) $this->qmd_itemtype = (string) $xml->itemmetadata->qmd_itemtype;

      if ($xml->itemmetadata->qmd_status) $this->qmd_status = (string) $xml->itemmetadata->qmd_status;

      if ($xml->itemmetadata->qmd_toolvendor) $this->qmd_toolvendor = (string) $xml->itemmetadata->qmd_toolvendor;
    }

    $this->LoadComments($xml);
    $this->LoadPresentation($xml->presentation);

    //}
    // process respcond
    if ($xml->resprocessing->respcondition) {
      foreach ($xml->resprocessing->respcondition as $respcondition) {
        $this->respconditions[] = new ST_QTI12_RespCondition($respcondition);
      }
    }

    // load all the feedback options
    foreach ($xml->itemfeedback as $itemfeedback) {
      $fb = new ST_QTI12_Itemfeedback($itemfeedback);
      $this->itemfeedback[$fb->id] = $fb;
    }

    // QMP FIX:
    // need to validate all id in ST_QTI12_CondVar against the correct ST_QTI12_Response object,
    // if the id isnt found attempt to look it up from the value

    foreach ($this->respconditions as & $respcondition) {
      foreach ($respcondition->conditions as $condition) {
        //echo $condition->__toString() . "<br>";
        $value = $condition->value;
        $respid = $condition->respident;

        if (!array_key_exists($respid, $this->responses)) {
          //echo "ERROR : Response ident from respcondition doesnt exist - $respid<br>";
          continue;
        }
        $response = $this->responses[$respid];

        if (!array_key_exists($value, $response->labels)) {
          //echo "ERROR : Value in respcondition doesnt match any of the labels, attempting to do a lookup<br>";	
          foreach ($response->labels as $label) {
            if (strtolower($label->material->GetText()) == strtolower($value)) {
              $condition->value = $label->id;
            }
          }
        }
      }
    }
  }

  function LoadComments($xml) {
    foreach ($xml->children() as $child) {
      $name = $child->getName();
      if ($name == "qticomment") {
        $com = (string) $child;
        $this->comments[] = $com;
        if (strpos($com, ":") > 0) {
          $bits = explode(":", $com, 2);
          $param = strtoupper($bits[0]);

          // parameter already exists
          if (array_key_exists($param, $this->params)) {
            // if its not an array, make it one
            if (!is_array($this->params[$param])) {
              $old = $this->params[$param];
              $this->params[$param] = array();
              $this->params[$param][] = $old;
            }
            $this->params[$param][] = $bits[1];
          } else {
            $this->params[$param] = $bits[1];
          }
        }
      } else {
        $this->LoadComments($child);
      }
    }
  }

  // <presentation> only 1 per <item>
  function LoadPresentation($xml) {
    if ($xml->flow) {
      $this->hasflow = 1;
      $this->LoadPresentation($xml->flow);
    }

    $this->presentation = $xml;
    $elementno = 1;

    foreach ($xml->children() as $child) {
      $name = $child->getName();

      // if there is question material, load it
      if ($name == 'material') $this->material->add($child, $elementno);

      // load all response_lid elements, followed by all other types

      //foreach ($xml->response_lid as $response)
      //{
      if ($name == 'response_lid') {
        $resp = new ST_QTI12_Response('lid', $child);
        $resp->order = $elementno;
        $this->responses[$resp->id] = $resp;
      }
      //}	

      //foreach ($xml->response_str as $response)
      //{
      if ($name == 'response_str') {
        $resp = new ST_QTI12_Response('str', $child);
        $resp->order = $elementno;
        $this->responses[$resp->id] = $resp;
      }
      //}

      //foreach ($xml->response_xy as $response)
      //{
      if ($name == 'response_xy') {
        $resp = new ST_QTI12_Response('xy', $child);
        $resp->order = $elementno;
        $this->responses[$resp->id] = $resp;
      }
      //}

      //foreach ($xml->response_num as $response)
      //{
      if ($name == 'response_num') {
        $resp = new ST_QTI12_Response('num', $child);
        $resp->order = $elementno;
        $this->responses[$resp->id] = $resp;
      }

      if ($name == 'response_grp') {
        $resp = new ST_QTI12_Response('grp', $child);
        $resp->order = $elementno;
        $this->responses[$resp->id] = $resp;
      }
      //}

      $elementno++;
    }
  }

  function CountStuff() {
    $count_response = count($this->responses);

    foreach ($this->responses as $response) {
      $this->counts[$response->type]++;
      $this->counts[$response->render]++;
      $this->counts['response']++;
    }

    $this->counts['material'] = $this->material->count;

    // work out cardinality
    $count_multi = 0;
    foreach ($this->responses as $res) {
      if ($res->ismulti) $count_multi++;
    }

    if ($count_multi == $this->counts['response']) $this->cardinality = "Multi";
    else if ($count_multi > 0) $this->cardinality = "Varies";

    // are all the sets of labels the same for stuff like matrixes etc
    $this->CompareLabels();
  }

  // verify all sets of lables for each lid are same or not
  function CompareLabels() {
    if ($this->counts['response'] > 1) {
      $allsame = 1;
      $first = true;

      // for all responses compare against the first one
      foreach ($this->responses as $res) {
        // find first set of responses as a baseline, and dont compare as its gonna be the same
        if ($first) {
          $baselabels = $res->labels;
          $first = false;
          continue;
        }

        // get new set of labels
        $itemlabels = $res->labels;

        // check that the count is the same
        if (count($itemlabels) != count($baselabels)) {
          //echo "Count wrong<br>";
          //print_p($itemlabels);
          $allsame = 0;
          break;
        }

        // if count is same, then all items must match or not all same

        foreach ($itemlabels as $id => $itemlabel) {

          // array key missing means option aint there!
          if (!array_key_exists($id, $baselabels)) {
            $allsame = 0;
            break;
          }

          // compare the data to see if the same
          if ($baselabels[$id]->material->GetText() != $itemlabel->material->GetText()) {
            $allsame = 0;
            break;
          }
        }

        // break out if not all same
        if (!$allsame) break;
      }

      //echo "Settings Labels as all same = $allsame<br>";
      $this->labelsets = $allsame;
    }
  }
}

// object to store question parts
class ST_QTI12_Response // <response_
  {
  var $id; // <response_lid ident=

  // type of the response_ object
  var $type = 'lid';

  var $material; // <material>
  var $ismulti = 0; // <response_lid rcardinality= // "Multiple" for 1

  var $render = 'choice';
  var $flow = 0;

  // attributes for rebder_choice
  var $shuffle = 0; // <render_choice shuffle="No"
  var $minnumber = 0; // <render_choice minnumber=
  var $maxnumber = 0; // <render_choice maxnumber=

  // attributes for render_fib
  var $fibtype = '';
  var $prompt = '';
  var $rows = 0;
  var $cols = 0;

  // attributes for render_slider

  // attributes for render_hotspot

  // <render_choice><response_label
  // ST_QTI12_Label
  // key by <response_label ident=
  var $labels = array();

  function __construct($type, $xml) {

    $this->id = (string) $xml->attributes()->ident;
    $this->type = $type;
    // should we allow multiple answers, ie check instead of radio
    $this->ismulti = strtolower($xml->attributes()->rcardinality) == 'multiple' ? 1 : 0;

    $this->material = new ST_QTI12_Material();
    // get material if available
    if ($xml->material) $this->material->add($xml->material);

    // as far as i can tell only ever 1 choice for each response_lid
    if ($xml->render_choice) {
      $render = 'choice';
      $this->shuffle = strtolower($xml->render_choice->attributes()->shuffle) == 'no' ? 0 : 1;
      $this->minnumber = (string) $xml->render_choice->attributes()->minnumber;
      $this->maxnumber = (string) $xml->render_choice->attributes()->maxnumber;

      $this->LoadRender($xml->render_choice);
    } else if ($xml->render_hotspot) {
      $this->render = 'hotspot';
      $this->LoadRenderHotspot($xml->render_hotspot);
    } else if ($xml->render_slider) {
      $this->render = 'slider';

    } else if ($xml->render_extension) {
      $this->render = 'extension';

    } else if ($xml->render_fib) {
      $this->render = 'fib';

      $this->fibtype = (string) $xml->render_fib->attributes()->fibtype;
      $this->prompt = (string) $xml->render_fib->attributes()->prompt;
      $this->rows = (int) $xml->render_fib->attributes()->rows;
      $this->cols = (int) $xml->render_fib->attributes()->columns;

      $this->LoadRender($xml->render_fib);
    }
  }

  // load <render_ segment, only ever 1 per <response_
  function LoadRender($xml) // <render_choice etc
  {
    if ($xml->material) $this->material->add($xml->material);

    foreach ($xml->response_label as $response_label) {
      $label = new ST_QTI12_Label($response_label);
      $this->labels[$label->id] = $label;
    }

    if ($xml->flow_label) {
      $this->flow = 1;
      foreach ($xml->flow_label as $flow_label) $this->LoadRender($flow_label);
    }
  }

  function LoadRenderHotspot($xml) {
    if ($xml->material) $this->material->add($xml->material);

    foreach ($xml->response_label as $response_label) {
      $label = new ST_QTI12_Label($response_label);
      $this->labels[$label->id] = $label;
    }

    if ($xml->flow_label) {
      $this->flow = 1;
      foreach ($xml->flow_label as $flow_label) $this->LoadRender($flow_label);
    }

  }
  function __toString() {
    $labeltxt = array();
    foreach ($this->labels as $label) $labeltxt[] = $label->__toString();

    if (count($labeltxt) == 0) $labeltxt[] = "<font color='green'>NONE</font>";

    return "<i>ID</i>: ".$this->id.", <i>Type</i>: ".$this->type.", <i>Render</i>: ".$this->render.", <i>Values</i>: ".implode(" | ", $labeltxt);
  }
}

// object to store labels
class ST_QTI12_Label // <response_label
  {
  var $id; // <response_label ident=
  var $material; // <material>
  var $flow = 0;

  function __construct($xml) {
    $this->id = (string) $xml->attributes()->ident;

    $this->material = new ST_QTI12_Material();
    // get material if available
    if ($xml->material) $this->material->add($xml->material);

    if ($xml->flow_mat) {
      $this->flow = 1;
      $this->material->add($xml->flow_mat->material);
    }
  }
  function __toString() {
    return $this->id."=".$this->material->__toString();
  }
}

// store each response processing condition
class ST_QTI12_RespCondition // <respcondition>
  {
  var $title; // <respcondition title=
  var $action = ""; // <setvar action=
  var $mark = 0; // <setvar>
  var $feedback = ''; // <displayfeedback linkrefid=
  var $continue = 0; // should further resp conditions be processed if this one is matched?
  var $other = 0; // if final match, matching any other tags
  var $used = 0;
  var $type = 'and';
  // not is in the individual condition as can have an and with some nots in it for mrq type questions

  // <conditionvar>
  // ST_QTI12_CondVar
  // no key
  var $conditions = array();

  function __construct($xml) {
    $this->title = (string) $xml->attributes()->title;

    $this->continue = strtolower($xml->attributes()->continue) == 'yes' ? 1 : 0;
    if ($xml->setvar) {
      $this->action = (string) $xml->setvar->attributes()->action;
      $this->mark = (string) $xml->setvar;
      if (strtolower($this->action) == "subtract") {
        $this->action = "Add";
        $this->mark = -$this->mark;
      }
    }

    // add all OR

    if ($xml->conditionvar->or) {
      $this->type = 'or';
      $this->LoadConditionVar($xml->conditionvar->or);
    } else {
      $this->LoadConditionVar($xml->conditionvar);
    }

    // feedback?
    if ($xml->displayfeedback) {
      $this->feedback = (string) $xml->displayfeedback->attributes()->linkrefid;
    }

  }

  function LoadConditionVar($xml) {
    //echo "<h4>Loading ConditionalVar</h4>";
    //print_p($xml->conditionvar);

    if ($xml->other) {
      $this->type = 'other';
      return;
    }

    // add all conditions

    foreach ($xml->children() as $child) {
      //echo "Loading conditionvar : " . $child->getName() . "<br>";
      if ($child->getName() == "not") continue;
      $this->conditions[] = new ST_QTI12_CondVar($child);
    }

    // add all NOT stuff
    // add all var equals	
    foreach ($xml->not as $condition) {
      if ($condition->varequal) {
        $cv = new ST_QTI12_CondVar($condition->varequal);
        $cv->not = 1;
        $this->conditions[] = $cv;
      }
    }
  }

  function __toString() {
    $conditions = array();
    foreach ($this->conditions as $condition) $conditions[] = $condition->__toString();

    if (count($conditions) == 0) $conditions[] = "<font color='green'>NONE</font>";

    return "<i>Mark</i>: ".$this->mark.", <i>Conditions</i>: ".implode(" | ", $conditions);
  }
}

// variable for each response proc condition
class ST_QTI12_CondVar // <conditionvar>
  {
  var $not = 0; // <not>
  var $respident; // <varequal respident=
  var $value; // <varequal>	
  var $index; // <varequal>	
  var $type = 'varequal';
  var $areatype = '';

  // should be passed a varequal
  function __construct($xml) {
    $this->respident = (string) $xml->attributes()->respident;
    $this->index = (string) $xml->attributes()->index;
    $this->areatype = (string) $xml->attributes()->areatype;
    $this->value = (string) $xml;
    $this->type = $xml->getName();
  }
  function __toString() {
    if ($this->not) return $this->respident."!=".$this->value;

    return $this->respident."=".$this->value;
  }
}

// feedback storage
class ST_QTI12_Itemfeedback // <itemfeedback>
  {

  var $id; // <itemfeedback ident=
  var $material; // <material>

  function __construct($xml) {
    $this->id = (string) $xml->attributes()->ident;

    $this->material = new ST_QTI12_Material();
    // get material if available

    if ($xml->material) $this->material->add($xml->material);

    if ($xml->flow_mat && $xml->flow_mat->material) $this->material->add($xml->flow_mat->material);
  }
  function __toString() {
    return "<i>ID</i>: ".$this->id.", <i>Text</i>: ".$this->material->__toString();
  }
}

class ST_QTI12_Material_Inner {
  var $data = array(); // <material><mattext>
  var $image = '';
  var $label = '';

  function GetHTML() {
    $output = implode("", $this->data);
    return $output;
  }

  function GetText() {
    $text = implode("", $this->data)."\n";

    while (strpos($text, "  ") > 0) $text = str_replace("  ", " ", $text);

    return trim($text);
  }
}

class ST_QTI12_Material // <material>
  {
  var $count = 0;
  var $chunks = array(); // array of ST_QTI12_Material_Inner
  var $image = '';
  var $x_scale = 1;
  var $y_scale = 1;
  var $media_width = 0;
  var $media_height = 0;

  function add($xml = '', $order = '') {
    if ($xml) {
      $this->count++;
      $chunk = new ST_QTI12_Material_Inner();
      $chunk->label = (string) $xml->attributes()->label;

      foreach ($xml->children() as $child) {
        $name = $child->getName();
        if ($name == 'mattext') $chunk->data[] = MakeValidHTML($this->ParseImages((string) $child));

        if ($name == 'matemtext') $chunk->data[] = "<em>".MakeValidHTML($this->ParseImages((string) $child))."</em>";

        if ($name == 'matbreak') $chunk->data[] = "<br />";
      }

      if ($order) {
        $this->chunks[$order] = $chunk;
      } else {
        $this->chunks[] = $chunk;
      }
      // load any images here
      if ($xml->matimage) {
        $this->addImage((string) $xml->matimage->attributes()->uri, (string) $xml->matimage->attributes()->width, (string) $xml->matimage->attributes()->height);
      }
    }
  }

  function addImage($image, $width = '', $height = '') {
    global $import_directory;
    global $q_warnings;
    global $q_errors;
    global $cfg_web_root;

    if (stripos(" ".$image, "notes_icon.gif") > 0) {
      return;
    }

    echo "Adding image $image<br>";
    // download any http images etc here and put location in $imagefile as a LOCAL file
    $basename = basename($image);
    $imagefile = FindFile($import_directory, $basename);
    echo "Converted \"$image\" to base name \"$imagefile\"<br>";
    $imagefile = $import_directory.$imagefile;

    if (file_exists($imagefile)) {
      $identifier_size = GetImageSize($imagefile);
      $this->media_width = $identifier_size[0];
      $this->media_height = $identifier_size[1];

      // if size different, then resize the image
      if ($width > 0 && $height > 0 && ($width != $this->media_width || $height != $this->media_height)) {
        $image = new SimpleImage();
        $image->load($imagefile);
        $image->resize($width, $height);
        $image->save($imagefile);
        echo "Resized $imagefile to $width x $height<br>";
        $this->x_scale = $width / $this->media_width;
        $this->y_scale = $height / $this->media_height;
        $this->media_width = $width;
        $this->media_height = $height;
      }

      $basename = basename($imagefile);
      $uniqueFilename = unique_filename($basename);

      copy($imagefile, $cfg_web_root.'media/'.$uniqueFilename);
      echo "Copied $imagefile to ".$cfg_web_root."media/$uniqueFilename<br />";
      $this->media = $uniqueFilename;
    } else {
      $this->media = basename($imagefile);
      $q_warnings[] = "Missing image $image";
    }
  }

  function ParseImages($text) {
    global $import_directory;
    global $q_warnings;
    global $q_errors;
    if (stripos(" ".$text, "<img") > 0) {
      $output = "";
      while ($text) {
        if (stripos(" ".$text, "<img") > 0) {
          $pre = substr($text, 0, stripos($text, "<img"));
          $imgtag = substr($text, stripos($text, "<img"));
          $imgtag = substr($imgtag, 0, stripos($imgtag, ">") + 1);
          $rest = substr($text, stripos($text, "<img"));
          $rest = substr($rest, stripos($rest, ">") + 1);

          $output .= $pre;

          // we have a src tag?
          if (stripos($imgtag, "src") > 0) {
            $data = parseHtml($imgtag);
            $src = $data['IMG'][0]['src'];
            $basename = basename($src);
            $filename = FindFile($import_directory, $basename);

            if ($filename) {
              //echo "Found \"$basename\" as \"$filename\" in zip<br>";

              //$identifier_size = GetImageSize($imagefile);
              //$this->media_width = $identifier_size[0];
              //$this->media_height = $identifier_size[1];

              $basename = basename($filename);
              $uniqueFilename = unique_filename($basename);

              copy($import_directory."/".$filename, $cfg_web_root.'media/'.$uniqueFilename);
              //echo "Copied $import_directory/$filename to /var/www/media/$uniqueFilename<br />";
              //$this->media = $basename;	

              $data['IMG'][0]['src'] = "/media/".$basename;
              // recreate img tag
              $imgtag = "<img ";
              foreach ($src = $data['IMG'][0] as $tag => $value) {
                $imgtag .= "$tag=\"$value\" ";
              }
              $imgtag .= "/>";
            } else {
              $q_warnings[] = "Missing image $basename";
            }
          }

          $output .= $imgtag;
          /*echo "PASS<BR>";
           echo "<pre>";
           echo "START BIT:\n";
           echo htmlentities($pre);
           echo "\nIMG TAG:\n";
           echo htmlentities($imgtag);
           echo "\nREST:\n";
           echo htmlentities($rest);
           echo "</pre>";
           echo "END PASS:\n";*/

          $text = $rest;
          //return $text;
          } else {
          $output .= $text;
          $text = "";
        }
      }

      $text = $output;
      //echo "IMAGE IN CDATA - <br>";	
      }
    /*echo "Parsed<br>";
     echo "<pre>";
     echo htmlentities($text);
     echo "</pre>";*/
    return $text;
  }

  function getItemCount() {
    return count($this->data);
  }

  function GetHTML() {
    //print_p($this);
    $output = "";
    $usediv = 0;
    if (count($this->chunks) > 1) $usediv = 1;

    foreach ($this->chunks as $chunk) {
      $text = trim(implode("", $chunk->data));

      if ($text) {
        if ($usediv) $output .= "<div>";

        $output .= implode("", $chunk->data);

        if ($usediv) $output .= "</div>";
      }
    }
    return $output;
  }

  function GetText() {
    $text = '';
    foreach ($this->chunks as $chunk) {
      $text .= implode("", $chunk->data)."\n";
    }

    while (strpos($text, "  ") > 0) $text = str_replace("  ", " ", $text);

    return trim($text);
  }

  function __toString() {
    $text = $this->GetText();
    if (trim($text)) return $text;

    return "<font color='green'>EMPTY</font>";
  }
}

function FindFile($basedir, $filename) {
  return FindFileSub($basedir, "", $filename);
}

function FindFileSub($basedir, $dir, $filename) {
  $dir_s = scandir($basedir."/".$dir);
  foreach ($dir_s as $entry) {
    if ($entry == ".") continue;
    if ($entry == "..") continue;
    if (is_dir($basedir."/".$dir."/".$entry)) {
      if ($dir) {
        $res = FindFileSub($basedir, $dir."/".$entry, $filename);
      } else {
        $res = FindFileSub($basedir, $entry, $filename);
      }
      if ($res != "") return $res;
    } else if (strtolower($entry) == strtolower($filename)) {
      if ($dir) {
        return $dir."/".$entry;
      } else {
        return $entry;
      }
    }
  }

  return "";
}

function parseHtml($s_str) {
  $i_indicatorL = 0;
  $i_indicatorR = 0;
  $s_tagOption = "";
  $i_arrayCounter = 0;
  $a_html = array();
  // Search for a tag in string
  while (is_int(($i_indicatorL = strpos($s_str, "<", $i_indicatorR)))) {
    // Get everything into tag...
    $i_indicatorL++;
    $i_indicatorR = strpos($s_str, ">", $i_indicatorL);
    $s_temp = substr($s_str, $i_indicatorL, ($i_indicatorR - $i_indicatorL));
    $a_tag = explode(' ', $s_temp);
    // Here we get the tag's name
    list(, $s_tagName, , ) = each($a_tag);
    $s_tagName = strtoupper($s_tagName);
    // Well, I am not interesting in <br>, </font> or anything else like that...
    // So, this is false for tags without options.
    $b_boolOptions = is_array(($s_tagOption = each($a_tag))) && $s_tagOption[1];
    if ($b_boolOptions) {
      // Without this, we will mess up the array
      $i_arrayCounter = (int) count($a_html[$s_tagName]);
      // get the tag options, like src="htt://". Here, s_tagTokOption is 'src' and s_tagTokValue is '"http://"'
      do {
        $s_tagTokOption = strtolower(strtok($s_tagOption[1], "="));
        $s_tagTokValue = trim(strtok("="));
        if (substr($s_tagTokValue, 0, 1) == "\"" && substr($s_tagTokValue, strlen($s_tagTokValue) - 1, 1) == "\"") $s_tagTokValue = substr($s_tagTokValue, 1, strlen($s_tagTokValue) - 2);
        if (substr($s_tagTokValue, 0, 1) == "'" && substr($s_tagTokValue, strlen($s_tagTokValue) - 1, 1) == "'") $s_tagTokValue = substr($s_tagTokValue, 1, strlen($s_tagTokValue) - 2);
        $a_html[$s_tagName][$i_arrayCounter][$s_tagTokOption] = $s_tagTokValue;
        $b_boolOptions = is_array(($s_tagOption = each($a_tag))) && $s_tagOption[1];
      } while ($b_boolOptions);
    }
  }
  return $a_html;
}
