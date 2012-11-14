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
* Rogō hompage. Uses ../include/options_menu.inc for the sidebar menu.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once '../include/staff_student_auth.inc';
require_once '../include/errors.inc';
require_once '../include/sidebar_menu.inc';
require_once '../classes/recyclebin.class.php';
require_once '../config/index.inc';
require_once '../classes/paperutils.class.php';

global $userObject;

// Redirect Students (if not also staff), External Examiners and Invigilators to their own areas.
if ($userObject->has_role('Student') and !($userObject->has_role(array('Staff', 'Admin', 'SysAdmin')))) {
  header("location: ../students/");
  exit;
} elseif ($userObject->has_role('ExternalExaminer')) {
  header("location: ../reviews/");
  exit;
} elseif ($userObject->has_role('Invigilator')) {
  header("location: ../invigilator/");
  exit;
}

// If we're still here we should be staff
require_once '../include/staff_auth.inc';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html; charset=<?php echo $cfg_page_charset ?>" />
  
  <title>Rogō<?php echo ' ' . $cfg_install_type; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <link rel="stylesheet" type="text/css" href="../css/announcements.css" />
  
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <?php echo $cfg_js_root ?>
  <script src="../js/sidebar.js" type="text/javascript"></script>
  <script language="JavaScript">
    function illegalChar(codeID) {
      if (codeID == 59) {
        alert("Character ';' illegal - please use alternative characters in folder name.");
      }
      event.returnValue = false;
    }

    function newPaper(paperID) {
      notice = window.open("../paper/new_paper1.php?folder=","properties","width=750,height=500,left="+(screen.width/2-375)+",top="+(screen.height/2-250)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
      if (window.focus) {
        notice.focus();
      }
    }

    function displayCredits(){
      notice=window.open("../credits/index.php","credits","width=696,height=500,scrollbars=no,resizable=no,toolbar=no,location=no,directories=no,status=0,menubar=0");
      notice.moveTo(screen.width/2-350,screen.height/2-250)
      if (window.focus) {
        notice.focus();
      }
    }
  </script>
</head>

<body onclick="hideMenus()">

<?php
  require '../include/options_menu.inc';
  require '../include/icon_display.inc';
?>

<div id="content" class="content">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<?php
  // -- Create new folder ---------------------------------------------------
  $duplicate_name = 0;
  if (isset($_POST['submit'])) {
    $new_folder_name = $_POST['folder_name'];
    $folder_details = $mysqli->prepare("SELECT name FROM folders WHERE ownerID=?");
    $folder_details->bind_param('i', $userObject->get_user_ID());
    $folder_details->execute();
    $folder_details->bind_result($existing_folder_name);
    $folder_details->fetch();
    while ($folder_details->fetch()) {
      if ($existing_folder_name == $new_folder_name) $duplicate_name = 1;
    }
    $folder_details->close();

    if ($duplicate_name == 0) {
      if ($folder_query = $mysqli->prepare("INSERT INTO folders VALUES (NULL, ?, ?, NOW(), 'yellow', NULL)")) {
        $folder_query->bind_param('is', $userObject->get_user_ID(), $new_folder_name);
        $folder_query->execute();
        $folder_query->close();
      } else {
        display_error("New Folder Error",$mysqli->error);
      }
    }
  }

  // Update the last log in date in users.
  $stmt = $mysqli->prepare("UPDATE users SET last_login=NOW() WHERE id=?");
  $stmt->bind_param('i', $userObject->get_user_ID());
  $stmt->execute();
  $stmt->close();
?>
<script language="JavaScript">
  function startPaper(paperID, fullsc) {
    var winwidth = screen.width-80;
    var winheight = screen.height-80;
    if (fullsc == 0) {
      window.open("../reviews/start.php?id="+paperID+"&review=1","paper","width="+winwidth+",height="+winheight+",left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    } else {
      window.open("../reviews/start.php?id="+paperID+"&review=1","paper","fullscreen=yes,left=20,top=10,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }
  }
</script>

<table cellpadding="0" cellspacing="0" border="0" class="header">
  <tr>
    <th style="padding-left:16px; padding-top:5px">
    
    <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0" style="float:left; padding-right:8px" />
    <div style="color:#1F497D; font-size:28pt; font-weight:bold">Rogō</div>
    <div style="color:#1F497D; font-size:9pt"><?php echo $string['eassessmentmanagementsystem']; ?></div>
    
    </th>
    <th style="text-align:right; padding-right:10px"><?php echo $logo_html; ?></th>
  </tr>
  <tr>
    <td colspan="2" class="bevel"></td>
  </tr>
</table>
<?php
  $as_pos = strpos($cfg_install_type,' as ');
  if ($as_pos !== false) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"width:32px\"><div class=\"greywarn\"><img src=\"../artwork/agent.png\" width=\"28\" height=\"28\" alt=\"Locked\" style=\"position:relative; left:6px; top:1px\" /></div></td><td><div class=\"greywarn\">&nbsp;&nbsp;" . $string['loggedinas'] . " " . substr($cfg_install_type, ($as_pos+4)) . "</div></td></tr></table>\n";
  }
?>
<div style="padding-left:6px; padding-right:14px">
<?php

  // Check for any news/announcements
  $news_icons = array('', 'news_64.png', 'new_64.png', 'tip_64.png', 'software_64.png', 'exclamation_64.png', 'sync_64.png', 'megaphone_64.png');
  $result = $mysqli->prepare("SELECT title, staff_msg, icon FROM announcements WHERE NOW() > startdate AND NOW() < enddate AND deleted IS NULL");
  $result->execute();
  $result->bind_result($news_title, $staff_msg, $icon);
  while ($result->fetch()) {
    echo "<br /><div class=\"announcement\"><div style=\"min-height:64px; padding-left:80px; background: transparent url('../artwork/" . $news_icons[$icon] . "') no-repeat top left;\"><strong>$news_title</strong><br />\n<br />\n$staff_msg</div></div>\n";
  }
  $result->close();

  echo "<br />\n";
  $icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');

  // -- Display top 10 recent papers ----------------------------------
  $result = $mysqli->prepare("SELECT paperID, paper_title, accessed, paper_type FROM (recent_papers, properties) WHERE userID=? AND recent_papers.paperID=properties.property_id ORDER BY accessed DESC LIMIT 10");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($paperID, $paper_title, $accessed, $paper_type);
  $result->store_result();
  echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $string['myrecentpapers'] . " (" . $result->num_rows() . ")</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  while ($result->fetch()) {
    $moduleIDs = Paper_utils::get_modules($paperID, $mysqli);  
    $moduleID = implode(',',$moduleIDs);
    echo "<div style=\"padding-left:22px\"><a href=\"../paper/details.php?paperID=" . $paperID . "&folder=&module=" . $moduleID . "\"><img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" border=\"0\" alt=\"" . $paper_type . "\" /></a>&nbsp;<a class=\"recent\"";
    if (strpos($paper_title,'[deleted') !== false) echo ' style="color:#808080"';
    echo "href=\"../paper/details.php?paperID=" . $paperID . "&folder=&module=" . $moduleID . "\">" . $paper_title . "</a></div>\n";
  }
  $result->close();

  // -- Display any papers for review ---------------------------------
  $result = $mysqli->prepare("SELECT paper_type, paper_title, property_id, bidirectional, fullscreen, DATE_FORMAT(internal_review_deadline,'%d/%m/%Y') AS internal_review_deadline, crypt_name FROM (properties, papers) WHERE deleted IS NULL AND internal_review_deadline >= NOW() AND properties.property_id=papers.paper AND internal_reviewers LIKE ? GROUP BY paper");
  $db=$mysqli;
  if ($db->error) {
    try {
      throw new Exception("0MySQL error $db->error <br> Query:<br> $query", $db->errno);
    }
    catch (Exception $e) {
      echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      echo nl2br($e->getTraceAsString());
    }
  }
  $tmp='%' . $userObject->get_user_ID() . '%';
  $result->bind_param('i', $tmp);
  $db=$mysqli;
  if ($db->error) {
    try {
      throw new Exception("0MySQL error $db->error <br> Query:<br> $query", $db->errno);
    }
    catch (Exception $e) {
      echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
      echo nl2br($e->getTraceAsString());
    }
  }
  $result->execute();
  $result->bind_result($paper_type, $paper_title, $property_id, $bidirectional, $fullscreen, $internal_review_deadline, $crypt_name);
  $result->store_result();

  if ($result->num_rows() > 0) {
    echo "<br />\n";
    echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>Papers for Review</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  }
  while ($result->fetch()) {
    $reviewed = '';
    $result2 = $mysqli->prepare("SELECT DATE_FORMAT(MAX(reviewed),'%d/%m/%Y %T') AS started FROM review_comments WHERE reviewer=? and q_paper=?");
    $result2->bind_param('ii', $userObject->get_user_ID(), $property_id);
    $result2->execute();
    $result2->bind_result($reviewed);
    $result2->fetch();
    $result2->close();
    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"#\" onclick=\"startPaper('" . $crypt_name . "'," . $fullscreen . "); return false;\"><img src=\"../artwork/summative.png\" width=\"48\" height=\"48\" alt=\"Paper Icon\" border=\"0\" /></a></td>\n";
    echo "  <td><a href=\"#\" onclick=\"startPaper('" . $crypt_name . "'," . $fullscreen . "); return false;\">" . $paper_title . "</a><br /><div style=\"color:#C00000\">Deadline: " . $internal_review_deadline . "</div>";
    if ($reviewed == '') {
      echo "<span style=\"color:white; background-color:red\">&nbsp;" . $string['notreviewed'] . "&nbsp;</span>";
    } else {
      echo "<span style=\"color:#808080\">" . $string['reviewed'] . ": $reviewed</span>";
    }
    echo "</td></tr></table></div>\n";
  }
  if ($result->num_rows() > 0) echo '<br clear="left" />';
  $result->close();
?>

<br />
<?php
  // -- Display personal folders --------------------------------------
  $module_sql = '';
  if (count($userObject->get_staff_modules()) > 0) {
    $module_sql = " OR idMod IN (" . implode(",",array_keys($userObject->get_staff_modules())) . ")";
  }

  $result = $mysqli->prepare("SELECT id, name, color FROM folders LEFT JOIN folders_modules_staff ON folders.id = folders_modules_staff.folders_id WHERE  (ownerID=? $module_sql) AND name NOT LIKE '%;%' AND deleted IS NULL ORDER BY name, id");
  $result->bind_param('i', $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($id, $name, $color);
  $result->store_result();

  echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $string['myfolders'] . " (" . ($result->num_rows() + 1) . ")</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
  while ($result->fetch()) {
    echo "<div class=\"f\" ><a href=\"../folder/details.php?folder=$id\" class=\"blacklink\"><img style=\"vertical-align:middle; padding-right:8px\" src=\"../artwork/" . $color . "_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" />$name</a></div>\n";
  }
  $result->close();

  if (isset($_GET['newfolder']) and $_GET['newfolder'] == 'y' or $duplicate_name == 1) {
    if (isset($_POST['submit']) and $_POST['submit'] and $duplicate_name == 1) {
      echo "<script language=\"JavaScript\">alert(\"" . $string['duplicatefoldername'] . "\")</script>";
      echo "<div class=\"f\"><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" />&nbsp;<input style=\"background-color:#FFC0C0\" type=\"text\" size=\"30\" name=\"folder_name\" value=\"$new_folder_name\" onkeypress=\"if (event.keyCode == 38 || event.keyCode == 59 || event.keyCode == 63 || event.keyCode == 64 || event.keyCode == 94 || event.keyCode == 126) illegalChar(event.keyCode);\" /><input type=\"submit\" name=\"submit\" value=\"" . $string['create'] . "\" /></div>\n";
    } elseif (!isset($_POST['submit'])) {
      echo "<div class=\"f\"><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" />&nbsp;<input type=\"text\" size=\"30\" name=\"folder_name\" value=\"" . $string['newfolder'] . "\" onkeypress=\"if (event.keyCode == 38 || event.keyCode == 59 || event.keyCode == 63 || event.keyCode == 64 || event.keyCode == 94 || event.keyCode == 126) illegalChar(event.keyCode);\" /><input type=\"submit\" name=\"submit\" value=\"" . $string['create'] . "\" /></div>\n";
    }
  }

  echo "<div class=\"f\"><a href=\"../delete/recycle_list.php\" class=\"blacklink\"><img style=\"vertical-align:middle; padding-right:8px\" src=\"../artwork/" . RecycleBin::get_recyclebin_icon($userObject->get_user_ID(), $staff_modules, $mysqli) . "\" width=\"48\" height=\"48\" alt=\"Recycle Bin\" border=\"0\" align=\"middle\" />" . $string['recyclebin'] . "</a></div>\n";
?>
<br clear="left" />
<?php
  if (!isset($_GET['folder']) OR $_GET['folder'] == '') {
    echo "<br />\n";
    // -- Display module folders ------------------------------------
    $module_no = count($modules_array);
    if ($userObject->has_role('Admin')) $module_no++;

    echo "<table border=\"0\" class=\"subsect\"><tr><td><nobr>" . $string['mymodules'] . " ($module_no)</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
    if ($userObject->has_role('SysAdmin')) {
      echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"../folder/all.php\"><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"../folder/all.php\" class=\"blacklink\"><strong>" . $string['allmodules']  . "</strong></a><br /><span style=\"color:#C00000\">(" . $string['sysadminonly'] . ")</span></td></tr></table></div>\n";
    } elseif ($userObject->has_role('Admin')) {
      echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"../folder/all.php\"><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"../folder/all.php\" class=\"blacklink\"><strong>" . $string['allmodulesinschool'] . "</strong></a><br /><span style=\"color:#C00000\">(" . $string['adminonly'] . ")</span></td></tr></table></div>\n";
    }
    foreach ($modules_array as $folder_title => $url) {
	    $title_parts = explode(' - ',$folder_title);
	    echo "<div class=\"f\"><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\"><tr><td style=\"width:60px\" align=\"center\"><a href=\"$url\"><img src=\"../artwork/yellow_folder.png\" width=\"48\" height=\"48\" alt=\"Folder\" border=\"0\" align=\"middle\" /></a>&nbsp;</td><td><a href=\"$url\" class=\"blacklink\">" . $title_parts[0] . "</a><br /><span style=\"color:#808080\">" . $title_parts[1] . "</span></td></tr></table></div>\n";
    }
    
    if ($module_no == 0) {
      echo '<div style="color:#C00000; padding-left:15px"><img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" alt="!" /> <strong>' . $string['warning'] . '</strong> ' . $string['nomodules'] . ' <a href="mailto:' . $support_email . '">' . $support_email . '</div>';
    }

    echo '<br clear="left" /><br />';

    // -- Display papers not assigned to a module -------------------
    $result = $mysqli->prepare("SELECT DISTINCT properties.property_id, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'$cfg_short_date') AS start_date, DATE_FORMAT(start_date,'$cfg_short_date %H:%i') AS display_start_date, DATE_FORMAT(end_date,'%d/%m/%y %H:%i') AS display_end_date, exam_duration, title, initials, surname, retired, properties.password FROM properties LEFT JOIN users ON properties.paper_ownerID=users.id LEFT JOIN papers ON properties.property_id=papers.paper LEFT JOIN properties_modules ON properties.property_id=properties_modules.property_id WHERE paper_ownerID=? AND idMod is NULL AND deleted IS NULL GROUP BY paper_title ORDER BY paper_title");
    $result->bind_param('i', $userObject->get_user_ID());
    $result->execute();
    $result->bind_result($property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password);
    $result->store_result();
    if ($result->num_rows > 0) {
      echo "<table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $string['unassignedpapers'] . " (" . $result->num_rows . ")<nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
      while ($result->fetch()) {
        display_paper_icon($userObject->get_user_ID(), $property_id, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $exam_duration, $title, $initials, $surname, $retired, $password, $userObject);
      }
    }
    $result->close();
  }

  $mysqli->close();



  ?>


</div>
</div>
</body>
</html>

