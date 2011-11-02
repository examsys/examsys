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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  require '../include/errors.inc';
  require '../include/demo_replace.inc';
  require_once '../classes/schoolutils.class.php';
  require_once '../classes/networkutils.class.php';
  
  check_var('userID', 'GET', true, false);

  if (strpos($userroles,'Demo') !== false) {
    $demo = true;
  } else {
    $demo = false;
  }
  
  if (isset($_GET['tab'])) {
    $tab = $_GET['tab'];
  } else {
    $tab = 'log';
  }
  function drawTabs($current_tab, $col_span, $right_text, $user_roles) {
    global $string;
  
    $html = "<tr><td colspan=\"" . ($col_span - 1) . "\" style=\"background-color:#F1F5FB\">";
    $html .= '<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%"><tr>';
    $tab_array = array('Log','Modules','Notes','Accessibility');
    if (strpos($user_roles,'Admin') !== false and strpos($user_roles,'SysAdmin') === false) {
      $tab_array = array('Log','Teams','Admin','Notes','Accessibility');
    } elseif (strpos($user_roles,'Staff') !== false) {
      $tab_array = array('Log','Teams','Notes','Accessibility');
    } else {
      $tab_array = array('Log','Modules','Notes','Accessibility','Metadata');
    }
    foreach($tab_array as $individual_tab) {
      if ($individual_tab == $current_tab) {
        $html .= "<td style=\"padding-top:0px; cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_on.gif)\" onclick=\"showTab('" . $individual_tab . "_tab')\">" . $string[strtolower($individual_tab)] . "</td>";
      } else {
        $html .= "<td style=\"padding-top:0px; cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab_off.gif)\" onclick=\"showTab('" . $individual_tab . "_tab')\">" . $string[strtolower($individual_tab)] . "</td>";
      }
    }
    $html .= "</tr></table></td><td align=\"right\" style=\"background-color:#F1F5FB\">$right_text</td></tr>\n";
    return $html;
  }

  function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
    foreach ($marray as $row) {
      $sortarr[] = $row[$column];
    }
    $sortarr = array_map('strtolower',$sortarr);
    $sort_method = SORT_STRING;
    if ($column == 'mark') $sort_method = SORT_NUMERIC;
    if ($sort_order == 'asc') {
      array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
    } else {
      array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
    }
    return $marray;
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

  if (isset($_POST['update']) and $demo == false) {
    $initials = '';
    $first_names_array = explode(' ',$_POST['first_names']);
    foreach ($first_names_array as $individual_name) {
      $initials .= trim(substr($individual_name,0,1));
    }
    //Update 'users' table.
    $tmp_roles = $_POST['roles'];
    $grade = $_POST['grade'];
    if ($grade == 'University Lecturer' or $grade == 'University Secretary' or $grade == 'University Librarian' or $grade == 'University Admin' or $grade == 'Technical Staff' or $grade == 'NHS Lecturer' or $grade == 'NHS Secretary' or $grade == 'NHS Admin' or $grade == 'NHS Librarian' or $grade == 'NHS IT' or $grade == 'Staff External Guest') {
      $tmp_roles = 'Staff';
      if (isset($_POST['sysadmin'])) {
        $tmp_roles .= ',SysAdmin';
      }
      if (isset($_POST['admin'])) {
        $tmp_roles .= ',Admin';
      }
    } elseif ($grade == 'Staff External Examiner') {
      $tmp_roles = 'External Examiner';
    }
    if ($grade == 'inactive') $tmp_roles = 'inactive';

    $tmp_first_names = $_POST['first_names'];
    $tmp_surname = $_POST['surname'];
    $tmp_email = $_POST['email'];
        
    if (isset($_POST['password']) and $_POST['password'] != '') {
      $result = $mysqli->prepare("UPDATE users SET roles=?, title=?, initials=?, surname=?, grade=?, yearofstudy=?, username=?, password=?, email=?, first_names=?, gender=? WHERE id=?");
      $result->bind_param('sssssisssssi', $tmp_roles, $_POST['title'], $initials, $tmp_surname, $grade, $_POST['year'], $_POST['username'], $_POST['password'], $tmp_email, $tmp_first_names, $_POST['gender'], $_POST['old_userID']); 
    } else {
      $result = $mysqli->prepare("UPDATE users SET roles=?, title=?, initials=?, surname=?, grade=?, yearofstudy=?, username=?, email=?, first_names=?, gender=? WHERE id=?");
      $result->bind_param('sssssissssi', $tmp_roles, $_POST['title'], $initials, $tmp_surname, $grade, $_POST['year'], $_POST['username'], $tmp_email, $tmp_first_names, $_POST['gender'], $_POST['old_userID']);
    }
    $result->execute();
    $result->close();

    //Remove from teams if 'left'.
    if ($grade == 'left') {
      $result = $mysqli->prepare("DELETE FROM teams WHERE memberID=?");
      $result->bind_param('i', $_POST['old_userID']);
      $result->execute();
      $result->close();
    }

    $username = $_POST['username'];
    //Update 'sid' table;
    $result = $mysqli->prepare("DELETE FROM sid WHERE userID=?");
    $result->bind_param('i', $_POST['old_userID']);
    $result->execute();
    $result->close();
    if (isset($_POST['sid']) and $_POST['sid'] != '' and $_POST['sid'] != '<unknown>') {
      $result = $mysqli->prepare("INSERT INTO sid VALUES (?,?)");
      $result->bind_param('si', $_POST['sid'], $_POST['old_userID']);
      $result->execute();
      $result->close();
    }
  } elseif (isset($_POST['updateadmin'])) {
    $result = $mysqli->prepare("DELETE FROM admin_access WHERE userID=?");
    $result->bind_param('i', $_GET['userID']);
    $result->execute();
    $result->close();
    
    for ($i=0; $i<$_POST['admin_school_no']; $i++) {
      if (isset($_POST["sch$i"])) {
        $result = $mysqli->prepare("INSERT INTO admin_access VALUES (NULL, ?, ?)");
        $result->bind_param('ii', $_GET['userID'], $_POST["sch$i"]);
        $result->execute();
        $result->close();
      }
    }
  } elseif (isset($_POST['updateaccess'])) {
    $background = $_POST['background'];
    if ($_POST['bg_radio'] == '0') $background = 'NULL';
    $foreground = $_POST['foreground'];
    if ($_POST['fg_radio'] == '0') $foreground = 'NULL';
    $textsize = $_POST['textsize'];
    $extra_time = $_POST['extra_time'];
    $marks_color = $_POST['marks_color'];
    if ($_POST['marks_radio'] == '0') $marks_color = 'NULL';
    $themecolor = $_POST['themecolor'];
    if ($_POST['theme_radio'] == '0') $themecolor = 'NULL';
    $labelcolor = $_POST['labelcolor'];
    if ($_POST['labels_radio'] == '0') $labelcolor = 'NULL';

    $result = $mysqli->prepare("DELETE FROM special_needs WHERE userID=?");
    $result->bind_param('i', $_GET['userID']);
    $result->execute();
    $result->close();

    if ($background != 'NULL' or $foreground != 'NULL' or $marks_color != 'NULL' or $textsize != 'null' or $extra_time != 'null' or $themecolor != 'NULL' or $labelcolor != 'NULL') {
      //echo "Database update<br />";
      $result = $mysqli->prepare("INSERT INTO special_needs VALUES (NULL,?,?,?,?,?,?,?,?,?)");
      $result->bind_param('issiissss', $_GET['userID'], $background, $foreground, $textsize, $extra_time, $marks_color, $themecolor, $labelcolor, $_POST['font']);
      $result->execute();
      $result->close();

      $result = $mysqli->prepare("UPDATE users SET special_needs=1 WHERE id=?");
      $result->bind_param('i', $_GET['userID']);
      $result->execute();
      $result->close();
    }
  } elseif (isset($_POST['save_metadata'])) {
    for ($i=0; $i<$_POST['metadata_no']; $i++) {
      //echo $i . '=' . $_POST["meta_value$i"] . ' (' . $_POST["meta_id$i"] . ')<br />';
      $result = $mysqli->prepare("UPDATE users_metadata SET value=? WHERE id=?");
      $result->bind_param('si', $_POST["meta_value$i"], $_POST["meta_id$i"]);
      $result->execute();
      $result->close();
    }
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?php echo $string['usermanagement'] ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style style="text/css">
body {font-size:100%}
td {padding-top:1px}
.coltitle {cursor:hand; background-color:#1E3C7B; color:white}
.sch_check {text-align:right; width:40px; padding-right:6px}
a.paper {color:black}
a.paper:hover {color:white; background-color:#000080}
a.access:link {color:blue}
a.access:visited {color:blue}
a.access:hover {color:white}
</style>

<script language="javascript">
  function reviewPaper(started, userid, surname, papername, log_type) {
    var winwidth = screen.width-80;
    var winheight = screen.height-80;
    window.open("../paper/finish.php?paperID="+papername+"&previous="+started+"&userid="+userid+"&surname="+surname+"&log_type="+log_type+"","paper","width="+winwidth+",height="+winheight+",left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
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
    editwin=window.open("reset_pwd.php?userID=<?php echo $_GET['userID']; ?>&username=" + username + "","editmodule","width=450,height=400,left="+(screen.width/2-200)+",top="+(screen.height/2-375)+",scrollbars=no,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
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
</script>
</head>

<body>

<?php
  require '../tools/colour_picker/colour_picker.inc';
  require '../include/user_search_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<table cellpadding="0" cellspacing="0" border="0" style="background-color:#F1F5FB; width:100%">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>?userID=<?php echo $_GET['userID']; ?>" method="post">
<?php
  $needs_result = $mysqli->prepare("SELECT special_id FROM special_needs WHERE userID=?");
  $needs_result->bind_param('i', $_GET['userID']);
  $needs_result->execute();
  $needs_result->bind_result($special_id);
  if ($needs_result->num_rows > 0) $special_needs = true;
  $needs_result->close();

  $user_result = $mysqli->prepare("SELECT DISTINCT id, roles, grade, title, initials, first_names, surname, email, yearofstudy, grade, password, gender, username, student_id FROM users LEFT JOIN sid ON users.id=sid.userID WHERE users.id=?");
  $user_result->bind_param('i', $_GET['userID']);
  $user_result->execute();
  $user_result->bind_result($tmp_id, $tmp_roles, $tmp_grade, $tmp_title, $tmp_initials, $tmp_first_names, $tmp_surname, $email, $tmp_year, $grade, $password, $gender, $username, $student_id);
  $user_result->fetch();
  $user_result->close();
  
  $original_username = $username;
  if ($demo == true) {
    // Hide the personal details.
    $tmp_surname = demo_replace($tmp_surname,$demo);
    $tmp_first_names = demo_replace($tmp_first_names,$demo);
    $tmp_initials = demo_replace($tmp_initials,$demo);
    $student_id = demo_replace_number($student_id,$demo);
    $username = demo_replace_username($username,$demo);
    $email = demo_replace_username($email,$demo);
  }
  
  $tmp_name = $tmp_title . ' ' . $tmp_initials . ' ' . $tmp_surname;

  $description = '';
  $user_query = $mysqli->prepare("SELECT DISTINCT description FROM degrees WHERE degree=? LIMIT 1");
  $user_query->bind_param('s', $grade);
  $user_query->execute();
  $user_query->bind_result($description);
  $user_query->fetch();
  $user_query->close();
  
  if (strpos($userroles,'Admin') !== false or strpos($userroles,'SysAdmin') !== false) {
    if (strpos($tmp_roles,'Student') !== false or strpos($tmp_roles,'graduate') !== false or strpos($tmp_roles,'left') !== false or strpos($tmp_roles,'suspended') !== false) {
      $student_photo =  $cfg_web_root . 'users/photos/' . $original_username . '.jpg';
      $row_no = 7;
      if (file_exists($student_photo)) {
        if ($demo == true) {
          echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img style=\"filter:progid:DXImageTransform.Microsoft.Pixelate(maxSquare=8)\" src=\"/users/photos/$original_username.jpg\" width=\"180\" height=\"270\" alt=\"Student Photo\" border=\"0\" /></td><td>&nbsp;Name</td><td colspan=\"3\">";
        } else {
          echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"/users/photos/$original_username.jpg\" width=\"180\" height=\"270\" alt=\"Student Photo\" border=\"0\" /></td><td>&nbsp;Name</td><td colspan=\"3\">";
        }
      } else {
        echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" border=\"0\" /></td><td>&nbsp;" . $string['name'] . "</td><td colspan=\"3\">";
      }
    } else {
      $row_no = 9;
      echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" border=\"0\" /></td><td>&nbsp;" . $string['name'] . "</td><td colspan=\"3\">";
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
    if (strpos($tmp_roles,'Student') !== false) {
      if ($student_id == '') $student_id = '&lt;unknown&gt;';
      echo "<td>&nbsp;" . $string['studentid'] . "</td><td colspan=\"2\"><input type=\"text\" size=\"15\" name=\"sid\" value=\"$student_id\" /></td></tr>\n";
    } else {
      if ($cfg_use_ldap == true and strpos($grade,'University') !== false) {
        // Try and get the telephone number from LDAP.
        $ldap = ldap_connect($cfg_ldap_server);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        if (ldap_bind($ldap, $cfg_ldap_bind_rdn, $cfg_ldap_bind_password)) {
          if (!($search=@ldap_search($ldap, $cfg_ldap_search_dn, 'cn=' . $username))) {
            echo "<td>&nbsp;" . $string['telephone'] . "</td><td>" . $string['ldapunavailable'] .  "</td></tr>\n";
            return false;
          } else {
            $info = ldap_get_entries($ldap, $search);
            if (!isset($info[0]['telephonenumber'][0])) {
              echo "<td>&nbsp;" . $string['telephone'] . "</td><td style=\"color:#808080\" colspan=\"2\">" . $string['unknown'] . "</td></tr>\n";
            } else {
              echo "<td>&nbsp;" . $string['telephone'] . "</td><td colspan=\"2\">" . $info[0]['telephonenumber'][0] . "</td></tr>\n";
            }
          }
          ldap_unbind($ldap);
        }
      } elseif (strpos($grade,'NHS ') !== false) {
        echo "<td>&nbsp;" . $string['telephone'] . "</td><td style=\"color:#808080\" colspan=\"2\">" . $string['unknown'] . "</td></tr>\n";
      } else {
        echo "<td colspan=\"3\"></td></tr>\n";
      }
    }
    if (strpos($tmp_roles,'Student') !== false or strpos($tmp_roles,'graduate') !== false or strpos($tmp_roles,'left') !== false or strpos($tmp_roles,'suspended') !== false) {
      // Student editing
      echo "<tr><td>&nbsp;" . $string['course'] . "</td><td><select name=\"grade\" style=\"width:300px\">";
      $found = 0;
      $degree_details = $mysqli->query("SELECT DISTINCT degree, description FROM degrees ORDER BY degree");
      while ($degree_row = $degree_details->fetch_assoc()) {
        if ($degree_row['degree'] == $grade) {
          $found = 1;
          echo "<option value=\"" . $degree_row['degree'] . "\" selected>" . $degree_row['degree'] . ": " . $degree_row['description'] . "</option>\n";
        } else {
          echo "<option value=\"" . $degree_row['degree'] . "\">" . $degree_row['degree'] . ": " . $degree_row['description'] . "</option>\n";
        }
      }
      if ($found == 0) echo "<option value=\"" . $grade . "\" selected>" . $grade . ": &lt;unknown degree&gt;</option>\n";
      $degree_details->close();
      echo "</select></td><td colspan=\"3\">&nbsp;</td></tr>\n";
      echo "<tr><td>&nbsp;" . $string['yearofstudy'] . "</td><td><select name=\"year\">";
      for ($i=1; $i<=6; $i++) {
        if ($i == $tmp_year) {
          echo "<option value=\"$i\" selected>Year $i</option>";
        } else {
          echo "<option value=\"$i\">Year $i</option>";
        }
      }
      echo "</select></td><td>&nbsp;" . $string['status'] . "</td><td colspan=\"2\"><select name=\"roles\">";
      $roles_array = array('External Examiner'=>'ExternalExaminers','graduate'=>'Graduate','left'=>'LeftUniversity','suspended'=>'Suspended','Staff'=>'Staff','Student'=>'Student');
      foreach ($roles_array as $key => $value) {
        if ($key == $tmp_roles) {
          echo "<option value=\"$key\" selected>" . $string[strtolower($value)] . "</option>";
        } else {
          echo "<option value=\"$key\">" . $string[strtolower($value)] . "</option>";
        }
      }
      echo "</select></td></tr>\n";
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
      echo "</select>\n&nbsp;";
      if (strpos($userroles,'SysAdmin') !== false) {
        if (strpos($tmp_roles,'SysAdmin') !== false) {
          echo "<input type=\"checkbox\" name=\"sysadmin\" value=\"1\" checked />" . $string['sysadmin'];
        } else {
          echo "<input type=\"checkbox\" name=\"sysadmin\" value=\"1\" />" . $string['sysadmin'];
        }
      } else {
        // Do not allow lower levels of permission to change their account to SysAdmin
      }
      
      if (strpos($tmp_roles,'Admin') !== false and strpos($tmp_roles,'SysAdmin') === false) {
        echo "&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" name=\"admin\" value=\"1\" checked />" . $string['admin'];
      } else {
        echo "&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" name=\"admin\" value=\"1\" />" . $string['admin'];
      }      
      
      echo "<input type=\"hidden\" name=\"roles\" value=\"$tmp_roles\" /></td>\n";
      
      echo "<td colspan=\"3\">&nbsp;</td></tr>\n";
    }

    if (strpos($userroles,'SysAdmin') !== false ) {
      echo "<tr><td>&nbsp;" . $string['username'] . "&nbsp;</td><td><input type=\"text\" size=\"15\" name=\"username\" value=\"$username\" /></td><td>&nbsp;" . $string['password'] . "</td><td colspan=\"2\">";
      if($cfg_use_ldap and array_reduce($cfg_institutional_domains, 'NetworkUtils::check_email_domain')) {
        echo $string['externalauth'];
      } else {
        $url_email = urlencode($email);
        echo "<input type=\"button\" onclick=\"resetPassword('$url_email')\" value=\"{$string['reset']}\" />";

        if(strpos($userroles, 'SysAdmin')) {
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
    if (strpos($tmp_roles,'Student') !== false) {
      $student_photo =  $cfg_web_root ."users/photos/$username.jpg";
      $row_no = 10;
      if (file_exists($student_photo)) {
        echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"/users/photos/$username.jpg\" width=\"180\" height=\"270\" alt=\"Student Photo\" border=\"0\" /></td><td width=\"110\">&nbsp;Name</td><td>$tmp_title $tmp_initials $tmp_surname</td></tr>\n";
      } else {
        echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" border=\"0\" /></td><td width=\"110\">&nbsp;Name:</td><td>$tmp_title $tmp_initials $tmp_surname</td></tr>\n";
      }
    } else {
      $row_no = 5;
      echo "<tr><td valign=\"top\" rowspan=\"$row_no\" width=\"70\" align=\"center\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" border=\"0\" /></td><td width=\"110\">&nbsp;Name</td><td>$tmp_title $tmp_initials $tmp_surname</td></tr>\n";
    }
    if (strpos($tmp_roles,'Student') !== false) {
      if ($student_id == '') $student_id = $string['unknown'];
      echo "<tr><td>&nbsp;Student ID</td><td>$student_id</td></tr>\n";
    }
    echo "<tr><td>&nbsp;Email</td><td><a href=\"mailto:$email\">$email</a></td></tr>\n";
    if ($tmp_roles == 'Student') {
      echo "<tr><td>&nbsp;" . $string['yearofstudy'] . "</td><td>{$string['year']} $year</td></tr>\n";
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
  echo drawTabs('Log',6,'',$tmp_roles);
  
  $sortby = 'q_paper';
  if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];
  
  $ordering = 'asc';
  if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

  $old_q_paper = '';
  $old_started = '';
  $old_duration = 0;
  $old_screen = 0;
  $old_paper_title = '';
  $results_no = 0;
  $paper = array();

  //$mysqli->select_db('touchstone');
  if (strpos($tmp_roles,'External Examiner') !== false) {      // Get the papers the External is down to review.
    $external_array = array();

    $stmt = $mysqli->prepare("SELECT DISTINCT paper_title, property_id, paper_type FROM properties LEFT JOIN review_comments ON property_id=review_comments.q_paper AND reviewer=? WHERE deleted IS NULL AND externals LIKE ? AND reviewed IS NULL ORDER BY paper_title");
    $tmp_id_like = '%' . $tmp_id . '%';
    $stmt->bind_param('is', $tmp_id, $tmp_id_like);
    $stmt->execute();
    $stmt->bind_result($paper_title, $property_id, $paper_type);
    while($stmt->fetch()) {
      $paper[$results_no]['q_paper'] = $paper_title;
      $paper[$results_no]['id'] = $property_id;
      $paper[$results_no]['paper_type'] = '2';
      $paper[$results_no]['started'] = '';
      $paper[$results_no]['duration'] = '';
      $paper[$results_no]['mark'] = '';
      $paper[$results_no]['totalpos'] = '';
      $paper[$results_no]['ipaddress'] = '';
      $results_no++;
    }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT paper_title, paper_type, q_paper, DATE_FORMAT(reviewed,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(reviewed,'$cfg_long_date_time') AS display_started, duration, screen, ipaddress FROM (properties, review_comments) WHERE properties.property_id=review_comments.q_paper AND reviewer=? ORDER BY q_paper, started, screen");
    $stmt->bind_param('i', $tmp_id);
  } else {
    $query_sql = "(SELECT paper_title, 0 AS paper_type, q_paper, DATE_FORMAT(log_metadata.started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(log_metadata.started,'$cfg_long_date_time') AS display_started, duration, screen, ipaddress FROM properties, log0, log_metadata WHERE log0.q_paper=log_metadata.paperID AND log0.started=log_metadata.started AND log0.userID=log_metadata.userID AND properties.property_id=log0.q_paper AND log0.userID=?)";
    $query_sql .= " UNION ALL (SELECT paper_title, 1 AS paper_type, q_paper, DATE_FORMAT(log_metadata.started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(log_metadata.started,'$cfg_long_date_time') AS display_started, duration, screen, ipaddress FROM properties, log1, log_metadata WHERE log1.q_paper=log_metadata.paperID AND log1.started=log_metadata.started AND log1.userID=log_metadata.userID AND properties.property_id=log1.q_paper AND log1.userID=?)";
    $query_sql .= " UNION ALL (SELECT paper_title, 2 AS paper_type, q_paper, DATE_FORMAT(log_metadata.started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(log_metadata.started,'$cfg_long_date_time') AS display_started, duration, screen, ipaddress FROM properties, log2, log_metadata WHERE log2.q_paper=log_metadata.paperID AND log2.started=log_metadata.started AND log2.userID=log_metadata.userID AND properties.property_id=log2.q_paper AND log2.userID=?)";
    $query_sql .= " UNION ALL (SELECT paper_title, 3 AS paper_type, q_paper, DATE_FORMAT(log_metadata.started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(log_metadata.started,'$cfg_long_date_time') AS display_started, duration, screen, ipaddress FROM properties, log3, log_metadata WHERE log3.q_paper=log_metadata.paperID AND log3.started=log_metadata.started AND log3.userID=log_metadata.userID AND properties.property_id=log3.q_paper AND log3.userID=?)";
    $query_sql .= " UNION ALL (SELECT paper_title, 4 AS paper_type, q_paper, DATE_FORMAT(started,'%Y%m%d%H%i%s') AS started, DATE_FORMAT(started,'$cfg_long_date_time') AS display_started, NULL AS duration, NULL AS screen, NULL AS ipaddress FROM properties, log4_overall WHERE properties.property_id=log4_overall.q_paper AND userID=?)";
    $query_sql .= " ORDER BY q_paper, started, screen";
    
    $stmt = $mysqli->prepare($query_sql);
    $stmt->bind_param('iiiii', $tmp_id, $tmp_id, $tmp_id, $tmp_id, $tmp_id);
  }
  
  $stmt->execute();
  $stmt->bind_result($paper_title, $paper_type, $q_paper, $started, $display_started, $duration, $screen, $ipaddress);
  while($stmt->fetch()) {
    if ($old_q_paper != $q_paper or $old_started != $started) {
      if ($old_q_paper != '') {
        $paper[$results_no]['q_paper'] = $old_paper_title;
        $paper[$results_no]['id'] = $old_q_paper;
        $paper[$results_no]['paper_type'] = $old_paper_type;
        $paper[$results_no]['started'] = $old_started;
        $paper[$results_no]['display_started'] = $old_display_started;
        $paper[$results_no]['duration'] = $old_duration;
        $paper[$results_no]['ipaddress'] = $old_ipaddress;
        $results_no++;
      }
      $old_screen = 0;
      $old_duration = 0;
    }
    if ($old_screen != $screen) {
      $old_duration += $duration;
    }
    $old_q_paper = $q_paper;
    $old_started = $started;
    $old_display_started = $display_started;
    $old_paper_type = $paper_type;
    $old_screen = $screen;
    $old_paper_title = $paper_title;
    $old_ipaddress = $ipaddress;
  }
  $stmt->close();
  
  if ($old_q_paper != '') {
    $paper[$results_no]['q_paper'] = $old_paper_title;
    $paper[$results_no]['id'] = $old_q_paper;
    $paper[$results_no]['paper_type'] = $old_paper_type;
    $paper[$results_no]['started'] = $old_started;
    $paper[$results_no]['display_started'] = $old_display_started;
    $paper[$results_no]['duration'] = $old_duration;
    $paper[$results_no]['ipaddress'] = $old_ipaddress;
    $results_no++;
  }
  if ($results_no > 0) {
    $paper = array_csort($paper,$sortby,$ordering);

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
    for ($i=0; $i<$results_no; $i++) {
      if (strpos($paper[$i]['q_paper'],'[deleted') !== false ) {
        $paper[$i]['q_paper'] = '<span style="color:#808080; text-decoration:line-through">' . $paper[$i]['q_paper'] . '</span>';
      }
      switch ($paper[$i]['paper_type']) {
        case 0:
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "','" . $_GET['userID'] . "','" . str_replace("'","&#8217;",$tmp_surname) . "','" . $paper[$i]['id'] . "'," . $paper[$i]['paper_type'] . "); return false;\"><img src=\"../artwork/formative_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $tmp_surname . "\" border=\"0\" /></a></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\" class=\"paper\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $string['formative'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . formatsec($paper[$i]['duration']) . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case 1:
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "','" . $_GET['userID'] . "','" . str_replace("'","&#8217;",$tmp_surname) . "','" . $paper[$i]['id'] . "'," . $paper[$i]['paper_type'] . "); return false;\"><img src=\"../artwork/progress_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $tmp_surname . "\" border=\"0\" /></a></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\" class=\"paper\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $string['progresstest'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td>" . formatsec($paper[$i]['duration']) . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case 2:
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><a href=\"#\" onclick=\"reviewPaper('" . $paper[$i]['started'] . "','" . $_GET['userID'] . "','" . str_replace("'","&#8217;",$tmp_surname) . "','" . $paper[$i]['id'] . "'," . $paper[$i]['paper_type'] . "); return false;\"><img src=\"../artwork/summative_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $tmp_surname . "\" border=\"0\" /></a></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\" class=\"paper\"";
          if ($paper[$i]['started'] == '') echo ' style="color:red"';
          echo ">" . $paper[$i]['q_paper'] . "</a></td><td";
          if ($paper[$i]['started'] == '') echo ' style="color:red"';
          echo ">Summative</td><td>" . $paper[$i]['display_started'] . "</td><td>" . formatsec($paper[$i]['duration']) . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case 3:
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><img src=\"../artwork/survey_16.gif\" width=\"16\" height=\"16\" alt=\"Survey data is anonymous, no entry.\" border=\"0\" /></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\" class=\"paper\">" . $paper[$i]['q_paper'] . "</a></td><td>Survey</td><td>" . $paper[$i]['display_started'] . "</td><td>" . formatsec($paper[$i]['duration']) . "</td><td>" . $paper[$i]['ipaddress'] . "</td></tr>\n";
          break;
        case 4:
          echo "<tr style=\"height:17px\"><td style=\"text-align:right\"><a href=\"#\" onclick=\"reviewOSCE('" . $paper[$i]['started'] . "','$username','" . str_replace("'","&#8217;",$tmp_surname) . "','" . $paper[$i]['id'] . "'," . $paper[$i]['paper_type'] . "); return false;\"><img src=\"../artwork/osce_16.gif\" width=\"16\" height=\"16\" alt=\"Display marked paper for " . $tmp_surname . "\" border=\"0\" /></a></td><td>&nbsp;<a href=\"../paper/details.php?paperID=" . $paper[$i]['id'] . "\" class=\"paper\">" . $paper[$i]['q_paper'] . "</a></td><td>" . $string['oscestation'] . "</td><td>" . $paper[$i]['display_started'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td><td style=\"color:#808080\">" . $string['na'] . "</td></tr>\n";
          break;
     }
    }
  } else {
    echo '<tr><td class="coltitle" align="right">&nbsp;</td><td class="coltitle" style="width:240px">&nbsp;&nbsp;&nbsp;&nbsp;' . $string['papername'] . '&nbsp;<img src="../artwork/desc.gif" width="9" height="7" border="0" /></td><td class="coltitle">' . $string['type'] . '&nbsp;</td><td class="coltitle">' . $string['started'] . '&nbsp;</td><td class="coltitle">' . $string['duration'] . '&nbsp;</td><td class="coltitle">' . $string['ipaddress'] . '&nbsp;</td></tr>';
    echo "<tr><td colspan=\"8\" style=\"color:#808080; text-align:center\">" . $string['noassessmentstaken'] . "</td></tr>\n";
  }
?>
</table>

<?php
  if ($tab == 'modules') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Modules_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Modules_tab\" style=\"width:100%; display:none\">\n";
  }
  
  $results = $mysqli->prepare("SELECT MAX(calendar_year) AS calendar_year FROM student_modules");
  $results->execute();
  $results->bind_result($most_recent_year);
  $results->fetch();
  $results->close();
  
  echo drawTabs('Modules',4,'',$tmp_roles);
  echo "<tr><td class=\"coltitle\" style=\"width:20px\">&nbsp;</td><td class=\"coltitle\">&nbsp;" . $string['moduleid'] . "</td><td class=\"coltitle\">" . $string['name'] . "</td><td class=\"coltitle\">" . $string['academicyear'] . "</td></tr>\n";
  $old_year = '';
  $html = '';
  $row_no = 0;
  $results = $mysqli->prepare("SELECT DISTINCT student_modules.moduleid, fullname, student_modules.calendar_year, attempt FROM (student_modules, modules) WHERE student_modules.moduleid=modules.moduleid AND userID=? ORDER BY student_modules.calendar_year DESC, student_modules.moduleid");
  $results->bind_param('i', $tmp_id);
  $results->execute();
  $results->store_result();
  $results->bind_result($moduleid, $fullname, $calendar_year, $attempt);
  while ($results->fetch()) {
    if ($row_no == 0 and $calendar_year != $most_recent_year and $tmp_roles == 'Student') {
      $html .= "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>$most_recent_year&nbsp;&nbsp;<a href=\"#\" style=\"color:blue\" onclick=\"editModules('$most_recent_year','$grade'); return false;\">" . $string['editmodules'] . "</a></nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    }
    if ($calendar_year != $old_year) {
      $html .= "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $calendar_year;
      if ($calendar_year == $most_recent_year) $html .= "&nbsp;&nbsp;<a href=\"#\" style=\"color:blue\" onclick=\"editModules('$calendar_year','$grade'); return false;\">" . $string['editmodules'] . "</a>";
      $html .= "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
    }
    $html .= '<tr>';
    if ($attempt == 1) {
      $html .= '<td></td>';
    } else {
      $html .= '<td><img src="../artwork/resit.png" width="16" height="16" alt="Resit" border="0" /></td>';
    }
    $html .= "<td>&nbsp;<a styele=\"color:blue\" href=\"../folder/details.php?module=$moduleid\">$moduleid</a></td><td><a style=\"color:blue\" href=\"../folder/details.php?module=$moduleid\">$fullname</a></td><td>$calendar_year</td></tr>\n";
    $old_year = $calendar_year;
    $row_no++;
  }
  if ($results->num_rows == 0) {
    $html .= "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>$most_recent_year&nbsp;&nbsp;<a href=\"#\" style=\"color:blue\" onclick=\"editModules('$most_recent_year','$grade'); return false;\">" . $string['editmodules'] . "</a></nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  }
  
  $results->close();
  echo $html;
?>
</table>

<?php
  if ($tab == 'admin') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Admin_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Admin_tab\" style=\"width:100%; display:none\">\n";
  }
  echo "<form name=\"accessibility\" action=\"" . $_SERVER['PHP_SELF'] . "?userID=$tmp_id&tab=admin\" method=\"post\">";
  
  echo drawTabs('Admin',1,'',$tmp_roles);
  echo "<tr><td class=\"coltitle\">&nbsp;</td></tr>\n";
  echo "<tr><td><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n";
  
  $current_schools = SchoolUtils::getAdminSchools($_GET['userID'], $mysqli);
   
  $old_faculty = '';
  $admin_school_no = 0;
  $results = $mysqli->prepare("SELECT schools.id, faculty.name, school FROM schools, faculty WHERE schools.facultyID=faculty.id ORDER BY faculty.name, school");
  $results->execute();  
  $results->bind_result($schoolID, $faculty, $school);
  while ($results->fetch()) {
    if ($old_faculty != $faculty) {
      echo '<tr><td colspan="2"><table border="0" style="padding-top:5px; width:100%; color:#1E3287"><tr><td><nobr>' . $faculty . '</nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>';
    }
    echo '<tr><td class="sch_check">';
    if (in_array($schoolID,$current_schools)) {
      echo "<input type=\"checkbox\" name=\"sch" . $admin_school_no . "\" value=\"$schoolID\" checked />";
    } else {
      echo "<input type=\"checkbox\" name=\"sch" . $admin_school_no . "\" value=\"$schoolID\" />";
    }
    echo "</td><td>$school</td></tr>\n";
    $old_faculty = $faculty;
    $admin_school_no++;
  }
  $results->close();
  echo "</table>\n</td></tr>\n";
  ?>
  <tr><td colspan="2" align="center"><input type="submit" name="updateadmin" value="<?php echo $string['save']; ?>" style="width:100px" /><input type="hidden" name="admin_school_no" value="<?php echo $admin_school_no; ?>" /></td></tr>
  </form>
  </table>
  <?php

  if ($tab == 'notes') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Notes_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Notes_tab\" style=\"width:100%; display:none\">\n";
  }
  $link_html = '<img src="../artwork/shortcut.png" onclick="newStudentNote()" width="10" height="10" border="0" />&nbsp;<a href="" onclick="newStudentNote(); return false;" class="access">' . $string['createnote'] . '</a>&nbsp;';
  echo drawTabs('Notes', 4, $link_html, $tmp_roles);
  echo "<tr><td class=\"coltitle\">&nbsp;&nbsp;&nbsp;" . $string['date'] . "</td><td class=\"coltitle\">" . $string['paper'] . "</td><td class=\"coltitle\">" . $string['note'] . "</td><td class=\"coltitle\">" . $string['author'] . "</td></tr>\n";
  
  $results = $mysqli->prepare("SELECT note, note_date, paper_id, moduleID, paper_title, CONCAT(title, ' ', initials, ' ', surname) AS note_author FROM (student_notes, properties, users) WHERE student_notes.paper_id=properties.property_id AND student_notes.note_authorID=users.id AND student_notes.userID=?");
  $results->bind_param('i', $tmp_id);
  $results->execute();
  $results->store_result();
  $results->bind_result($note, $note_date, $note_paper_id, $note_moduleID, $paper_title, $note_author);
  while ($results->fetch()) {
    echo "<tr><td>&nbsp;<img src=\"../artwork/notes_icon.gif\" width=\"14\" height=\"14\" alt=\"Note\" />&nbsp;$note_date</td><td><a href=\"../paper/details.php?paperID=" . $note_paper_id . "&module=" . $note_moduleID . "\">$paper_title</a></td><td>$note</td><td>$note_author</td></tr>";
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
  echo drawTabs('Accessibility', 1, '', $tmp_roles);
  echo "<tr><td class=\"coltitle\">&nbsp;</td></tr>\n";
  echo "<tr><td align=\"center\"><table cellspacing=\"1\" cellpadding=\"1\" border=\"0\" style=\"text-align:left\">";
  $query = "SELECT * FROM special_needs WHERE userID=$tmp_id";
  $needs_query = $mysqli->query($query);
  if ($needs_query->num_rows > 0) {
    $row = $needs_query->fetch_assoc();
    $special_needs = true;
    $textsize = $row['textsize'];
    $background = $row['background'];
    if ($background == 'NULL') $background = '';
    $foreground = $row['foreground'];
    if ($foreground == 'NULL') $foreground = '';
    $extra_time = $row['extra_time'];
    $themecolor = $row['themecolor'];
    if ($themecolor == 'NULL') $themecolor = '';
    $labelcolor = $row['labelcolor'];
    if ($labelcolor == 'NULL') $labelcolor = '';
    $marks_color = $row['marks_color'];
    if ($marks_color == 'NULL') $marks_color = '';
    $font = $row['font'];
    if ($font == 'NULL') $font = '';
  } else {
    $textsize = '';
    $background = '';
    $foreground = '';
    $extra_time = '';
    $themecolor = '';
    $labelcolor = '';
    $marks_color = '';
    $font = '';
  }
  $needs_query->close();
?>
<tr>
<td><?php echo $string['extratime']; ?></td>
<td colspan="2">
<select name="extra_time">
<option value="null"><?php echo $string['noextratime']; ?></option>
<?php
  $times = array(10, 25, 33, 50, 100);
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
</tr>
<tr>
<td><?php echo $string['fontsize']; ?></td>
<td colspan="2">
<select name="textsize">
<option value="null"><?php echo $string['angledefault']; ?></option>
<?php
  $fontsizes = array(90, 100, 120, 150, 200, 300, 400);
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
<select name="font">
<option value="null"><?php echo $string['angledefault']; ?></option>
<?php
  $fontfamily = array('Arial','Arial Black','Calibri','Comic Sans MS','Courier New','Helvetica','Tahoma','Times New Roman','Verdana');
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
<td><input type="radio" name="bg_radio" value="0"<?php if ($background == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" name="bg_radio" value="1"<?php if ($background != '') echo ' checked'; ?> />
<?php
  if ($background == '') {
    echo "<div onclick=\"showPicker('background',event); document.accessibility.bg_radio[1].checked=true;\" id=\"span_background\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"background\" name=\"background\" value=\"$background\" />";
  } else {
    echo "<div onclick=\"showPicker('background',event); document.accessibility.bg_radio[1].checked=true;\" id=\"span_background\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$background\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"background\" name=\"background\" value=\"$background\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['foreground']; ?></td>
<td><input type="radio" name="fg_radio" value="0"<?php if ($foreground == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" name="fg_radio" value="1"<?php if ($foreground != '') echo ' checked'; ?> />
<?php
  if ($foreground == '') {
    echo "<div onclick=\"showPicker('foreground',event); document.accessibility.fg_radio[1].checked=true;\" id=\"span_foreground\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"foreground\" name=\"foreground\" value=\"$foreground\" />";
  } else {
    echo "<div onclick=\"showPicker('foreground',event); document.accessibility.fg_radio[1].checked=true;\" id=\"span_foreground\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$foreground\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"foreground\" name=\"foreground\" value=\"$foreground\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['markscolour']; ?></td>
<td><input type="radio" name="marks_radio" value="0"<?php if ($marks_color == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" name="marks_radio" value="1"<?php if ($marks_color != '') echo ' checked'; ?> />
<?php
  if ($marks_color == '') {
    echo "<div onclick=\"showPicker('marks_color',event); document.accessibility.marks_radio[1].checked=true;\" id=\"span_marks_color\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"marks_color\" name=\"marks_color\" value=\"$marks_color\" />";
  } else {
    echo "<div onclick=\"showPicker('marks_color',event); document.accessibility.marks_radio[1].checked=true;\" id=\"span_marks_color\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$marks_color\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"marks_color\" name=\"marks_color\" value=\"$marks_color\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['themecolour']; ?></td>
<td><input type="radio" name="theme_radio" value="0"<?php if ($themecolor == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" name="theme_radio" value="1"<?php if ($themecolor != '') echo ' checked'; ?> />
<?php
  if ($themecolor == '') {
    echo "<div onclick=\"showPicker('themecolor',event); document.accessibility.theme_radio[1].checked=true;\" id=\"span_themecolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" />";
  } else {
    echo "<div onclick=\"showPicker('themecolor',event); document.accessibility.theme_radio[1].checked=true;\" id=\"span_themecolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$themecolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"themecolor\" name=\"themecolor\" value=\"$themecolor\" />";
  }
?>
</td>
</tr>
<tr>
<td><?php echo $string['labelscolour']; ?></td>
<td><input type="radio" name="labels_radio" value="0"<?php if ($labelcolor == '') echo ' checked'; ?> /><?php echo $string['default']; ?></td>
<td><input type="radio" name="labels_radio" value="1"<?php if ($labelcolor != '') echo ' checked'; ?> />
<?php
  if ($labelcolor == '') {
    echo "<div onclick=\"showPicker('labelcolor',event); document.accessibility.labels_radio[1].checked=true;\" id=\"span_labelcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:white\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" />";
  } else {
    echo "<div onclick=\"showPicker('labelcolor',event); document.accessibility.labels_radio[1].checked=true;\" id=\"span_labelcolor\" style=\"display:inline; border:1px solid #C5C5C5; width:20px; background-color:$labelcolor\">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type=\"hidden\" id=\"labelcolor\" name=\"labelcolor\" value=\"$labelcolor\" />";
  }
?>
</td>
</tr>
<tr><td colspan="3">&nbsp;</td></tr>
<tr><td colspan="3" align="center"><input type="submit" name="updateaccess" value="<?php echo $string['save']; ?>" style="width:100px" /></td></tr>
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
  echo drawTabs('Metadata', 5, '', $tmp_roles);
  echo "<tr><td class=\"coltitle\">&nbsp;" . $string['moduleid'] . "</td><td class=\"coltitle\">" . $string['academicyear'] . "</td><td class=\"coltitle\">" . $string['type'] . "</td><td class=\"coltitle\">" . $string['value'] . "</td><td class=\"coltitle\" style=\"width:30%\">&nbsp;</td></tr>\n";
  $stmt = $mysqli->prepare("SELECT users_metadata.id, modules.id, modules.moduleID, fullname, calendar_year, type, value FROM users_metadata, modules WHERE users_metadata.moduleID=modules.id AND userID=?");
  $stmt->bind_param('i', $_GET['userID']);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($meta_id, $mod_id, $moduleID, $fullname, $calendar_year, $type, $value);
  while ($stmt->fetch()) {
    echo "<tr><td>&nbsp;$moduleID: $fullname</td><td>$calendar_year</td><td>$type</td><td><input type=\"hidden\" name=\"meta_id$metadata_no\" value=\"$meta_id\" /><select name=\"meta_value$metadata_no\">";
    $result = $mysqli->prepare("SELECT DISTINCT value FROM users_metadata WHERE calendar_year=? AND moduleID=? AND type=?");
    $result->bind_param('sis', $calendar_year, $mod_id, $type);
    $result->execute();
    $result->store_result();
    $result->bind_result($unique_value);
    while($result->fetch()) {
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
  echo "<tr><td colspan=\"5\" style=\"text-align:center\"><input type=\"submit\" name=\"save_metadata\" value=\"" . $string['save'] . "\" style=\"width:100px\" /><input type=\"hidden\" name=\"metadata_no\" value=\"$metadata_no\" /></td></tr>\n";
?>
</form>
</table>

<?php
  if ($tab == 'teams') {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Teams_tab\" style=\"width:100%\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"Teams_tab\" style=\"width:100%; display:none\">\n";
  }
  echo drawTabs('Teams', 3, '', $tmp_roles);
  echo "<tr><td class=\"coltitle\">&nbsp;" . $string['team'] . "</td><td class=\"coltitle\">" . $string['dateadded'] . "</td><td class=\"coltitle\">" . $string['type'] . "</td></tr>\n";
  if (strpos($userroles,'Admin') !== false) {
  echo "<tr><td colspan=\"3\"><a href=\"\" onclick=\"editMultiTeams(); return false;\">&nbsp;" . $string['editteams'] . "</a></td></tr>\n";
  }
  $query_string = "SELECT name, fullname, DATE_FORMAT(added,'%d/%m/%Y') AS added, type FROM teams, modules WHERE teams.name=modules.moduleid AND memberID=$tmp_id ORDER BY name";
  $results = $mysqli->query($query_string);
  while ($row = $results->fetch_assoc()) {
    echo "<tr><td>&nbsp;" . $row['name'] . ": " . $row['fullname'] . "</td><td>" . $row['added'] . "</td><td>" . $string[strtolower($row['type'])] . "</td></tr>\n";
  }
  $mysqli->close();
?>
</table>
</div>

</body>
</html>
