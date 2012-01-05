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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once 'add_edit.inc';

function displayEditTab() {
  global $string, $cfg_short_date;

  $html = "<div id=\"editortab\" style=\"font-size:100%\">\n";
  $html .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; width:100%; background-color:#D6DFF7\">\n<tr><td>\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:90%; width:252px\">\n<tr>";
  $html .= "<td style=\"width:126px; height:21px; color:white; text-align:center; font-weight:bold; background-image:url(/artwork/tab_on.gif)\">" . $string['editor'] . "</td>\n";
  if (isset($_GET['paperID']) and $_GET['paperID'] != '') {
    $html .= "<td style=\"cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; background-image:url(/artwork/tab_off.gif)\" onclick=\"showTab('mappingtab');\">" . $string['mapping'] . "</td>\n";
  } else {
    $html .= "<td style=\"width:126px; height:21px; color:white; text-align:center; font-weight:bold; background-image:url(/artwork/tab_disabled.gif)\">" . $string['mapping'] . "</td>\n";
  }
  $html .= "</tr></table>\n</td><td style=\"width:100%; background-color:#DFECFF; text-align:right; font-size:90%\"><strong>" . $string['created'] . "</strong>&nbsp;" . date(str_replace('%','',$cfg_short_date)) . "&nbsp;</td></tr>\n";
  $html .= "<tr><td colspan=\"2\" style=\"background-color:#1E3C7B\">&nbsp;</td></tr>\n</table>\n";

  return $html;
}

function redirect($p_id) {
  global $protocol, $mysqli;
  
  if (substr($p_id,0,5) == 'list:') {
    $type = substr($p_id,5);
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/question/list.php");
  } elseif ($p_id != '') {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/paper/details.php?paperID=$p_id&module=" . $_POST['module'] . "&folder=" . $_POST['folder']);
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/folder/details.php?module=" . $_POST['module'] . "&folder=" . $_POST['folder']);
  } else {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/");
  }
  $mysqli->close();
  exit;   // Stop script execution after the redirect.
}

function insert_into_questions($q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $q_group, $status, $option_order) {
  global $mysqli;
  
  //$theme = htmlspecialchars($theme);
  //$scenario = htmlspecialchars($scenario);
  //$leadin = htmlspecialchars($leadin);
  //$correct_fback = htmlspecialchars($correct_fback);
  //$incorrect_fback = htmlspecialchars($incorrect_fback);
  //$notes = htmlspecialchars($notes);
  
  $scenario_plain = trim(strip_tags($scenario));
  $leadin_plain = trim(strip_tags($leadin));
  // Make bloom field safe for MySQL strict mode
  $bloom = ($bloom == '') ? NULL : $bloom;

  if ($result = $mysqli->prepare("INSERT INTO questions VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NULL,NULL,NULL,NULL,NULL,?,?,NULL)")) {
    $result->bind_param('ssssssssisssssssssss', $q_type, $theme, $scenario, $leadin, $correct_fback, $incorrect_fback, $display_method, $notes, $ownerID, $q_media, $q_media_width, $q_media_height, $creation_date, $last_edited, $bloom, $q_group, $scenario_plain, $leadin_plain, $status, $option_order);
    $result->execute();
    $result->close();
  } else {
    display_error("Question Add Error",$mysqli->error);
  }

  return $mysqli->insert_id;
}

function insert_into_papers($p_id, $q_id, $branching_question=false) {
  global $mysqli;
  $result = $mysqli->prepare("SELECT MAX(screen) AS screen, MAX(display_pos) AS display_pos FROM papers WHERE paper=?");
  $result->bind_param('i', $p_id);
  $result->execute();
  $result->bind_result($screen, $display_pos);
  $result->fetch();
  $result->close();
  $display_pos++;

  if ($screen == 0) $screen++;
  
  if ($branching_question == true) {
    //Get the Question ID of the source question
    $result = $mysqli->prepare("SELECT scenario FROM questions WHERE q_id=?");
    $result->bind_param('i', $q_id);
    $result->execute();
    $result->bind_result($scenario);
    $result->fetch();
    $result->close();
    
    //Get the screen number of the source question on the paper
    $result = $mysqli->prepare("SELECT screen FROM papers WHERE paper=? AND question=?");
    $result->bind_param('ii', $p_id, $scenario);
    $result->execute();
    $result->bind_result($source_screen);
    $result->fetch();
    $result->close();
    
    if ($source_screen == $screen) $screen++;
  }
  
  //force random mark to recaculate
  $result = $mysqli->prepare("UPDATE properties SET random_mark=NULL, total_mark=NULL WHERE property_id=?");
  $result->bind_param('i', $p_id);
  $result->execute();  
  $result->close();

  if ($result = $mysqli->prepare("INSERT INTO papers VALUES (NULL,?,?,?,?)")) {
    $result->bind_param('iiii', $p_id, $q_id, $screen, $display_pos);
    $result->execute();
    $result->close();
  } else {
    display_error("Papers Add Error",$mysqli->error);
  }
  echo $mysqli->error;
  return $mysqli->insert_id;
}

function hidden_add_fields() {
  $html = '';

  if (isset($_GET['paperID'])) {
    $html .= '<input type="hidden" name="paperID" value="' . $_GET['paperID'] . '" />';
  } else {
    $html .= '<input type="hidden" name="paperID" value="" />';
  }
 
  if (isset($_GET['module'])) {
    $html .= '<input type="hidden" name="module" value="' . $_GET['module'] . '" />';
  } else {
    $html .= '<input type="hidden" name="module" value="" />';
  }
 
  if (isset($_GET['folder'])) {
    $html .= '<input type="hidden" name="folder" value="' . $_GET['folder'] . '" />';
  } else {
    $html .= '<input type="hidden" name="folder" value="" />';
  }
 
  if (isset($_GET['scrOfY'])) {
    $html .= '<input type="hidden" name="scrOfY" value="' . $_GET['scrOfY'] . '" />';
  } else {
    $html .= '<input type="hidden" name="scrOfY" value="" />';
  }
 
  return $html;
}
?>