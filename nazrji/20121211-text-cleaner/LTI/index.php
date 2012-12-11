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
 * LTI landing page.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

require_once '../include/staff_student_auth.inc';
require_once '../include/sidebar_menu.inc';
require_once '../include/lti_func.php';

require_once '../config/index.inc';

require_once '../classes/searchutils.class.php';
require_once '../classes/dateutils.class.php';
require_once '../classes/userutils.class.php';
require_once '../classes/moduleutils.class.php';
require_once '../classes/personal_folders.php';
require_once '../classes/lti_integration.class.php';
require_once '../classes/smsutils.class.php';
require_once '../classes/schoolutils.class.php';
require_once '../classes/facultyutils.class.php';


$choicetype='radio';

function listtreemodules($mysqli, $moduleid, $block_id, $plk, $flat = false, $explode = false, $type='') {
  global $configObject, $icons;

  $query_string = "SELECT DISTINCT crypt_name, paper_type, paper_title, retired, moduleID FROM properties WHERE (moduleID = '" . $moduleid . "' OR moduleID LIKE '%," . $moduleid . ",%' OR moduleID LIKE '" . $moduleid . ",%' OR moduleID LIKE '%," . $moduleid . "') AND deleted IS NULL AND paper_type IN ('0','1','3') ORDER BY paper_type, paper_title";
  $results2 = $mysqli->prepare($query_string);
  $results2->execute();
  $results2->bind_result($crypt_name, $paper_type, $paper_title, $retired, $moduleID);
  $results2->store_result();
  if ($results2->num_rows() > 0) {
    @ob_flush();
    @flush();
    $rt = $results2->num_rows();
    if (!$flat) {
      echo "<div class=\"mod\"><img src=\"../artwork/folder_16.png\" width=\"16\" height=\"16\" alt=\"folder\"border=\"0\" onclick=\"showHide($block_id)\"  /><a href=\"\" style=\"color:blue\" onclick=\"showHide($block_id); return false;\">&nbsp;$moduleid: $paper_title ($rt)</a></div>\n";
      if ($explode === true) {
        echo "<div id=\"block$block_id\">";
      } else {
        echo "<div id=\"block$block_id\" style=\"display:none\">";
      }
    } else {
      echo '<div>';
    }
    $type='radio';
    while ($results2->fetch()) {
      if ($type == 'radio') {
        $extra = "<input type=\"radio\" name=\"paperlinkID\" id=\"paperlinkID-$plk\" value=\"$plk\"><label for=\"paperlinkID-$plk\">";
        $extra1 = "</label>";
      } elseif ($type == '') {
        $extra = "<a href=\"?paperlinkID=" . $plk . "\">";
        $extra1 = "</a>";
      }
      echo "<div style=\"padding-left:52px\">$extra<img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $paper_type . "\" />&nbsp;";
      if (strpos($paper_title, '[deleted') !== false) {
        echo ' style="color:#808080"';
      }
      echo  $paper_title . "$extra1</div>\n";


      /*
      echo "<div style=\"padding-left:52px\"><a href=\"?paperlinkID=" . $plk . "\"><img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $paper_type . "\" /></a>&nbsp;<a class=\"recent\"";
      if (strpos($paper_title, '[deleted') !== false) {
        echo ' style="color:#808080"';
      }
      echo "href=\"?paperlinkID=" . $plk . "\">" . $paper_title . "</a></div>\n";
       */


      $_SESSION['postlookup'][$plk] = array($crypt_name, $moduleid);
      $plk++;
    }
    echo '</div>';
    $block_id++;
  } else {
    // no papers
  }
  $results2->close();
  return (array($block_id, $plk));
}


if (!$lti->valid) {
  $tempvar = $lti->message;
  if (!isset($string[$tempvar])) {
    $string[$tempvar] = $lti->message;
  }
  $message = $string[$tempvar];
  UserNotices::display_notice($string['LTIFAILURE'], $message, '/artwork/access_denied.png', '#C00000');
  $mysqli->close();
  exit;
}



if (!isset($lti_i)) {
  $lti_i = lti_integration::load();
}

if (isset($_REQUEST['paperlinkID'])) {
  list($retlookup, $retlookup2) = $_SESSION['postlookup'][$_REQUEST['paperlinkID']];
  unset($_SESSION['postlookup']);
  if ($retlookup > 0) {
    $info = $lti->getResourceKey(1);
    $lti->add_lti_resource($retlookup, 'paper');
  }
}
unset($_SESSION['postlookup']);

$returned = $lti->lookup_lti_resource();

if (!$lti->isInstructor()) {
  //student
  if ($returned === false) {
    // no data selected for this
    UserNotices::display_notice($string['warning'], $string['ltinotconfigured'], $configObject->get('cfg_root_path') . '/artwork/access_denied.png', $title_color = '#C00000');
    echo "\n</body>\n</html>\n";
    exit();
  } else {
    //valid data
    list($c_internal_id, $upd) = $lti->lookup_lti_context();
    $session = date_utils::get_current_academic_year();

    $data = $lti_i::module_code_translate($c_internal_id);

    foreach ($data as $v) {
      $returned_check = module_utils::get_full_details_by_ID($v[1], $mysqli);

      if (!UserUtils::is_user_on_module($userObject->get_user_ID(), $v[1], $session, $mysqli) and $returned_check !== false and $lti_i::allow_module_self_reg($v)) {
        list($fullname, $school, $active, $selfenroll) = $returned_check;
        if ($returned_check['active'] == 1 and $returned_check['selfenroll'] == 1 and !UserUtils::is_user_on_module($userObject->get_user_ID(), $v[1], $session, $mysqli)) {
          // Insert new module enrollment
          UserUtils::add_student_to_module($userObject->get_user_ID(), $v[1], 1, $session, $mysqli);
        }
      }
    }
    // do 'something' here
    $_SESSION['lti']['paperlink'] = $returned[0];
    header("location: ../user_index.php?id=" . $returned[0]);
    echo "Please click <a href='../user_index.php?id=" . $returned[0] . ".>here</a> to continue";
    exit();

  }
} else {
  //staff

  if ($returned !== false) {
    // goto link

    $returned2 = $lti->lookup_lti_context();
    $mod = $returned2[0];
    $data = $lti_i::module_code_translate($mod);
    foreach ($data as $v) {
      if (!UserUtils::staff_on_team($v[1], $mysqli) and $lti_i::allow_staff_module_register($v)) {
        UserUtils::add_staff_to_team($userObject->get_user_ID(), $v[1], $mysqli);
      } elseif (!UserUtils::staff_on_team($v[1], $mysqli) and !$lti_i::allow_staff_module_register($v)) {
        $error[] = '<img src="' . $configObject->get('cfg_root_path') . '../artwork/exclamation_64.png' . '"><h1>' . $string['NotAddedToModuleTitle'] . '</h1>' . $string['NotAddedToModule'] . $v[1] . '<br />';
      }
    }


    if (!$lti_i::allow_staff_edit_link()) {
      $_SESSION['lti']['paperlink'] = $returned[0];
      header("location: ../user_index.php?id=" . $returned[0]);
      echo "Please click <a href='../user_index.php?id=" . $returned[0] . ".>here</a> to continue";
      exit();
    } else {
      // allow editing of the stored link
      //TODO NO SUPPORT YET IMPLIMENTED
    }

  } else {
    // no existing stored link so need to create one

    $returned2 = $lti->lookup_lti_context();


    if ($returned2 === false) {

      //no context
      $data = $lti_i::module_code_translate($lti->getCourseName(), $lti->get_context_title());

//      /var_dump($data);
      foreach ($data as $v) {
        if (!module_utils::module_exists($v[1], $mysqli) and  $lti_i::allow_module_create($v)) {

          $peer = 1;
          $external = 1;
          $stdset = 0;
          $mapping = 1;
          $neg_marking = 1;


          $selfEnroll = 0;
          if ($v[0] == 'Manual') {
            $selfEnroll = 1;
            $peer = 0;
            $external = 0;
            $stdset = 0;
            $mapping = 0;
            $neg_marking = 1;
          }

          $sms_api = $lti_i::sms_api($v);
          $schoolID = SchoolUtils::get_school_id_by_name($v[3], $mysqli);
          $modcreate = module_utils::add_modules($v[1], $v[5], 1, $schoolID, '', $sms_api, $selfEnroll, $peer, $external, $stdset, $mapping, $neg_marking, 0, $mysqli, 1);
        } elseif (!module_utils::module_exists($v[1], $mysqli) and  !$lti_i::allow_module_create($v)) {
          UserNotices::display_notice($string['NoModCreateTitle'], $string['NoModCreate'] . $v[1], '../artwork/exclamation_64.png');
          exit();
        }
        if (!UserUtils::staff_on_team($v[1], $mysqli) and $lti_i::allow_staff_module_register($v)) {
          UserUtils::add_staff_to_team($userObject->get_user_ID(), $v[1], $mysqli);
        } elseif (!UserUtils::staff_on_team($v[1], $mysqli) and !$lti_i::allow_staff_module_register($v)) {
          UserNotices::display_notice($string['NotAddedToModuleTitle'], $string['NotAddedToModule'] . $v[1], '../artwork/exclamation_64.png');
          exit();

        }
      }
      $module_store = $lti_i::module_code_translated_store($data);
      $lti->add_lti_context($module_store);
      $returned2 = $lti->lookup_lti_context();
    }
    $mod = $returned2[0];
    $data = $lti_i::module_code_translate($mod);
    list($c_internal_id, $upd) = $returned2;
    $moduleid = $c_internal_id;
    $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');
    echo <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset={$configObject->get('cfg_page_charset')}" />
  
  <title>Rogō $configObject->get('cfg_install_type')</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
  h1 {font-size:150%}
  .divider {padding-left:16px; padding-bottom:2px; font-weight:bold}
  .sch {padding-left:32px; text-indent:-20px}
  .greysch {padding-left:12px; color:#808080}
  .mod {padding-left:60px; text-indent:-30px}
  </style>
   {$configObject->get('cfg_js_root')}
  <script language="JavaScript">
    function showHide(sectionID) {
      sectionID = 'block' + sectionID;
      current = (document.getElementById(sectionID).style.display == 'block') ? 'none' : 'block';
      document.getElementById(sectionID).style.display = current;
    }
  </script>
</head>
<body style="padding-left: 21px;">
<div id="content" class="content" style="font-size:80%;">

END;

    $plk = 0;
    $block_id = 0;


    if (isset($error)) {
      foreach ($error as $e) {
        echo $e;
      }
    }

    @ob_flush();
    @ob_start();

    echo '<h1>' . $string['describemodulechoice'] . '</h1>';

    //if there is a context and therefore a course already selected display that
    $modinfo = '';
    $exit=0;

    foreach ($data as $v) {
      $modinfo = $modinfo . ', ' . $v[1];
      if($v[1]=='') {
        $exit=1;
      }
    }
    $modinfo = substr($modinfo, 2);

    echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['papersoncurrentmodule'] . ' ' . $modinfo . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    if($choicetype == 'radio') {
    echo '<form method="POST">';
    }
    foreach ($data as $v) {
      $moduleid = $v[1];

      list($block_id, $plk) = listtreemodules($mysqli, $moduleid, $block_id, $plk, true, $choicetype);
    }
    if($choicetype == 'radio') {
      $strng=$string['SELECT'];
    print <<<END
			<div>
<input type="submit" name="submit" value="$strng"></form>
			</div></form>
			<div>Module: $modinfo </div>
END;

    }
    echo '<br />';
    if($exit==1) {
      $plk=0;
      $modinfo="Undefined Module. Please contact Support.";
    }

    if ($plk == 0) {
      @ob_clean();
      unset($_SESSION['_lti_context']);
      unset($_SESSION['lti']);
      UserNotices::display_notice($string['NoPapers'], $string['NoPapersDesc'], '/artwork/access_denied.png', '#C00000');

      echo '<p>Module(s): ' . $modinfo .'</p>';

    }


  }
}
?>