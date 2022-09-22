<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

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

function drawTabs($current_tab, $col_span, $right_text, $user_roles, $bg_color, $string)
{
    $html = '<tr><td colspan="' . ($col_span - 1) . "\" style=\"background-color:$bg_color\">";
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
        $tab_array[] = 'profileaudit';
    }

    $tab_array[] = 'roles';

    foreach ($tab_array as $individual_tab) {
        if ($individual_tab == $current_tab) {
            $html .= '<td class="tabon" data-tabid="' . $individual_tab . '_tab' . '">' . $string[mb_strtolower($individual_tab)] . '</td>';
        } else {
            $html .= '<td class="taboff" data-tabid="' . $individual_tab . '_tab' . '">' . $string[mb_strtolower($individual_tab)] . '</td>';
        }
    }
    $html .= "</tr></table></td><td align=\"right\" style=\"background-color:$bg_color\">$right_text</td></tr>\n";
    return $html;
}

function formatsec($seconds)
{
    if ($seconds == '') {
        $timestring = '';
    } else {
        $diff_hour = ($seconds / 60) / 60;
        $tmp_position = mb_strpos($diff_hour, '.');
        if ($tmp_position > 0) {
            $diff_hour = mb_substr($diff_hour, 0, $tmp_position);
        }
        if ($diff_hour > 0) {
            $seconds -= ($diff_hour * 60) * 60;
        }
        $diff_min = $seconds / 60;
        $tmp_position = mb_strpos($diff_min, '.');
        if ($tmp_position > 0) {
            $diff_min = mb_substr($diff_min, 0, $tmp_position);
        }
        if ($diff_min > 0) {
            $seconds -= $diff_min * 60;
        }
        $diff_sec = $seconds;
        $timestring = '';
        if ($diff_hour < 10) {
            $timestring = '0';
        }
        $timestring .= "$diff_hour:";
        if ($diff_min < 10) {
            $timestring .= '0';
        }
        $timestring .= "$diff_min:";
        if ($diff_sec < 10) {
            $timestring .= '0';
        }
        $timestring .= $diff_sec;
    }
    return $timestring;
}

$updateadmin = param::optional('updateadmin', null, param::ALPHA, param::FETCH_POST);
$updateaccess = param::optional('updateaccess', null, param::ALPHA, param::FETCH_POST);
$save_metadata = param::optional('save_metadata', null, param::ALPHA, param::FETCH_POST);
$save_roles = param::optional('updateroles', null, param::ALPHA, param::FETCH_POST);

// SysAdmins can edit the role of anyone.
$is_sysadmin = $userObject->has_role('SysAdmin');

// An Admin can change the roles of users as long as they are not a AysAdmin.
$user_roles = Role::getUsersRoles($userID);
$is_admin = $userObject->has_role('Admin');
$user_requires_sysadmin = (
        (array_search('SysAdmin', $user_roles) !== false) or
        (array_search('SysCron', $user_roles) !== false)
);
$can_edit_roles = ($is_sysadmin or ($is_admin and !$user_requires_sysadmin));

if (!is_null($updateadmin) and $userObject->has_role('SysAdmin')) {
    UserUtils::clear_admin_access($userID, $mysqli);

    $admin_school_no = param::optional('admin_school_no', 0, param::INT, param::FETCH_POST);
    for ($i = 0; $i < $admin_school_no; $i++) {
        $school = param::optional("sch$i", null, param::INT, param::FETCH_POST);
        if (!is_null($school)) {
            $result = $mysqli->prepare('INSERT INTO admin_access VALUES (NULL, ?, ?)');
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
    $colour_globaltheme = param::optional('colour_globaltheme_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_globalthemefont = param::optional('colour_globalthemefont_radio', false, param::BOOLEAN, param::FETCH_POST);
    $colour_highlight = param::optional('colour_highlight_radio', false, param::BOOLEAN, param::FETCH_POST);

    if ($colour_background) {
        $background = param::optional('background', null, param::TEXT, param::FETCH_POST);
    } else {
        $background = UserObject::BGCOLOUR;
    }
    if ($colour_forground) {
        $foreground = param::optional('foreground', null, param::TEXT, param::FETCH_POST);
    } else {
        $foreground = UserObject::FGCOLOUR;
    }
    $textsize = param::optional('textsize', UserObject::TEXTSIZE, param::INT, param::FETCH_POST);
    $extra_time = param::optional('extra_time', 0, param::INT, param::FETCH_POST);
    $break_time = param::optional('break_time', 0, param::INT, param::FETCH_POST);
    $font = param::optional('font', UserObject::FONT, param::ALPHA, param::FETCH_POST);
    if ($colour_marks) {
        $marks_color = param::optional('marks_color', null, param::TEXT, param::FETCH_POST);
    } else {
        $marks_color = UserObject::MARKSCOLOUR;
    }
    if ($colour_theme) {
        $themecolor = param::optional('themecolor', null, param::TEXT, param::FETCH_POST);
    } else {
        $themecolor = UserObject::THEMECOLOUR;
    }
    if ($colour_labels) {
        $labelcolor = param::optional('labelcolor', null, param::TEXT, param::FETCH_POST);
    } else {
        $labelcolor = UserObject::LABELCOLOUR;
    }
    if ($colour_unanswered) {
        $unansweredcolor = param::optional('unansweredcolor', null, param::TEXT, param::FETCH_POST);
    } else {
        $unansweredcolor = UserObject::UNANSWEREDCOLOUR;
    }
    if ($colour_dismiss) {
        $dismisscolor = param::optional('dismisscolor', null, param::TEXT, param::FETCH_POST);
    } else {
        $dismisscolor = UserObject::DISMISSCOLOUR;
    }
    if ($colour_globaltheme) {
        $globalthemecolour = param::optional('globalthemecolour', null, param::TEXT, param::FETCH_POST);
    } else {
        $globalthemecolour = UserObject::GLOBALTHEMECOLOUR;
    }
    if ($colour_globalthemefont) {
        $globalthemefontcolour = param::optional('globalthemefontcolour', null, param::TEXT, param::FETCH_POST);
    } else {
        $globalthemefontcolour = UserObject::GLOBALTHEMEFONTCOLOUR;
    }
    if ($colour_highlight) {
        $highlightcolour = param::optional('highlightcolour', null, param::TEXT, param::FETCH_POST);
    } else {
        $highlightcolour = UserObject::HIGHLIGHTCOLOUR;
    }

    $medical = trim(param::optional('medical', '', param::TEXT, param::FETCH_POST));
    $breaks = trim(param::optional('breaks', '', param::TEXT, param::FETCH_POST));

    $details = UserUtils::getUserProfile($userID, $mysqli);

    $result = $mysqli->prepare('DELETE FROM special_needs WHERE userID = ?');
    $result->bind_param('i', $userID);
    $result->execute();
    $result->close();

    // Log changes.
    $logger = new Logger($mysqli);
    if ($background != $details['background']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['background'],
            $background,
            'background'
        );
    }

    if ($foreground != $details['foreground']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['foreground'],
            $foreground,
            'foreground'
        );
    }

    if ($marks_color != $details['marks']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['marks'],
            $marks_color,
            'marks'
        );
    }

    if ($textsize != $details['textsize']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['textsize'],
            $textsize,
            'textsize'
        );
    }

    if ($font != $details['font']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['font'],
            $font,
            'font'
        );
    }

    if ($themecolor != $details['theme']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['theme'],
            $themecolor,
            'theme'
        );
    }

    if ($labelcolor != $details['label']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['label'],
            $labelcolor,
            'label'
        );
    }

    if ($unansweredcolor != $details['unanswered']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['unanswered'],
            $unansweredcolor,
            'unanswered'
        );
    }

    if ($dismisscolor != $details['dismiss']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['dismiss'],
            $dismisscolor,
            'dismiss'
        );
    }

    if ($globalthemecolour != $details['globaltheme']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['globaltheme'],
            $globalthemecolour,
            'globaltheme'
        );
    }

    if ($globalthemefontcolour != $details['globalthemefontcolour']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['globalthemefontcolour'],
            $globalthemefontcolour,
            'globalthemefont'
        );
    }

    if ($highlightcolour != $details['highlight']) {
        $logger->track_change(
            'User Profile',
            $userID,
            $userObject->get_user_ID(),
            $details['highlight'],
            $highlightcolour,
            'highlight'
        );
    }
    if (
        $background != null
        or $foreground != null
        or $marks_color != null
        or $textsize != 0
        or $extra_time != 0
        or $font != null
        or $themecolor != null
        or $labelcolor != null
        or $unansweredcolor != null
        or $dismisscolor != null
        or $medical != ''
        or $breaks != ''
        or $break_time != 0
        or $globalthemecolour != null
        or $globalthemefontcolour != null
        or $highlightcolour != null
    ) {
        $result = $mysqli->prepare('INSERT INTO special_needs VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $result->bind_param(
            'issiissssssssisss',
            $userID,
            $background,
            $foreground,
            $textsize,
            $extra_time,
            $marks_color,
            $themecolor,
            $labelcolor,
            $font,
            $unansweredcolor,
            $dismisscolor,
            $medical,
            $breaks,
            $break_time,
            $globalthemecolour,
            $globalthemefontcolour,
            $highlightcolour
        );
        $result->execute();
        $result->close();
    }

    // Only flag as special needs student if extra / break time given or a medical note attached.
    if (
        $extra_time != 0
        or $medical != ''
        or $breaks != ''
        or $break_time != 0
    ) {
        $result = $mysqli->prepare('UPDATE users SET special_needs = 1 WHERE id = ?');
        $result->bind_param('i', $userID);
        $result->execute();
        $result->close();
    }
} elseif (!is_null($save_metadata) and $userObject->has_role(array('Admin', 'SysAdmin'))) {
    $metadata_no = param::optional('metadata_no', 0, param::INT, param::FETCH_POST);
    for ($i = 0; $i < $metadata_no; $i++) {
        $meta_moduleID = param::optional("meta_moduleID$i", null, param::INT, param::FETCH_POST);
        $meta_type = param::optional("meta_type$i", null, param::TEXT, param::FETCH_POST);
        $meta_value = param::optional("meta_value$i", null, param::TEXT, param::FETCH_POST);
        $meta_calendar_year = param::optional("meta_calendar_year$i", null, param::INT, param::FETCH_POST);

        $result = $mysqli->prepare('REPLACE INTO users_metadata (userID, idMod, type, value, calendar_year) VALUES (?, ?, ?, ?, ?)');
        $result->bind_param('iisss', $userID, $meta_moduleID, $meta_type, $meta_value, $meta_calendar_year);
        $result->execute();
        $result->close();
    }
} elseif (!is_null($save_roles) and $can_edit_roles) {
    // Fetch the roles the user will have.
    $roles = param::optional('roles', [], param::ALPHA, param::FETCH_POST);

    $can_change_roles = ($is_sysadmin or (
            // Test that roles that require SysAdmin privileges are not present.
            (array_search('SysAdmin', $roles) === false) and
            (array_search('SysCron', $roles) === false)
    ));

    if ($can_change_roles) {
        // The user is allowed to add all the selected roles.
        try {
            Role::validateCombination($roles);
            Role::updateRoles($userID, $roles);
        } catch (InvalidRole $e) {
            // Something went wrong.
            $role_save_error = true;
        }
    } else {
        // An Admin tried to add the SysAdmin role.
        $role_save_error = true;
    }

    // Update the user roles.
    $user_roles = Role::getUsersRoles($userID);
}

// We want to be able to use the renderer anywhere when generating the page.
$render = new render($configObject);
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
  <link rel="stylesheet" type="text/css" href="../css/userdetails.css" />
  <link rel="stylesheet" type="text/css" href="../css/accessiblity.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
</head>

<body>
<?php
  $user_details = UserUtils::get_user_details($userID, $mysqli);
if ($user_details === false) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

  require '../tools/colour_picker/colour_picker.inc';
  require '../include/user_search_options.php';
  require '../include/toprightmenu.inc';
    echo draw_toprightmenu();

if ($demo == true) {
    // Hide the personal details.
    $user_details['surname'] = \demo::demo_replace($user_details['surname'], $demo);
    $user_details['first_names'] = \demo::demo_replace($user_details['first_names'], $demo);
    $user_details['initials'] = \demo::demo_replace($user_details['initials'], $demo);
    $user_details['student_id'] = \demo::demo_replace_number($user_details['student_id'], $demo);
    $user_details['email'] = \demo::demo_replace_username($user_details['email'], $demo);
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
            echo '<tr><td rowspan="6" style="vertical-align:top; width:' . $photo_size[2] . 'px"><img src="./pixel_photo.php?username=' . $user_details['username'] . '" ' . $photo_size[3] . ' alt="Photo" /></td>';
        } else {
            echo '<tr><td rowspan="6" style="vertical-align:top; width:' . $photo_size[2] . 'px"><img src="' . $photodirectory->url($student_photo) . '" ' . $photo_size[3] . ' alt="Photo" /></td>';
        }
    } else {
        echo "<tr><td rowspan=\"6\" width=\"100\" style=\"vertical-align:top; text-align:center; padding-top:6px\"><img src=\"$generic_icon\" width=\"64\" height=\"64\" alt=\"User Folder\"  style=\"background-color:white; padding:5px; border:2px solid #9A6508\" /></td>\n";
    }
} else {
    echo "<tr><td rowspan=\"6\" width=\"100\" style=\"vertical-align:top; text-align:center; padding-top:6px\"><img src=\"$generic_icon\" width=\"64\" height=\"64\" alt=\"User Folder\"  style=\"background-color:white; padding:5px; border:2px solid #9A6508\" /></td>\n";
}
  echo '<td colspan="4" style="vertical-align:top">&nbsp;<a href="../index.php">' . $string['home'] . '</a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="search.php">' . $string['usersearch'] . '</a><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />';
if ($userObject->has_role('SysAdmin')) {
    echo '<input type="button" id="edit" value="' . $string['edit'] . '" style="float:right; width:100px" class="ok" />';
}
  echo "</td></tr>\n";
if (stripos($user_details['roles'], 'Student') !== false) {
    if ($user_details['student_id'] == '') {
        $user_details['student_id'] = $string['unknown'];
    }
    $sid = $user_details['student_id'];
} else {
    $sid = '';
    $string['studentid'] = '';
}
  echo '<tr><td colspan="2" class="page_title" style="padding-left:2px !important">' . $string['user'] . ' <span style="font-weight:normal">' . $user_details['title'] . ' ' . $user_details['first_names'] . ' ' . $user_details['surname'] . '</span></td>';
  echo '<td class="field">' . $string['studentid'] . '</td><td>' . $sid . "</td></tr>\n";

  echo '<tr><td class="field">' . $string['email'] . '</td><td><a href="mailto:' . $user_details['email'] . '">' . $user_details['email'] . '</a></td>';
if (stripos($user_details['roles'], 'Student') !== false) {
    echo '<td class="field">' . $string['yearofstudy'] . '</td><td>' . $user_details['yearofstudy'] . '</td>';
} else {
    echo '<td class="field"></td><td></td>';
}
  echo "</tr>\n";
if (stripos($user_details['roles'], 'Student') !== false) {
    echo '<tr><td class="field">' . $string['course'] . '</td><td>' . $user_details['grade'] . ' - ' . $course_details['description'] . '</td>';
    echo '<td class="field"></td>';
    echo "</tr>\n";
} else {
    echo '<tr><td class="field">' . $string['type'] . '</td><td>' . $user_details['grade'] . '</td>';
    echo '<td class="field"></td>';
    echo "</tr>\n";
}
if ($demo) {
    echo '<tr><td class="field">' . $string['username'] . '</td><td>' . \demo::demo_replace_username($user_details['username'], $demo) . '</td>';
} else {
    echo '<tr><td class="field">' . $string['username'] . '</td><td>' . $user_details['username'] . '</td>';
}
if ($userObject->has_role('SysAdmin')) {
    echo '<td class="field">' . $string['password'] . '</td><td>';
    $authinfo = $authentication->version_info(true, false);
    if (stripos($authinfo, 'LDAP') === false) {    // Don't show if LDAP is on.
        echo "<input id=\"emailpasswordreset\" type=\"button\" value=\"{$string['reset']}\" />&nbsp;";
    }
    echo "<input id=\"forcepasswordreset\" type=\"button\" value=\"{$string['forcereset']}\" /></td></tr>\n";
} else {
    echo "<td class=\"field\"></td><td></td></tr>\n";
}
  echo "</tr>\n";
  echo '<tr><td class="field">' . $string['gender'] . '</td><td>' . $user_details['gender'] . '</td>';
if ($userObject->has_role('SysAdmin')) {
    echo "<td class=\"field\">ExamSys ID</td><td>$userID</td></tr>\n";
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

  echo '<tr><td>';
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

    for ($i = 0; $i < $results_no; $i++) {
        if (mb_strpos($paper[$i]['q_paper'], '[deleted') !== false) {
            $paper[$i]['q_paper'] = '<span style="color:#808080; text-decoration:line-through">' . $paper[$i]['q_paper'] . '</span>';
        }
        switch ($paper[$i]['type']) {
            case '0':
                echo '<tr><td><a class="paperreview" href="#" data-papername="' . $paper[$i]['crypt_name'] . '" data-papertype="' . $paper[$i]['type'] . '" data-metadataid="' . $paper[$i]['metadataID'] . '"><img src="../artwork/formative_16.gif" width="16" height="16" alt="Display marked paper for ' . $user_details['surname'] . '" /></a></td><td><a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '">' . $paper[$i]['q_paper'] . '</a></td><td>' . $paper[$i]['paper_type'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td>' . $paper[$i]['ipaddress'] . "</td></tr>\n";
                break;
            case '1':
                echo '<tr><td><a class="paperreview" href="#" data-papername="' . $paper[$i]['crypt_name'] . '" data-papertype="' . $paper[$i]['type'] . '" data-metadataid="' . $paper[$i]['metadataID'] . '"><img src="../artwork/progress_16.gif" width="16" height="16" alt="Display marked paper for ' . $user_details['surname'] . '" /></a></td><td><a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '">' . $paper[$i]['q_paper'] . '</a></td><td>' . $paper[$i]['paper_type'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td>' . $paper[$i]['ipaddress'] . "</td></tr>\n";
                break;
            case '2':
                if (stripos($user_details['roles'], 'External Examiner') !== false or stripos($user_details['roles'], 'Internal Reviewer') !== false) {
                    echo '<tr><td><img src="../artwork/summative_16.gif" width="16" height="16" /></td><td>&nbsp;<a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '"';
                } else {
                    echo '<tr><td><a class="paperreview" href="#" data-papername="' . $paper[$i]['crypt_name'] . '" data-papertype="' . $paper[$i]['type'] . '" data-metadataid="' . $paper[$i]['metadataID'] . '"><img src="../artwork/summative_16.gif" width="16" height="16" alt="Display marked paper for ' . $user_details['surname'] . '" /></a></td><td><a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '"';
                }
                if ($paper[$i]['started'] == '') {
                    echo ' style="color:red"';
                }
                echo '>' . $paper[$i]['q_paper'] . '</a></td><td';
                if ($paper[$i]['started'] == '') {
                    echo ' style="color:red"';
                }
                echo '>' . $string['summative'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td>' . $paper[$i]['ipaddress'] . "</td></tr>\n";
                break;
            case '3':
                echo '<tr><td><img src="../artwork/survey_16.gif" width="16" height="16" alt="Survey data is anonymous, no entry." /></td><td><a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '" class="paper">' . $paper[$i]['q_paper'] . '</a></td><td>' . $string['survey'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td>' . $paper[$i]['ipaddress'] . "</td></tr>\n";
                break;
            case '4':
                echo '<tr><td><a class="oscereview" href="#" data-paperid="' . $paper[$i]['id'] . '"><img src="../artwork/osce_16.gif" width="16" height="16" alt="Display marked paper for ' . $user_details['surname'] . '" /></a></td><td><a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '">' . $paper[$i]['q_paper'] . '</a></td><td>' . $paper[$i]['paper_type'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td style="color:#808080">' . $string['na'] . "</td></tr>\n";
                break;
            case '5':
                echo '<tr><td><img src="../artwork/offline_16.gif" width="16" height="16" alt="" /></td><td>' . $paper[$i]['q_paper'] . '</td><td>' . $string['offlinepaper'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td style="color:#808080">' . $string['na'] . "</td></tr>\n";
                break;
            case '6':
                echo '<tr><td><img src="../artwork/peer_16.gif" width="16" height="16" alt="" /></td><td><a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '">' . $paper[$i]['q_paper'] . '</a></td><td>' . $paper[$i]['paper_type'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td style="color:#808080">' . $string['na'] . "</td></tr>\n";
                break;
            case 'Objectives-based feedback report':
                echo '<tr><td><img src="../artwork/objectives_feedback_16.gif" width="16" height="16" alt="" /></td><td><a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '">' . $paper[$i]['q_paper'] . '</a></td><td>' . $string['Objectives Feedback report'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td>' . $paper[$i]['ipaddress'] . "</td></tr>\n";
                break;
            case 'Question-based feedback report':
                echo '<tr><td><img src="../artwork/questions_feedback_16.gif" width="16" height="16" alt="" /></td><td><a href="../paper/details.php?paperID=' . $paper[$i]['id'] . '">' . $paper[$i]['q_paper'] . '</a></td><td>' . $string['Questions Feedback report'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td>' . $paper[$i]['ipaddress'] . "</td></tr>\n";
                break;
            case $string['pagenotfound']:
                echo '<tr style="color:#C00000"><td><img src="../artwork/access_denied_16.gif" width="16" height="16" alt="" /></td><td>' . $paper[$i]['q_paper'] . '</td><td>' . $paper[$i]['paper_type'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td>' . $paper[$i]['ipaddress'] . "</td></tr>\n";
                break;
            case $string['accessdenied']:
                echo '<tr style="color:#C00000"><td><img src="../artwork/access_denied_16.gif" width="16" height="16" alt="" /></td><td>' . $paper[$i]['q_paper'] . '</td><td>' . $paper[$i]['paper_type'] . '</td><td>' . $paper[$i]['display_started'] . '</td><td>' . $paper[$i]['ipaddress'] . "</td></tr>\n";
                break;
        }
    }
    if ($results_no == 0) {
        echo '<tr><td colspan="8" style="color:#808080; text-align:center">' . $string['noassessmentstaken'] . "</td></tr>\n";
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

  $results = $mysqli->prepare('SELECT MAX(calendar_year) AS calendar_year FROM modules_student');
  $results->execute();
  $results->bind_result($most_recent_year);
  $results->fetch();
  $results->close();

  echo drawTabs('Modules', 4, '', $user_details['roles'], $bg_color, $string);
  echo '<tr><td class="coltitle" style="width:20px">&nbsp;</td><td class="coltitle">&nbsp;' . $string['moduleid'] . '</td><td class="coltitle">' . $string['name'] . '</td><td class="coltitle">' . $string['academicyear'] . "</td></tr>\n";
  $old_year = '';
  $row_no = 0;
  $user_modules = array();
  $current_year = false;

  $results = $mysqli->prepare('SELECT DISTINCT modules.id, modules.moduleid, fullname, modules_student.calendar_year, attempt FROM (modules_student, modules) WHERE mod_deleted IS NULL AND modules_student.idMod = modules.id AND userID = ? ORDER BY modules_student.calendar_year DESC, modules.moduleid');
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
    echo '<tr><td colspan="4"><table border="0" style="padding-bottom:5px; width:100%; color:#1E3287"><tr><td><nobr>' . $current_session;
    if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
        echo '&nbsp;&nbsp;<a class="modules" href="#" data-session="' . $current_session . '" data-grade="' . $user_details['grade'] . '"><img src="../artwork/pencil_16.png" width="16" height="16" alt="' . $string['editmodules'] . '" /></a>';
    }
    echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
}

for ($i = 0; $i < $row_no; $i++) {
    if ($user_modules[$i]['calendar_year'] != $old_year) {
        $display_year = $year_names[$user_modules[$i]['calendar_year']];
        echo '<tr><td colspan="4"><table border="0" style="padding-bottom:5px; width:100%; color:#1E3287"><tr><td><nobr>' . $display_year;
        if (($user_modules[$i]['calendar_year'] == $most_recent_year or $user_modules[$i]['calendar_year'] == $current_session) and $userObject->has_role(array('Admin', 'SysAdmin'))) {
            echo '&nbsp;&nbsp;<a class="modules" href="#" data-session="' . $user_modules[$i]['calendar_year'] . '" data-grade="' . $user_details['grade'] . '"); return false;"><img src="../artwork/pencil_16.png" width="16" height="16" alt="' . $string['editmodules'] . '" /></a>';
        }
        echo "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    }
    echo '<tr><td>';
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
  echo '<form name="accessibility" action="' . $_SERVER['PHP_SELF'] . "?userID=$userID&tab=admin\" method=\"post\" autocomplete=\"off\">";

  echo drawTabs('Admin', 1, '', $user_details['roles'], $bg_color, $string);
  echo "<tr><td class=\"coltitle\">&nbsp;</td></tr>\n";
  echo "<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n";

  $current_schools = SchoolUtils::get_admin_schools($userID, $mysqli);

  $old_faculty = '';
  $admin_school_no = 0;
  $results = $mysqli->prepare('SELECT schools.id, faculty.name, school FROM schools, faculty WHERE schools.facultyID = faculty.id ORDER BY faculty.name, school');
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
            echo '<input type="checkbox" name="sch' . $admin_school_no . "\" value=\"$schoolID\" checked />";
        } else {
            echo '<input type="checkbox" name="sch' . $admin_school_no . "\" value=\"$schoolID\" />";
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
    echo '<tr><td class="coltitle">&nbsp;&nbsp;&nbsp;' . $string['date'] . '</td><td class="coltitle">' . $string['paper'] . '</td><td class="coltitle">' . $string['note'] . '</td><td class="coltitle">' . $string['author'] . "</td></tr>\n";

    echo '<tr><td colspan="4"><input id="createname" type="button" name="createname" value="' .  $string['newnote'] . "\" /></td></tr>\n";

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
  echo drawTabs('Accessibility', 1, '', $user_details['roles'], $bg_color, $string);
  echo "<tr><td class=\"coltitle\">&nbsp;</td></tr>\n";
  echo '<tr><td>';

$result = $mysqli->prepare('
SELECT
    background, foreground, textsize, extra_time, marks_color, themecolor, labelcolor, font, unanswered,
    dismiss, medical, breaks, break_time, globalthemecolour, globalthemefont_colour, highlight_bgcolour
FROM
    special_needs
WHERE
    userID = ?
LIMIT 1
');
$result->bind_param('i', $userID);
$result->execute();
$result->store_result();
$result->bind_result(
    $background,
    $foreground,
    $textsize,
    $extra_time,
    $marks_color,
    $themecolor,
    $labelcolor,
    $font,
    $unansweredcolor,
    $dismisscolor,
    $medical,
    $breaks,
    $break_time,
    $globalthemecolour,
    $globalthemefontcolour,
    $highlightcolour
);
$result->fetch();
if ($result->num_rows > 0) {
    $special_needs = true;
}
if (!isset($background)) {
    $background = '';
}
if (!isset($foreground)) {
    $foreground = '';
}
if (!isset($themecolor)) {
    $themecolor = '';
}
if (!isset($labelcolor)) {
    $labelcolor = '';
}
if (!isset($marks_color)) {
    $marks_color = '';
}
if (!isset($textsize)) {
    $textsize = 0;
}
if (!isset($extra_time)) {
    $extra_time = 0;
}
if (!isset($break_time)) {
    $break_time = 0;
}
if (!isset($font)) {
    $font = '';
}
if (!isset($unansweredcolor)) {
    $unansweredcolor = '';
}
if (!isset($dismisscolor)) {
    $dismisscolor = '';
}
if (!isset($globalthemecolour)) {
    $globalthemecolour = '';
}
if (!isset($globalthemefontcolour)) {
    $globalthemefontcolour = '';
}
if (!isset($highlightcolour)) {
    $highlightcolour = '';
}
$result->close();

$times = array(5, 10, 25, 33, 50, 100, 200, 300);
$accessibilitydata['extratime'] = array();
foreach ($times as $individual_time) {
    if ($individual_time == $extra_time) {
        $accessibilitydata['extratime'][$individual_time] = true;
    } else {
        $accessibilitydata['extratime'][$individual_time] = false;
    }
}
$fontsizes = array(90, 100, 110, 120, 130, 140, 150, 175, 200, 300, 400);
$accessibilitydata['fontsize'] = array();
foreach ($fontsizes as $individual_fontsize) {
    if ($individual_fontsize == $textsize) {
        $accessibilitydata['fontsize'][$individual_fontsize] = true;
    } else {
        $accessibilitydata['fontsize'][$individual_fontsize] = false;
    }
}

$fontfamily = array('Arial', 'Arial Black', 'Calibri', 'Comic Sans MS', 'Courier New', 'Helvetica', 'Tahoma', 'Times New Roman', 'Verdana');
$accessibilitydata['fontfamily'] = array();
foreach ($fontfamily as $individual_fontfamily) {
    if ($individual_fontfamily == $font) {
        $accessibilitydata['fontfamily'][$individual_fontfamily] = true;
    } else {
        $accessibilitydata['fontfamily'][$individual_fontfamily] = false;
    }
}
$accessibilitydata['colours'] = array(
    array(
        'name' => $string['background'],
        'value' => $background,
        'radio_name' => 'bg',
        'input_name' => 'background'
    ),
    array(
        'name' => $string['foreground'],
        'value' => $foreground,
        'radio_name' => 'fg',
        'input_name' => 'foreground'
    ),
    array(
        'name' => $string['markscolour'],
        'value' => $marks_color,
        'radio_name' => 'marks',
        'input_name' => 'marks_color'
    ),
    array(
        'name' => $string['themecolour'],
        'value' => $themecolor,
        'radio_name' => 'theme',
        'input_name' => 'themecolor'
    ),
    array(
        'name' => $string['labelscolour'],
        'value' => $labelcolor,
        'radio_name' => 'labels',
        'input_name' => 'labelcolor'
    ),
    array(
        'name' => $string['unanswered'],
        'value' => $unansweredcolor,
        'radio_name' => 'unanswered',
        'input_name' => 'unansweredcolor'
    ),
    array(
        'name' => $string['dismisscolor'],
        'value' => $dismisscolor,
        'radio_name' => 'dismiss',
        'input_name' => 'dismisscolor'
    ),
    array(
        'name' => $string['highlightcolour'],
        'value' => $highlightcolour,
        'radio_name' => 'colour_highlight',
        'input_name' => 'highlightcolour'
    ),
    array(
        'name' => $string['globalthemecolour'],
        'value' => $globalthemecolour,
        'radio_name' => 'colour_globaltheme',
        'input_name' => 'globalthemecolour'
    ),
    array(
        'name' => $string['globalthemefontcolour'],
        'value' => $globalthemefontcolour,
        'radio_name' => 'colour_globalthemefont',
        'input_name' => 'globalthemefontcolour'
    ),
);
$accessibilitydata['medical'] = $medical;
$accessibilitydata['breaks'] = $breaks;
if ($configObject->get_setting('core', 'paper_breaktime_mins')) {
    $accessibilitydata['breaksscaletype'] = $string['breaktimeminpserhour'];
} else {
    $accessibilitydata['breaksscaletype'] = $string['breaktime'];
}
$scale = $configObject->get_setting('core', 'paper_breaktime_scale');
foreach ($scale as $individual_time) {
    if ($individual_time == $break_time) {
        $accessibilitydata['breaksscale'][$individual_time] = true;
    } else {
        $accessibilitydata['breaksscale'][$individual_time] = false;
    }
}
// Still display students settings even if no longer available as an option.
if ($break_time != 0 and !in_array($break_time, $scale)) {
    $accessibilitydata['oldbreakscale'] = array($break_time, true);
} else {
    $accessibilitydata['oldbreakscale'] = '';
}
if ($userObject->has_role(array('Admin', 'SysAdmin'))) {
    $accessibilitydata['admin'] = true;
} else {
    $accessibilitydata['admin'] = false;
}
$accessibilitydata['userid'] = $userID;
$render->render($accessibilitydata, $string, 'users/accessibility/accessibility.html');
?>

</td>
</tr>
</table>

<?php
  // Metadata tab.
  $metadata_no = 0;
if ($tab == 'metadata') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Metadata_tab\" style=\"width:100%\">\n";
} else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Metadata_tab\" style=\"width:100%; display:none\">\n";
}
  echo '<form name="metadata" action="' . $_SERVER['PHP_SELF'] . "?userID=$userID&tab=metadata\" method=\"post\" autocomplete=\"off\">";
  echo drawTabs('Metadata', 5, '', $user_details['roles'], $bg_color, $string);
  echo '<tr><td class="coltitle">&nbsp;' . $string['moduleid'] . '</td><td class="coltitle">' . $string['academicyear'] . '</td><td class="coltitle">' . $string['type'] . '</td><td class="coltitle">' . $string['value'] . "</td><td class=\"coltitle\" style=\"width:30%\">&nbsp;</td></tr>\n";
  $stmt = $mysqli->prepare('SELECT modules.id, modules.moduleID, fullname, calendar_year, type, value FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND userID = ?');
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($mod_id, $moduleID, $fullname, $calendar_year, $type, $value);
while ($stmt->fetch()) {
    echo "<tr><td>&nbsp;$moduleID: $fullname<input type=\"hidden\" name=\"meta_moduleID$metadata_no\" value=\"$mod_id\" /></td><td>$calendar_year<input type=\"hidden\" name=\"meta_calendar_year$metadata_no\" value=\"$calendar_year\" /></td><td>$type<input type=\"hidden\" name=\"meta_type$metadata_no\" value=\"$type\" /></td><td><select name=\"meta_value$metadata_no\">";
    $result = $mysqli->prepare('SELECT DISTINCT value FROM users_metadata WHERE calendar_year = ? AND idMod = ? AND type = ?');
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
    echo '<tr><td colspan="5" style="text-align:center">';
    if ($metadata_no > 0) {
        echo '<input type="submit" name="save_metadata" value="' . $string['save'] . '" class="ok" />';
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
  echo '<tr><td class="coltitle">&nbsp;' . $string['team'] . '</td><td class="coltitle">&nbsp;</td><td class="coltitle">' . $string['dateadded'] . "</td></tr>\n";
if ($userObject->has_role('Admin') or $userObject->has_role('SysAdmin')) {
    echo '<tr><td id="teams" colspan="4">&nbsp;<img src="../artwork/pencil_16.png" width="16" height="16" alt="' . $string['editteams'] . '" />&nbsp;<a href="#">' . $string['editteams'] . "</a></td></tr>\n";
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

?>
</table>
<?php
if ($tab == 'roles') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"roles_tab\" style=\"width:100%\">\n";
} else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"roles_tab\" style=\"width:100%; display:none\">\n";
}
echo drawTabs('roles', 1, '', $user_details['roles'], $bg_color, $string);
echo '<tr><td class="coltitle">&nbsp;</td></tr>' . "\n";
echo '<tr><td>';

if ($can_edit_roles) {
    $roles_template = 'users/details/editroles.html';
} else {
    $roles_template = 'users/details/roles.html';
}

$roledata = [
    'error' => isset($role_save_error),
    'rolelist' => Role::listByGrouping(),
    'userroles' => $user_roles,
    'userid' => $userID,
];
$render->render($roledata, $string, $roles_template);

echo '</td></tr>';
?>
</table>
<?php
if ($tab == 'profileaudit') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"profileaudit_tab\" style=\"width:100%\">\n";
} else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"profileaudit_tab\" style=\"width:100%; display:none\">\n";
}
echo drawTabs('profileaudit', 1, '', $user_details['roles'], $bg_color, $string);
echo '<tr><td class="coltitle">&nbsp;</td></tr>' . "\n";
echo '<tr><td>';


$logger = new Logger($mysqli);

// Get the changes to be used later
$changes = $logger->get_changes('User Profile', $userID);
$profileauditdata = [];
$profilecount = 0;
foreach ($changes as $change) {
    $profileauditdata[] = array(
        'date' => date($configObject->get('cfg_very_short_datetime_php'), $change['date']),
        'part' => $change['part'],
        'old' => $change['old'],
        'new' => $change['new'],
        'title' => $change['title'],
        'initials' => $change['initials'],
        'surname' => $change['surname'],
        'rowclass' => $profilecount % 2 ? 'auditrow auditaltrow' : 'auditrow',
        'displaycolour' => $change['part'] != 'font' && $change['part'] != 'textsize' ? true : false,
    );
    $profilecount++;
}
$render->render($profileauditdata, $string, 'users/details/profileaudit.html');

echo '</td></tr>';
?>
</table>
</div>
<script src="../js/userdetailsinit.min.js"></script>
<?php

$mysqli->close();

$dataset['name'] = 'dataset';
$dataset['attributes']['datetime'] = $configObject->get('cfg_tablesorter_date_time');
$dataset['attributes']['userid'] = $userID;
$dataset['attributes']['surname'] = str_replace("'", '&#8217;', $user_details['surname']);
$dataset['attributes']['email'] = urlencode($user_details['email']);
$dataset['attributes']['sid'] = $student_id;
$dataset['attributes']['searchusername'] = $search_username;
$dataset['attributes']['searchsurname'] = $search_surname;
$dataset['attributes']['sysadmin'] = $is_sysadmin;
$render->render($dataset, array(), 'dataset.html');
?>
</body>
</html>
