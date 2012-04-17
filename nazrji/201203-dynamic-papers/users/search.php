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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

$sortby = 'surname';
$ordering = 'asc';
$moduleID = '%';
$calendar_year = '%';

if (isset($_GET['submit'])) {
  $username_sql = '';
  $title_sql = '';
  $surname_sql = '';
  $initials_sql = '';
  $student_id_sql = '';
  $title = '';

  if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
  if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];
  if (isset($_GET['team']) and $_GET['team'] != '') $moduleID = $_GET['team'];
  if (isset($_GET['calendar_year']) and $_GET['calendar_year'] != '') $calendar_year = $_GET['calendar_year'];
  
  if (isset($_GET['search_surname']) and $_GET['search_surname'] != '') {
    $tmp_surname = str_replace("*","%",trim($_GET['search_surname']));
    
    $tmp_titles = explode(',', $string['title_types']);
    foreach ($tmp_titles as $tmp_title) {
      if (substr_count(strtolower($tmp_surname), strtolower($tmp_title . ' ')) > 0) $title_sql = " AND title='$tmp_title'";
      $tmp_surname = preg_replace("/(" . $tmp_title . " )/i","",$tmp_surname);
    }
    
    $sections = preg_split('[,.]',$tmp_surname);
    if (count($sections) > 1) {    // Search for initials.
      if (strlen($sections[0]) < strlen($sections[1])) {
        $initials_sql = " AND initials LIKE '" . trim($sections[0]) . "%'";
        $tmp_surname = trim($sections[1]);
      } else {
        $initials_sql = " AND initials LIKE '" . trim($sections[1]) . "%'";
        $tmp_surname = trim($sections[0]);
      }
    } else {
      $initials_sql = '';
    }
    $tmp_surname = str_replace('*','%',$tmp_surname);
    $surname_sql = " AND surname LIKE '$tmp_surname'";
  }
  if ($_GET['search_username'] != '') {
    $tmp_username = str_replace('*','%',trim($_GET['search_username']));
    $username_sql = " AND users.username LIKE '$tmp_username'";
  }

  if ($_GET['student_id'] != '') {
    $student_id_sql = " AND student_id ='" . trim($_GET['student_id']) . "'";
  }

  $roles_sql = '';
  if ((isset($_GET['students']) and $_GET['students'] != '') or (isset($_GET['student_id']) and $_GET['student_id'] != '') ) $roles_sql .= " OR roles='Student'";
  if (isset($_GET['staff']) and $_GET['staff'] != '') $roles_sql .= " OR roles LIKE '%Staff%'";
  if (isset($_GET['adminstaff']) and $_GET['adminstaff'] != '') $roles_sql .= " OR roles LIKE '%,Admin%'";
  if (isset($_GET['inactive']) and $_GET['inactive'] != '') $roles_sql .= " OR roles LIKE '%inactive%'";
  if (isset($_GET['externals']) and $_GET['externals'] != '') $roles_sql .= " OR (roles = 'External Examiner' AND grade != 'left')";
  if (isset($_GET['invigilators']) and $_GET['invigilators'] != '') $roles_sql .= " OR roles = 'Invigilator'";
  if (isset($_GET['graduates']) and $_GET['graduates'] != '') $roles_sql .= " OR roles = 'Graduate'";
  if (isset($_GET['leavers']) and $_GET['leavers'] != '') $roles_sql .= " OR roles = 'left'";
  if (isset($_GET['suspended']) and $_GET['suspended'] != '') $roles_sql .= " OR roles = 'suspended'";
  if ($roles_sql != '') $roles_sql = '(' . substr($roles_sql,4) . ')';
  if (isset($_GET['leavers']) and $_GET['leavers'] == '' and isset($_GET['staff']) and  $_GET['staff'] != '') $roles_sql .= " AND grade != 'left'";

  $needs_array = array();
  $result = $mysqli->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $needs_array[$tmp_userID] = '1';
  }
  $result->close();
  
  $user_no = 0;
  if ($roles_sql != '') {
    if ((isset($_GET['staff']) and $_GET['staff'] != '') or (isset($_GET['inactive']) and $_GET['inactive'] != '') or (isset($_GET['adminstaff']) and $_GET['adminstaff'] != '') or (isset($_GET['invigilators']) and $_GET['invigilators'] != '')) {
      $query_string = "SELECT DISTINCT users.id, roles, NULL AS student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM users LEFT JOIN teams ON users.id=teams.memberID AND teams.name LIKE '" . $_GET['team'] . "' WHERE $roles_sql$surname_sql$title_sql$username_sql$initials_sql ORDER BY " . $sortby . " " . $ordering;
    } elseif (isset($_GET['externals']) and $_GET['externals'] != '') {
      $query_string = "SELECT DISTINCT users.id, roles, NULL AS student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM users WHERE $roles_sql$surname_sql$title_sql$username_sql$initials_sql ORDER BY " . $sortby . " " . $ordering;
    } else {
      // Student search
      if ($moduleID == '%') {
        $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM users LEFT JOIN sid ON users.id=sid.userID WHERE $roles_sql$surname_sql$title_sql$username_sql$student_id_sql$initials_sql ORDER BY " . $sortby . " " . $ordering;
      } else {
        $roles_sql = 'AND ' . $roles_sql;
        if ($moduleID == '%') {
          $module_sql = '';
        } else {
          $module_sql = " AND moduleid LIKE '$moduleID'";
        }
        if ($calendar_year == '%') {
          $calendar_year_sql = '';
        } else {
          $calendar_year_sql = " AND calendar_year LIKE '$calendar_year'";
        }
        $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email FROM (users, student_modules) LEFT JOIN sid ON users.id=sid.userID WHERE users.id=student_modules.userID $module_sql$calendar_year_sql$roles_sql$surname_sql$title_sql$username_sql$student_id_sql$initials_sql ORDER BY " . $sortby . " " . $ordering;
      }
    }
    
    $user_data = $mysqli->prepare($query_string);
    $user_data->execute();
    $user_data->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email);
    $user_data->store_result();
    $user_no = number_format($user_data->num_rows);
  }
} elseif (isset($_GET['paperID'])) {
  if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
  if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

  $needs_array = array();
  $result = $mysqli->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $needs_array[$tmp_userID] = '1';
  }
  $result->close();
  
  // Get the year and modules from the paper properties.
  $result = $mysqli->prepare("SELECT calendar_year, moduleID FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_calendar_year, $paper_moduleID);
  $result->fetch();
  $result->close();
  
  $moduleID = str_replace(",", "','", $paper_moduleID);
  $roles_sql = "AND roles='Student' AND grade != 'left'";

  $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email, moduleid FROM (users, student_modules) LEFT JOIN sid ON users.id=sid.userID WHERE users.id=student_modules.userID AND moduleid IN ('$moduleID') AND calendar_year='$paper_calendar_year' $roles_sql ORDER BY " . $sortby . " " . $ordering;
  $user_data = $mysqli->prepare($query_string);
  $user_data->execute();
  $user_data->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email, $tmp_moduleid);
  $user_data->store_result();
  $user_no = number_format($user_data->num_rows);
} elseif (isset($_GET['moduleID'])) {
  if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
  if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

  $needs_array = array();
  $result = $mysqli->prepare("SELECT userID FROM special_needs");
  $result->execute();
  $result->bind_result($tmp_userID);
  while ($result->fetch()) {
    $needs_array[$tmp_userID] = '1';
  }
  $result->close();
  
  $calendar_year = '2011/12';
  
  $moduleID = $_GET['moduleID'];
  $roles_sql = "AND roles='Student' AND grade != 'left'";

  $query_string = "SELECT DISTINCT users.id, roles, student_id, surname, initials, first_names, title, users.username, grade, yearofstudy, email, moduleid FROM (users, student_modules) LEFT JOIN sid ON users.id=sid.userID WHERE users.id=student_modules.userID AND moduleid IN ('$moduleID') AND calendar_year='$calendar_year' $roles_sql ORDER BY " . $sortby . " " . $ordering;
  $user_data = $mysqli->prepare($query_string);
  $user_data->execute();
  $user_data->bind_result($tmp_id, $tmp_roles, $tmp_student_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title, $tmp_username, $tmp_grade, $tmp_yearofstudy, $tmp_email, $tmp_moduleid);
  $user_data->store_result();
  $user_no = number_format($user_data->num_rows);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō: <?php echo $string['usermanagement'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
  a {color:black}
  input[type=text], select {font-family:Arail,sans-serif; border: 1px solid #7F9DB9}
  .coltitle {cursor:hand; background-color:#F1F5FB; color:black}
  #usertable td {padding-left:6px}
  .fn {color:#A5A5A5}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script type="text/javascript">
    function selUser(userID, lineID, menuID) {
      tmp_ID = document.PapersMenu.oldUserID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }
      document.getElementById('menu2a').style.display = 'none';
      document.getElementById('menu' + menuID).style.display = 'block';

      document.PapersMenu.userID.value = userID;

      document.getElementById(lineID).style.backgroundColor = '#B3C8E8';

      document.PapersMenu.oldUserID.value = lineID;
    }

    function userOff() {
      document.getElementById('menu2a').style.display = 'block';
      document.getElementById('menu2b').style.display = 'none';
      document.getElementById('menu2c').style.display = 'none';
      tmp_ID = document.PapersMenu.oldUserID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }
    }

    function updateCohortDetails() {
      document.PapersMenu.tmp_surname.value = '<?php if (isset($_GET['surname'])) echo $_GET['surname']; ?>';
      document.PapersMenu.tmp_courseID.value = '<?php if (isset($courseID)) echo $courseID; ?>';
      document.PapersMenu.tmp_yearID.value = '<?php if (isset($yearID)) echo $yearID; ?>';
    }

    function lon(lineID) {
      if (lineID != document.PapersMenu.oldUserID.value) {
        document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function loff(lineID) {
      if (lineID != document.PapersMenu.oldUserID.value) {
        document.getElementById(lineID).style.backgroundColor = '';
      }
    }

    function profile(userID) {
      document.location.href='details.php?search_surname=<?php if (isset($_GET['search_surname'])) echo $_GET['search_surname']; ?>&search_username=<?php if (isset($_GET['search_username'])) echo $_GET['search_username']; ?>&student_id=<?php if (isset($_GET['student_id'])) echo $_GET['student_id']; ?>&moduleID=<?php if (isset($_GET['team'])) echo $_GET['team']; ?>&calendar_year=<?php if (isset($_GET['calendar_year'])) echo $_GET['calendar_year']; ?>&students=<?php if (isset($_GET['students'])) echo $_GET['students']; ?>&submit=Search&userID=' + userID + '&email=<?php if (isset($_GET['email'])) echo $_GET['email']; ?>&oldUserID=<?php if (isset($_GET['oldUserID'])) echo $_GET['oldUserID']; ?>&tmp_surname=<?php if (isset($_GET['tmp_surname'])) echo $_GET['tmp_surname']; ?>&tmp_courseID=<?php if (isset($_GET['tmp_courseID'])) echo $_GET['tmp_courseID']; ?>&tmp_yearID=<?php if (isset($_GET['tmp_yearID'])) echo $_GET['tmp_yearID']; ?>';
    }
  </script>
</head>

<?php
  if (isset($_GET['submit']) or isset($_GET['paperID']) or isset($_GET['moduleID'])) {
    echo "<body onload=\"updateCohortDetails();\">\n";
    include '../include/user_search_options.inc';
    echo "<div id=\"content\" class=\"content\" style=\"font-size:80%\">\n";
  } else {
    echo "<body style=\"margin:0px; background-color:white; color:black\">\n";
    include '../include/user_search_options.inc';
    echo "<div id=\"content\" class=\"content\" style=\"font-size:80%\">\n";
    echo "<table class=\"header\">\n";
    echo "<tr><th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:16px\"><strong>" . $string['usersearch'] . "</div></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(92); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>";
    echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n</div>\n</body></html>\n";
    exit;
  }
?>

<form method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>?sortby=<?php echo $sortby; ?>&order=<?php echo $ordering; ?>">
<table class="header" id="usertable">

<?php
echo "<tr><th style=\"padding-left:16px\" colspan=\"7\"><div class=\"breadcrumb\" style=\"margin-left:0px\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div onclick=\"qOff()\" style=\"font-size:200%\"><strong>Users ($user_no):&nbsp;</strong>";
if (isset($_GET['paperID'])) {
  echo str_replace(',', ', ', $paper_moduleID) . ' (' . $paper_calendar_year . ')';
} elseif (isset($_GET['search_surname']) and $_GET['search_surname'] != '') {
  echo $_GET['search_surname'];
} elseif (isset($_GET['team']) and $_GET['team'] != '%') {
  echo $_GET['team'];
  if (isset($_GET['calendar_year']) and $_GET['calendar_year'] != '' and isset($_GET['students']) and $_GET['students'] != '') {
    echo ' (' . $_GET['calendar_year'] . ')';
  }
} elseif (isset($_GET['search_username']) and $_GET['search_username'] != '') {
  echo $_GET['search_username'];
} elseif (isset($_GET['student_id']) and $_GET['student_id'] != '') {
  echo $_GET['student_id'];
} elseif (isset($_GET['calendar_year']) and $_GET['calendar_year'] != '%') {
  echo $_GET['calendar_year'];
}
echo "</div></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(92); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";

if ($ordering == 'asc') {
  $new_order = 'desc';
} else {
  $new_order = 'asc';
}

if (isset($_GET['search_surname'])) {
  $tmp_surname = $_GET['search_surname'];
} else {
  $tmp_surname = '';
}

if (isset($_GET['search_username'])) {
  $tmp_username = $_GET['search_username'];
} else {
  $tmp_username = '';
}

if (isset($_GET['students'])) {
  $tmp_students = $_GET['students'];
} else {
  $tmp_students = '';
}

if (isset($_GET['staff'])) {
  $tmp_staff = $_GET['staff'];
} else {
  $tmp_staff = '';
}

if (isset($_GET['student_id'])) {
  $tmp_student_id = $_GET['student_id'];
} else {
  $tmp_student_id = '';
}

if (isset($_GET['team'])) {
  $tmp_team = $_GET['team'];
} else {
  $tmp_team = '';
}

if (isset($_GET['paperID'])) {
  $additional_param = '&paperID=' . $_GET['paperID'];
} else {
  $additional_param = '&team=' . $tmp_team . '&search_surname=' . $tmp_surname . '&search_username=' . $tmp_username . '&student_id=' . $tmp_student_id . '&moduleID=' . $moduleID . '&calendar_year=' . $calendar_year . '&submit=Search&userID=';
}
$user_types = array('students', 'graduates', 'leavers', 'suspended', 'staff', 'adminstaff', 'inactive', 'externals', 'invigilators');
foreach ($user_types as $user_type) {
  if (isset($_GET[$user_type])) {
    $additional_param .= '&' .  $user_type . '=' . $_GET[$user_type];
  }
}

if (isset($_GET['paperID'])) {
  $string['course'] = $string['module'];   // Override course with module if called from a paper.
}

if ($sortby == 'title') {
  echo '<tr><th class="coltitle" style="width:16px"></th><th class="coltitle" onclick="window.location=\'search.php?sortby=title&ordering=' . $new_order . $additional_param . '\'">&nbsp;' . $string['title'] . '&nbsp;<img src="../artwork/desc.gif" width="9" height="7" border="0" /></th><th class="coltitle"></th><th class="coltitle" style="width:240px; padding-left:0px" onclick="window.location=\'search.php?sortby=surname&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['name'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=username&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['username'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=student_id&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['studentid'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=yearofstudy&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['year'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=grade&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['course'] . '&nbsp;</th></tr>';
} elseif ($sortby == 'surname') {
  echo '<tr><th class="coltitle" style="width:16px"></th><th class="coltitle" onclick="window.location=\'search.php?sortby=title&ordering=asc' . $additional_param . '\'">&nbsp;' . $string['title'] . '&nbsp;</th><th class="coltitle"></th><th class="coltitle" style="width:240px; padding-left:0px" onclick="window.location=\'search.php?sortby=surname&ordering=' . $new_order . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['name'] . '&nbsp;<img src="../artwork/desc.gif" width="9" height="7" border="0" /></th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=username&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['username'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=student_id&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['studentid'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=yearofstudy&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['year'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=grade&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['course'] . '&nbsp;</th></tr>';
} elseif ($sortby == 'username') {
  echo '<tr><th class="coltitle" style="width:16px"></th><th class="coltitle" onclick="window.location=\'search.php?sortby=title&ordering=asc' . $additional_param . '\'">&nbsp;' . $string['title'] . '&nbsp;</th><th class="coltitle"></th><th class="coltitle" style="width:240px; padding-left:0px" onclick="window.location=\'search.php?sortby=surname&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['name'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=username&ordering=' . $new_order . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['username'] . '&nbsp;<img src="../artwork/desc.gif" width="9" height="7" border="0" /></th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=student_id&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['studentid'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=yearofstudy&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['year'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=grade&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['course'] . '&nbsp;</th></tr>';
} elseif ($sortby == 'student_id') {
  echo '<tr><th class="coltitle" style="width:16px"></th><th class="coltitle" onclick="window.location=\'search.php?sortby=title&ordering=asc' . $additional_param . '\'">&nbsp;' . $string['title'] . '&nbsp;</th><th class="coltitle"></th><th class="coltitle" style="width:240px; padding-left:0px" onclick="window.location=\'search.php?sortby=surname&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['name'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=username&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['username'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=student_id&ordering=' . $new_order . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['studentid'] . '&nbsp;<img src="../artwork/desc.gif" width="9" height="7" border="0" /></th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=yearofstudy&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['year'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=grade&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['course'] . '&nbsp;</th></tr>';
} elseif ($sortby == 'yearofstudy') {
  echo '<tr><th class="coltitle" style="width:16px"></th><th class="coltitle" onclick="window.location=\'search.php?sortby=title&ordering=asc' . $additional_param . '\'">&nbsp;' . $string['title'] . '&nbsp;</th><th class="coltitle"></th><th class="coltitle" style="width:240px; padding-left:0px" onclick="window.location=\'search.php?sortby=surname&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['name'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=username&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['username'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=student_id&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['studentid'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=yearofstudy&ordering=' . $new_order . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['year'] . '&nbsp;<img src="../artwork/desc.gif" width="9" height="7" border="0" /></th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=grade&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['course'] . '&nbsp;</th></tr>';
} elseif ($sortby == 'grade') {
  echo '<tr><th class="coltitle" style="width:16px"></th><th class="coltitle" onclick="window.location=\'search.php?sortby=title&ordering=asc' . $additional_param . '\'">&nbsp;' . $string['title'] . '&nbsp;</th><th class="coltitle"></th><th class="coltitle" style="width:240px; padding-left:0px" onclick="window.location=\'search.php?sortby=surname&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['name'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=username&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['username'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=student_id&ordering=asc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['studentid'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=yearofstudy&ordering=desc' . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['year'] . '&nbsp;</th><th class="coltitle" style="padding-left:0px" onclick="window.location=\'search.php?sortby=grade&ordering=' . $new_order . $additional_param . '\'"><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;' . $string['course'] . '&nbsp;<img src="../artwork/desc.gif" width="9" height="7" border="0" /></th></tr>';
}
?>
<tr><th colspan="8" class="bevel"></th></tr>
<?php
  if ($roles_sql == '') {
    echo "</table>\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\"><tr><td style=\"width:60px; height:32px; text-align:right; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\"><img src=\"../artwork/red_warning.png\" width=\"32\" height=\"32\" alt=\"Locked\" />&nbsp;&nbsp;</td><td style=\"height:32px; vertical-align:middle; background-image:url('../artwork/non_owner_gradient.gif'); background-repeat:repeat-x\">".$string['msg1']."</td></tr></table>\n</body>\n</html>\n";
    exit;
  }

  if ($user_data->num_rows == 0) {
    echo "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\" style=\"margin: 0px auto; width:75%; border: 1px solid #C0C0C0; text-align:left\">\n<tr><td colspan=\"2\" style=\"background-color:#F2B100; height:3px\"> </td></tr>\n<tr><td style=\"width:16px; padding-top:5px; padding-bottom:5px\"><img src=\"../artwork/information_icon.gif\" width=\"16\" height=\"16\" alt=\"i\" border=\"0\" /></td><td style=\"padding-top:5px; padding-bottom:5px\">&nbsp;".$string['msg2']."</td></tr></table>\n";
  }
  
  $old_letter = '';
  $old_title = '';
  $old_username = '';
  $old_grade = '';
  $old_year = '';
  $x = 0;
  while ($user_data->fetch()) {
    if ($old_letter != strtoupper(substr($tmp_surname, 0, 1)) and $sortby == 'surname') {
      echo "<tr><td colspan=\"8\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td>" . strtoupper(substr($tmp_surname,0,1)) . "</td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_title != $tmp_title and $sortby == 'title') {
      echo "<tr><td colspan=\"8\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td>" . $string[strtolower($tmp_title)] . "</td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_username != substr($tmp_username, 0, 4) and $sortby == 'username') {
      echo "<tr><td colspan=\"8\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td>" . substr($tmp_username,0,4) . "</td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_grade != $tmp_grade and $sortby == 'grade') {
      echo "<tr><td colspan=\"8\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td>" . $tmp_grade . "</td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    } elseif ($old_year != $tmp_yearofstudy and $sortby == 'yearofstudy') {
      echo "<tr><td colspan=\"8\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>Year " . $tmp_yearofstudy . "</nobr></td><td style=\"width:99%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n</td></tr>\n";
    }

    if (strpos($userroles,'SysAdmin') !== false) {
      echo "<tr id=\"$x\" onmouseover=\"lon($x)\" onmouseout=\"loff($x)\" style=\"cursor:pointer\" onclick=\"selUser('$tmp_id',$x,'2c'); return false;\" ondblclick=\"profile('$tmp_id'); return false;\">";
      if (file_exists($cfg_web_root . 'users/photos/' . $tmp_username . '.jpg')) {
        echo '<td><img src="../artwork/photo.png" width="16" height="16" alt="Photo" /></td>';
      } else {
        echo '<td></td>';
      }
      if (array_key_exists($tmp_id, $needs_array)) {
        echo "<td>" . $string[strtolower($tmp_title)] . "</td><td style=\"width:20px\"><img src=\"../artwork/accessibility_16.png\" width=\"16\" height=\"16\" border=\"0\" /></td><td>$tmp_surname, ";
        if ($tmp_first_names != '') {
          echo '<span class="fn">' . $tmp_first_names . '</span>';
        } else {
          echo $tmp_initials;
        }
        echo  "</td><td>$tmp_username</td>";
      } else {
        if (isset($tmp_title) and $tmp_title != '') {
          $tmp_title = $tmp_title;
        } else {
          $tmp_title = '';
        }
        echo "<td>$tmp_title</td><td></td><td>$tmp_surname, ";
        if ($tmp_first_names != '') {
          echo '<span class="fn">' . $tmp_first_names . '</span>';
        } else {
          echo $tmp_initials;
        }
        echo "</td><td>$tmp_username</td>";
      }
    } else {
      echo "<tr id=\"$x\" onmouseover=\"lon($x)\" onmouseout=\"loff($x)\" style=\"cursor:pointer\" onclick=\"selUser('$tmp_id',$x,'2b'); return false;\" ondblclick=\"profile('$tmp_id'); return false;\">";
      if (file_exists($cfg_web_root . '/users/photos/' . $tmp_username . '.jpg')) {
        echo '<td><img src="../artwork/photo.png" width="16" height="16" alt="Photo" /></td>';
      } else {
        echo '<td></td>';
      }
      if (array_key_exists($tmp_id, $needs_array)) {
        echo "<td>" . $tmp_title . "</td><td style=\"width:20px\"><img src=\"../artwork/accessibility_16.png\" width=\"16\" height=\"16\" border=\"0\" /></td><td>$tmp_surname, ";
        if ($tmp_first_names != '') {
          echo '<span class="fn">' . $tmp_first_names . '</span>';
        } else {
          echo $tmp_initials;
        }
        echo "</a></td><td>$tmp_username</td>";
      } else {
        echo "<td>&nbsp;$tmp_title</td><td></td><td>$tmp_surname, ";
        if ($tmp_first_names != '') {
          echo '<span class="fn">' . $tmp_first_names . '</span>';
        } else {
          echo $tmp_initials;
        }
        echo "</a></td><td>$tmp_username</td>";
      }
    }
    if ($tmp_roles == 'Student') {
      if ($tmp_student_id == NULL) {
        echo "<td class=\"fn\">" . $string['unknown'] . "</td>";
      } else {
        echo "<td>$tmp_student_id</td>";
      }
    } else {
      echo "<td class=\"fn\">" . $string['na'] . "</td>";
    }
    echo "<td>$tmp_yearofstudy</td><td>&nbsp;";
    if (isset($_GET['paperID'])) {
      echo $tmp_moduleid;
    } else {
      echo $tmp_grade; 
    }
    echo "</td></tr>\n";
    $old_letter = strtoupper(substr($tmp_surname, 0, 1));
    $old_title = $tmp_title;
    $old_username = substr($tmp_username, 0, 4);
    $old_grade = $tmp_grade;
    $old_year = $tmp_yearofstudy;
    $x++;
  }
?>
</table>
</div>

<script language="JavaScript">
<?php
  if ($user_data->num_rows > 0) {
    if (strpos($userroles,'SysAdmin') !== false) {
      echo "document.getElementById('menu3a').style.display = 'none';\n";
      echo "document.getElementById('menu3c').style.display = 'block';\n";
    } else {
      echo "document.getElementById('menu3a').style.display = 'none';\n";
      echo "document.getElementById('menu3b').style.display = 'block';\n";    
    }
  } else {
    echo "document.getElementById('menu3a').style.display = 'block';\n";
    echo "document.getElementById('menu3b').style.display = 'none';\n";    
    echo "document.getElementById('menu3c').style.display = 'none';\n";
  }
  $user_data->close();
  $mysqli->close();
?>
</script>
</body>
</html>