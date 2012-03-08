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
  require_once '../classes/networkutils.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title><?php echo $string['systeminformation']; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<script src="../js/staff_help.js" type="text/javascript"></script>

<style type="text/css">
.sechead {background-color:#EBF2F7; color:#00156E; border-bottom: 1px solid #CFDBEB}
a {font-family:Arial,sans-serif; color:#215DC6}
a:hover {color:#428EFF}
a.heading {color:#215DC6; font-weight:bold}
a.heading:hover {color:#428EFF; font-weight:bold}
</style>
</head>

<body>
<?php
  include '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">

<table class="header">
<tr>
<th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><?php echo $string['systeminformation']; ?></div></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(240); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<br />
<div align="center">
<table cellspacing="0" cellpadding="0" border="0" style="font-size:100%; text-align:left">
<tr><td style="vertical-align:top">
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; text-align:left">
<tr><td style="width:120px" class="sechead"><?php echo $string['table']; ?></td><td class="sechead"><?php echo $string['records']; ?></td><td class="sechead"><?php echo $string['updated']; ?><td class="sechead"><?php echo $string['engine']; ?></td>
</tr>
<?php
  $result = $mysqli->prepare("SHOW TABLE STATUS");
  $result->execute();
  $result->bind_result($Name, $Engine, $Version, $Row_format, $Rows, $Avg_row_length, $Data_length, $Max_data_length, $Index_length, $Data_free, $Auto_increment, $Create_time, $Update_time, $Check_time, $Collation, $Checksum, $Create_options, $Comment);
  while ($result->fetch()) {
    if (($Name == 'log_late' or $Name == 'temp_users') and $Rows > 0) {
      echo "<tr><td style=\"color:#C00000\">" . $Name . "&nbsp;<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['warning'] . "\" /></td><td style=\"text-align:right; color:#C00000\">" . number_format($Rows) . "</td>";
    } else {
      echo "<tr><td>" . $Name . "</td><td style=\"text-align:right\">" . number_format($Rows) . "</td>";
    }
    if ($Engine == 'InnoDB') {
      echo "<td>&nbsp;<span style=\"color:#808080\">" . $string['na'] . "</span></td>";
    } else {
      echo "<td>&nbsp;" . substr($Update_time, 8, 2) . "/" . substr($Update_time, 5, 2) . "/" . substr($Update_time, 0, 4) .  "</td>";
    }
    echo "<td>" . $Engine . "</td></tr>\n";
  }
  $result->close();

  echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
  echo "<tr><td colspan=\"2\" class=\"sechead\">" . $string['mysqlstatus'] . "</td><td colspan=\"2\"></td></tr>\n";
  $status = explode('  ', $mysqli->stat());
  for ($i=0; $i<=7; $i++) {
    $parts = explode(': ', $status[$i]);
    if ($i < 7) {
      echo "<tr><td>" . $string[strtolower($parts[0])] . "</td><td style=\"text-align:right\">" . number_format($parts[1]) . "</td><td colspan=\"2\"></td></tr>\n";
    } else {
      echo "<tr><td>" . $string[strtolower($parts[0])] . "</td><td style=\"text-align:right\">" . $parts[1] . "</td><td colspan=\"2\"></td></tr>\n";
    }
  }
  echo "</table>\n<br />\n";
?>
</td>
<td style="width:50px">&nbsp;</td>
<td style="vertical-align:top">
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; width:400px">
<tr><td colspan="2" class="sechead"><?php echo $string['application']; ?></td></tr>
<tr><td><?php echo $string['version']; ?></td><td><?php echo $ts_version; ?></td></tr>
<tr><td><?php echo $string['webroot']; ?></td><td><?php echo $cfg_web_root; ?></td></tr>
<tr><td><?php echo $string['database']; ?></td><td><?php echo $cfg_db_database; ?></td></tr>
<?php
if ($cfg_use_ldap == true) {
  echo "<tr><td>" . $string['authentication'] . "</td><td>LDAP</td></tr>\n";
} else {
  echo "<tr><td>" . $string['authentication'] . "</td><td>Internal</td></tr>\n";
}
?>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><td colspan="2" class="sechead"><?php echo $string['serverinformation']; ?></td></tr>
<?php

   if (php_uname('s') != 'Windows NT') {
    // Try Linux command first
    $results = shell_exec('cat /proc/cpuinfo');
    if ($results != '') {
      $lines = explode('<br />',nl2br($results));
      $core_no = 0;
      $processor = '';
      foreach ($lines as $individual_line) {
        $components = explode(':',$individual_line);
        if (trim($components[0]) == 'model name') {
          $core_no++;
          $processor = trim($components[1]);
        }
      }
      echo "<tr><td>" . $string['processor'] . "</td><td>$processor</td></tr>\n";
      echo "<tr><td>" . $string['cores'] . "</td><td>$core_no</td></tr>\n";
    } else {
      // Try Solaris command
      $results = shell_exec('psrinfo -pv');
      $lines = explode('<br />',nl2br($results));
      $physical = 0;
      $virtual = 0;
      $processor = '';
      foreach ($lines as $individual_line) {
        if (strpos($individual_line,'The physical processor') !== false) {
          $tmp_line = str_replace('The physical processor has ','',trim($individual_line));
          $physical++;
          $virtual += substr($tmp_line,0,1);
        }
        if (strpos($individual_line,'clock') !== false) {
          $processor = trim($individual_line);
          $processor_parts = explode("\(",$processor);
          $speed_parts = explode('clock ',$processor_parts[1]);
          $speed = str_replace(')','',$speed_parts[1]);
        }
      }
    }
    if (isset($processor_parts[0])) {
      echo "<tr><td>" . $string['processor'] . "</td><td>" . $processor_parts[0] . "($speed)</td></tr>\n";
      echo "<tr><td>" . $string['cpus'] . "</td><td>$physical ($virtual virtual)</td></tr>\n";
    }
  } else {
    echo "<tr><td>" . $string['processor'] . "</td><td>" . php_uname('m') . "</td></tr>\n";
  }
      
  echo "<tr><td style=\"width:90px\">" . $string['servername'] . "</td><td>" . gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME'])) . "</td></tr>\n";
  echo "<tr><td>" . $string['hostname'] . "</td><td>" . $_SERVER['HTTP_HOST'] . "</td></tr>\n";
  echo "<tr><td>" . $string['ipaddress'] . "</td><td>" . apache_getenv("SERVER_ADDR") . "</td></tr>\n";
  echo "<tr><td>" . $string['clock'] . "</td><td>" . date('d F Y H:i:s') . "</td></tr>\n";;
  echo "<tr><td>" . $string['os'] . "</td><td>" . php_uname('s') . "</td></tr>\n";;
  echo "<tr><td>" . $string['apache'] . "</td><td>" . apache_get_version() . "</td></tr>\n";
  echo "<tr><td>" . $string['php'] . "</td><td>" . phpversion() . "</td></tr>\n";
  echo "<tr><td>" . $string['mysql'] . "</td><td>" . $mysqli->server_info . "</td></tr>\n";
  
  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '<tr><td colspan="2" class="sechead">' . $string['clientcomputer'] . '</td></tr>';
  echo '<tr><td>' . $string['ipaddress'] . '</td><td>' . NetworkUtils::get_ipaddress() . '</td></tr>';
  echo '<tr><td>' . $string['clock'] . '</td><td><script language="JavaScript">the_date = new Date(); document.write(the_date.toLocaleString()); </script></td></tr>';
  echo '<tr><td>' . $string['browser'] . '</td><td>' . $_SERVER['HTTP_USER_AGENT'] . '</td></tr>';

  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '<tr><td colspan="2" class="sechead">' . $string['partitions'] . '</td></tr>';

  echo '<tr><td colspan="2" rowspan="18" valign="top" align="left"><table cellspacing="0" cellpadding="2" border="0" style="font-size:90%">';
    
  if (php_uname('s') == 'Windows NT') {
    $disks = `fsutil fsinfo drives`;
    $disks = str_word_count($disks,1);
    $i = 0;
    foreach ($disks as $key=>$disk) {
      if ($disk != 'Drives') {
        $driveID = strtoupper($disk) . ':';
        $master_array[$i][3] = round(((@disk_free_space($driveID) / 1024) / 1024) / 1024) . 'G';
        $master_array[$i][1] = round(((@disk_total_space($driveID) / 1024) / 1024) / 1024) . 'G';
        $master_array[$i][5] = $disk . ':';
      }
      $i++;
    }
    $row_no = $i + 1;
  } else {
    $master_array = array();
    $results = shell_exec('df -h');
    $lines = explode('<br />',nl2br($results));
    $row_no = 0;
    foreach ($lines as $individual_line) {
      if ($row_no > 0) {
        $cols = explode(' ',$individual_line);
        foreach($cols as $individual_col) {
          if ($individual_col != '') {
            $master_array[$row_no][] = $individual_col;
          }
        }
      }
      $row_no++;
    }
  }
  for ($i=1; $i<($row_no-1);$i++) {
    if ($master_array[$i][5] != '' and $master_array[$i][1] != '0K') {
      echo '<tr><td><img src="../artwork/drive_icon.png" width="48" height="48" alt="' . $string['driveicon'] . '" border="0" /></td><td>' . $master_array[$i][5] . '<br /><span style="border: 1px solid #808080; display:block; height:11px; width:150px">';
      if (intval($master_array[$i][3]) < intval($master_array[$i][1])) {
        if ((intval($master_array[$i][1]) - intval($master_array[$i][3])) > (intval($master_array[$i][1]) * 0.9)) {
          echo '<img src="red_bar.png" width="' . round((1 - (intval($master_array[$i][3]) / intval($master_array[$i][1]))) * 148) . '" height="11" alt="" border="0" />';
        } else {
          echo '<img src="blue_bar.png" width="' . round((1 - (intval($master_array[$i][3]) / intval($master_array[$i][1]))) * 148) . '" height="11" alt="" border="0" />';
        }
      } else {
        echo '<img src="blank_bar.png" width="20" height="11" border="0" />';
      }
      echo '</span><span style="color:#808080">' . sprintf($string['freespace'], $master_array[$i][3], $master_array[$i][1]) . '</span></td></tr>';
    }
  }
  echo '</table></td></tr>';

  echo "</table>\n<br />\n";
  $mysqli->close();
?>
</td></tr>
</table>
</div>
</div>
</body>
</html>