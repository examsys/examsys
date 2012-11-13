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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require_once './include/staff_student_auth.inc';
require_once './include/errors.inc';
require_once './classes/dateutils.class.php';
require_once './classes/userutils.class.php';
require_once './classes/moduleutils.class.php';
require_once './classes/smsutils.class.php';

check_var('moduleid', 'GET', true, false);
$session = date_utils::get_current_academic_year();

//dose the user have an account?
if (UserUtils::username_exists($_SERVER['PHP_AUTH_USER'], $mysqli) === false ) {
  //the user has no Rogo Account but has an LDAP acount so lets make one !

  //taken from lti integration

  if (!isset($lti_i)) {
    $lti_i = lti_integration::load();
  }

  if($lti_i->description !=='Default') {
    $returned=$lti_i::user_add($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
  } else {
    $SMS = SMSutils::GetSmsUtils();
    $user_data = $SMS->getUserData($_SERVER['PHP_AUTH_USER']);
    if ($user_data !== false) {
      //valid acount found create user
      UserUtils::create_user(
        $_SERVER['PHP_AUTH_USER'],
        $_SERVER['PHP_AUTH_PW'],
        $user_data['Title'],
        $user_data['Forename'],
        $user_data['Surname'],
        $user_data['Email'],
        $user_data['CourseCode'],
        $user_data['Gender'],
        $user_data['YearofStudy'],
        'Student',
        $user_data['StudentID'],
        $mysqli
      );
      db_auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], $mysqli);
    } else {
      //no account information found
      display_error($string['noaccountfound'], '', false, true);
    }
  }
}
$returned_check = module_utils::module_check_self_enrol($_GET['moduleid'], $mysqli);
if ($returned_check === false) {
  display_error('Module ID error', 'Module code ' . $_GET['moduleid'] . ' not found.', false, true);
}

list($fullname, $school, $active, $selfenroll) = $returned_check;

if ($active == 1 and $selfenroll == 1 and isset($_POST['submit']) and !UserUtils::is_user_on_module($userObject->get_user_ID(), $_GET['moduleid'], $_POST['session'], $mysqli)) {
  // Insert new module enrollment
  UserUtils::add_student_to_module($userObject->get_user_ID(), $_GET['moduleid'], 1, $_POST['session'], $mysqli);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['moduleselfenrolment'] . ' ' . $cfg_install_type; ?></title>
  
  <link rel="stylesheet" type="text/css" href="./css/body.css" />
  <style type="text/css">
  body {font-size:90%}
  .field {padding-top:4px; padding-left:6px; font-weight:bold}
  .topbar {
    height:70px;
    background: -moz-linear-gradient(top, #EEEEEE, #C9C9C9);
    background: -webkit-linear-gradient(top, #EEEEEE, #C9C9C9);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#EEEEEE', endColorstr='#C9C9C9');
    vertical-align:middle;
    font-size:150%;
    font-weight:bold;
    padding-left:6px
  }
  </style>
</head>

<body>
<form name="myform" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?moduleid=' . $_GET['moduleid']; ?>">
<?php

  $year_parts = explode('/',$session);
  $next_session = ($year_parts[0] + 1) . '/' . ($year_parts[1] + 1);
  
  $years = array($session, $next_session);
  
  echo '<br /><div align="center"><table cellpadding="0" cellspacing="0" style="width:500px; border:1px #C8C8C8 solid">';
  echo '<tr><td class="topbar" style="text-align:right; width:55px"><img src="./artwork/modules_icon.png" width="48" height="48" alt="modules" /></td><td class="topbar" style="padding-left:15px; text-align:left">' . $string['moduleselfenrolment'] . '</td></tr>';
  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '<tr><td colspan="2"><table border="0" style="width:100%; text-align:left"><tr><td class="field" style="width:120px">' . $string['moduleid'] . '</td><td>' . $_GET['moduleid'] . '</td></tr>';
  echo '<tr><td class="field">' . $string['name'] . '</td><td>' . $fullname . '</td></tr>';
  echo '<tr><td class="field">' . $string['school'] . '</td><td>' . $school . '</td></tr>';
  echo '<tr><td class="field">' . $string['academicyear'] . '</td><td><select name="session">';
  foreach ($years as $year) {
    if (isset($_POST['session']) and $_POST['session'] == $year) {
      echo '<option value="' . $year . '" selected>' . $year . '</option>';
    } else {
      echo '<option value="' . $year . '">' . $year . '</option>';
    }
  }
  echo '</select></td></tr>';
  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  if (isset($_POST['submit'])) {
    echo '<tr><td colspan="2"><strong>' . $string['enrolmentcompleted'] . '</strong></td></tr>';
    echo '<tr><td colspan="2">&nbsp;</td></tr>';
    echo '<tr><td colspan="2"><a href="/"><img src="/artwork/link.png" width="16" height="16" alt=">" border="0" /></a>&nbsp;<strong><a href="/students/" style="color:blue">' . $string['icanaccess'] . '</a></strong></td></tr>';
  } else {
    echo '<tr><td colspan="2">&nbsp;' . sprintf($string['iwouldliketo'], $title, $initials, $surname, $username) . '</td></tr>';
    echo '<tr><td colspan="2">&nbsp;</td></tr>';
    if ($active == 0) {
      echo '<tr><td colspan="2" style="color:#C00000">' . $string['notactive'] . '</td></tr>';
      echo '<tr><td colspan="2" style="text-align:center"><input type="submit" name="submitdisabled" value="' . $string['enroll'] . '" style="width:100px" disabled />&nbsp;<input type="button" name="cancel" value="' . $string['cancel'] . '" onclick="history.back();" style="width:100px" /></td></tr>';
    } else {
      if ($selfenroll == 0) {
        echo '<tr><td colspan="2" style="color:#C00000">' . $string['notavailableselfenrollment'] . '</td></tr>';
        echo '<tr><td colspan="2" style="text-align:center"><input type="submit" name="submitdisabled" value="' . $string['enroll'] . '" style="width:100px" disabled />&nbsp;<input type="button" name="cancel" value="' . $string['cancel'] . '" onclick="history.back();" style="width:100px" /></td></tr>';
      } else {
        echo '<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="' . $string['enroll'] . '" style="width:100px" />&nbsp;<input type="button" name="cancel" value="' . $string['cancel'] . '" style="width:100px" onclick="history.back();" /></td></tr>';

      }
    }
  }
  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '</table></td></tr></table></div>';
  
  $mysqli->close();
?>
</form>

</body>
</html>