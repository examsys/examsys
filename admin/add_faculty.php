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

require '../include/staff_auth.inc';
require '../include/errors.php';
  
$duplicate = false;
if (isset($_POST['ok'])) {
  $add_faculty = check_var('add_faculty', 'POST', true, false, true);
  $code = check_var('code', 'POST', false, false, true);
  $externalid = check_var('externalid', 'POST', false, false, true);
  $externalsys = check_var('externalsys', 'POST', false, false, true);
  if (!is_null($add_faculty)) {
    // Check for existing faculty code.
    if (!is_null($code)) {
        if (FacultyUtils::get_facultyid_by_code($code, $mysqli)) {
            $duplicate = 'code';
        }
    } else {
        // Check for existing faculty name.
        if (FacultyUtils::facultyname_exists($add_faculty, $mysqli)) {
            $duplicate = 'name';
        }
    }
    if ($duplicate === false) {
      FacultyUtils::add_faculty($add_faculty, $mysqli, $code, $externalid, $externalsys);
    }
  }
  if (!$duplicate) {
?>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<title><?php echo $string['addfaculty']; ?></title>
</head>
<?php
  if ($add_faculty != '') {
    echo "<body onload=\"window.opener.location.href='list_faculties.php'; window.close();\">\n";
  } else {
    echo "<body onload=\"window.close();\">\n";
  }
?>
</body>
</html>
<?php
    exit();
  }
} else {
    $add_faculty = '';
    $code = '';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['addfaculty']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; margin:2px; background-color:#EAEAEA}
    h1 {font-size:140%; font-weight:normal}
  </style>
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
    });
  </script>
</head>

<body>
<h1><?php echo $string['addfaculty'] ?></h1>
<form id="theform" name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
<table cellpadding="0" cellspacing="2" border="0">
<?php
if ($duplicate == 'code') {
  echo '<tr><td class="field">' . $string['name'] . '</td><td><input type="text" style="width:99%" id="add_faculty" name="add_faculty" maxlength="80" value="' . $add_faculty .'" required autofocus /></td></tr>';
  echo '<tr><td class="field">' . $string["code"] . '</td><td><input type="text" style="width:99%; background-color:#FFC0C0; border:solid 1px #C00000; color:#800000" name="code" value="' . $code . '" size="30" maxlength="30" required autofocus /></td></tr>';
  echo "<script language=\"JavaScript\">\nalert('" . $string['facultywarning'] . "');\n</script>\n";
} elseif($duplicate == 'name') {
  echo '<tr><td class="field">' . $string['name'] . '</td><td><input type="text" style="width:99%; background-color:#FFC0C0; border:solid 1px #C00000; color:#800000" id="add_faculty" name="add_faculty" maxlength="80" value="' . $add_faculty .'" required autofocus /></td></tr>';
  echo '<tr><td class="field">' . $string["code"] . '</td><td><input type="text" style="width:99%" name="code" value="' . $code . '" size="30" maxlength="30" required autofocus /></td></tr>';
  echo "<script language=\"JavaScript\">\nalert('" . $string['facultywarning'] . "');\n</script>\n";
} else {
  echo '<tr><td class="field">' . $string['name'] . '</td><td><input type="text" style="width:99%" id="add_faculty" name="add_faculty" maxlength="80" value="" required autofocus /></td></tr>';
  echo '<tr><td class="field">' . $string["code"] . '</td><td><input type="text" size="30" maxlength="30" name="code" value=""/></td></tr>';
}
$external = new \external_systems();
$extsys = $external->get_all_externalsystems();
?>
<tr><td class="field"><?php echo $string['externalsys'] ?></td><td><select name="externalsys">
<?php
  echo "<option value=\"\"></option>\n";
  foreach ($extsys as $i => $s) {
    if (isset($externalsys) and $s == $externalsys) {
      $selected = "selected";
    } else {
      $selected = "";
    }
    echo "<option value=\"$s\" $selected>$s</option>\n";
  }
?>
</select></td></tr>
<tr><td class="field"><?php echo $string['externalid'] ?></td><td><input type="text" size="30" maxlength="255" name="externalid" value=""></td></tr>
</table>
<div align="right"><input type="submit" name="ok" value="<?php echo $string['ok'] ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" style="margin-right:0" onclick="window.close();" /><input type="hidden" name="module" value="<?php if (isset($_GET['module'])) echo $_GET['module']; ?>" /></div>
</form>

</body>
</html>
<?php
$mysqli->close();
?>