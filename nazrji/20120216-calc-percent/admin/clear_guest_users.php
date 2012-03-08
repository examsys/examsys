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
  require '../include/sidebar_menu.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Clear Guest Users</title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<script language="JavaScript" src="../js/staff_help.js"></script>
<style type="text/css">
.sechead {background-color:#EBF2F7; color:#00156E; border-bottom: 1px solid #CFDBEB}
.l {border-bottom:1px solid #EEEEEE}
.loff {border-bottom:1px solid #EEEEEE; color:#808080}
</style>
</head>

<body>
<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">
<table class="header">
<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><?php echo $string['clearguestaccounts']; ?></div></th><th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(243); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th></tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<br />

<?php
  if (isset($_POST['submit'])) {
    for ($i=1; $i<=100; $i++) {
      if (isset($_POST["clear$i"])) {
        $stmt = $mysqli->prepare("DELETE FROM temp_users WHERE id=?");
        $stmt->bind_param('i', $_POST["clear$i"]);
        $stmt->execute();
      }
    }
  }
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<blockquote>
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; width:100%">
<tr><td class="sechead"><?php echo $string['clear']; ?></td><td class="sechead"><?php echo $string['user']; ?></td><td class="sechead"><?php echo $string['password']; ?></td><td class="sechead"><?php echo $string['surname']; ?></td><td class="sechead"><?php echo $string['firstnames']; ?></td><td class="sechead"><?php echo $string['title']; ?></td><td class="sechead"><?php echo $string['studentid']; ?></td><td class="sechead"><?php echo $string['datereserved']; ?></td><td class="sechead"><?php echo $string['assessmenttaken']; ?></td></tr>
<?php
  $used = array();

  $result = $mysqli->prepare("SELECT id, first_names, surname, title, student_id, assigned_account, DATE_FORMAT(reserved,'%d/%m/%Y %H:%i:%s') FROM temp_users");
  $result->execute();
  $result->bind_result($id, $first_names, $surname, $title, $student_id, $assigned_account, $reserved);
  while ($row = $result->fetch()) {
    $assigned_account = str_replace('user','',$assigned_account);
    
    $used[$assigned_account]['id'] = $id;
    $used[$assigned_account]['first_names'] = $first_names;
    $used[$assigned_account]['surname'] = $surname;
    $used[$assigned_account]['title'] = $title;
    $used[$assigned_account]['student_id'] = $student_id;
    $used[$assigned_account]['assigned_account'] = $assigned_account;
    $used[$assigned_account]['reserved'] = $reserved;
  }
  $result->close();
  
  for ($i=1; $i<=100; $i++) {
    if (isset($used[$i]['reserved']) and $used[$i]['reserved'] != '') {
      $paper_title = '';
      $result = $mysqli->prepare("SELECT DISTINCT q_paper, paper_title FROM log2, properties, users WHERE log2.userID=users.id AND log2.q_paper=properties.property_id AND username='user$i'");
      $result->execute();
      $result->bind_result($q_paper, $paper_title);
      $result->fetch();
      $result->close();
    
      if ($used[$i]['surname'] == '') $used[$i]['surname'] = '<span style="color:#C00000">' . $string['unset'] . '</span>';
      if ($used[$i]['first_names'] == '') $used[$i]['first_names'] = '<span style="color:#C00000">' . $string['unset'] . '</span>';
      if ($used[$i]['title'] == '') $used[$i]['title'] = '<span style="color:#C00000">' . $string['unset'] . '</span>';
      if ($used[$i]['student_id'] == '') $used[$i]['student_id'] = '<span style="color:#C00000">' . $string['unset'] . '</span>';
    
      echo "<tr><td class=\"l\">";
      if ($paper_title == '') {
        echo "<input type=\"checkbox\" name=\"clear$i\" value=\"" . $used[$i]['id'] . "\" />";
      } else {
        echo "<input type=\"checkbox\" name=\"clear$i\" value=\"\" disabled />";
      }
      echo "</td><td class=\"l\">user$i</td><td class=\"l\">guest$i</td><td class=\"l\">" . $used[$i]['surname'] . "</td><td class=\"l\">" . $used[$i]['first_names'] . "</td><td class=\"l\">" . $used[$i]['title'] . "</td><td class=\"l\">" . $used[$i]['student_id'] . "</td><td class=\"l\">" . $used[$i]['reserved'] . "</td>";
      if ($paper_title == '') {
        echo "<td class=\"loff\">not taken</td>";
      } else {
        echo "<td class=\"l\"><a href=\"../paper/details.php?paperID=$q_paper\">$paper_title</td>";
      }
      echo "</tr>";
    } else {
      echo "<tr><td class=\"loff\"><input type=\"checkbox\" name=\"clear$i\" value=\"\" disabled /></td><td class=\"loff\">user$i</td><td class=\"loff\">guest$i</td><td colspan=\"6\" class=\"loff\" style=\"text-align:center\">" . $string['free'] . "</td></tr>";
    }
  }
  $mysqli->close();
?>
<tr><td colspan="9" style="text-align:center"><input style="width:120px" type="submit" name="submit" value="<?php echo $string['cleanup']; ?>" /></td></tr>
</table>
</blockquote>
</form>
</div>

</body>
</html>