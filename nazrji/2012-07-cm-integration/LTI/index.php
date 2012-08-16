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

global $cfg_long_date_time;

function listtreemodules($mysqli, $moduleid, $block_id, $plk, $flat = false, $explode = false) {
  global $cfg_long_date_time, $icons;
  $query_string = "SELECT DISTINCT crypt_name, paper_type, 'f', paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'$cfg_long_date_time') AS display_start_date, DATE_FORMAT(end_date,'$cfg_long_date_time') AS display_end_date, title, initials, surname, retired, moduleID  FROM (properties, users) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.paper_ownerID=users.id AND (moduleID = '" . $moduleid . "' OR moduleID LIKE '%," . $moduleid . ",%' OR moduleID LIKE '" . $moduleid . ",%' OR moduleID LIKE '%," . $moduleid . "') AND deleted IS NULL AND paper_type IN (0,1,3)  GROUP BY moduleID,paper_title ORDER BY paper_type, paper_title";
  $results2 = $mysqli->prepare($query_string);
  $results2->execute();
  $results2->bind_result($crypt_name, $paper_type, $screens, $paper_title, $start_date, $start_date_disp, $end_date_display, $title, $initials, $surname, $retired, $moduleID);
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
      echo "<div>";
    }
    while ($results2->fetch()) {
      echo "<div style=\"padding-left:52px\"><a href=\"?paperlinkID=" . $plk . "\"><img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $paper_type . "\" /></a>&nbsp;<a class=\"recent\"";
      if (strpos($paper_title, '[deleted') !== false) {
        echo ' style="color:#808080"';
      }
      echo "href=\"?paperlinkID=" . $plk . "\">" . $paper_title . "</a></div>\n";
      $_SESSION['postlookup'][$plk] = array($crypt_name, $moduleid);
      $plk++;
    }
    echo "</div>";
    $block_id++;
  }
  else {
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
  display_notice($string['LTIFAILURE'], $message, '/artwork/access_denied.png', '#C00000');
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

    //      if ($retlookup2 !== 0) {
    //        $info = $lti->getCourseKey(1);
    //        addlticontext($mysqli, $info[0], $info[1], $retlookup1);
    //      }
  }
}


$returned = $lti->lookup_lti_resource();

if (!$lti->isInstructor()) {
  //student
  if ($returned === false) {
    // no data selected for this
    echo "<html>\n<head>\n<title>" . $string['unavailablepaper'] . "</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#BF0000;font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"{$cfg_root_path}/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">" . $string['unavailablepaper'] . "</h1>\n";
    exit();
  } else {
    //valid data
    list($c_internal_id, $upd) = $lti->lookup_lti_context();
    $session = date_utils::get_current_academic_year();

    // $c_internal_id is module code
    //    $module_id_out=i_lti_module_code_translate($c_internal_id);

    $returned_check = module_utils::module_check_self_enrol($c_internal_id, $mysqli);

    if (!UserUtils::is_user_on_module($userID, $c_internal_id, $session, $mysqli) and $returned_check !== false and !$lti_i::allow_module_self_reg($c_internal_id)) {
      list($fullname, $school, $active, $selfenroll) = $returned_check;
      if ($active == 1 and $selfenroll == 1 and !UserUtils::is_user_on_module($userID, $_GET['moduleid'], $_POST['session'], $mysqli)) {
        // Insert new module enrollment
        UserUtils::add_student_to_module($userID, $c_internal_id, 1, $session, $mysqli);
      }
    }
    // do something here
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
    $data = $lti_i->module_code_translate($mod);
    foreach ($data as $v) {
      if (!UserUtils::staff_on_team($v[1], $mysqli)) {
        UserUtils::add_staff_to_team($userID, $v[1], $mysqli);
      }
    }


    if (!$lti_i::allow_staff_edit_link()) {
      $_SESSION['lti']['paperlink'] = $returned[0];
      header("location: ../user_index.php?id=" . $returned[0]);
      echo "Please click <a href='../user_index.php?id=" . $returned[0] . ".>here</a> to continue";
      exit();
    } else {
      // allow editing of the stored link
      //TODO NO SUPPORT YET DONE
    }

  } else {
    // no existing stored link so need to create one

    $returned2 = $lti->lookup_lti_context();


    if ($returned2 === false) {

      //no context


      $data = $lti_i->module_code_translate($lti->getCourseName(), $lti->get_context_title());


      foreach ($data as $v) {

        if (module_utils::module_exists($v[1], $mysqli)) {

          $selfEnroll = 0;
          if ($v[0] == 'Manual') {
            $selfEnroll = 1;
          }

          $sms_api = $lti_i->sms_api($v);

          $modcreate = module_utils::add_modules($v[1], $v[5], 1, $schoolID, '', $sms_api, $selfEnroll, 0, 0, 0, 0, 1, 0, $mysqli);

          UserUtils::add_staff_to_team($userID, $v[1], $mysqli);

        }

      }

      $module_store = $lti_i->module_code_translated_store($data);


      $lti->add_lti_context($module_store);

      $returned2 = $lti->lookup_lti_context();
    }

    $mod = $returned2[0];
    $data = $lti_i->module_code_translate($mod);

    list($c_internal_id, $upd) = $returned2;
    $moduleid = $c_internal_id;

    $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');
    print <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=$cfg_page_charset" />
  <title>Rogō $cfg_install_type</title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
  h1 {font-size:150%}
  .divider {padding-left:16px; padding-bottom:2px; font-weight:bold}
  .sch {padding-left:32px; text-indent:-20px}
  .greysch {padding-left:12px; color:#808080}
  .mod {padding-left:60px; text-indent:-30px}
  </style>
   $cfg_js_root
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

    echo '<h1>' . $string['describemodulechoice'] . '</h1>';


    //if there is a context and therefore a course already selected display that


    echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['papersoncurrentmodule'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";


    foreach ($data as $v)
    {
      $moduleid = $v[1];


      list($block_id, $plk) = listtreemodules($mysqli, $moduleid, $block_id, $plk, true);
    }
    echo "<br/>";

    $personalfolders = new personal_folders($mysqli);
    $personalfolders->loadpersonalfolders($userID);
    $personalfolders->process();
    //    echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['myfolders'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    //    list($block_id, $plk) = $personalfolders->listtree(0, $block_id, $plk, 0);
  }
}
exit();


if ($returned === false AND !$lti->isInstructor()) { //!((strpos($userroles, 'SysAdmin') !== false) OR (strpos($userroles, 'Staff') !== false))) {
  echo "<html>\n<head>\n<title>" . $string['unavailablepaper'] . "</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#BF0000;font-size:140%}\n</style>\n</head>\n<body>\n";
  echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"{$cfg_root_path}/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
  echo "<h1 style=\"margin-left:60px\">" . $string['unavailablepaper'] . "</h1>\n";
  exit();
} elseif ($returned === false) {
  //paper choice display
  $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');
  print <<<END
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html>
      <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta http-equiv="content-type" content="text/html;charset=$cfg_page_charset" />
        <title>Rogō $cfg_install_type</title>
        <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
        <link rel="stylesheet" type="text/css" href="../css/header.css" />
        <style type="text/css">
        h1 {font-size:150%}
        .divider {padding-left:16px; padding-bottom:2px; font-weight:bold}
        .sch {padding-left:32px; text-indent:-20px}
        .greysch {padding-left:12px; color:#808080}
        .mod {padding-left:60px; text-indent:-30px}
        </style>
         $cfg_js_root
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

  echo '<h1>' . $string['describemodulechoice'] . '</h1>';

  /*
     *
     *
    $info = $lti->getCourseKey(1);
    $stmt = $mysqli->prepare("SELECT c_internal_id FROM lti_context WHERE  oauth_consumer_key=? AND lti_context_id=?");
    $stmt->bind_param('ss', $info[0], $info[1]);
    $stmt->execute();
    $stmt->store_result();
    $rows = $stmt->num_rows;
    $stmt->bind_result($c_internal_id);

    if ($rows > 0) {
      //if there is a context and therefore a course already selected display that
      $stmt->fetch();
      /*
        echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['papersoncurrentmodule'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
      $moduleid = $c_internal_id;
      list($block_id, $plk) = listtreemodules($mysqli, $moduleid, $block_id, $plk, true);
      * /
    }

    $stmt->close();
  */
  list($c_internal_id, $upd) = $lti->lookup_lti_context();


  $personalfolders = new personal_folders($mysqli);
  $personalfolders->loadpersonalfolders($userID);
  $personalfolders->process();
  echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['myfolders'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  list($block_id, $plk) = $personalfolders->listtree(0, 0, $plk, 0);


  echo "<table border=\"0\" style=\"padding-top:10px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['bymodulecode'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  $old_faculty = '';
  $old_letter = '';
  $module_block = false;
  $teams = getUserTeams($userID, $mysqli);
  $modlist = search_utils::get_teams($teams, $userroles, $userID, $mysqli);
  foreach ($modlist as $value) {
    $moduleid = $value['id'];
    if ($moduleid !== '') {
      $explode = false;
      if ($c_internal_id == $moduleid) {
        $explode = true;
      }
      list($block_id, $plk) = listtreemodules($mysqli, $moduleid, $block_id, $plk, false, $explode);
    }
  }
  echo "</div>\n"; // -- End of 'content' div ------------------
  echo "</td></tr></table>";
  exit();
} elseif ($returned[1] == 'paper') {
  header("location: ../user_index.php?id=" . $returned[0]);
}
exit();
