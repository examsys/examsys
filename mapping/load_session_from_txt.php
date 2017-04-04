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

ini_set("auto_detect_line_endings", true);

$modID = check_var('module', 'REQUEST', true, false, true);
if (isset($_POST['submit'])) {
  $session = $_POST['session'];
  $session_flag = false;

  if ($_FILES['txtfile']['name'] != 'none' and $_FILES['txtfile']['name'] != '') {
    if (!move_uploaded_file($_FILES['txtfile']['tmp_name'],  $configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_load_objectives.txt'))  {
      echo uploadError($_FILES['txtfile']['error']);
      exit;
    } else {
      $result = $mysqli->prepare("SELECT MAX(obj_id) AS largest FROM objectives");
      $result->execute();
      $result->bind_result($largest);
      $i = 0;
      while ($result->fetch()) {
        $obj_id = $largest + 1;
      }
      if ($obj_id < 10) {
        $obj_id = 123;
      }
      $result->close();
      
      $identifier = 0;
      $result = $mysqli->prepare("SELECT MAX(identifier) AS largest FROM sessions");
      $result->execute();
      $result->bind_result($largest);
      $result->fetch();
      $result->close();
      $identifier = $largest + 1;
      
      $lines = file($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_load_objectives.txt');
      foreach ($lines as $separate_line) {

        if (substr($separate_line,0,1) == '#') {   // Sub-heading
          $title = substr($separate_line,1);
          $identifier++;
     
          $stmt = $mysqli->prepare("INSERT INTO sessions VALUES (NULL, ?, ?, ?, '', ?, NOW())");
          $stmt->bind_param('siss', $identifier, $modID, $title, $session);
          $stmt->execute();
          $stmt->close();
          $session_flag = true;
        } else {                                   // Objective
          if ($session_flag == false) {
            $stmt = $mysqli->prepare("INSERT INTO sessions VALUES (NULL, ?, ?, 'Temp Session Title', '', ?, NOW())");
            $stmt->bind_param('sis', $identifier, $modID, $session);
            $stmt->execute();
            $stmt->close();
            $session_flag = true;
          }
        
          $stmt = $mysqli->prepare("INSERT INTO objectives VALUES (?, ?, ?, ?, ?, ?)");
          $stmt->bind_param('isissi', $obj_id, $separate_line, $modID, $identifier, $session, $obj_id);
          $stmt->execute();
          $stmt->close();
          $obj_id++;
        }
      }
    }
  }
  
  unlink($configObject->get('cfg_tmpdir') . $userObject->get_user_ID() . '_load_objectives.txt');
  header("location: " . $configObject->get('cfg_root_path') . "/mapping/sessions_list.php?module=" . $modID);
	exit();
} else {
  // Display the form
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
	
  <title><?php echo $string['importfromfile'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
	
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/dialog.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .editBox {width:90%}
    .field {text-align:right; font-weight:bold}
    .note {width:90%}
  </style>
	
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body onclick="hideSessCopyMenu(event);">
<?php
  require '../include/sessions_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=<?php echo $modID ?>"><?php echo module_utils::get_moduleid_from_id($modID, $mysqli) ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="sessions_list.php?module=<?php echo $modID ?>"><?php echo $string['manageobjectives'] ?></a></div>
  <div class="page_title"><?php echo $string['importfromfile'] ?></div>
</div>

<br />
<br />

<div align="center">

<table class="dialog_border" style="width:600px">
<tr>
  <td align="left" style="background-color:white; width:32px"><img src="../artwork/upload_48.png" width="48" height="48" alt="Icon" /></td><td class="dialog_header midblue_header"><?php echo $string['importobjectives']; ?></td>
</tr>
<tr>
<td align="left" class="dialog_body" colspan="2">

<p><?php echo $string['msg']; ?></p>

<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">

<table cellpadding="3" cellspacing="0" border="0" style="text-align:left">
<tr>
<td style="text-align:right"><?php echo $string['objectivesfile']; ?></td><td><input type="file" size="50" name="txtfile" />
<input type="hidden" name="module" value="<?php echo $modID ?>" /></td>
</tr>

<tr>
<?php
  $yearutils = new yearutils($mysqli);
  echo "<td style=\"text-align:right\">" . $string['session'] . "</td><td><select name=\"session\">\n";
  $startyear = ( date('Y') - 1 );
  for ($i = 0; $i < 2; $i++) {
    $tmp_session = ($startyear + $i) . '/' . substr(($startyear + $i + 1),2);
    $sel = ($tmp_session == $yearutils->get_current_session()) ? ' selected="selected"' : '';
    echo "<option value=\"$tmp_session\"$sel>$tmp_session</option>\n";
  }
  echo "</select></td>\n";
?>
</tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" class="ok" value="<?php echo $string['import']; ?>" name="submit" /><input class="cancel" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></td></tr>
</form>
</div>
</td>
</tr>
</table>

</div>
</div>
<?php	
}
$mysqli->close();
?>
</body>
</html>