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
* Shows information on the currently selected user: name, username, email, etc
* plus the details of any taken assessment or survey. SysAdmin users also have the ability
* to edit personal details such as name, username, password, etc.
*
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../include/demo_replace.inc';
require_once '../include/sort.inc';
require_once '../classes/schoolutils.class.php';
require_once '../classes/networkutils.class.php';
require_once '../classes/dateutils.class.php';
require_once '../classes/userutils.class.php';

check_var('userID', 'GET', true, false, false);

if ($userObject->has_role('Demo')) {
  $demo = true;
} else {
  $demo = false;
}

if (isset($_GET['tab'])) {
  $tab = $_GET['tab'];
} else {
  $tab = 'log';
}

function drawTabs($current_tab, $col_span, $right_text, $user_roles, $bg_color) {
  global $string;

  $html = "<tr><td colspan=\"" . ($col_span - 1) . "\" style=\"background-color:$bg_color\">";
  $html .= '<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%"><tr>';

  $tab_array = array('Log');

  if (stripos($user_roles, 'Staff') !== false) {
    $tab_array[] = 'Teams';
  }

  if (stripos($user_roles, 'Admin') !== false and stripos($user_roles, 'SysAdmin') === false) {
    $tab_array[] = 'Admin';
  }

  if (stripos($user_roles, 'Student') !== false or stripos($user_roles, 'Graduate') !== false) {
    $tab_array[] = 'Modules';
    $tab_array[] = 'Notes';
    $tab_array[] = 'Accessibility';
    $tab_array[] = 'Metadata';
  }

  foreach($tab_array as $individual_tab) {
    if ($individual_tab == $current_tab) {
      $html .= "<td style=\"padding-top:0px; cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:100%; background-image:url(../artwork/tab_on.gif)\" onclick=\"showTab('" . $individual_tab . "_tab')\">" . $string[strtolower($individual_tab)] . "</td>";
    } else {
      $html .= "<td style=\"padding-top:0px; cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:100%; background-image:url(../artwork/tab_off.gif)\" onclick=\"showTab('" . $individual_tab . "_tab')\">" . $string[strtolower($individual_tab)] . "</td>";
    }
  }
  $html .= "</tr></table></td><td align=\"right\" style=\"background-color:$bg_color\">$right_text</td></tr>\n";
  return $html;
}

function formatsec($seconds) {
  if ($seconds == '') {
    $timestring = '';
  } else {
    $diff_hour = ($seconds / 60) / 60;
    $tmp_position = strpos($diff_hour, ".");
    if ($tmp_position > 0) $diff_hour = substr($diff_hour, 0, $tmp_position);
    if ($diff_hour > 0) $seconds -= ($diff_hour * 60) * 60;
    $diff_min = $seconds / 60;
    $tmp_position = strpos($diff_min, ".");
    if ($tmp_position > 0) {
      $diff_min = substr($diff_min, 0, $tmp_position);
    }
    if ($diff_min > 0) $seconds -= $diff_min * 60;
    $diff_sec = $seconds;
    $timestring = '';
    if ($diff_hour < 10) $timestring = '0';
    $timestring .= "$diff_hour:";
    if ($diff_min < 10) $timestring .= '0';
    $timestring .= "$diff_min:";
    if ($diff_sec < 10) $timestring .= '0';
    $timestring .= $diff_sec;
  }
  return $timestring;
}

if (isset($_POST['update']) and $demo == false and $userObject->has_role(array('Admin', 'SysAdmin'))) {
  $initials = '';
  $first_names_array = explode(' ', $_POST['first_names']);
  foreach ($first_names_array as $individual_name) {
    $initials .= trim(substr($individual_name,0,1));
  }
  //Update 'users' table.
  $tmp_roles = $_POST['roles'];
  $grade = $_POST['grade'];

  $tmp_first_names = $_POST['first_names'];
  $tmp_surname = $_POST['surname'];
  $tmp_email = $_POST['email'];

  if (isset($_POST['password']) and $_POST['password'] != '') {
    $result = $mysqli->prepare("UPDATE users SET roles = ?, title = ?, initials = ?, surname = ?, grade = ?, yearofstudy = ?, username = ?, password = ?, email = ?, first_names = ?, gender = ? WHERE id = ?");
    $result->bind_param('sssssisssssi', $tmp_roles, $_POST['title'], $initials, $tmp_surname, $grade, $_POST['year'], $_POST['username'], $_POST['password'], $tmp_email, $tmp_first_names, $_POST['gender'], $_POST['old_userID']);
  } else {
    $result = $mysqli->prepare("UPDATE users SET roles = ?, title = ?, initials = ?, surname = ?, grade = ?, yearofstudy = ?, username = ?, email = ?, first_names = ?, gender = ? WHERE id = ?");
    $result->bind_param('sssssissssi', $tmp_roles, $_POST['title'], $initials, $tmp_surname, $grade, $_POST['year'], $_POST['username'], $tmp_email, $tmp_first_names, $_POST['gender'], $_POST['old_userID']);
  }
  $result->execute();
  $result->close();

  //Remove from teams if 'left'.
  if (strtolower($tmp_roles) == 'left') {
    UserUtils::clear_staff_modules_by_userID($_POST['old_userID'], $mysqli);
  }

  // Remove from admin access if role changed from Admin
  if ($tmp_roles != $_POST['prev_roles'] and $_POST['prev_roles'] == 'Staff,Admin') {
    UserUtils::clear_admin_access($_POST['old_userID'], $mysqli);
  }

  $username = $_POST['username'];
  //Update 'sid' table;
  $result = $mysqli->prepare("DELETE FROM sid WHERE userID = ?");
  $result->bind_param('i', $_POST['old_userID']);
  $result->execute();
  $result->close();

  if (isset($_POST['sid']) and $_POST['sid'] != '' and $_POST['sid'] != $string['unknown']) {
    $result = $mysqli->prepare("INSERT INTO sid VALUES (?, ?)");
    $result->bind_param('si', $_POST['sid'], $_POST['old_userID']);
    $result->execute();
    $result->close();
  }
} elseif (isset($_POST['updateadmin'])) {
  UserUtils::clear_admin_access($_GET['userID'], $mysqli);

  for ($i=0; $i<$_POST['admin_school_no']; $i++) {
    if (isset($_POST["sch$i"])) {
      $result = $mysqli->prepare("INSERT INTO admin_access VALUES (NULL, ?, ?)");
      $result->bind_param('ii', $_GET['userID'], $_POST["sch$i"]);
      $result->execute();
      $result->close();
    }
  }
} elseif (isset($_POST['updateaccess']) and $userObject->has_role(array('Admin', 'SysAdmin'))) {
  $background = $_POST['background'];
  if ($_POST['bg_radio'] == '0') $background = NULL;
  $foreground = $_POST['foreground'];
  if ($_POST['fg_radio'] == '0') $foreground = NULL;
  $textsize = $_POST['textsize'];
  $extra_time = $_POST['extra_time'];
  $font = ($_POST['font'] != '') ? $_POST['font'] : NULL;
  $marks_color = $_POST['marks_color'];
  if ($_POST['marks_radio'] == '0') $marks_color = NULL;
  $themecolor = $_POST['themecolor'];
  if ($_POST['theme_radio'] == '0') $themecolor = NULL;
  $labelcolor = $_POST['labelcolor'];
  if ($_POST['labels_radio'] == '0') $labelcolor = NULL;
  $unansweredcolor = $_POST['unansweredcolor'];
  if ($_POST['unanswered_radio'] == '0') $unansweredcolor = NULL;

  $result = $mysqli->prepare("DELETE FROM special_needs WHERE userID = ?");
  $result->bind_param('i', $_GET['userID']);
  $result->execute();
  $result->close();

  if ($background != NULL or $foreground != NULL or $marks_color != NULL or $textsize != 0 or $extra_time != 0 or $font != NULL or $themecolor != NULL or $labelcolor != NULL or $unansweredcolor != NULL) {
    $result = $mysqli->prepare("INSERT INTO special_needs VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result->bind_param('issiisssss', $_GET['userID'], $background, $foreground, $textsize, $extra_time, $marks_color, $themecolor, $labelcolor, $font, $unansweredcolor);
    $result->execute();
    $result->close();

    $result = $mysqli->prepare("UPDATE users SET special_needs = 1 WHERE id = ?");
    $result->bind_param('i', $_GET['userID']);
    $result->execute();
    $result->close();
  }
} elseif (isset($_POST['save_metadata']) and $userObject->has_role(array('Admin', 'SysAdmin'))) {
  for ($i=0; $i<$_POST['metadata_no']; $i++) {
    $result = $mysqli->prepare("REPLACE INTO users_metadata (userID, idMod, type, value, calendar_year) VALUES (?, ?, ?, ?, ?)");
    $result->bind_param('iisss', $_GET['userID'], $_POST["meta_moduleID$i"], $_POST["meta_type$i"], $_POST["meta_value$i"], $_POST["meta_calendar_year$i"]);
    $result->execute();
    $result->close();
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['usermanagement'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    td {padding-top:1px}
    .coltitle {cursor:hand; background-color:#1E3C7B; color:white}
    .sch_check {text-align:right; width:40px; padding-right:6px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script language="javascript">
    function reviewPaper(started, userid, surname, papername, log_type) {
      var winwidth = screen.width - 80;
      var winheight = screen.height - 80;
      window.open("../paper/finish.php?id="+papername+"&previous="+started+"&userid="+userid+"&surname="+surname+"&log_type="+log_type+"","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }

    function showTab(tabID) {
      document.getElementById('Log_tab').style.display = 'none';
      document.getElementById('Modules_tab').style.display = 'none';
      document.getElementById('Admin_tab').style.display = 'none';
      document.getElementById('Notes_tab').style.display = 'none';
      document.getElementById('Accessibility_tab').style.display = 'none';
      document.getElementById('Teams_tab').style.display = 'none';
      document.getElementById('Metadata_tab').style.display = 'none';

      document.getElementById(tabID).style.display = '';
    }

    function newStudentNote() {
      note = window.open("new_student_note.php?userID=<?php echo $_GET['userID']; ?>","note","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        note.focus();
      }
    }

    function addModule() {
      note = window.open("add_student_module.php?userID=<?php echo $_GET['userID']; ?>","module","width=600,height=" + (screen.height - 120) + ",left="+(screen.width/2-300)+",top=50,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        note.focus();
      }
    }

    function editModules(session, grade) {
      editwin=window.open("edit_modules_popup.php?userID=<?php echo $_GET['userID']; ?>&session=" + session + "&grade=" + grade + "","editmodule","width=650,height=750,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
      if (window.focus) {
        editwin.focus();
      }
    }

    function editMultiTeams() {
      editwin=window.open("../folder/edit_multi_teams_popup.php?userID=<?php echo $_GET['userID']; ?>","editmodule","width=550,height=750,left="+(screen.width/2-200)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        editwin.focus();
      }
    }

    function forceResetPassword(username) {
      editwin=window.open("reset_pwd.php?userID=<?php echo $_GET['userID']; ?>","editmodule","width=450,height=400,left="+(screen.width/2-200)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        editwin.focus();
      }
    }

    function resetPassword(email) {
      editwin=window.open("forgotten_password.php?email=" + email + "","editmodule","width=600,height=400,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        editwin.focus();
      }
    }

    function updateAccessDemo() {

      var e = document.getElementById("textsize");
      var textsize = e.options[e.selectedIndex].text;
      if (textsize == '<default>') {
        textsize = '100%';
      }
      document.getElementById('demo_paper_background').style.fontSize = textsize;

      e = document.getElementById("font");
      var font = e.options[e.selectedIndex].text;
      if (font == '<default>') {
        font = 'Arial';
      }
      document.getElementById('demo_paper_background').style.fontFamily = font;

      if (document.getElementById("bg_radio_on").checked) {
        document.getElementById('demo_paper_background').style.backgroundColor = document.getElementById('span_background').style.backgroundColor;
      } else {
        document.getElementById('demo_paper_background').style.backgroundColor = '#FFFFFF';
      }

      if (document.getElementById("fg_radio_on").checked) {
        document.getElementById('demo_paper_background').style.color = document.getElementById('span_foreground').style.backgroundColor;
      } else {
        document.getElementById('demo_paper_background').style.color = '#000000';
      }

      if (document.getElementById("theme_radio_on").checked) {
        document.getElementById('demo_theme').style.color = document.getElementById('span_themecolor').style.backgroundColor;
      } else {
        document.getElementById('demo_theme').style.color = '#316AC5';
      }

      if (document.getElementById("labels_radio_on").checked) {
        document.getElementById('demo_true_label').style.color = document.getElementById('span_labelcolor').style.backgroundColor;
        document.getElementById('demo_false_label').style.color = document.getElementById('span_labelcolor').style.backgroundColor;
      } else {
        document.getElementById('demo_true_label').style.color = '#C00000';
        document.getElementById('demo_false_label').style.color = '#C00000';
      }

      if (document.getElementById("unanswered_radio_on").checked) {
        document.getElementById('demo_unanswered').style.backgroundColor = document.getElementById('span_unansweredcolor').style.backgroundColor;
      } else {
        document.getElementById('demo_unanswered').style.backgroundColor = '#FFC0C0';
      }

      if (document.getElementById("marks_radio_on").checked) {
        document.getElementById('demo_marks').style.color = document.getElementById('span_marks_color').style.backgroundColor;
      } else {
        document.getElementById('demo_marks').style.color = '#808080';
      }
    }

    $(document).ready(updateAccessDemo);
  </script>
</head>

<body>
<?php
  $records_found = 0;
    
  $user_result = $mysqli->prepare("SELECT DISTINCT id, roles, grade, title, initials, first_names, surname, email, yearofstudy, grade, password, gender, username, student_id, user_deleted FROM users LEFT JOIN sid ON users.id = sid.userID WHERE users.id = ?");
  $user_result->bind_param('i', $_GET['userID']);
  $user_result->execute();
  $user_result->bind_result($tmp_id, $tmp_roles, $tmp_grade, $tmp_title, $tmp_initials, $tmp_first_names, $tmp_surname, $email, $tmp_year, $grade, $password, $gender, $username, $student_id, $user_deleted);
  $user_result->store_result();
  $user_result->fetch();
  $records_found = $user_result->num_rows;
  $user_result->close();
  

  if ($records_found == 0) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }

  $needs_result = $mysqli->prepare("SELECT special_id FROM special_needs WHERE userID = ?");
  $needs_result->bind_param('i', $_GET['userID']);
  $needs_result->execute();
  $needs_result->bind_result($special_id);
  if ($needs_result->num_rows > 0) $special_needs = true;
  $needs_result->close();

  require '../tools/colour_picker/colour_picker.inc';
  require '../include/user_search_options.inc';

  $original_username = $username;
  if ($demo == true) {
    // Hide the personal details.
    $tmp_surname = demo_replace($tmp_surname, $demo);
    $tmp_first_names = demo_replace($tmp_first_names, $demo);
    $tmp_initials = demo_replace($tmp_initials, $demo);
    $student_id = demo_replace_number($student_id, $demo);
    $username = demo_replace_username($username, $demo);
    $email = demo_replace_username($email, $demo);
  }

  $tmp_name = $tmp_title . ' ' . $tmp_initials . ' ' . $tmp_surname;

  $description = '';
  $user_query = $mysqli->prepare("SELECT DISTINCT description FROM courses WHERE name = ? LIMIT 1");
  $user_query->bind_param('s', $grade);
  $user_query->execute();
  $user_query->bind_result($description);
  $user_query->fetch();
  $user_query->close();

  if ($user_deleted == '') {
    $bg_color = '#F1F5FB';
  } else {
    $bg_color = '#FFC0C0';
  }
?>
<div id="content" class="content">
<table cellpadding="0" cellspacing="0" border="0" style="background-color:<?php echo $bg_color; ?>; width:100%">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>?userID=<?php echo $_GET['userID']; ?>" method="post">
<?php

  if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    if (strpos($tmp_roles, 'Student') !== false or stripos($tmp_roles, 'graduate') !== false or strpos($tmp_roles, 'left') !== false or strpos($tmp_roles, 'suspended') !== false) {
      $student_photo =  $cfg_web_root . 'users/photos/' . $original_username . '.jpg';
      $row_no = 7;
      if (file_exists($student_photo)) {
        if ($demo == true) {
          echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img style=\"filter:progid:DXImageTransform.Microsoft.Pixelate(maxSquare=8)\" src=\"photos/$original_username.jpg\" width=\"180\" height=\"270\" alt=\"Student Photo\" /></td><td>&nbsp;" . $string['name'] . "</td><td colspan=\"3\">";
        } else {
          echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"photos/$original_username.jpg\" width=\"180\" height=\"270\" alt=\"Student Photo\" &nbsp;" . $string['name'] . "</td><td colspan=\"3\">";
        }
      } else {
        echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" /></td><td>&nbsp;" . $string['name'] . "</td><td colspan=\"3\">";
      }
    } else {
      $row_no = 9;
      echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" /></td><td>&nbsp;" . $string['name'] . "</td><td colspan=\"3\">";
    }
    $title_array = explode(',', $string['title_types']);
    echo '<select name="title">';
    foreach ($title_array as $individual_title) {
      if ($individual_title == $tmp_title) {
        echo '<option value="' . $individual_title . '" selected>' . $individual_title . '</option>';
      } else {
        echo '<option value="' . $individual_title . '">' . $individual_title . '</option>';
      }
    }
    echo "</select>&nbsp;<input type=\"text\" name=\"first_names\" size=\"20\" value=\"$tmp_first_names\" />&nbsp;<input type=\"text\" size=\"15\" name=\"surname\" value=\"$tmp_surname\" /></td><td style=\"text-align:right\"><input type=\"submit\" name=\"update\" value=\"" . $string['update'] . "\" /></td></td></tr>\n";
    echo "<tr><td>&nbsp;" . $string['email'] . "</td><td><input type=\"text\" size=\"35\" name=\"email\" value=\"$email\" /></td>\n";
    if (stripos($tmp_roles, 'Student') !== false or stripos($tmp_roles, 'Graduate') !== false) {
      if ($student_id == '') $student_id = $string['unknown'];
      echo "<td>&nbsp;" . $string['studentid'] . "</td><td colspan=\"2\"><input type=\"text\" size=\"15\" name=\"sid\" value=\"$student_id\" /></td></tr>\n";
    } else {
      echo "<td colspan=\"3\"></td></tr>\n";
    }
    if (stripos($tmp_roles, 'Student') !== false or stripos($tmp_roles,'graduate') !== false or stripos($tmp_roles,'left') !== false or stripos($tmp_roles,'suspended') !== false) {
      // Student editing
      echo "<tr><td>&nbsp;" . $string['course'] . "</td><td><select name=\"grade\" style=\"width:300px\">";
      $found = 0;

      $course_details = $mysqli->prepare("SELECT DISTINCT name, description FROM courses ORDER BY name");
      $course_details->execute();
      $course_details->bind_result($name, $description);
      while ($course_details->fetch()) {
        if ($name == $grade) {
          $found = 1;
          echo "<option value=\"$name\" selected>$name: $description</option>\n";
        } else {
          echo "<option value=\"$name\">$name: $description</option>\n";
        }
      }
      if ($found == 0) echo "<option value=\"" . $grade . "\" selected>" . $grade . ": " . $string['unknown'] . "</option>\n";
      $course_details->close();
      echo "</select></td><td colspan=\"3\">&nbsp;</td></tr>\n";
      echo "<tr><td>&nbsp;" . $string['yearofstudy'] . "</td><td><select name=\"year\">";
      for ($i=1; $i<=6; $i++) {
        if ($i == $tmp_year) {
          echo "<option value=\"$i\" selected>" . $string['year'] . " $i</option>";
        } else {
          echo "<option value=\"$i\">" . $string['year'] . " $i</option>";
        }
      }
      echo "</select></td>";
    } else {
      // Staff editing
      echo "<tr><td>&nbsp;" . $string['type'] . "<input type=\"hidden\" name=\"year\" value=\"$tmp_year\" /></td><td>";
      echo "<select name=\"grade\">\n<option value=\"\"></option>\n";
      ?>
      <option value="University Lecturer"<?php if ($grade == 'University Lecturer' and $tmp_roles != 'inactive') echo ' selected'; ?>><?php echo $string['universitylecturer'] ?></option>
      <option value="University Librarian"<?php if ($grade == 'University Librarian' and $tmp_roles != 'inactive') echo ' selected'; ?>><?php echo $string['universitylibrarian'] ?></option>
      <option value="University Admin"<?php if ($grade == 'University Admin' and $tmp_roles != 'inactive') echo ' selected'; ?>><?php echo $string['universityadmin'] ?></option>
      <option value="Technical Staff"<?php if ($grade == 'Technical Staff' and $tmp_roles != 'inactive') echo ' selected'; ?>><?php echo $string['universitytechnical'] ?></option>
      <option value="NHS Lecturer"<?php if ($grade == 'NHS Lecturer' and $tmp_roles != 'inactive') echo ' selected'; ?>><?php echo $string['nhslecturer'] ?></option>
      <option value="NHS Admin"<?php if ($grade == 'NHS Admin' and $tmp_roles != 'inactive') echo ' selected'; ?>><?php echo $string['nhsadmin'] ?></option>
      <option value="Staff External Examiner"<?php if ($grade == 'Staff External Examiner' and $tmp_roles != 'inactive') echo ' selected'; ?>><?php echo $string['externalexaminer'] ?></option>
      <option value="Invigilator"<?php if ($grade == 'Invigilator' and $tmp_roles != 'inactive') echo ' selected'; ?>><?php echo $string['invigilator'] ?></option>
      <option value="inactive"<?php if ($tmp_roles == 'inactive') echo ' selected'; ?>><?php echo $string['inactivestaff'] ?></option>
      <option value="left"<?php if ($tmp_grade == 'left') echo ' selected'; ?>><?php echo $string['leftuniversity'] ?></option>
      <?php
      echo "</select>\n";
    }
    echo "<td>&nbsp;" . $string['status'] . "</td><td colspan=\"2\"><select name=\"roles\">";
    $old_optgroup = '';

    $roles_array = array('#Staff', 'Staff');
    if ($userObject->has_role('SysAdmin')) {
      $roles_array[] = 'Staff,Admin';
      $roles_array[] = 'Staff,SysAdmin';
    } elseif ($userObject->has_role('Admin')) {
      $roles_array[] = 'Staff,Admin';
    }
    $roles_array[] = 'Staff,Student';
    $roles_array[] = 'External Examiner';
    $roles_array[] = 'Invigilator';
    $roles_array[] = '#Students';
    $roles_array[] = 'Student';
    $roles_array[] = 'Graduate';
    $roles_array[] = 'Left';
    $roles_array[] = 'Suspended';

    foreach ($roles_array as $value) {
      if (substr($value,0,1) == '#') {
        if ($old_optgroup != '') echo "</optgroup>\n";
        echo "<optgroup label=\"" . substr($value,1) . "\">\n";
        $old_optgroup = $value;
      } else {
        $display_val = str_replace(' ', '', $value);
        $display_val = str_replace(',', '', $display_val);
        $display_val = $string[strtolower($display_val)];
        if (strtolower($value) == strtolower($tmp_roles)) {
          echo "<option value=\"$value\" selected>$display_val</option>";
        } else {
          echo "<option value=\"$value\">$display_val</option>";
        }
      }
    }
    echo "</optgroup>\n</select>\n";
    echo "<input type=\"hidden\" name=\"prev_roles\" value=\"$tmp_roles\" /></td></tr>\n";

    if ($userObject->has_role('SysAdmin')) {
      echo "<tr><td>&nbsp;" . $string['username'] . "&nbsp;</td><td><input type=\"text\" size=\"15\" name=\"username\" value=\"$username\" /></td><td>&nbsp;" . $string['password'] . "</td><td colspan=\"2\">";
      if ($configObject->get('cfg_use_ldap') and array_reduce($configObject->get('cfg_institutional_domains'), 'NetworkUtils::check_email_domain')) {
        echo $string['externalauth'];
      } else {
        $url_email = urlencode($email);
        echo "<input type=\"button\" onclick=\"resetPassword('$url_email')\" value=\"{$string['reset']}\" />";

        if ($userObject->has_role('SysAdmin')) {
          echo "&nbsp;<input type=\"button\" onclick=\"forceResetPassword('$username')\" value=\"{$string['forcereset']}\" />";
        }

      }
      echo "<input type=\"hidden\" name=\"old_userID\" value=\"$tmp_id\" /></td></tr>\n";
    } else {
      echo "<tr><td>&nbsp;" . $string['username'] . "&nbsp;</td><td><input type=\"text\" size=\"15\" name=\"uneditableusername\" value=\"$username\" disabled /><input type=\"hidden\" name=\"username\" value=\"$username\" /></td><td colspan=\"2\">&nbsp;</td><td>&nbsp;<input type=\"hidden\" name=\"old_userID\" value=\"$tmp_id\" /></td></tr>\n";
    }
    echo "<tr><td>&nbsp;" . $string['gender'] . "&nbsp;</td><td><select name=\"gender\">\n";
    if ($gender == 'Male') {
      echo "<option value=\"Male\" selected>" . $string['male'] . "</option>\n<option value=\"Female\">" . $string['female'] . "</option>\n";
    } elseif ($gender == 'Female') {
      echo "<option value=\"Male\">" . $string['male'] . "</option>\n<option value=\"Female\" selected>" . $string['female'] . "</option>\n";
    } else {
      echo "<option value=\"\"></option>\n<option value=\"Male\">" . $string['male'] . "</option>\n<option value=\"Female\">" . $string['female'] . "</option>\n";
    }
    echo "</select></td><td>&nbsp;" . $string['databaseid'] . "</td><td colspan=\"2\">" . $_GET['userID'] . "</td></tr>\n";
    echo "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";
  } else {
    if (stripos($tmp_roles, 'Student') !== false) {
      $student_photo = $cfg_web_root . 'users/photos/$username.jpg';
      $row_no = 10;
      if (file_exists($student_photo)) {
        echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"photos/$username.jpg\" width=\"180\" height=\"270\" alt=\"Student Photo\" border=\"0\" /></td><td width=\"110\">&nbsp;Name</td><td>$tmp_title $tmp_initials $tmp_surname</td></tr>\n";
      } else {
        echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" border=\"0\" /></td><td width=\"110\">&nbsp;Name:</td><td>$tmp_title $tmp_initials $tmp_surname</td></tr>\n";
      }
    } else {
      $row_no = 5;
      echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" border=\"0\" /></td><td width=\"110\">&nbsp;Name</td><td>$tmp_title $tmp_initials $tmp_surname</td></tr>\n";
    }
    if (stripos($tmp_roles,'Student') !== false) {
      if ($student_id == '') $student_id = $string['unknown'];
      echo "<tr><td>&nbsp;" . $string['studentid'] . "</td><td>$student_id</td></tr>\n";
    }
    echo "<tr><td>&nbsp;" . $string['email'] . "</td><td><a href=\"mailto:$email\">$email</a></td></tr>\n";
    if (stripos($tmp_roles, 'Student') !== false) {
      echo "<tr><td>&nbsp;" . $string['yearofstudy'] . "</td><td>{$string['year']} $tmp_year</td></tr>\n";
      echo "<tr><td>&nbsp;" . $string['course'] . "</td><td>$grade - $description</td></tr>\n";
    }
    echo "<tr><td>&nbsp;" . $string['username'] . "</td><td>$username</td></tr>\n";
    echo "<tr><td>&nbsp;" . $string['password'] . "</td><td style=\"color:#808080\">&lt;{$string['classifiedinfo']}&gt;</td></tr>\n";
    echo "<tr><td>&nbsp;" . $string['gender'] . "</td><td>$gender</td></tr>\n";
    echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
  }
?>
</form>
</table>
<?php
  if ($tab == 'log') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Log_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Log_tab\" style=\"width:100%; display:none\">\n";
  }
  echo drawTabs('Log', 6, '', $tmp_roles, $bg_color);

  $sortby = 'started';
  if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];

  $ordering = 'desc';
  if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

  $old_q_paper = '';
  $old_started = '';
  $old_duration = 0;
  $old_screen = 0;
  $old_paper_title = '';
  $results_no = 0;
  $paper = array();

  if ($ordering == 'asc') {
    $new_order = 'desc';
  } else {
    $new_order = 'asc';
  }
  if ($sortby == 'q_paper') {
    echo '<tr><td colspan="2" class="coltitle" style="width:240px" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=q_paper&ordering=' . $new_order . '\'">&nbsp;&nbsp;&nbsp;&nbsp;' . $string['papername'] . '&nbsp;<img src="../artwork/' . $new_order . '.gif" width="9" height="7" border="0" /></td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=paper_type&ordering=asc\'">' . $string['type'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=started&ordering=asc\'">' . $string['started'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=duration&ordering=asc\'">' . $string['duration'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=ipaddress&ordering=asc\'">' . $string['ipaddress'] . '&nbsp;</td></tr>';
  } elseif ($sortby == 'paper_type') {
    echo '<tr><td colspan="2" class="coltitle" style="width:240px" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=q_paper&ordering=asc\'">&nbsp;&nbsp;&nbsp;&nbsp;' . $string['papername'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=paper_type&ordering=' . $new_order . '\'">' . $string['type'] . '&nbsp;<img src="../artwork/' . $new_order . '.gif" width="9" height="7" border="0" /></td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=started&ordering=asc\'">' . $string['started'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=duration&ordering=asc\'">' . $string['duration'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=ipaddress&ordering=asc\'">' . $string['ipaddress'] . '&nbsp;</td></tr>';
  } elseif ($sortby == 'started') {
    echo '<tr><td colspan="2" class="coltitle" style="width:240px" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=q_paper&ordering=asc\'">&nbsp;&nbsp;&nbsp;&nbsp;' . $string['papername'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=paper_type&ordering=asc\'">' . $string['type'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=started&ordering=' . $new_order . '\'">' . $string['started'] . '&nbsp;<img src="../artwork/' . $new_order . '.gif" width="9" height="7" border="0" /></td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=duration&ordering=asc\'">' . $string['duration'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=ipaddress&ordering=asc\'">' . $string['ipaddress'] . '&nbsp;</td></tr>';
  } elseif ($sortby == 'duration') {
    echo '<tr><td colspan="2" class="coltitle" style="width:240px" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=q_paper&ordering=asc\'">&nbsp;&nbsp;&nbsp;&nbsp;' . $string['papername'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=paper_type&ordering=asc\'">' . $string['type'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=started&ordering=asc\'">' . $string['started'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=duration&ordering=' . $new_order . '\'">' . $string['duration'] . '&nbsp;<img src="../artwork/' . $new_order . '.gif" width="9" height="7" border="0" /></td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=ipaddress&ordering=asc\'">' . $string['ipaddress'] . '&nbsp;</td></tr>';
  } elseif ($sortby == 'ipaddress') {
    echo '<tr><td colspan="2" class="coltitle" style="width:240px" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=q_paper&ordering=asc\'">&nbsp;&nbsp;&nbsp;&nbsp;' . $string['papername'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=paper_type&ordering=asc\'">' . $string['type'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=started&ordering=asc\'">' . $string['started'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=duration&ordering=asc\'">' . $string['duration'] . '&nbsp;</td><td class="coltitle" onclick="window.location=\'details.php?userID=' . $_GET['userID'] . '&sortby=ipaddress&ordering=' . $new_order . '\'">' . $string['ipaddress'] . '&nbsp;<img src="../artwork/' . $new_order . '.gif" width="9" height="7" border="0" />&nbsp;</td></tr>';
  }

  $stmt = false;

  if ($userObject->has_role(array('Admin', 'SysAdmin')) or $userObject->get_user_ID() == $_GET['userID']) {
    $log_viewable = true;
  } else {
    $idMod = array_keys($userObject->get_staff_modules());
    $log_viewable = UserUtils::is_user_on_module($_GET['userID'], $idMod, '', $mysqli);
  }

  $paper_types = array('Formative Self-Assessment', 'Progress Test', 'Summative Exam', 'Survey', 'OSCE Station', 'Offline Paper', 'Peer Review');

  if (stripos($tmp_roles, 'External Examiner') !== false) {      // Get the papers the External is down to review.
    $external_array = array();

    $stmt = $mysqli->prepare("SELECT DISTINCT crypt_name, paper_title, property_id, paper_type FROM properties LEFT JOIN review_comments ON property_id=review_comments.q_paper AND reviewer=? WHERE deleted IS NULL AND externals LIKE ? AND reviewed IS NULL ORDER BY paper_title");
    $tmp_id_like = '%' . $tmp_id . '%';
    $stmt->bind_param('is', $tmp_id, $tmp_id_like);
    $stmt->execute();
    $stmt->bind_result($crypt_name, $paper_title, $property_id, $paper_type);
    while ($stmt->fetch()) {
      $paper[$results_no]['crypt_name'] = $crypt_name;
      $paper[$results_no]['q_paper'] = $paper_title;
      $paper[$results_no]['id'] = $property_id;
      $paper[$results_no]['paper_type'] = '2';
      $paper[$results_no]['started'] = '';
      $paper[$results_no]['display_started'] = '';
      $paper[$results_no]['duration'] = '';
      $paper[$results_no]['mark'] = '';
      $paper[$results_no]['totalpos'] = '';
      $paper[$results_no]['ipaddress'] = '';
      $results_no++;
    }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT crypt_name, paper_title, paper_type, q_paper, DATE_FORMAT(reviewed,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(reviewed,'{$configObject->get('cfg_long_date_time')}') AS display_started, duration, screen, ipaddress FROM (properties, review_comments) WHERE properties.property_id=review_comments.q_paper AND reviewer=? ORDER BY q_paper, started, screen");
    $stmt->bind_param('i', $tmp_id);
  } elseif ($log_viewable) {
    // Only allow Admin/SysAdmin or current user to view this information
    $queries = array();
    $queries[] = "SELECT crypt_name, paper_title, 0 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, duration, screen, ipaddress FROM properties, log0, log_metadata WHERE log0.metadataID = log_metadata.id AND properties.property_id = log_metadata.paperID AND log_metadata.userID = ? ORDER BY started, screen";
    $queries[] = "SELECT crypt_name, paper_title, 1 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, duration, screen, ipaddress FROM properties, log1, log_metadata WHERE log1.metadataID = log_metadata.id AND properties.property_id = log_metadata.paperID AND log_metadata.userID = ? ORDER BY started, screen";
    $queries[] = "SELECT crypt_name, paper_title, 2 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, duration, screen, ipaddress FROM properties, log2, log_metadata WHERE log2.metadataID = log_metadata.id AND properties.property_id = log_metadata.paperID AND log_metadata.userID = ? ORDER BY started, screen";
    $queries[] = "SELECT crypt_name, paper_title, 3 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, duration, screen, ipaddress FROM properties, log3, log_metadata WHERE log3.metadataID = log_metadata.id AND properties.property_id = log_metadata.paperID AND log_metadata.userID = ? ORDER BY started, screen";
    $queries[] = "SELECT crypt_name, paper_title, 4 AS paper_type, q_paper, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, NULL AS duration, NULL AS screen, NULL AS ipaddress FROM properties, log4_overall WHERE properties.property_id = log4_overall.q_paper AND userID = ? ORDER BY started, screen";
    $queries[] = "SELECT crypt_name, paper_title, 5 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, NULL AS duration, NULL AS screen, NULL AS ipaddress FROM properties, log5, log_metadata WHERE log5.metadataID = log_metadata.id AND properties.property_id = log_metadata.paperID AND log_metadata.userID = ? ORDER BY started, screen";
    $queries[] = "SELECT crypt_name, paper_title, 6 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, NULL AS duration, NULL AS screen, NULL AS ipaddress FROM properties, log6 WHERE properties.property_id = log6.paperID AND reviewerID = ? ORDER BY started, screen";

    foreach ($queries as $query_sql) {
      $stmt = $mysqli->prepare($query_sql);
      $stmt->bind_param('i', $tmp_id);
      $stmt->execute();
      $stmt->bind_result($crypt_name, $paper_title, $paper_type, $q_paper, $started, $display_started, $duration, $screen, $ipaddress);
      while ($stmt->fetch()) {
        if ($old_q_paper != $q_paper or $old_started != $started) {
          if ($old_q_paper != '') {
            $paper[$results_no]['crypt_name']       = $old_crypt_name;
            $paper[$results_no]['q_paper']          = $old_paper_title;
            $paper[$results_no]['id']               = $old_q_paper;
            $paper[$results_no]['type']             = $old_paper_type;
            $paper[$results_no]['paper_type']       = $paper_types[$old_paper_type];
            $paper[$results_no]['started']          = $old_started;
            $paper[$results_no]['display_started']  = $old_display_started;
            $paper[$results_no]['duration']         = $old_duration;
            $paper[$results_no]['ipaddress']        = $old_ipaddress;
            $results_no++;
          }
          $old_screen   = 0;
          $old_duration = 0;
        }
        if ($old_screen != $screen) {
          $old_duration += $duration;
        }
        $old_crypt_name       = $crypt_name;
        $old_q_paper          = $q_paper;
        $old_started          = $started;
        $old_display_started  = $display_started;
        $old_paper_type       = $paper_type;
        $old_screen           = $screen;
        $old_paper_title      = $paper_title;
        $old_ipaddress        = $ipaddress;
      }
      $stmt->close();
    }

    if ($old_q_paper != '') {
      $paper[$results_no]['crypt_name']       = $old_crypt_name;
      $paper[$results_no]['q_paper']          = $old_paper_title;
      $paper[$results_no]['id']               = $old_q_paper;
      $paper[$results_no]['type']             = $old_paper_type;
      $paper[$results_no]['paper_type']       = $paper_types[$old_paper_type];
      $paper[$results_no]['started']          = $old_started;
      $paper[$results_no]['display_started']  = $old_display_started;
      $paper[$results_no]['duration']         = $old_duration;
      $paper[$results_no]['ipaddress']        = $old_ipaddress;
      $results_no++;
    }

    // Add in feedback
    $stmt = $mysqli->prepare("SELECT page, ipaddress, DATE_FORMAT(accessed, '%Y%m%d%H%i%s') AS accessed, DATE_FORMAT(accessed,'{$configObject->get('cfg_long_date_time')}') AS display_started, crypt_name, type, paper_title FROM access_log, properties WHERE access_log.page = properties.property_id AND userID = ?");
    $stmt->bind_param('i', $_GET['userID']);
    $stmt->execute();
    $stmt->bind_result($page, $ipaddress, $accessed, $display_started, $crypt_name, $type, $paper_title);
    while ($stmt->fetch()) {
      $paper[$results_no]['crypt_name']       = $crypt_name;
      $paper[$results_no]['q_paper']          = $paper_title;
      $paper[$results_no]['id']               = $page;
      $paper[$results_no]['type']             = $type;
      $paper[$results_no]['paper_type']       = $type;
      $paper[$results_no]['started']          = $accessed;
      $paper[$results_no]['display_started']  = $display_started;
      $paper[$results_no]['duration']         = 'N/A';
      $paper[$results_no]['ipaddress']        = $ipaddress;
      $results_no++;
    }
    $stmt->close();

    // Add in any access denied warnings
    $stmt = $mysqli->prepare("SELECT page, ipaddress, DATE_FORMAT(tried, '%Y%m%d%H%i%s') AS tried, DATE_FORMAT(tried,'{$configObject->get('cfg_long_date_time')}') AS display_started, title FROM denied_log WHERE userID = ?");
    $stmt->bind_param('i', $_GET['userID']);
    $stmt->execute();
    $stmt->bind_result($page, $ipaddress, $tried, $display_started, $title);
    while ($stmt->fetch()) {
      $paper[$results_no]['crypt_name']       = '';
      $paper[$results_no]['q_paper']          = '/' . $page;
      $paper[$results_no]['type']             = $title;
      $paper[$results_no]['paper_type']       = $title;
      $paper[$results_no]['started']          = $tried;
      $paper[$results_no]['display_started']  = $display_started;
      $paper[$results_no]['duration']         = 'N/A';
      $paper[$results_no]['ipaddress']        = $ipaddress;
      $results_no++;
    }
    $stmt->close();

    if ($results_no > 0) {
      $paper = array_csort($paper, $sortby, $ordering);
    }

    for ($i=0; $i<$results_no; $i++) {
      if (strpos($paper[$i]['q_paper'],'[deleted') !== false ) {
        $paper[$i]['q_paper'] = '<span style="color:#808080; text-decoration:line-through">' . $paper[$i]['q_paper'] . '</span>';
      }
      switch ($paper[$i]['type']) {
        case '0':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "','" . $_GET['userID'] . "','" . str_replace("'","&#8217;",$tmp_surname) . "','" . $paper[$i]['crypt_name'] . "'," . $paper[$i]['type'] . "); return false;\"><img src=\"../artwork/formative_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $tmp_surname . "\" /></a></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . formatsec($paper[$i]['duration']) . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case '1':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "','" . $_GET['userID'] . "','" . str_replace("'","&#8217;",$tmp_surname) . "','" . $paper[$i]['crypt_name'] . "'," . $paper[$i]['type'] . "); return false;\"><img src=\"../artwork/progress_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $tmp_surname . "\" /></a></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . formatsec($paper[$i]['duration']) . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case '2':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "','" . $_GET['userID'] . "','" . str_replace("'","&#8217;",$tmp_surname) . "','" . $paper[$i]['crypt_name'] . "'," . $paper[$i]['type'] . "); return false;\"><img src=\"../artwork/summative_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $tmp_surname . "\" /></a></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\"";
          if ($paper[$i]['started'] == '') echo ' style="color:red"';
          echo ">" . $paper[$i]['q_paper'] . "</a></td><td";
          if ($paper[$i]['started'] == '') echo ' style="color:red"';
          echo ">" . $string['summative'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . formatsec($paper[$i]['duration']) . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case '3':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><img src=\"../artwork/survey_16.gif\" width=\"16\" height=\"16\" alt=\"Survey data is anonymous, no entry.\" /></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\" class=\"paper\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $string['survey'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . formatsec($paper[$i]['duration']) . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case '4':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><a href=\"#\" onclick=\"reviewOSCE('" . $paper[$i]['started'] . "','$username','" . str_replace("'","&#8217;",$tmp_surname) . "','" . $paper[$i]['crypt_name'] . "'," . $paper[$i]['type'] . "); return false;\"><img src=\"../artwork/osce_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $tmp_surname . "\" /></a></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td></tr>\n";
          break;
        case '5':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><img src=\"../artwork/offline_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>&nbsp;" . $paper[$i]['q_paper'] . "</td><td>" . $string['offlinepaper'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td></tr>\n";
          break;
        case '6':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><img src=\"../artwork/peer_review_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td></tr>\n";
          break;
        case 'Objectives-based feedback report':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><img src=\"../artwork/objectives_feedback_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>Objectives Feedback report</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case 'Question-based feedback report':
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><img src=\"../artwork/questions_feedback_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>Questions Feedback report</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case $string['pagenotfound']:
          echo "<tr style=\"height:17px; color:#C00000\"><td style=\"text-align:right\"><img src=\"../artwork/access_denied_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>&nbsp;" . $paper[$i]['q_paper'] . "</td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case $string['accessdenied']:
          echo "<tr style=\"height:17px; color:#C00000\"><td style=\"text-align:right\"><img src=\"../artwork/access_denied_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>&nbsp;" . $paper[$i]['q_paper'] . "</td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
      }
    }
    if ($results_no == 0) {
      echo "<tr><td colspan=\"8\" style=\"color:#808080; text-align:center\">" . $string['noassessmentstaken'] . "</td></tr>\n";
    }
  } else {
    echo "<tr><td colspan=\"5\" style=\"color:#808080; text-align:center\">&lt;classified information&gt;</td></tr>\n";
  }
?>
</table>

<?php
  if ($tab == 'modules') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Modules_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Modules_tab\" style=\"width:100%; display:none\">\n";
  }

  $results = $mysqli->prepare("SELECT MAX(calendar_year) AS calendar_year FROM modules_student");
  $results->execute();
  $results->bind_result($most_recent_year);
  $results->fetch();
  $results->close();

  echo drawTabs('Modules', 4, '', $tmp_roles, $bg_color);
  echo "<tr><td class=\"coltitle\" style=\"width:20px\">&nbsp;</td><td class=\"coltitle\">&nbsp;" . $string['moduleid'] . "</td><td class=\"coltitle\">" . $string['name'] . "</td><td class=\"coltitle\">" . $string['academicyear'] . "</td></tr>\n";
  $old_year = '';
  $row_no = 0;
  $user_modules = array();
  $current_year = false;

  $results = $mysqli->prepare("SELECT DISTINCT modules.id, modules.moduleid, fullname, modules_student.calendar_year, attempt FROM (modules_student, modules) WHERE modules_student.idMod=modules.id AND userID=? ORDER BY modules_student.calendar_year DESC, modules.moduleid");
  $results->bind_param('i', $tmp_id);
  $results->execute();
  $results->store_result();
  $results->bind_result($idMod, $moduleid, $fullname, $calendar_year, $attempt);
  while ($results->fetch()) {
    $user_modules[$row_no]['moduleid'] = $moduleid;
    $user_modules[$row_no]['fullname'] = $fullname;
    $user_modules[$row_no]['calendar_year'] = $calendar_year;
    $user_modules[$row_no]['attempt'] = $attempt;
    $user_modules[$row_no]['idMod'] = $idMod;
    if ($calendar_year == date_utils::get_current_academic_year()) {
      $current_year = true;
    }
    $row_no++;
  }
  $results->close();

  if ($current_year == false) {
    echo "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . date_utils::get_current_academic_year();
    if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
      echo "&nbsp;&nbsp;<a href=\"#\" style=\"color:blue\" onclick=\"editModules('" . date_utils::get_current_academic_year() . "','$grade'); return false;\"><img src=\"../artwork/pencil_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['editmodules'] . "\" /></a>";
    }
    echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  }

  for ($i=0; $i<$row_no; $i++) {
    if ($user_modules[$i]['calendar_year'] != $old_year) {
      echo "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $user_modules[$i]['calendar_year'];
      if (($user_modules[$i]['calendar_year'] == $most_recent_year or $user_modules[$i]['calendar_year'] == date_utils::get_current_academic_year()) and $userObject->has_role(array('Admin', 'SysAdmin'))) {
        echo "&nbsp;&nbsp;<a href=\"#\" style=\"color:blue\" onclick=\"editModules('" . $user_modules[$i]['calendar_year'] . "','$grade'); return false;\"><img src=\"../artwork/pencil_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['editmodules'] . "\" /></a>";
      }
      echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    }
    echo "<tr><td></td><td><a style=\"color:blue\" href=\"../folder/details.php?module={$user_modules[$i]['idMod']}\">{$user_modules[$i]['moduleid']}</a></td><td>&nbsp;<a style=\"color:blue\" href=\"../folder/details.php?module={$user_modules[$i]['idMod']}\">{$user_modules[$i]['fullname']}</a></td><td>{$user_modules[$i]['calendar_year']}</td></tr>\n";
    $old_year = $user_modules[$i]['calendar_year'];
  }

?>
</table>

<?php
  if ($tab == 'admin') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Admin_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Admin_tab\" style=\"width:100%; display:none\">\n";
  }
  echo "<form name=\"accessibility\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=$tmp_id&tab=admin\" method=\"post\">";

  echo drawTabs('Admin', 1, '', $tmp_roles, $bg_color);
  echo "<tr><td class=\"coltitle\">&nbsp;</td></tr>\n";
  echo "<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n";

  $current_schools = SchoolUtils::get_admin_schools($_GET['userID'], $mysqli);

  $old_faculty = '';
  $admin_school_no = 0;
  $results = $mysqli->prepare("SELECT schools.id, faculty.name, school FROM schools, faculty WHERE schools.facultyID=faculty.id ORDER BY faculty.name, school");
  $results->execute();
  $results->bind_result($schoolID, $faculty, $school);
  while ($results->fetch()) {
    if ($old_faculty != $faculty) {
      echo '<tr><td colspan="2"><table border="0" style="padding-top:5px; width:100%; color:#1E3287"><tr><td><nobr>' . $faculty . '</nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>';
    }

    if (!$userObject->has_role('SysAdmin')) {
      if (in_array($schoolID, $current_schools)) {
      echo "<tr><td style=\"padding-left:20px\">$school</td></tr>\n";
      }
    } else {
      echo '<tr><td class="sch_check">';
      if (in_array($schoolID, $current_schools)) {
        echo "<input type=\"checkbox\" name=\"sch" . $admin_school_no . "\" value=\"$schoolID\" checked />";
      } else {
        echo "<input type=\"checkbox\" name=\"sch" . $admin_school_no . "\" value=\"$schoolID\" />";
      }
      echo "</td><td>$school</td></tr>\n";
    }

    $old_faculty = $faculty;
    $admin_school_no++;
  }
  $results->close();
  echo "</table>\n</td></tr>\n";
  if ($userObject->has_role(array('SysAdmin', 'Admin'))) {
    echo '<tr><td colspan="2" align="center"><input type="submit" name="updateadmin" value="' . $string['save'] . '" style="width:100px" /><input type="hidden" name="admin_school_no" value="' . $admin_school_no . '" /></td></tr>';
  }
  ?>
  </form>
  </table>
  <?php

  if ($tab == 'notes') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Notes_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Notes_tab\" style=\"width:100%; display:none\">\n";
  }
  $link_html = '<nobr><img src="../artwork/shortcut.png" onclick="newStudentNote()" width="10" height="10" border="0" />&nbsp;<a href="" onclick="newStudentNote(); return false;" class="access">' . $string['createnote'] . '</a>&nbsp;</nobr>';
  echo drawTabs('Notes', 4, $link_html, $tmp_roles, $bg_color);
  echo "<tr><td class=\"coltitle\">&nbsp;&nbsp;&nbsp;" . $string['date'] . "</td><td class=\"coltitle\">" . $string['paper'] . "</td><td class=\"coltitle\">" . $string['note'] . "</td><td class=\"coltitle\">" . $string['author'] . "</td></tr>\n";

  $results = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date, \" {$configObject->get('cfg_short_date')}\"), paper_id, paper_title, CONCAT(title, ' ', initials, ' ', surname) AS note_author FROM (student_notes, properties, users) WHERE student_notes.paper_id=properties.property_id AND student_notes.note_authorID = users.id AND student_notes.userID = ?");
  $results->bind_param('i', $tmp_id);
  $results->execute();
  $results->store_result();
  $results->bind_result($note, $note_date, $note_paper_id, $paper_title, $note_author);
  while ($results->fetch()) {
    echo "<tr><td><nobr>&nbsp;<img src=\"../artwork/notes_icon.gif\" width=\"14\" height=\"14\" alt=\"Note\" />&nbsp;$note_date</nobr></td><td style=\"padding-right:20px\"><nobr><a href=\"../paper/details.php?paperID=$note_paper_id\">$paper_title</a></nobr></td><td>$note</td><td>$note_author</td></tr>";
  }
  $results->close();
?>
</table>

<?php
  if ($tab == 'accessibility') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Accessibility_tab\" style=\"width:100%; text-align:left\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Accessibility_tab\" style=\"width:100%; text-align:left; display:none\">\n";
  }
  echo "<form name=\"accessibility\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=$tmp_id&tab=accessibility\" method=\"post\">";
  echo drawTabs('Accessibility', 1, '', $tmp_roles, $bg_color);
  echo "<tr><td class=\"coltitle\">&nbsp;</td></tr>\n";
  echo "<tr><td align=\"center\"><table cellspacing=\"1\" cellpadding=\"1\" border=\"0\" style=\"padding-top:20px; text-align:left\">";

  $result = $mysqli->prepare("SELECT background, foreground, textsize, extra_time, marks_color, themecolor, labelcolor, font, unanswered FROM special_needs WHERE userID = ? LIMIT 1");
  $result->bind_param('i', $tmp_id);
  $result->execute();
  $result->store_result();
  $result->bind_result($background, $foreground, $textsize, $extra_time, $marks_color, $themecolor, $labelcolor, $font, $unansweredcolor);
  $result->fetch();
  if ($result->num_rows > 0) {
    $special_needs = true;
  }
  if (!isset($background))  $background = '';
  if (!isset($foreground))  $foreground = '';
  if (!isset($themecolor))  $themecolor = '';
  if (!isset($labelcolor))  $labelcolor = '';
  if (!isset($marks_color)) $marks_color = '';
  if (!isset($textsize))    $textsize = 0;
  if (!isset($extra_time))  $extra_time = 0;
  if (!isset($font))        $font = '';
  if (!isset($unansweredcolor)) $unansweredcolor = '';
  $result->close();
?>
<tr>
<td><?php echo $string['extratime']; ?></td>
<td colspan="2">
<select name="extra_time">
<option value="0"><?php echo $string['noextratime']; ?></option>
<?php
  $times = array(5, 10, 25, 33, 50, 100, 200, 300);
  foreach ($times as $individual_time) {
    if ($individual_time == $extra_time) {
      echo "<option value=\"$individual_time\" selected>$individual_time%</option>\n";
    } else {
      echo "<option value=\"$individual_time\">$individual_time%</option>\n";
    }
  }
?>
</select>
</td>
<td rowspan="11" style="width:40px">&nbsp;</td>
<td rowspan="11" style="font-size:110%">
<div id="demo_paper_background" style="width:450px; height:300px; border:1px solid #EAEAEA; box-shadow: 3px 3px 4px #808080; padding:15px; float:right">

<span id="demo_theme" style="font-size:150%; font-weight:bold; color:#316AC5">Cities</span>

<p>1. &nbsp;Which of the following are European cities?</p>

<table cellspacing="0" cellpadding="2" border="0" style="margin-left:30px; width:200px">
<tr><td style="text-align:center; color:#C00000" id="demo_true_label">True</td><td style="text-align:center; color:#C00000" id="demo_false_label">False</td><td></td>
<tr><td style="text-align:center"><input type="radio" name="q1" value="t" checked="checked" /></td><td style="text-align:center"><input type="radio" name="q1" value="f" /></td><td>London</td></tr>
<tr><td style="text-align:center"><input type="radio" name="q2" value="t" /></td><td style="text-align:center"><input type="radio" name="q2" value="f" checked="checked" /></td><td>New York</td></tr>
<tr id="demo_unanswered" style="background-color:#FFC0C0"><td style="text-align:center"><input type="radio" name="q3" value="t" /></td><td style="text-align:center"><input type="radio" name="q3" value="f" /></td><td>Paris</td></tr>
</table>
<br />
<span id="demo_marks" style="font-size:90%; color:#808080">(3 marks)</span>

</div>

</td>
</tr>
<tr>
<td><?php echo $string['fontsize']; ?></td>
<td colspan="2">
<select name="textsize" id="textsize" onchange="updateAccessDemo()">
<option value="0"><?php echo $string['angledefault']; ?></option>
<?php
  $fontsizes = array(90, 100, 110, 120, 130, 140, 150, 175, 200, 300, 400);
  foreach ($fontsizes as $individual_fontsize) {
    if ($individual_fontsize == $textsize) {
      echo "<option value=\"$individual_fontsize\" selected>$individual_fontsize%</option>\n";
    } else {
      echo "<option value=\"$individual_fontsize\">$individual_fontsize%</option>\n";
    }
  }
?>
</select>
</td>
</tr>
<tr>
<td><?php echo $string['typeface']; ?></td>
<td colspan="2">
<select name="font" id="font" onchange="updateAccessDemo()">
<option value=""><?php echo $string['angledefault']; ?></option>
<?php
  $fontfamily = array('Arial', 'Arial Black', 'Calibri', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Tahoma', 'Times New Roman', 'Verdana');
  foreach ($fontfamily as $individual_fontfamily) {
    if ($individual_fontfamily == $font) {
      echo "<option style=\"font-family:$individual_fontfamily\" value=\"$individual_fontfamily\" selected>$individual_fontfamily</option>\n";
    } else {
      echo "<option style=\"font-family:$individual_fontfamily\" value=\"$individual_fontfamily\">$individual_fontfamily</option>\n";
    }
  }
?>
</select>
</td>
</tr>
<tr>
<td><?php echo $string['background']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="bg_radio" value="0"<?php if ($background == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="bg_radio" id="bg_radio_on" value="1"<?php if ($background != '') echo ' checked'; ?> />
<?php
  if ($background == '') {
    echo "<div onclick=\"showPicker('background',event); document.getElementById('bg_radio_on').checked=true;\" id=\"span_background\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"background\" name=\"background\" value=\"$background\" />";
  } else {
    echo "<div onclick=\"showPicker('background',event); document.getElementById('bg_radio_on').checked=true;\" id=\"span_background\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$background\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"background\" name=\"background\" value=\"$background\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['foreground']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="fg_radio" value="0"<?php if ($foreground == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="fg_radio" id="fg_radio_on" value="1"<?php if ($foreground != '') echo ' checked'; ?> />
<?php
  if ($foreground == '') {
    echo "<div onclick=\"showPicker('foreground',event); document.getElementById('fg_radio_on').checked=true;\" id=\"span_foreground\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"foreground\" name=\"foreground\" value=\"$foreground\" />";
  } else {
    echo "<div onclick=\"showPicker('foreground',event); document.getElementById('fg_radio_on').checked=true;\" id=\"span_foreground\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$foreground\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"foreground\" name=\"foreground\" value=\"$foreground\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['markscolour']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="marks_radio" value="0"<?php if ($marks_color == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="marks_radio" id="marks_radio_on" value="1"<?php if ($marks_color != '') echo ' checked'; ?> />
<?php
  if ($marks_color == '') {
    echo "<div onclick=\"showPicker('marks_color',event); document.getElementById('marks_radio_on').checked=true;\" id=\"span_marks_color\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"marks_color\" name=\"marks_color\" value=\"$marks_color\" />";
  } else {
    echo "<div onclick=\"showPicker('marks_color',event); document.getElementById('marks_radio_on').checked=true;\" id=\"span_marks_color\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$marks_color\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"marks_color\" name=\"marks_color\" value=\"$marks_color\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['themecolour']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="theme_radio" value="0"<?php if ($themecolor == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="theme_radio" id="theme_radio_on" value="1"<?php if ($themecolor != '') echo ' checked'; ?> />
<?php
  if ($themecolor == '') {
    echo "<div onclick=\"showPicker('themecolor',event); document.getElementById('theme_radio_on').checked=true;\" id=\"span_themecolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" />";
  } else {
    echo "<div onclick=\"showPicker('themecolor',event); document.getElementById('theme_radio_on').checked=true;\" id=\"span_themecolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$themecolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['labelscolour']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="labels_radio" value="0"<?php if ($labelcolor == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="labels_radio" id="labels_radio_on" value="1"<?php if ($labelcolor != '') echo ' checked'; ?> />
<?php
  if ($labelcolor == '') {
    echo "<div onclick=\"showPicker('labelcolor',event); document.getElementById('labels_radio_on').checked=true;\" id=\"span_labelcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" />";
  } else {
    echo "<div onclick=\"showPicker('labelcolor',event); document.getElementById('labels_radio_on').checked=true;\" id=\"span_labelcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$labelcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['unanswered']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="unanswered_radio" value="0"<?php if ($unansweredcolor == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" onchange="updateAccessDemo()" name="unanswered_radio" id="unanswered_radio_on" value="1"<?php if ($unansweredcolor != '') echo ' checked'; ?> />
<?php
  if ($unansweredcolor == '') {
    echo "<div onclick=\"showPicker('unansweredcolor',event); document.getElementById('unanswered_radio_on').checked=true;\" id=\"span_unansweredcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"unansweredcolor\" name=\"unansweredcolor\" value=\"$unansweredcolor\" />";
  } else {
    echo "<div onclick=\"showPicker('unansweredcolor',event); document.getElementById('unanswered_radio_on').checked=true;\" id=\"span_unansweredcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$unansweredcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"unansweredcolor\" name=\"unansweredcolor\" value=\"$unansweredcolor\" />";
  }
?>
</td>
</tr>
<tr><td colspan="3">&nbsp;</td></tr>
<?php
if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
  echo "<tr><td colspan=\"3\" align=\"center\"><input type=\"submit\" name=\"updateaccess\" value=\"" . $string['save'] . "\" style=\"width:100px\" /></td></tr>\n";
}
?>
</table>


</td>
</tr>
</form>
</table>

<?php
  $metadata_no = 0;
  if ($tab == 'metadata') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Metadata_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Metadata_tab\" style=\"width:100%; display:none\">\n";
  }
  echo "<form name=\"metadata\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=$tmp_id&tab=metadata\" method=\"post\">";
  echo drawTabs('Metadata', 5, '', $tmp_roles, $bg_color);
  echo "<tr><td class=\"coltitle\">&nbsp;" . $string['moduleid'] . "</td><td class=\"coltitle\">" . $string['academicyear'] . "</td><td class=\"coltitle\">" . $string['type'] . "</td><td class=\"coltitle\">" . $string['value'] . "</td><td class=\"coltitle\" style=\"width:30%\">&nbsp;</td></tr>\n";
  $stmt = $mysqli->prepare("SELECT modules.id, modules.moduleID, fullname, calendar_year, type, value FROM users_metadata, modules WHERE users_metadata.idMod=modules.id AND userID=?");
  $stmt->bind_param('i', $_GET['userID']);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($mod_id, $moduleID, $fullname, $calendar_year, $type, $value);
  while ($stmt->fetch()) {
    echo "<tr><td>&nbsp;$moduleID: $fullname<input type=\"hidden\" name=\"meta_moduleID$metadata_no\" value=\"$mod_id\" /></td><td>$calendar_year<input type=\"hidden\" name=\"meta_calendar_year$metadata_no\" value=\"$calendar_year\" /></td><td>$type<input type=\"hidden\" name=\"meta_type$metadata_no\" value=\"$type\" /></td><td><select name=\"meta_value$metadata_no\">";
    $result = $mysqli->prepare("SELECT DISTINCT value FROM users_metadata WHERE calendar_year = ? AND idMod = ? AND type = ?");
    $result->bind_param('sis', $calendar_year, $mod_id, $type);
    $result->execute();
    $result->store_result();
    $result->bind_result($unique_value);
    while ($result->fetch()) {
      if ($unique_value == $value) {
        echo "<option value=\"$unique_value\" selected>$unique_value</option>\n";
      } else {
        echo "<option value=\"$unique_value\">$unique_value</option>\n";
      }
    }
    $result->close();
    echo "</select></td><td></td></tr>\n";
    $metadata_no++;
  }
  $stmt->close();

  echo "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";
  if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    echo "<tr><td colspan=\"5\" style=\"text-align:center\"><input type=\"submit\" name=\"save_metadata\" value=\"" . $string['save'] . "\" style=\"width:100px\" /><input type=\"hidden\" name=\"metadata_no\" value=\"$metadata_no\" /></td></tr>\n";
  }
?>
</form>
</table>

<?php
  if ($tab == 'teams') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Teams_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Teams_tab\" style=\"width:100%; display:none\">\n";
  }
  echo drawTabs('Teams', 4, '', $tmp_roles, $bg_color);
  echo "<tr><td class=\"coltitle\">&nbsp;" . $string['team'] . "</td><td class=\"coltitle\">&nbsp;</td><td class=\"coltitle\">" . $string['dateadded'] . "</td><td class=\"coltitle\">" . $string['type'] . "</td></tr>\n";
  if ($userObject->has_role('Admin') or $userObject->has_role('SysAdmin')) {
    echo "<tr><td colspan=\"4\">&nbsp;<img onclick=\"editMultiTeams(); return false;\" src=\"../artwork/pencil_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['editteams'] . "\" />&nbsp;<a href=\"\" onclick=\"editMultiTeams(); return false;\">" . $string['editteams'] . "</a></td></tr>\n";
  }

  if ($userObject->has_role(array('SysAdmin', 'Admin')) or $userObject->get_user_ID() == $_GET['userID']) {   // Only allow Admin/SysAdmin or current user to view this information
    $result = $mysqli->prepare("SELECT moduleID, fullname, DATE_FORMAT(added,'%d/%m/%Y') AS added, type FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND memberID = ? ORDER BY moduleID");
    $result->bind_param('i', $tmp_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($moduleID, $fullname, $added, $type);
    while ($result->fetch()) {
      echo "<tr><td>&nbsp;$moduleID</td><td>$fullname</td><td>$added</td><td>" . $string[strtolower($type)] . "</td></tr>\n";
    }
    $result->close();
  } else {
    echo "<tr><td colspan=\"4\" style=\"color:#808080; text-align:center\">&lt;classified information&gt;</td></tr>\n";
  }

  $mysqli->close();
?>
</table>
</div>

</body>
</html>
