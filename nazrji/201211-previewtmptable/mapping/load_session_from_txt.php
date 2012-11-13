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

require '../include/sysadmin_auth.inc';  

if (isset($_POST['submit'])) {
  $moduleID = $_POST['module'];
  $session = $_POST['session'];
  $session_flag = false;

  if ($_FILES['txtfile']['name'] != 'none' and $_FILES['txtfile']['name'] != '') {
    if (!move_uploaded_file($_FILES['txtfile']['tmp_name'], $cfg_tmpdir . $userObject->get_user_ID() . '_load_objectives.txt'))  {
      echo uploadError($_FILES['txtfile']['error']);
      exit;
    } else {
      $result = $mysqli->prepare("SELECT MAX(obj_id) AS largest FROM objectives");
      $result->execute();
      $result->bind_result($largest);
      $i = 0;
      while ($row = $result->fetch()) {
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
      
      $lines = file($cfg_tmpdir . $userObject->get_user_ID() . '_load_objectives.txt');
      foreach ($lines as $separate_line) {

        if (substr($separate_line,0,1) == '#') {   // Sub-heading
          $title = substr($separate_line,1);
          $identifier++;
     
          $stmt = $mysqli->prepare("INSERT INTO sessions VALUES (NULL,?,?,?,'',?,NOW())");
          $stmt->bind_param('ssss', $identifier, $moduleID, $title, $session);
          $stmt->execute();
          $stmt->close();
          $session_flag = true;
        } else {                                   // Objective
          if ($session_flag == false) {
            $stmt = $mysqli->prepare("INSERT INTO sessions VALUES (NULL,?,?,'Temp Session Title','',?,NOW())");
            $stmt->bind_param('sss',$identifier,$moduleID,$session);
            $stmt->execute();
            $stmt->close();
            $session_flag = true;
          }
        
          $stmt = $mysqli->prepare("INSERT INTO objectives VALUES (?,?,?,?,?,?)");
          $stmt->bind_param('issssi', $obj_id, $separate_line, $moduleID, $identifier, $session, $obj_id);
          $stmt->execute();
          $stmt->close();
          $obj_id++;
        }
      }
    }
  }
  
  unlink($cfg_tmpdir . $userObject->get_user_ID() . '_load_objectives.txt');
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . $cfg_root_path . "/mapping/sessions_list.php?module=" . $_POST['module']);
} else {
  //display the form
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogo: <?php echo $string['importfromfile'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    img {border:none;}
    .editBox {width:90%}
    .field {text-align:right; font-weight:bold}
    .note {width:90%}
  </style>
</head>

<body onclick="hideSessCopyMenu(event);">
<?php
  require '../include/sessions_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<?php
  $module = $_GET['module'];

  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n";
  echo "<tr><td style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../folder/details.php?module=$module\">$module</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./sessions_list.php?module=$module\">" . $string['manageobjectives'] . "</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['importfromfile'] . "</strong></div></td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(0); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></td></tr>\n";
  echo "<tr><td colspan=\"2\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
  echo "</table>\n";
?>

<br />
<br />

<div align="center">

<table border="0" cellpadding="4" cellspacing="0" style="border:1px solid #C0C0C0; width:600px">
<tr>
<td valign="middle" align="left" style="background-color:white"><img src="../artwork/import.gif" width="32" height="32" alt="Icon" />&nbsp;&nbsp;<span style="font-size:160%; font-weight:bold; color:#5582D2"><?php echo $string['importobjectives']; ?></span></td>
</tr>
<tr>
<td align="left" style="background-color:#EEEEEE">

<p><?php echo $string['msg']; ?></p>

<div align="center">
<form name="import" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">

<table cellpadding="3" cellspacing="0" border="0" style="text-align:left">
<tr>
<td style="text-align:right"><?php echo $string['objectivesfile']; ?></td><td><input type="file" size="50" name="txtfile" />
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" /></td>
</tr>

<tr>
<?php

  echo "<td style=\"text-align:right\">" . $string['session'] . "</td><td><select name=\"session\">\n";
  $startyear = ( date('Y') - 1 );
  for ($i = 0; $i < 2; $i++) {
    $tmp_session = ($startyear + $i) . '/' . substr(($startyear + $i + 1),2);
    $sel = ($tmp_session == date_utils::get_current_academic_year()) ? ' selected="selected"' : ''; 
    echo "<option value=\"$tmp_session\"$sel>$tmp_session</option>\n";
  }
  echo "</select></td>\n";
?>
</tr>

<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" style="text-align:center"><input type="submit" style="width:100px" value="<?php echo $string['import']; ?>" name="submit" />&nbsp;<input style="width:100px" type="button" value="<?php echo $string['cancel']; ?>" name="cancel" onclick="history.go(-1)" /></td></tr>
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