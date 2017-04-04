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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/demo_replace.inc';

$userID = check_var('userID', 'GET', true, false, true, param::INT);
$student_id = check_var('student_id', 'GET', false, false, true, param::TEXT);
$search_surname = check_var('search_surname', 'GET', false, false, true, param::TEXT);
$search_username = check_var('search_username', 'GET', false, false, true, param::TEXT);

if ($userObject->has_role('Demo')) {
  $demo = true;
} else {
  $demo = false;
}

$tab = param::optional('tab', 'log', param::ALPHA, param::FETCH_GET);

function drawTabs($current_tab, $col_span, $right_text, $user_roles, $bg_color, $string) {
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
      $html .= "<td class=\"tabon\" onclick=\"showTab('" . $individual_tab . "_tab')\">" . $string[strtolower($individual_tab)] . "</td>";
    } else {
      $html .= "<td class=\"taboff\" onclick=\"showTab('" . $individual_tab . "_tab')\">" . $string[strtolower($individual_tab)] . "</td>";
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

$updateadmin = param::optional('updateadmin', null, param::ALPHA, param::FETCH_POST);
$updateaccess = param::optional('updateaccess', null, param::ALPHA, param::FETCH_POST);
$save_metadata = param::optional('save_metadata', null, param::ALPHA, param::FETCH_POST);

if (!is_null($updateadmin) and $userObject->has_role('SysAdmin')) {
  UserUtils::clear_admin_access($userID, $mysqli);

  $admin_school_no = param::optional('admin_school_no', 0, param::INT, param::FETCH_POST);
  for ($i=0; $i < $admin_school_no; $i++) {
    $school = param::optional("sch$i", null, param::INT, param::FETCH_POST);
    if (!is_null($school)) {
      $result = $mysqli->prepare("INSERT INTO admin_access VALUES (NULL, ?, ?)");
      $result->bind_param('ii', $userID, $school);
      $result->execute();
      $result->close();
    }
  }
} elseif (!is_null($updateaccess) and $userObject->has_role(array('Admin', 'SysAdmin'))) {
  $colour_background = param::optional('bg_radio', false, param::BOOLEAN, param::FETCH_POST);
  $colour_forground = param::optional('fg_radio', false, param::BOOLEAN, param::FETCH_POST);
  $colour_marks = param::optional('marks_radio', false, param::BOOLEAN, param::FETCH_POST);
  $colour_theme = param::optional('theme_radio', false, param::BOOLEAN, param::FETCH_POST);
  $colour_labels = param::optional('labels_radio', false, param::BOOLEAN, param::FETCH_POST);
  $colour_unanswered = param::optional('unanswered_radio', false, param::BOOLEAN, param::FETCH_POST);
  $colour_dismiss = param::optional('dismiss_radio', false, param::BOOLEAN, param::FETCH_POST);

  if ($colour_background) {
    $background = param::optional('background', null, param::TEXT, param::FETCH_POST);
  } else {
    $background = null;
  }
  if ($colour_forground) {
    $foreground = param::optional('foreground', null, param::TEXT, param::FETCH_POST);
  } else {
    $foreground = null;
  }
  $textsize = param::optional('textsize', 0, param::INT, param::FETCH_POST);
  $extra_time = param::optional('extra_time', 0, param::INT, param::FETCH_POST);
  $font = param::optional('font', null, param::ALPHA, param::FETCH_POST);
  if ($colour_marks) {
    $marks_color = param::optional('marks_color', null, param::TEXT, param::FETCH_POST);
  } else {
    $marks_color = null;
  }
  if ($colour_theme) {
    $themecolor = param::optional('themecolor', null, param::TEXT, param::FETCH_POST);
  } else {
    $themecolor = null;
  }
  if ($colour_labels) {
    $labelcolor = param::optional('labelcolor', null, param::TEXT, param::FETCH_POST);
  } else {
    $labelcolor = null;
  }
  if ($colour_unanswered) {
    $unansweredcolor = param::optional('unansweredcolor', null, param::TEXT, param::FETCH_POST);
  } else {
    $unansweredcolor = null;
  }
  if ($colour_dismiss) {
    $dismisscolor = param::optional('dismisscolor', null, param::TEXT, param::FETCH_POST);
  } else {
    $dismisscolor = null;
  }
  $medical = trim(param::optional('medical', '', param::TEXT, param::FETCH_POST));
  $breaks = trim(param::optional('breaks', '', param::TEXT, param::FETCH_POST));

  $result = $mysqli->prepare("DELETE FROM special_needs WHERE userID = ?");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->close();

  if ($background != NULL or $foreground != NULL or $marks_color != NULL or $textsize != 0 or $extra_time != 0 or $font != NULL or $themecolor != NULL or $labelcolor != NULL or $unansweredcolor != NULL or $dismisscolor != NULL or $medical != '' or $breaks != '') {
    $result = $mysqli->prepare("INSERT INTO special_needs VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result->bind_param('issiissssssss', $userID, $background, $foreground, $textsize, $extra_time, $marks_color, $themecolor, $labelcolor, $font, $unansweredcolor, $dismisscolor, $medical, $breaks);
    $result->execute();
    $result->close();

    $result = $mysqli->prepare("UPDATE users SET special_needs = 1 WHERE id = ?");
    $result->bind_param('i', $userID);
    $result->execute();
    $result->close();
  }
} elseif (!is_null($save_metadata) and $userObject->has_role(array('Admin', 'SysAdmin'))) {
  $metadata_no = param::optional('metadata_no', 0, param::INT, param::FETCH_POST);
  for ($i=0; $i < $metadata_no; $i++) {
    $meta_moduleID = param::optional("meta_moduleID$i", null, param::INT, param::FETCH_POST);
    $meta_type = param::optional("meta_type$i", null, param::TEXT, param::FETCH_POST);
    $meta_value = param::optional("meta_value$i", null, param::TEXT, param::FETCH_POST);
    $meta_calendar_year = param::optional("meta_calendar_year$i", null, param::INT, param::FETCH_POST);

    $result = $mysqli->prepare("REPLACE INTO users_metadata (userID, idMod, type, value, calendar_year) VALUES (?, ?, ?, ?, ?)");
    $result->bind_param('iisss', $userID, $meta_moduleID, $meta_type, $meta_value, $meta_calendar_year);
    $result->execute();
    $result->close();
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['usermanagement'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/tabs.css" />
  <link rel="stylesheet" type="text/css" href="../css/tablesort.css" />
  <style type="text/css">
    .coltitle {cursor:hand; background-color:#1E3C7B; color:white}
    .sch_check {text-align:right; width:40px; padding-right:6px}
    .medical {background-image: url('../artwork/medical_16.gif'); background-repeat:no-repeat; vertical-align:top; padding-left:20px}
    .breaks {background-image: url('../artwork/moon_16.gif'); background-repeat:no-repeat; vertical-align:top; padding-left:20px}
    .field {padding-left:4px; width:95px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function reviewPaper(started, userid, surname, papername, log_type, metadataID) {
      var winwidth = screen.width - 80;
      var winheight = screen.height - 80;
      window.open("../paper/finish.php?id="+papername+"&previous="+started+"&userID="+userid+"&metadataID=" + metadataID + "&surname="+surname+"&log_type="+log_type+"","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
    }

    function showTab(tabID) {
      $('#Log_tab').hide();
      $('#Modules_tab').hide();
      $('#Admin_tab').hide();
      $('#Notes_tab').hide();
      $('#Accessibility_tab').hide();
      $('#Teams_tab').hide();
      $('#Metadata_tab').hide();

      $('#' + tabID).show();
    }

    function newStudentNote() {
      note = window.open("new_student_note.php?userID=<?php echo $userID ?>","note","width=600,height=400,left="+(screen.width/2-300)+",top="+(screen.height/2-200)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        note.focus();
      }
    }

    function addModule() {
      note = window.open("add_student_module.php?userID=<?php echo $userID ?>","module","width=600,height=" + (screen.height - 120) + ",left="+(screen.width/2-300)+",top=50,scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        note.focus();
      }
    }

    function editModules(session, grade) {
      var student = "&student_id=<?php echo $student_id;?>";
      var username = "&search_username=<?php echo $search_username; ?>";
      var surname = "&search_surname=<?php echo $search_surname; ?>";
      editwin=window.open("edit_modules_popup.php?userID=<?php echo $userID ?>" + student + username + surname + "&session=" + session + "&grade=" + grade + "","editmodule","width=650,height=750,left="+(screen.width/2-250)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=yes,menubar=no,resizable");
      if (window.focus) {
        editwin.focus();
      }
    }

    function editMultiTeams() {
      editwin=window.open("../module/edit_multi_teams_popup.php?userID=<?php echo $userID ?>","editmodule","width=550,height=750,left="+(screen.width/2-200)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      if (window.focus) {
        editwin.focus();
      }
    }

    function forceResetPassword(username) {
      editwin=window.open("reset_pwd.php?userID=<?php echo $userID ?>","editmodule","width=450,height=400,left="+(screen.width/2-200)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
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
      var textsize = $('select[name="textsize"] option:selected').text();
      if (textsize == '<default>') {
        textsize = '100%';
      }
      $('#demo_paper_background').css('font-size', textsize);

      var font = $('select[name="font"] option:selected').text();
      if (font == '<default>') {
        font = 'Arial';
      }
      $('#demo_paper_background').css('font-family', font);

      if ($("#bg_radio_on").attr('checked')) {
        $('#demo_paper_background').css('background-color', $('#span_background').css('background-color'));
      } else {
        $('#demo_paper_background').css('background-color', '#FFFFFF');
      }

      if ($("#fg_radio_on").attr('checked')) {
        $('#demo_paper_background').css('color', $('#span_foreground').css('background-color'));
      } else {
        $('#demo_paper_background').css('color', '#000000');
      }

      if ($("#theme_radio_on").attr('checked')) {
        $('#demo_theme').css('color', $('#span_themecolor').css('background-color'));
      } else {
        $('#demo_theme').css('color', '#316AC5');
      }

      if ($("#labels_radio_on").attr('checked')) {
        $('#demo_true_label').css('color', $('#span_labelcolor').css('background-color'));
        $('#demo_false_label').css('color', $('#span_labelcolor').css('background-color'));
      } else {
        $('#demo_true_label').css('color', '#C00000');
        $('#demo_false_label').css('color', '#C00000');
      }

      if ($("#unanswered_radio_on").attr('checked')) {
        $('#demo_unanswered').css('background-color', $('#span_unansweredcolor').css('background-color'));
      } else {
        $('#demo_unanswered').css('background-color', '#FFC0C0');
      }

      if ($("#marks_radio_on").attr('checked')) {
        $('#demo_marks').css('color', $('#span_marks_color').css('background-color'));
      } else {
        $('#demo_marks').css('color', '#808080');
      }
    }

    $(function () {
      updateAccessDemo();
      
      $('#userID').val(',<?php echo $userID ?>');
      
      $('#menu2a').hide();
      $('#menu2b').show();
      
      $('#edit').click(function() {
        editwin=window.open("edit_details.php?userID=<?php echo $userID ?>","edituser","width=600,height=450,left="+(screen.width/2-260)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
        if (window.focus) {
          editwin.focus();
        }        
      });
      
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({
          dateFormat: '<?php echo $configObject->get('cfg_tablesorter_date_time'); ?>',
          sortList: [[1,0]] 
        });
      }

    });  
  </script>
</head>

<body>
<?php
  $user_details = UserUtils::get_user_details($userID, $mysqli);
  if ($user_details === false) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
  }
  
  require '../tools/colour_picker/colour_picker.inc';
  require '../include/user_search_options.php';
  require '../include/toprightmenu.inc';
	echo draw_toprightmenu();

  if ($demo == true) {
    // Hide the personal details.
    $user_details['surname'] = demo_replace($user_details['surname'], $demo);
    $user_details['first_names'] = demo_replace($user_details['first_names'], $demo);
    $user_details['initials'] = demo_replace($user_details['initials'], $demo);
    $user_details['student_id'] = demo_replace_number($user_details['student_id'], $demo);
    $user_details['email'] = demo_replace_username($user_details['email'], $demo);
  }

  $course_details = CourseUtils::get_course_details_by_name($user_details['grade'], $mysqli);

  if ($user_details['user_deleted'] == '') {
    $bg_color = '#EEF4FF';
  } else {
    $bg_color = '#FFC0C0';
  }
?>
<div id="content">
<table cellpadding="0" cellspacing="0" border="0" style="background-color:<?php echo $bg_color; ?>; width:100%; line-height:175%; padding-bottom:10px">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>?userID=<?php echo $userID ?>" method="post" autocomplete="off">
<?php
  if ($user_details['gender'] == 'Male') {
    $generic_icon = '../artwork/user_male_64.png';
  } elseif ($user_details['gender'] == 'Female') {
    $generic_icon = '../artwork/user_female_64.png';
  } else {
    $generic_icon = '../artwork/user_mx_64.png';
  }
  if (stripos($user_details['roles'], 'Student') !== false) {
    $student_photo = UserUtils::student_photo_exist($user_details['username']);
    $photodirectory = rogo_directory::get_directory('user_photo');
    if ($student_photo) {
      $photo_size = getimagesize($photodirectory->fullpath($student_photo));
      if (isset($demo) and $demo == true) {
        echo "<tr><td rowspan=\"6\" style=\"vertical-align:top; width:" . $photo_size[2] . "px\"><img src=\"./pixel_photo.php?username=" . $user_details['username'] . "\" " . $photo_size[3] . " alt=\"Photo\" /></td>";
      } else {
        echo "<tr><td rowspan=\"6\" style=\"vertical-align:top; width:" . $photo_size[2] . "px\"><img src=\"" . $photodirectory->url($student_photo) . "\" " . $photo_size[3] . " alt=\"Photo\" /></td>";
      }
    } else {
      echo "<tr><td rowspan=\"6\" width=\"100\" style=\"vertical-align:top; text-align:center; padding-top:6px\"><img src=\"$generic_icon\" width=\"64\" height=\"64\" alt=\"User Folder\"  style=\"background-color:white; padding:5px; border:2px solid #9A6508\" /></td>\n";
    }
  } else {
    echo "<tr><td rowspan=\"6\" width=\"100\" style=\"vertical-align:top; text-align:center; padding-top:6px\"><img src=\"$generic_icon\" width=\"64\" height=\"64\" alt=\"User Folder\"  style=\"background-color:white; padding:5px; border:2px solid #9A6508\" /></td>\n";
  }
  echo "<td colspan=\"4\" style=\"vertical-align:top\">&nbsp;<a href=\"../index.php\">" . $string['home'] . "</a><img src=\"../artwork/breadcrumb_arrow.png\" class=\"breadcrumb_arrow\" alt=\"-\" /><a href=\"search.php\">" . $string['usersearch'] . "</a><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" />";
  if ($userObject->has_role('SysAdmin')) {
    echo "<input type=\"button\" id=\"edit\" value=\"" . $string['edit'] . "\" style=\"float:right; width:100px\" class=\"ok\" />";
  }
  echo "</td></tr>\n";
  if (stripos($user_details['roles'],'Student') !== false) {
    if ($user_details['student_id'] == '') $user_details['student_id'] = $string['unknown'];
    $sid = $user_details['student_id'];
  }  else {
    $sid = '';
    $string['studentid'] = '';
  }
  echo "<tr><td colspan=\"2\" class=\"page_title\" style=\"padding-left:2px !important\">" . $string['user'] . " <span style=\"font-weight:normal\">" . $user_details['title'] . ' ' . $user_details['first_names'] . ' ' . $user_details['surname'] . "</span></td>";
  echo "<td class=\"field\">" . $string['studentid'] . "</td><td>" . $sid . "</td></tr>\n";

  echo "<tr><td class=\"field\">" . $string['email'] . "</td><td><a href=\"mailto:" . $user_details['email'] . "\">" . $user_details['email'] . "</a></td>";
  if (stripos($user_details['roles'],'Student') !== false) {
    echo "<td class=\"field\">" . $string['yearofstudy'] . "</td><td>" . $user_details['yearofstudy'] . "</td>";
  } else {
    echo "<td class=\"field\"></td><td></td>";
  }
  echo "</tr>\n";
  if (stripos($user_details['roles'], 'Student') !== false) {
    echo "<tr><td class=\"field\">" . $string['course'] . "</td><td>" . $user_details['grade'] . " - " . $course_details['description'] . "</td>";
    echo "<td class=\"field\">" . $string['status'] . "</td><td>" . $user_details['roles'] . "</td>";
    echo "</tr>\n";
  } else {
    echo "<tr><td class=\"field\">" . $string['type'] . "</td><td>" . $user_details['grade'] . "</td>";
    echo "<td class=\"field\">" . $string['status'] . "</td><td>" . $user_details['roles'] . "</td>";
    echo "</tr>\n";    
  }
  if ($demo) {
    echo "<tr><td class=\"field\">" . $string['username'] . "</td><td>" . demo_replace_username($user_details['username'], $demo) . "</td>";
  } else {
    echo "<tr><td class=\"field\">" . $string['username'] . "</td><td>" . $user_details['username'] . "</td>";
  }
  if ($userObject->has_role('SysAdmin')) {
    echo "<td class=\"field\">" . $string['password'] . "</td><td>";
    $authinfo = $authentication->version_info(true, false);
    if (stripos($authinfo, 'LDAP') === false) {    // Don't show if LDAP is on.
      echo "<input type=\"button\" onclick=\"resetPassword('" .  urlencode($user_details['email']) . "')\" value=\"{$string['reset']}\" />&nbsp;";
    }
    echo "<input type=\"button\" onclick=\"forceResetPassword('" . $user_details['username'] . "')\" value=\"{$string['forcereset']}\" /></td></tr>\n";
  } else {
    echo "<td class=\"field\"></td><td></td></tr>\n";
  }
  echo "</tr>\n";
  echo "<tr><td class=\"field\">" . $string['gender'] . "</td><td>" . $user_details['gender'] . "</td>";
  if ($userObject->has_role('SysAdmin')) {
    echo "<td class=\"field\">Rog&#333; ID</td><td>$userID</td></tr>\n";
  } else {
    echo "<td class=\"field\"></td><td></td></tr>\n";
  }
  echo "</tr>\n";

  
?>
</form>
</table>
<?php
  if ($tab == 'log') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Log_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Log_tab\" style=\"width:100%; display:none\">\n";
  }
  echo drawTabs('Log', 6, '', $user_details['roles'], $bg_color, $string);

  $old_q_paper = '';
  $old_started = '';
  $old_duration = 0;
  $old_screen = 0;
  $old_paper_title = '';
  $results_no = 0;
  $paper = array();
  
  echo "<tr><td>";
  $table_order = array('', $string['papername'], $string['type'], $string['started'], $string['ipaddress']);
  echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"1\" border=\"0\" style=\"width:100%\">\n";
  echo "<thead>\n";
  echo "<tr>\n";
  foreach ($table_order as $col_title) {
    echo "<th style=\"background-color:#1E3C7B\" class=\"coltitle\">$col_title</th>\n";
  }
?>
  </tr>
  </thead>

  <tbody>
<?php
  $stmt = false;

  if ($userObject->has_role(array('Admin', 'SysAdmin')) or $userObject->get_user_ID() == $userID) {
    $log_viewable = true;
  } else {
    $idMod = array_keys($userObject->get_staff_modules());
    $log_viewable = UserUtils::is_user_on_module($userID, $idMod, '', $mysqli);
  }

  $paper_types = array('Formative Self-Assessment', 'Progress Test', 'Summative Exam', 'Survey', 'OSCE Station', 'Offline Paper', 'Peer Review');

  if ($log_viewable) {
    // Get the papers the External/Internal is down to review.
    if (stripos($user_details['roles'], 'External Examiner') !== false or stripos($user_details['roles'], 'Internal Reviewer') !== false) {
      $external_array = array();

      $sql = "SELECT DISTINCT
								crypt_name, paper_title, property_id, paper_type, started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started
							FROM
								(properties, properties_reviewers)
							LEFT JOIN
								review_metadata
							ON
								properties.property_id = review_metadata.paperID AND review_metadata.reviewerID = ?
							WHERE
								properties.property_id = properties_reviewers.paperID AND
								properties_reviewers.reviewerID = ? AND
								deleted IS NULL
							ORDER BY
								paper_title";
      $stmt = $mysqli->prepare($sql);
      $stmt->bind_param('ii', $userID, $userID);
      $stmt->execute();
      $stmt->bind_result($crypt_name, $paper_title, $property_id, $paper_type, $started, $display_started);
      while ($stmt->fetch()) {
        $paper[$results_no]['crypt_name'] = $crypt_name;
        $paper[$results_no]['q_paper'] = $paper_title;
        $paper[$results_no]['id'] = $property_id;
        $paper[$results_no]['type'] = '2';
        $paper[$results_no]['paper_type'] = '2';
        $paper[$results_no]['started'] = $started;
        $paper[$results_no]['display_started'] = $display_started;
        $paper[$results_no]['duration'] = '';
        $paper[$results_no]['mark'] = '';
        $paper[$results_no]['totalpos'] = '';
        $paper[$results_no]['ipaddress'] = '';
        $results_no++;
      }
      $stmt->close();
    } else {
      // Only allow Admin/SysAdmin or current user to view this information
      $queries = array();
			
      $queries[] = "SELECT DISTINCT crypt_name, paper_title, 0 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, ipaddress, log_metadata.id FROM properties, log_metadata, log0 WHERE properties.property_id = log_metadata.paperID AND log_metadata.id = log0.metadataID AND log_metadata.userID = ? AND paper_type IN ('0','1') ORDER BY started";
      $queries[] = "SELECT DISTINCT crypt_name, paper_title, 1 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, ipaddress, log_metadata.id FROM properties, log_metadata, log1 WHERE properties.property_id = log_metadata.paperID AND log_metadata.id = log1.metadataID AND log_metadata.userID = ? AND paper_type IN ('0','1') ORDER BY started";
      $queries[] = "SELECT DISTINCT crypt_name, paper_title, 2 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, ipaddress, log_metadata.id FROM properties, log_metadata WHERE properties.property_id = log_metadata.paperID AND log_metadata.userID = ? AND paper_type = '2' ORDER BY started";
      $queries[] = "SELECT DISTINCT crypt_name, paper_title, 3 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, ipaddress, log_metadata.id FROM properties, log_metadata WHERE properties.property_id = log_metadata.paperID AND log_metadata.userID = ? AND paper_type = '3' ORDER BY started";
      $queries[] = "SELECT crypt_name, paper_title, 4 AS paper_type, q_paper, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, NULL AS ipaddress, NULL AS metadataID FROM properties, log4_overall WHERE properties.property_id = log4_overall.q_paper AND userID = ? ORDER BY started";
      $queries[] = "SELECT DISTINCT crypt_name, paper_title, 5 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, ipaddress, log_metadata.id FROM properties, log_metadata WHERE properties.property_id = log_metadata.paperID AND log_metadata.userID = ? AND paper_type = '5' ORDER BY started";
      $queries[] = "SELECT DISTINCT crypt_name, paper_title, 6 AS paper_type, paperID, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'{$configObject->get('cfg_long_date_time')}') AS display_started, NULL AS ipaddress, NULL AS metadataID FROM properties, log6 WHERE properties.property_id = log6.paperID AND reviewerID = ? ORDER BY started";

      foreach ($queries as $query_sql) {
        $stmt = $mysqli->prepare($query_sql);
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $stmt->bind_result($crypt_name, $paper_title, $paper_type, $q_paper, $started, $display_started, $ipaddress, $metadataID);
        while ($stmt->fetch()) {
					$paper[$results_no]['crypt_name']       = $crypt_name;
					$paper[$results_no]['q_paper']          = $paper_title;
					$paper[$results_no]['id']               = $q_paper;
					$paper[$results_no]['type']             = $paper_type;
					$paper[$results_no]['paper_type']       = $paper_types[$paper_type];
					$paper[$results_no]['started']          = $started;
					$paper[$results_no]['display_started']  = $display_started;
					$paper[$results_no]['ipaddress']        = $ipaddress;
					$paper[$results_no]['metadataID']       = $metadataID;
					$results_no++;
        }
        $stmt->close();
      }

      // Add in feedback
      $stmt = $mysqli->prepare("SELECT page, ipaddress, DATE_FORMAT(accessed, '%Y%m%d%H%i%s') AS accessed, DATE_FORMAT(accessed,'{$configObject->get('cfg_long_date_time')}') AS display_started, crypt_name, type, paper_title FROM access_log, properties WHERE access_log.page = properties.property_id AND userID = ?");
      $stmt->bind_param('i', $userID);
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
        $paper[$results_no]['ipaddress']        = $ipaddress;
        $paper[$results_no]['metadataID']       = null;
        $results_no++;
      }
      $stmt->close();

      // Add in any access denied warnings
      $stmt = $mysqli->prepare("SELECT page, ipaddress, DATE_FORMAT(tried, '%Y%m%d%H%i%s') AS tried, DATE_FORMAT(tried,'{$configObject->get('cfg_long_date_time')}') AS display_started, title FROM denied_log WHERE userID = ?");
      $stmt->bind_param('i', $userID);
      $stmt->execute();
      $stmt->bind_result($page, $ipaddress, $tried, $display_started, $title);
      while ($stmt->fetch()) {
        $paper[$results_no]['crypt_name']       = '';
        $paper[$results_no]['q_paper']          = '/' . $page;
        $paper[$results_no]['type']             = $title;
        $paper[$results_no]['paper_type']       = $title;
        $paper[$results_no]['started']          = $tried;
        $paper[$results_no]['display_started']  = $display_started;
        $paper[$results_no]['ipaddress']        = $ipaddress;
        $paper[$results_no]['metadataID']       = null;
        $results_no++;
      }
      $stmt->close();
    }

    for ($i=0; $i<$results_no; $i++) {
      if (strpos($paper[$i]['q_paper'],'[deleted') !== false ) {
        $paper[$i]['q_paper'] = '<span style="color:#808080; text-decoration:line-through">' . $paper[$i]['q_paper'] . '</span>';
      }
      switch ($paper[$i]['type']) {
        case '0':
          echo "<tr><td><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "'," . $userID . ",'" . str_replace("'","&#8217;",$user_details['surname']) . "','" . $paper[$i]['crypt_name'] . "'," . $paper[$i]['type'] . ",'" . $paper[$i]['metadataID'] . "'); return false;\"><img src=\"../artwork/formative_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $user_details['surname'] . "\" /></a></td><td><a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case '1':
          echo "<tr><td><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "'," . $userID . ",'" . str_replace("'","&#8217;",$user_details['surname']) . "','" . $paper[$i]['crypt_name'] . "'," . $paper[$i]['type'] . ",'" . $paper[$i]['metadataID'] . "'); return false;\"><img src=\"../artwork/progress_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $user_details['surname'] . "\" /></a></td><td><a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case '2':
          if (stripos($user_details['roles'], 'External Examiner') !== false or stripos($user_details['roles'], 'Internal Reviewer') !== false) {
            echo "<tr><td><img src=\"../artwork/summative_16.gif\" width=\"16\" height=\"16\" /></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\"";
          } else {
            echo "<tr><td><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "'," . $userID . ",'" . str_replace("'","&#8217;",$user_details['surname']) . "','" . $paper[$i]['crypt_name'] . "'," . $paper[$i]['type'] . ",'" . $paper[$i]['metadataID'] . "'); return false;\"><img src=\"../artwork/summative_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $user_details['surname'] . "\" /></a></td><td><a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\"";
          }
          if ($paper[$i]['started'] == '') echo ' style="color:red"';
          echo ">" . $paper[$i]['q_paper'] . "</a></td><td";
          if ($paper[$i]['started'] == '') echo ' style="color:red"';
          echo ">" . $string['summative'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case '3':
          echo "<tr><td><img src=\"../artwork/survey_16.gif\" width=\"16\" height=\"16\" alt=\"Survey data is anonymous, no entry.\" /></td><td><a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\" class=\"paper\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $string['survey'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case '4':
          echo "<tr><td><a href=\"#\" onclick=\"reviewOSCE('" . $paper[$i]['started'] . "','" . $user_details['username'] . "','" . str_replace("'","&#8217;",$user_details['surname']) . "','" . $paper[$i]['crypt_name'] . "'," . $paper[$i]['type'] . "); return false;\"><img src=\"../artwork/osce_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $user_details['surname'] . "\" /></a></td><td><a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td></tr>\n";
          break;
        case '5':
          echo "<tr><td><img src=\"../artwork/offline_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>" . $paper[$i]['q_paper'] . "</td><td>" . $string['offlinepaper'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td></tr>\n";
          break;
        case '6':
          echo "<tr><td><img src=\"../artwork/peer_review_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td><a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td></tr>\n";
          break;
        case 'Objectives-based feedback report':
          echo "<tr><td><img src=\"../artwork/objectives_feedback_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td><a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $string['Objectives Feedback report'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case 'Question-based feedback report':
          echo "<tr><td><img src=\"../artwork/questions_feedback_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td><a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $string['Questions Feedback report'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case $string['pagenotfound']:
          echo "<tr style=\"color:#C00000\"><td><img src=\"../artwork/access_denied_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>" . $paper[$i]['q_paper'] . "</td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case $string['accessdenied']:
          echo "<tr style=\"color:#C00000\"><td><img src=\"../artwork/access_denied_16.gif\" width=\"16\" height=\"16\" alt=\"\" /></td><td>" . $paper[$i]['q_paper'] . "</td><td>" . $paper[$i]['paper_type'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
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
  </tbody>
</table>
</td></tr>
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

  echo drawTabs('Modules', 4, '', $user_details['roles'], $bg_color, $string);
  echo "<tr><td class=\"coltitle\" style=\"width:20px\">&nbsp;</td><td class=\"coltitle\">&nbsp;" . $string['moduleid'] . "</td><td class=\"coltitle\">" . $string['name'] . "</td><td class=\"coltitle\">" . $string['academicyear'] . "</td></tr>\n";
  $old_year = '';
  $row_no = 0;
  $user_modules = array();
  $current_year = false;

  $results = $mysqli->prepare("SELECT DISTINCT modules.id, modules.moduleid, fullname, modules_student.calendar_year, attempt FROM (modules_student, modules) WHERE modules_student.idMod = modules.id AND userID = ? ORDER BY modules_student.calendar_year DESC, modules.moduleid");
  $results->bind_param('i', $userID);
  $results->execute();
  $results->store_result();
  $results->bind_result($idMod, $moduleid, $fullname, $calendar_year, $attempt);

  $yearutils = new yearutils($mysqli);
  $current_session = $yearutils->get_current_session();
  $year_names = $yearutils->get_supported_years();
  while ($results->fetch()) {
    $user_modules[$row_no]['moduleid'] = $moduleid;
    $user_modules[$row_no]['fullname'] = $fullname;
    $user_modules[$row_no]['calendar_year'] = $calendar_year;
    $user_modules[$row_no]['attempt'] = $attempt;
    $user_modules[$row_no]['idMod'] = $idMod;
    if ($calendar_year == $current_session) {
      $current_year = true;
    }
    $row_no++;
  }
  $results->close();

  if ($current_year == false) {
    echo "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $current_session;
    if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
      echo "&nbsp;&nbsp;<a href=\"#\" onclick=\"editModules('" . $current_session . "','" . $user_details['grade'] . "'); return false;\"><img src=\"../artwork/pencil_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['editmodules'] . "\" /></a>";
    }
    echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  }

  for ($i=0; $i<$row_no; $i++) {
    if ($user_modules[$i]['calendar_year'] != $old_year) {
      $display_year = $year_names[$user_modules[$i]['calendar_year']];
      echo "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $display_year;
      if (($user_modules[$i]['calendar_year'] == $most_recent_year or $user_modules[$i]['calendar_year'] == $current_session) and $userObject->has_role(array('Admin', 'SysAdmin'))) {
        echo "&nbsp;&nbsp;<a href=\"#\" onclick=\"editModules('" . $user_modules[$i]['calendar_year'] . "','" . $user_details['grade'] . "'); return false;\"><img src=\"../artwork/pencil_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['editmodules'] . "\" /></a>";
      }
      echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    }
    echo "<tr><td>";
    if ($user_modules[$i]['attempt'] != 1) {
      echo '<img src="../artwork/resit.png" width="16" height="16" alt="Resit" title="' . $string['resitcandidate'] . '" />';
    }
    echo "</td><td><a href=\"../module/index.php?module={$user_modules[$i]['idMod']}\">{$user_modules[$i]['moduleid']}</a></td><td>&nbsp;<a href=\"../module/index.php?module={$user_modules[$i]['idMod']}\">{$user_modules[$i]['fullname']}</a></td><td>{$user_modules[$i]['calendar_year']}</td></tr>\n";
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
  echo "<form name=\"accessibility\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=$userID&tab=admin\" method=\"post\" autocomplete=\"off\">";

  echo drawTabs('Admin', 1, '', $user_details['roles'], $bg_color, $string);
  echo "<tr><td class=\"coltitle\">&nbsp;</td></tr>\n";
  echo "<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n";

  $current_schools = SchoolUtils::get_admin_schools($userID, $mysqli);

  $old_faculty = '';
  $admin_school_no = 0;
  $results = $mysqli->prepare("SELECT schools.id, faculty.name, school FROM schools, faculty WHERE schools.facultyID = faculty.id ORDER BY faculty.name, school");
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
  if ($userObject->has_role('SysAdmin')) {
    echo '<tr><td colspan="2" align="center"><input type="submit" name="updateadmin" value="' . $string['save'] . '" class="ok" /><input type="hidden" name="admin_school_no" value="' . $admin_school_no . '" /></td></tr>';
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
  echo drawTabs('Notes', 4, '', $user_details['roles'], $bg_color, $string);
  echo "<tr><td class=\"coltitle\">&nbsp;&nbsp;&nbsp;" . $string['date'] . "</td><td class=\"coltitle\">" . $string['paper'] . "</td><td class=\"coltitle\">" . $string['note'] . "</td><td class=\"coltitle\">" . $string['author'] . "</td></tr>\n";
  
	echo "<tr><td colspan=\"4\"><input type=\"button\" name=\"createname\" onclick=\"newStudentNote()\" value=\"" .  $string['newnote'] . "\" /></td></tr>\n";

  $results = $mysqli->prepare("SELECT note, DATE_FORMAT(note_date, \" {$configObject->get('cfg_short_date')}\"), paper_id, paper_title, CONCAT(title, ' ', initials, ' ', surname) AS note_author FROM (student_notes, properties, users) WHERE student_notes.paper_id=properties.property_id AND student_notes.note_authorID = users.id AND student_notes.userID = ?");
  $results->bind_param('i', $userID);
  $results->execute();
  $results->store_result();
  $results->bind_result($note, $note_date, $note_paper_id, $paper_title, $note_author);
  while ($results->fetch()) {
    echo "<tr><td><nobr>&nbsp;<img src=\"../artwork/notes_icon.gif\" width=\"16\" height=\"16\" alt=\"Note\" />&nbsp;$note_date</nobr></td><td style=\"padding-right:20px\"><nobr><a href=\"../paper/details.php?paperID=$note_paper_id\">$paper_title</a></nobr></td><td>$note</td><td>$note_author</td></tr>";
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
  echo "<form name=\"accessibility\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=$userID&tab=accessibility\" method=\"post\" autocomplete=\"off\">";
  echo drawTabs('Accessibility', 1, '', $user_details['roles'], $bg_color, $string);
  echo "<tr><td class=\"coltitle\">&nbsp;</td></tr>\n";
  echo "<tr><td align=\"center\"><table cellspacing=\"1\" cellpadding=\"1\" border=\"0\" style=\"padding-top:20px; text-align:left\">";

  $result = $mysqli->prepare("SELECT background, foreground, textsize, extra_time, marks_color, themecolor, labelcolor, font, unanswered, dismiss, medical, breaks FROM special_needs WHERE userID = ? LIMIT 1");
  $result->bind_param('i', $userID);
  $result->execute();
  $result->store_result();
  $result->bind_result($background, $foreground, $textsize, $extra_time, $marks_color, $themecolor, $labelcolor, $font, $unansweredcolor, $dismisscolor, $medical, $breaks);
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
  if (!isset($dismisscolor)) $dismisscolor = '';
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
<td rowspan="10" style="width:40px">&nbsp;</td>
<td rowspan="10" style="font-size:110%">
<div id="demo_paper_background" style="width:450px; height:300px; border:1px solid #909090; padding:15px; float:right">

<span id="demo_theme" style="font-size:150%; font-weight:bold; color:#316AC5"><?php echo $string['demo1']; ?></span>

<p>1. &nbsp;<?php echo $string['demo2']; ?></p>

<table cellspacing="0" cellpadding="2" border="0" style="margin-left:30px; width:200px">
  <tr><td style="text-align:center; color:#C00000" id="demo_true_label"><?php echo $string['demo3']; ?></td><td style="text-align:center; color:#C00000" id="demo_false_label"><?php echo $string['demo4']; ?></td><td></tr>
<tr><td style="text-align:center"><input type="radio" name="q1" value="t" checked="checked" /></td><td style="text-align:center"><input type="radio" name="q1" value="f" /></td><td><?php echo $string['demo5']; ?></td></tr>
<tr><td style="text-align:center"><input type="radio" name="q2" value="t" /></td><td style="text-align:center"><input type="radio" name="q2" value="f" checked="checked" /></td><td><?php echo $string['demo6']; ?></td></tr>
<tr id="demo_unanswered" style="background-color:#FFC0C0"><td style="text-align:center"><input type="radio" name="q3" value="t" /></td><td style="text-align:center"><input type="radio" name="q3" value="f" /></td><td><?php echo $string['demo7']; ?></td></tr>
</table>
<br />
<span id="demo_marks" style="font-size:90%; color:#808080">(<?php echo $string['demo8']; ?>)</span>

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
    echo "<div onclick=\"showPicker('background',event); \$('#bg_radio_on').attr('checked', true);\" id=\"span_background\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"background\" name=\"background\" value=\"$background\" />";
  } else {
    echo "<div onclick=\"showPicker('background',event); \$('#bg_radio_on').attr('checked', true);\" id=\"span_background\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$background\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"background\" name=\"background\" value=\"$background\" />";
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
    echo "<div onclick=\"showPicker('foreground',event); \$('#fg_radio_on').attr('checked', true);\" id=\"span_foreground\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"foreground\" name=\"foreground\" value=\"$foreground\" />";
  } else {
    echo "<div onclick=\"showPicker('foreground',event); \$('#fg_radio_on').attr('checked', true);\" id=\"span_foreground\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$foreground\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"foreground\" name=\"foreground\" value=\"$foreground\" />";
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
    echo "<div onclick=\"showPicker('marks_color',event); $('#marks_radio_on').attr('checked', true);\" id=\"span_marks_color\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"marks_color\" name=\"marks_color\" value=\"$marks_color\" />";
  } else {
    echo "<div onclick=\"showPicker('marks_color',event); $('#marks_radio_on').attr('checked', true);\" id=\"span_marks_color\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$marks_color\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"marks_color\" name=\"marks_color\" value=\"$marks_color\" />";
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
    echo "<div onclick=\"showPicker('themecolor',event); $('#theme_radio_on').attr('checked', true);\" id=\"span_themecolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" />";
  } else {
    echo "<div onclick=\"showPicker('themecolor',event); $('#theme_radio_on').attr('checked', true);\" id=\"span_themecolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$themecolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" />";
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
    echo "<div onclick=\"showPicker('labelcolor',event); $('#labels_radio_on').attr('checked', true);\" id=\"span_labelcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" />";
  } else {
    echo "<div onclick=\"showPicker('labelcolor',event); $('#labels_radio_on').attr('checked', true);\" id=\"span_labelcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$labelcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" />";
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
    echo "<div onclick=\"showPicker('unansweredcolor',event); $('#unanswered_radio_on').attr('checked', true);\" id=\"span_unansweredcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"unansweredcolor\" name=\"unansweredcolor\" value=\"$unansweredcolor\" />";
  } else {
    echo "<div onclick=\"showPicker('unansweredcolor',event); $('#unanswered_radio_on').attr('checked', true);\" id=\"span_unansweredcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$unansweredcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"unansweredcolor\" name=\"unansweredcolor\" value=\"$unansweredcolor\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['dismisscolor']; ?></td>
<td><input type="radio" name="dismiss_radio" value="0"<?php if ($dismisscolor == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" name="dismiss_radio" id="dismiss_radio_on" value="1"<?php if ($dismisscolor != '') echo ' checked'; ?> />
<?php
  if ($dismisscolor == '') {
    echo "<div onclick=\"showPicker('dismisscolor',event); $('#dismiss_radio_on').attr('checked', true);\" id=\"span_dismisscolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"dismisscolor\" name=\"dismisscolor\" value=\"$dismisscolor\" />";
  } else {
    echo "<div onclick=\"showPicker('dismisscolor',event); $('#dismiss_radio_on').attr('checked', true);\" id=\"span_dismisscolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$dismisscolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"dismisscolor\" name=\"dismisscolor\" value=\"$dismisscolor\" />";
  }
?>
</td>
</tr>
<tr><td colspan="5">&nbsp;</td></tr>
<tr><td class="medical"><?php echo $string['medical'] ?></td><td colspan="4"><textarea cols="60" rows="3" name="medical" style="width:100%"><?php echo $medical ?></textarea></td></tr>
<tr><td class="breaks"><?php echo $string['breaks'] ?></td><td colspan="4"><textarea cols="60" rows="3" name="breaks" style="width:100%"><?php echo $breaks ?></textarea></td></tr>
<tr><td colspan="5">&nbsp;</td></tr>
<?php
if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
  echo "<tr><td colspan=\"5\" align=\"center\"><input type=\"submit\" name=\"updateaccess\" value=\"" . $string['save'] . "\" class=\"ok\" /></td></tr>\n";
}
?>
</table>


</td>
</tr>
</form>
</table>

<?php
  // Metadata tab.
  $metadata_no = 0;
  if ($tab == 'metadata') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Metadata_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Metadata_tab\" style=\"width:100%; display:none\">\n";
  }
  echo "<form name=\"metadata\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=$userID&tab=metadata\" method=\"post\" autocomplete=\"off\">";
  echo drawTabs('Metadata', 5, '', $user_details['roles'], $bg_color, $string);
  echo "<tr><td class=\"coltitle\">&nbsp;" . $string['moduleid'] . "</td><td class=\"coltitle\">" . $string['academicyear'] . "</td><td class=\"coltitle\">" . $string['type'] . "</td><td class=\"coltitle\">" . $string['value'] . "</td><td class=\"coltitle\" style=\"width:30%\">&nbsp;</td></tr>\n";
  $stmt = $mysqli->prepare("SELECT modules.id, modules.moduleID, fullname, calendar_year, type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND userID = ?");
  $stmt->bind_param('i', $userID);
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
    echo "<tr><td colspan=\"5\" style=\"text-align:center\">";
    if ($metadata_no > 0) {
      echo "<input type=\"submit\" name=\"save_metadata\" value=\"" . $string['save'] . "\" class=\"ok\" />";
    }
    echo "<input type=\"hidden\" name=\"metadata_no\" value=\"$metadata_no\" /></td></tr>\n";
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
  echo drawTabs('Teams', 4, '', $user_details['roles'], $bg_color, $string);
  echo "<tr><td class=\"coltitle\">&nbsp;" . $string['team'] . "</td><td class=\"coltitle\">&nbsp;</td><td class=\"coltitle\">" . $string['dateadded'] . "</td></tr>\n";
  if ($userObject->has_role('Admin') or $userObject->has_role('SysAdmin')) {
    echo "<tr><td colspan=\"4\">&nbsp;<img onclick=\"editMultiTeams(); return false;\" src=\"../artwork/pencil_16.png\" width=\"16\" height=\"16\" alt=\"" . $string['editteams'] . "\" />&nbsp;<a href=\"\" onclick=\"editMultiTeams(); return false;\">" . $string['editteams'] . "</a></td></tr>\n";
  }

  if ($userObject->has_role(array('SysAdmin', 'Admin')) or $userObject->get_user_ID() == $userID) {   // Only allow Admin/SysAdmin or current user to view this information
    $result = $mysqli->prepare("SELECT moduleID, fullname, DATE_FORMAT(added,'%d/%m/%Y') AS added FROM modules_staff, modules WHERE modules_staff.idMod = modules.id AND memberID = ? ORDER BY moduleID");
    $result->bind_param('i', $userID);
    $result->execute();
    $result->store_result();
    $result->bind_result($moduleID, $fullname, $added);
    while ($result->fetch()) {
      echo "<tr><td>&nbsp;$moduleID</td><td>$fullname</td><td>$added</td></tr>\n";
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
