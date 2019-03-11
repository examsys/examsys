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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once './include/staff_student_auth.inc';
require_once './include/errors.php';

if (isset($_GET['moduleid'])) {   // Old format
  $module = $_GET['moduleid'];
} elseif (isset($_GET['mod'])) {  // New shorter format
  $module = $_GET['mod'];  
} else {
  display_error($string['fatalerrormsg0'], $string['fatalerrormsg1'], true);
}

$yearutils = new yearutils($mysqli);
$session = $yearutils->get_current_session();

$modID = module_utils::get_idMod($module, $mysqli);  // Translate module code into ID

$mod_details = module_utils::get_full_details_by_ID($modID, $mysqli);
if ($mod_details === false) {
  $msg = sprintf($string['nomodule'], $module);
  display_error('Module ID error', $msg, false, true);
}

if ($mod_details['active'] == 1 and $mod_details['selfenroll'] == 1 and isset($_POST['submit'])) {
  if (!$userObject->has_role('Student')) {  // Add role of 'Student' if current user doesn't have it.
    UserUtils::add_role('Student', $userObject->get_user_ID(), $mysqli);
  }
  
  // Insert new module enrollment
  UserUtils::add_student_to_module($userObject->get_user_ID(), $modID, 1, $_POST['session'], $mysqli);
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['moduleselfenrolment']); ?></title>
  
  <link rel="stylesheet" type="text/css" href="./css/body.css" />
  <style type="text/css">
  body {font-size:90%}
  .field {padding-top:4px; padding-right:4px; text-align:right}
  .topbar {
    height:70px;
    background:#EEEEEE;
    vertical-align:middle;
    font-size:150%;
    font-weight:bold;
    padding-left:6px
  }
  </style>
</head>

<body>
<form name="myform" method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?mod=' . $module ?>" autocomplete="off">
<?php

  $years = array($session, $session + 1);
  
  echo '<br /><div align="center"><table cellpadding="0" cellspacing="0" style="width:500px; border:1px #C8C8C8 solid">';
  echo '<tr><td class="topbar" style="text-align:right; width:55px"><img src="./artwork/modules_icon.png" width="48" height="48" alt="modules" /></td><td class="topbar" style="padding-left:15px; text-align:left">' . $string['moduleselfenrolment'] . '</td></tr>';
  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '<tr><td colspan="2"><table border="0" style="width:100%; text-align:left"><tr><td class="field" style="width:120px">' . $string['moduleid'] . '</td><td>' . $module . '</td></tr>';
  echo '<tr><td class="field">' . $string['name'] . '</td><td>' . $mod_details['fullname'] . '</td></tr>';
  echo '<tr><td class="field">' . $string['school'] . '</td><td>' . $mod_details['school'] . '</td></tr>';
  echo '<tr><td class="field">' . $string['academicyear'] . '</td><td><select name="session">';
  foreach ($years as $year) {
    if (isset($_POST['session']) and $_POST['session'] == $year) {
      echo '<option value="' . $year . '" selected>' . $yearutils->get_academic_session($year) . '</option>';
    } else {
      echo '<option value="' . $year . '">' . $yearutils->get_academic_session($year) . '</option>';
    }
  }
  echo '</select></td></tr>';
  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  if (isset($_POST['submit'])) {
    echo '<tr><td colspan="2">&nbsp;<strong>' . $string['enrolmentcompleted'] . '</strong></td></tr>';
    echo '<tr><td colspan="2">&nbsp;</td></tr>';
    echo '<tr><td colspan="2">&nbsp;<a href="/"><img src="./artwork/small_link.png" width="11" height="11" alt=">" /></a>&nbsp;<a href="' . $configObject->get('cfg_root_path') . '/students/">' . $string['icanaccess'] . '</a></td></tr>';
  } else {
    echo '<tr><td colspan="2">&nbsp;' . sprintf($string['iwouldliketo'], $userObject->get_title(), $userObject->get_initials(), $userObject->get_surname(), $userObject->get_username()) . '</td></tr>';
    echo '<tr><td colspan="2">&nbsp;</td></tr>';
    if ($mod_details['active'] == 0) {
      echo '<tr><td colspan="2" style="color:#C00000">' . $string['notactive'] . '</td></tr>';
      echo '<tr><td colspan="2" style="text-align:center"><input type="submit" name="submitdisabled" value="' . $string['enroll'] . '" class="ok" disabled /><input type="button" name="cancel" value="' . $string['cancel'] . '" onclick="history.back();" class="cancel" /></td></tr>';
    } else {
      if ($mod_details['selfenroll'] == 0) {
        echo '<tr><td colspan="2" style="color:#C00000">' . $string['notavailableselfenrollment'] . '</td></tr>';
        echo '<tr><td colspan="2" style="text-align:center"><input type="submit" name="submitdisabled" value="' . $string['enroll'] . '" class="ok" disabled /><input type="button" name="cancel" value="' . $string['cancel'] . '" onclick="history.back();" class="cancel" /></td></tr>';
      } else {
        echo '<tr><td colspan="2" style="text-align:center"><input type="submit" name="submit" value="' . $string['enroll'] . '" class="ok" /><input type="button" name="cancel" value="' . $string['cancel'] . '" class="cancel" onclick="history.back();" /></td></tr>';

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