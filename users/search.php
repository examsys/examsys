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
* The results screen of a search for a user(s).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/demo_replace.inc';
require_once '../include/errors.php';

function get_special_needs($db) {
  $needs_array = array();
  $result = $db->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $needs_array[$tmp_userID] = '1';
  }
  $result->close();
  
  return $needs_array;
}

if ($userObject->has_role('Demo')) {
  $demo = true;
} else {
  $demo = false;
}
$sortby = 'surname';
$ordering = 'asc';
$moduleID = check_var('module', $_REQUEST, false, true, true);
$calendar_year = check_var('calendar_year', $_GET, false, true, true);

$get_staff = !is_null(check_var('staff', $_GET, false, true, true));
$get_inactive = !is_null(check_var('inactive', $_GET, false, true, true));
$get_sysadmin = !is_null(check_var('sysadminstaff', $_GET, false, true, true));
$get_admin = !is_null(check_var('adminstaff', $_GET, false, true, true));
$get_invigilators = !is_null(check_var('invigilators', $_GET, false, true, true));
$get_standardstaff = !is_null(check_var('standardsstaff', $_GET, false, true, true));
$get_external = !is_null(check_var('externals', $_GET, false, true, true));
$get_internal = !is_null(check_var('internals', $_GET, false, true, true));
$get_students = !is_null(check_var('students', $_GET, false, true, true));
$get_graduates = !is_null(check_var('graduates', $_GET, false, true, true));
$get_leavers = !is_null(check_var('leavers', $_GET, false, true, true));
$get_suspended = !is_null(check_var('suspended', $_GET, false, true, true));
$get_locked = !is_null(check_var('locked', $_GET, false, true, true));

$student_id = check_var('student_id', $_GET, false, true, true);
$search_surname = check_var('search_surname', $_GET, false, true, true);
$search_username = check_var('search_username', $_GET, false, true, true);

if (is_null($calendar_year) or $calendar_year === '%') {
  $calendar_year_sql = '';
  $calendar_year_param_types = '';
  $calendar_year_params = array();
} else {
  $calendar_year_sql = " AND calendar_year = ?";
  $calendar_year_param_types = 'i';
  $calendar_year_params = array($calendar_year);
}

$needs_array = get_special_needs($mysqli);

// We should only display the first 10,000 rows to avoid browser issues.
$limit = 10000;

if (isset($_GET['submit'])) {
  $username_sql = '';
  $username_param_types = '';
  $username_params = array();
  $title_sql = '';
  $title_param_types = '';
  $title_params = array();
  $surname_sql = '';
  $surname_param_types = '';
  $surname_params = array();
  $initials_sql = '';
  $initials_param_types = '';
  $initials_params = array();
  $student_id_sql = '';
  $student_id_param_types = '';
  $student_id_params = array();
  $param_types = '';
  $params = array();

  if (!is_null($search_surname)) {
    $tmp_surname = str_replace("*", "%", trim($search_surname));

    $tmp_titles = explode(',', $string['title_types']);
    foreach ($tmp_titles as $tmp_title) {
      if (substr_count(strtolower($tmp_surname), strtolower($tmp_title . ' ')) > 0) {
        $title_sql = " AND title = ?";
        $title_param_types = 's';
        $title_params = array($tmp_title);
      }
      $tmp_surname = preg_replace("/(" . $tmp_title . " )/i","",$tmp_surname);
    }

    $sections = preg_split('[,.]',$tmp_surname);
    if (count($sections) > 1) {    // Search for initials.
      if (strlen($sections[0]) < strlen($sections[1])) {
        $tmp_initials = $mysqli->real_escape_string(trim($sections[0]));
        $tmp_surname = trim($sections[1]);
      } else {
        $tmp_initials = $mysqli->real_escape_string(trim($sections[1]));
        $tmp_surname = trim($sections[0]);
      }
      $initials_sql = " AND initials LIKE ?";
      $initials_param_types = 's';
      $initials_params = array($tmp_initials . '%');
    }
    $tmp_surname = $mysqli->real_escape_string(str_replace('*', '%', $tmp_surname));
    $surname_sql = " AND surname LIKE ?";
    $surname_param_types = 's';
    $surname_params = array($tmp_surname);
  }

  if (!is_null($search_username) and $search_username !== '') {
    $tmp_username = $mysqli->real_escape_string(str_replace('*', '%', trim($search_username)));
    $username_sql = " AND users.username LIKE ?";
    $username_param_types = 's';
    $username_params[] = $tmp_username;
  }

  if (!is_null($student_id) and $student_id !== '') {
    $tmp_studentid = $mysqli->real_escape_string(trim($student_id));
    $student_id_sql = " AND student_id = ?";
    $student_id_param_types = 'i';
    $student_id_params[] = $tmp_studentid;
  }

  $roles_sql = '';
  if ($get_students or (!is_null($student_id) and $student_id !== '')) $roles_sql .= " OR roles LIKE '%Student'";
  if ($get_staff) $roles_sql .= " OR roles LIKE '%Staff%'";
  if ($get_admin) $roles_sql .= " OR roles LIKE '%,Admin%'";
  if ($get_sysadmin) $roles_sql .= " OR roles LIKE '%,SysAdmin%'";
  if ($get_standardstaff) $roles_sql .= " OR roles LIKE '%,Standards Setter%'";
  if ($get_inactive) $roles_sql .= " OR roles LIKE '%inactive%'";
  if ($get_external) $roles_sql .= " OR (roles = 'External Examiner' AND grade != 'left')";
  if ($get_internal) $roles_sql .= " OR (roles = 'Internal Reviewer' AND grade != 'left')";
  if ($get_invigilators) $roles_sql .= " OR roles = 'Invigilator'";
  if ($get_graduates) $roles_sql .= " OR roles = 'Graduate'";
  if ($get_leavers) $roles_sql .= " OR roles = 'left'";
  if ($get_suspended) $roles_sql .= " OR roles = 'suspended'";
  if ($get_locked) $roles_sql .= " OR roles = 'locked'";
  if ($roles_sql != '') $roles_sql = '(' . substr($roles_sql,4) . ')';
  if (!$get_leavers and $get_staff) $roles_sql .= " AND grade != 'left'";

	$user_no = 0;
  if ($roles_sql != '') {
    $seach_for_staff = ($get_staff or $get_inactive or $get_sysadmin or $get_admin or $get_invigilators or $get_standardstaff);
    $search_for_reviewers = ($get_external or $get_internal);

    if ($seach_for_staff and !is_null($moduleID)) {
      $query_string = "(SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
      FROM (users, modules_student, modules)
      LEFT JOIN sid ON users.id = sid.userID
      WHERE modules_student.idMod = modules.id
      AND users.id = modules_student.userID
      AND modules_student.idMod = ?
      AND $roles_sql$surname_sql$title_sql$username_sql$initials_sql$calendar_year_sql
      AND user_deleted IS NULL)
      UNION
      (SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
      FROM (users, modules_staff, modules)
      LEFT JOIN sid ON users.id = sid.userID
      WHERE modules_staff.idMod = modules.id
      AND users.id = modules_staff.memberID
      AND modules_staff.idMod = ?
      AND $roles_sql$surname_sql$title_sql$username_sql$initials_sql
      AND user_deleted IS NULL LIMIT $limit)";
      $sql_params = array($moduleID);
      $param_types = 's' . $surname_param_types . $title_param_types . $username_param_types . $initials_param_types .
          $calendar_year_param_types . 's' . $surname_param_types . $title_param_types . $username_param_types .
          $initials_param_types;
      $params = array_merge($sql_params, $surname_params, $title_params, $username_params, $initials_params,
          $calendar_year_params, $sql_params, $surname_params, $title_params, $username_params, $initials_params);
    } elseif ($seach_for_staff or $search_for_reviewers) {
      $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
        FROM users
        LEFT JOIN sid ON users.id = sid.userID
        WHERE $roles_sql$surname_sql$title_sql$username_sql$initials_sql
        AND user_deleted IS NULL LIMIT $limit";
      $param_types = $surname_param_types . $title_param_types . $username_param_types . $initials_param_types;
      $params = array_merge($surname_params, $title_params, $username_params, $initials_params);
    } elseif (is_null($moduleID)) {
      // Students no module link.
      $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
        FROM users
        LEFT JOIN sid ON users.id = sid.userID
        WHERE $roles_sql$surname_sql$title_sql$username_sql$student_id_sql$initials_sql
        AND user_deleted IS NULL LIMIT $limit";
      $param_types = $surname_param_types . $title_param_types . $username_param_types . $student_id_param_types .
          $initials_param_types;
      $params = array_merge($surname_params, $title_params, $username_params, $student_id_params, $initials_params);
    } else {
      // Students on a particular module.
      $roles_sql = ' AND ' . $roles_sql;
      $module_sql = " AND idMod LIKE ? ";
      $module_params = array($moduleID);
      $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email
        FROM (users, modules_student)
        LEFT JOIN sid ON users.id = sid.userID
        WHERE users.id = modules_student.userID $module_sql$calendar_year_sql$roles_sql$surname_sql$title_sql$username_sql$student_id_sql$initials_sql
        AND user_deleted IS NULL LIMIT $limit";
      $param_types = 's' . $calendar_year_param_types . $surname_param_types . $title_param_types . $username_param_types .
          $student_id_param_types . $initials_param_types;
      $params = array_merge($module_params, $calendar_year_params, $surname_params, $title_params, $username_params,
          $student_id_params, $initials_params);
    }

    // Create an array of references to the parameter values.
    $ref_params = array();
    foreach ($params as &$param) {
      $ref_params[] = &$param;
    }

    $user_data = $mysqli->prepare($query_string);
    if (count($params) > 0) {
      // Only call if the query has parameters.
      call_user_func_array(array($user_data, "bind_param"), array_merge(array($param_types), $ref_params));
    }
    $user_data->execute();
    $user_data->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email);
    $user_data->store_result();
    $user_no = number_format($user_data->num_rows);
  }
}

$module_id = check_var('moduleID', $_GET, false, true, true);
$paper_id = check_var('paperID', $_GET, false, true, true);
$team = check_var('team', $_GET, false, true, true);
$email = check_var('email', $_GET, false, true, true);
$temporary_surname = check_var('tmp_surname', $_GET, false, true, true);
$temporary_courseid = check_var('tmp_courseID', $_GET, false, true, true);
$temporary_yearid = check_var('tmp_yearID', $_GET, false, true, true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rog&#333;: <?php echo $string['usermanagement'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
  <style type="text/css">
    a {color:black}
    .coltitle {cursor:hand; background-color:#F1F5FB; color:black}
    #usertable td {padding-left:6px}
    .fn {color:#A5A5A5}
    .uline {line-height: 150%}
    .uline:hover {background-color:#FFE7A2}
    .uline.highlight {background-color:#FFBD69}
    td {padding-left: 0 !important}
    .l {line-height: 160%}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function addUserID(ID, clearall) {
      if (clearall) {
        $('#userID').val(',' + ID);
      } else {
        cur_value = $('#userID').val() + ',' + ID;
        $('#userID').val(cur_value);
      }
    }

    function subUserID(ID) {
      var tmpuserID = ',' + ID;
      new_value = $('#userID').val().replace(tmpuserID, '');
      $('#userID').val(new_value);
    }

    function clearAll() {
      $('.highlight').removeClass('highlight');
    }

    function selUser(userID, lineID, menuID, roles, evt) {
      $('#menu2a').hide();
      $('#menu' + menuID).show();

      if (evt.ctrlKey == false && evt.metaKey == false) {
        clearAll();
        $('#' + lineID).addClass('highlight');
        addUserID(userID, true);
      } else {
        if ($('#' + lineID).hasClass('highlight')) {
          $('#' + lineID).removeClass('highlight');
          subUserID(userID);
        } else {
          $('#' + lineID).addClass('highlight');
          addUserID(userID, false);
        }
      }
      $('#roles').val(roles);
      checkRoles();
      
      evt.stopPropagation();
    }

    function userOff() {
      $('#menu2a').show();
      $('#menu2b').hide();
      $('#menu2c').hide();

      clearAll();
    }

    function profile(userID) {
      document.location.href='details.php?search_surname=<?php echo $search_surname; ?>'
              + '&search_username=<?php echo $search_username ?>&student_id=<?php echo $student_id; ?>'
              + '&moduleID=<?php echo $team; if (!is_null($moduleID)) echo '&module=' . $moduleID; ?>'
              + '&calendar_year=<?php echo $calendar_year ?>&students=<?php if ($get_students) echo 'on'; ?>'
              + '&submit=Search&userID=' + userID + '&email=<?php echo $email; ?>'
              + '&tmp_surname=<?php echo $temporary_surname; ?>&tmp_courseID=<?php echo $temporary_courseid; ?>'
              + '&tmp_yearID=<?php echo $temporary_yearid; ?>';
    }
    
    $(function () {
      if ($("#maindata").find("tr").size() > 1) {
        $("#maindata").tablesorter({ 
          // sort on the third column, order asc 
          sortList: [[3,0]] 
        });
      }

      $(document).click(function() {
        $('#menudiv').hide();
      });
    });
  </script>
</head>

<?php
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu(92);
	
  if (isset($_GET['submit']) or !is_null($paper_id) or !is_null($module_id)) {
    echo "<body>\n";

    include '../include/user_search_options.php';

    echo "<div id=\"content\" class=\"content\">\n";
  } else {
    echo "<body>\n";

    include '../include/user_search_options.php';

    echo "<div id=\"content\" class=\"content\">\n";
    echo "<div class=\"head_title\">\n";
    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>";
    echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
    if (!is_null($moduleID)) {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $moduleID . '">' . module_utils::get_moduleid_from_id($moduleID, $mysqli) . '</a>';
    }
    echo "</div><div class=\"page_title\">" . $string['usersearch'] . "</div>";
    echo "</div>\n</div>\n</body></html>\n";
    exit();     // There is no search submit so just exit.
  }
?>

<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>?sortby=<?php echo $sortby; ?>&order=<?php echo $ordering; ?>" autocomplete="off">

<div class="head_title">
<div style="float:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
<?php
echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
if (!is_null($moduleID)) {
  echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $moduleID . '">' . module_utils::get_moduleid_from_id($moduleID, $mysqli) . '</a>';
}
echo "</div><div class=\"page_title\">" . $string['usersearch'] . " ($user_no): <span style=\"font-weight: normal\">";
if (!is_null($paper_id)) {
  echo implode(', ', array_values($paper_modules)) . ' (' . $paper_calendar_year . ')';
} elseif (!is_null($search_surname)) {
  echo "'" . $search_surname . "'";
} elseif (!is_null($moduleID) and $moduleID !== '%') {
  echo module_utils::get_moduleid_from_id($moduleID, $mysqli);
  if (!is_null($calendar_year) and $calendar_year !== '%' and $get_students) {
    echo ' (' . $calendar_year . ')';
  }
} elseif (!is_null($search_username)) {
  echo $search_username;
} elseif (!is_null($student_id)) {
  echo $student_id;
} elseif (!is_null($calendar_year) and $calendar_year != '%') {
  echo $calendar_year;
}
echo "</span></div>\n";
echo "</div>\n";

if ($roles_sql == '') {
  echo "<div>" . $notice->info_strip($string['msg1'], 100) . "</div>";
  exit();
}

if ($user_data->num_rows == $limit) {
  echo " <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"width:100%\"><tr><td class=\"redwarn\" style=\"width:40px; line-height:0; padding-left:0\"><img src=\"../artwork/exclamation_red_bg.png\" width=\"32\" height=\"32\" alt=\""
    . $string['warning'] . "\" /></td>" . "<td class=\"redwarn\">" . $string['largeresult'] . "</td></tr></table>";
}

$table_order = array('#1', '#2', $string['title'], 'Surname', 'First Names', $string['username'], $string['studentid'], $string['year'], $string['course']);
echo "<table id=\"maindata\" class=\"header tablesorter\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%\">\n";
echo "<thead>\n";
echo "<tr>\n";
foreach ($table_order as $display) {
  if ($display{0} == '#') {
    echo "<th>&nbsp;</th>";
  } else {
    echo "<th class=\"col\">$display</th>\n";
  }    
}
?>
</tr>
</thead>

<tbody>
<?php

if ($user_data->num_rows == 0) {
  echo "</table>" . $notice->info_strip($string['msg2'], 100) . "</div>\n</body>\n</html>\n";
  exit();
}

$x = 0;
$photodirectory = rogo_directory::get_directory('user_photo');
while ($user_data->fetch()) {
  if ($userObject->has_role('SysAdmin')) {
    echo "<tr class=\"l\" id=\"$x\" onclick=\"selUser('$tmp_id',$x,'2c','" . $tmp_roles . "',event); return false;\" ondblclick=\"profile('$tmp_id'); return false;\">";
  } else {
    echo "<tr class=\"l\" id=\"$x\" onclick=\"selUser('$tmp_id',$x,'2b','" . $tmp_roles . "',event); return false;\" ondblclick=\"profile('$tmp_id'); return false;\">";
  }
  $photoname = UserUtils::student_photo_exist($tmp_username);
  if ($photoname) {
    echo '<td><img src="../artwork/photo.png" width="16" height="16" alt="Photo" /></td>';
  } else {
    echo '<td></td>';
  }
  if (array_key_exists($tmp_id, $needs_array)) {
    echo '<td><img src="../artwork/accessibility_16.png" width="16" height="16" /></td>';
  } else {
    echo '<td></td>';
  }

  if ($tmp_title != null) {
    $lowertitle = mb_strtolower($tmp_title);
    if (array_key_exists($lowertitle, $string)) {
      echo '<td>' . $string[$lowertitle] . '</td>';
    } else {
      echo '<td></td>';
    }
  } else {
    echo '<td></td>';
  }
  
  if ($tmp_first_names == '') $tmp_first_names = ' ';
  if ($tmp_surname == '') $tmp_surname = ' ';
  echo '<td>' . demo_replace($tmp_surname, $demo, true, $tmp_surname{0}) . '</td>';
  echo '<td>' . demo_replace($tmp_first_names, $demo, true, $tmp_first_names{0}) . '</td>';
  echo '<td>' . demo_replace($tmp_username, $demo, false) . '</td>';
      
  if (strpos($tmp_roles, 'Student') !== false) {
    if ($tmp_student_id == NULL) {
      echo '<td class="fn">' . $string['unknown'] . '</td>';
    } else {
      echo '<td>' . demo_replace_number($tmp_student_id, $demo) . '</td>';
    }
  } elseif (strpos($tmp_roles, 'Staff') !== false) {
    echo "<td>Staff</td>";
  } else {
    echo "<td class=\"fn\">" . $string['na'] . "</td>";
  }
  echo "<td>$tmp_yearofstudy</td>";
  echo "<td>$tmp_grade</td></tr>\n";
  
  $x++;
}

$user_data->close();
$mysqli->close();
?>
</tbody>
</table>
</div>

</body>
</html>