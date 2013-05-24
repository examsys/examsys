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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  require '../include/sidebar_menu.inc';
  set_time_limit (0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['optimizetables']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <script src="../js/staff_help.js" type="text/javascript"></script>
</head>

<body>
<?php
  require '../include/admin_options.inc';
?>

<div id="content" class="content">
<table class="header">
<tr>
<th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><?php echo $string['optimizetables']; ?></div></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(235); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<br />

<?php
  if (isset($_POST['submit'])) {
    ob_start();

    echo "<blockquote>\n";
    $getTables = $mysqli->query("SHOW TABLES");
    while ($table = $getTables->fetch_array(MYSQLI_NUM)) {
      if (isset($_POST[$table[0]]) and $_POST[$table[0]] == 1) {
        if (!$mysqli->query("OPTIMIZE TABLE " . $table[0])) {
          echo "<div>" . $mysqli->errno . ": " . $mysqli->error . "</div>\n";
        } else {
          echo "<div>" . $table[0] . " " . $string['optimized'] . "</div>\n";
        }
        flush();
        ob_flush();
      }
    }
    echo "<br /><br />" . $string['finished'] . "</blockquote>\n";
    
    ob_end_flush();
  } else {
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<blockquote>
<div><strong><?php echo $string['tables']; ?></strong></div>
<?php
  $getTables = $mysqli->query("SHOW TABLES");
  while ($table = $getTables->fetch_array(MYSQLI_NUM)) {
    echo "<input type=\"checkbox\" name=\"" . $table[0] . "\" value=\"1\" checked />&nbsp;" . $table[0] . "<br />\n";
  }
?>
<br />
<input style="width:120px" type="submit" name="submit" value="<?php echo $string['optimize']; ?>" />
</blockquote>
</form>
</div>
<?php
  }
  $mysqli->close();
?>
</body>
</html>