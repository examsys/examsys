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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../classes/mathsutils.class.php';

function parse_leadin_word_2003($text,$parts) {
  global $q_id;
  $count = 0;
  $calss = 'part';

  $text = str_replace("\n",'',$text);
  $text = str_replace("\r",'',$text);
  $text = str_replace("\t",'',$text);
  $text = str_replace("&nbsp;",' ',$text);
  $text = str_replace('<strong>','',$text);
  $text = str_replace('</strong>','',$text);
  $text = str_replace('<ul>','',$text);
  $text = str_replace('</ul>','',$text);
  $text = str_replace('<div>','',$text);
  $text = str_replace('</div>','<w:br/>',$text);

  $starts = explode('[',$text);
  $count = count($starts);
  if($parts == '') {
    $value = str_pad('',$count-1,'0');
  } else {
    $value = $parts;
  }

  $tmptext = '';
  if ($count > 1) {
    for($i = 0; $i < $count; $i++) {

      if($i > 0) {
        if($value{$i-1} == 1) {
          $tmptext .= "<w:r><w:rPr><w:color w:val=\"00B050\"/><w:u w:val=\"single\"/></w:rPr><w:t>";
        } else {
          $tmptext .= "<w:r><w:rPr></w:rPr><w:t>";
        }

        $tmptext .= ltrim($starts[$i]);

        if ($i < ($count -1)) {
	      if (substr($starts[$i],-1) != ']' OR substr($starts[$i],-1) != 'li>') {
	        $end_tags = '</w:t></w:r>';
	        $rep_tags = '</w:t></w:r><w:r><w:t>';
	      } else {
	        $end_tags = '</w:t></w:r>';
	        $rep_tags = '';
	      }
	    } else {
	      $end_tags = '</w:t></w:r>';
	      $rep_tags = '';
        }

        $tmptext .= $end_tags;
        $tmptext = preg_replace('/\]/'," $rep_tags",$tmptext);
      } else {
        if (trim($starts[$i]) != '' and trim($starts[$i]) != '<li>') {
          $tmptext .= '<w:r><w:t>|' . trim(htmlspecialchars($starts[$i])) . '|</w:t></w:r></w:p><w:p>';
        }
      }
    }
    $x = 0;
    $tmptext = str_replace('<li>','',$tmptext,$x);
    if ($x > 0) {
      $tmptext = str_replace('<w:r><w:rPr><w:color w:val="00B050"/><w:u w:val="single"/></w:rPr><w:t>','<w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="\xE2\x80\xA2"/><wx:font wx:val="Calibri"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:rPr><w:color w:val="00B050"/><w:u w:val="single"/></w:rPr><w:t>' . "\n\n",$tmptext);
      $tmptext = str_replace('<w:r><w:rPr></w:rPr><w:t>','<w:pPr><w:listPr><w:ilvl w:val="0"/><w:ilfo w:val="3"/><wx:t wx:val="\xE2\x80\xA2"/><wx:font wx:val="Calibri"/></w:listPr><w:spacing w:after="0" w:line="240" w:line-rule="auto"/></w:pPr><w:r><w:t>' . "\n\n",$tmptext);
      $tmptext = str_replace('</li></w:t></w:r>','</w:t></w:r></w:p><w:p>',$tmptext);
    }

    return $tmptext;
  } else {
    return  '<w:r><w:t>' . $text . '</w:t></w:r>';
  }

}

function parse_leadin($text, $parts) {
  global $q_id;
  $count = 0;
  $calss = 'part';

  $starts = explode('[',$text);
  $count = count($starts);
  if($parts == '') {
    $value = str_pad('',$count-1,'0');
  } else {
    $value = $parts;
  }
  $tmptext = '';
  if ($count > 1) {
    $tmptext .= $starts[0];
    for($i = 1; $i < $count; $i++) {
      if($value{$i-1} == 1) {
         $class = 'part_ok';
      } else {
        $class = 'part';
      }
      $tmptext .= "<span id=\"" . $q_id . "_" . ($i) . "\" class=\"$class\" onclick=\"subpart(this);\">";
      $tmptext .= $starts[$i];
    }
    $tmptext = preg_replace('/\]/','</span>',$tmptext);

    $tmptext .= "\n<input id=\"" . $q_id . "_parts\" name=\"" . $q_id . "_parts\" type=\"hidden\" value=\"$value\" />\n";

    return $tmptext;
  } else {
    return $text;
  }
}

function isBorderline($result, $propertyObj) {
  if ($propertyObj->get_marking() == 3 and $result == 2) {                          // Clear Fail | Borderline | Clear Pass
    return true;
  } elseif ($propertyObj->get_marking() == 4 and ($result == 2 or $result == 3)) {  // Fail | Borderline fail | Borderline pass | Pass | Good pass
    return true;
  } elseif ($propertyObj->get_marking() == 6 and $result == 2) {                    // Clear FAIL | BORDERLINE | Clear PASS | Honours PASS
    return true;
  } else {
    return false;
  }
}

function getBlinePassmk($user_results, $user_no, $propertyObj) {
  $passmark = 0;
  
  $borderlines = array();
  
  for ($i=0; $i<$user_no; $i++) {
    if (isBorderline($user_results[$i]['rating'], $propertyObj)) {
      $borderlines[] = $user_results[$i]['numeric_score'];
    }
  }
  
  if (count($borderlines) > 0) {
    $passmark = MathsUtils::median($borderlines);
  }
  
  return $passmark;
}

// Get the students who are enrolled on the module/session.
function load_results($propertyObj, $demo, $configObj, $db) {

  $modIDs = implode(',', array_keys(Paper_utils::get_modules($_GET['paperID'], $db)));
  $user_no = 0;
  $calendar_year = $propertyObj->get_calendar_year();
  
  if (!isset($_GET['absent']) or $_GET['absent'] == 0) {
    $result = $db->prepare("SELECT DISTINCT students.id, students.gender, log4_overall.student_grade, log4_overall.year, students.surname, students.initials, students.first_names, students.title, student_id, started, DATE_FORMAT(started,'{$configObj->get('cfg_long_date_time')}') AS display_started, overall_rating, numeric_score, feedback, examiners.title, examiners.surname, examiners.initials FROM (users AS students, users AS examiners, sid, log4_overall) WHERE students.id = log4_overall.userID AND examiners.id = log4_overall.examinerID AND started >= ? AND started <= ? AND q_paper = ? AND students.id=sid.userID ORDER BY students.surname, students.initials");
    $result->bind_param('ssi', $_GET['startdate'], $_GET['enddate'], $_GET['paperID']);
  } else {
    $result = $db->prepare("SELECT students.id, students.gender, log4_overall.student_grade, log4_overall.year, students.surname, students.initials, students.first_names, students.title, student_id, started, DATE_FORMAT(started,'{$configObj->get('cfg_long_date_time')}') AS display_started, overall_rating, numeric_score, feedback, examiners.title, examiners.surname, examiners.initials FROM (modules_student, users AS students, sid) LEFT JOIN log4_overall ON students.id = log4_overall.userID AND started >= ? AND started <= ? AND q_paper = ? LEFT JOIN users AS examiners ON log4_overall.examinerID = examiners.id WHERE modules_student.userID=students.id AND students.id = sid.userID AND modules_student.idMod IN ($modIDs) AND calendar_year = ? ORDER BY students.surname, students.initials");
    $result->bind_param('ssis', $_GET['startdate'], $_GET['enddate'], $_GET['paperID'], $calendar_year);
  }
  $result->execute();
  $result->bind_result($userID, $gender, $grade, $year, $surname, $initials, $first_names, $title, $student_id, $started, $display_started, $overall_rating, $numeric_score, $feedback, $examiner_title, $examiner_surname, $examiner_initials);
  $user_results = array();
  while ($result->fetch()) {
    $user_results[$user_no]['name']             = $surname . $initials;
    $user_results[$user_no]['display_name']     = $title . ' ' . $surname . ', <span style="color:#808080">' . $first_names . '</span>';
    $user_results[$user_no]['classification']   = '';
    $user_results[$user_no]['rating']           = $overall_rating;
    $user_results[$user_no]['numeric_score']    = $numeric_score;
    $user_results[$user_no]['grade']            = $grade;
    $user_results[$user_no]['started']          = $started;
    $user_results[$user_no]['userID']           = $userID;
    $user_results[$user_no]['student_grade']    = $grade;
    $user_results[$user_no]['year']             = $year;
    $user_results[$user_no]['display_started']  = $display_started;
    $user_results[$user_no]['title']            = $title;  
    $user_results[$user_no]['surname']          = demo_replace($surname, $demo);
    $user_results[$user_no]['initials']         = demo_replace($initials, $demo);
    $user_results[$user_no]['first_names']      = demo_replace($first_names, $demo);
    $user_results[$user_no]['student_id']       = demo_replace_number($student_id, $demo);
    $user_results[$user_no]['gender']           = $gender;
    $user_results[$user_no]['feedback']         = $feedback;
    $user_results[$user_no]['examiner']         = $examiner_title . ' ' . $examiner_initials . ' ' . $examiner_surname;
    $user_results[$user_no]['visible']          = 1;    // Default to visible unless switched off below.

    $user_no++;
  }
  $result->close();
  
  return $user_results;
}

function set_classification(&$user_results, $passmark, $user_no, $string) {
  for ($i=0; $i<$user_no; $i++) {
    if ($user_results[$i]['numeric_score'] >= $passmark) {
      $user_results[$i]['classification'] = $string['pass'];
    } else {
      $user_results[$i]['classification'] = $string['fail'];
    }
  }
}

function rating_num_text(&$user_results, $user_no, $propertyObj, $string) {
  $labels = get_labels($propertyObj);

  for ($i=0; $i<$user_no; $i++) {
    if (isset($user_results[$i]['rating'])) {
      $user_results[$i]['rating'] = $labels[$user_results[$i]['rating']];
    }
  }
}

function get_labels($propertyObj) {
  switch ($propertyObj->get_marking()) {
    case '1':
      $labels = array(1=>'Fail', 2=>'Pass', 'ERROR'=>'ERROR');
      break;
    case '3':
      $labels = array(1=>'Clear Fail', 2=>'Borderline', 3=>'Clear Pass', 'ERROR'=>'ERROR');
      break;
    case '4':
      $labels = array(1=>'Fail', 2=>'Borderline fail', 3=>'Borderline pass', 4=>'Pass', 5=>'Good pass', 'ERROR'=>'ERROR');
      break;
    case '5':
      $labels = array(1=>'Unsatisfactory', 2=>'Competent', 'ERROR'=>'ERROR');
      break;
    case '6':
      $labels = array(1=>'Clear FAIL', 2=>'BORDERLINE', 3=>'Clear PASS', 4=>'Honours PASS', 'ERROR'=>'ERROR');
      break;
  }
  return $labels;
}

?>