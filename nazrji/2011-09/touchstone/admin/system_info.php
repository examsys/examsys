<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  require '../include/sidebar_menu.inc';
  require_once '../classes/networkutils.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>

<title>System State</title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<script src="../javascript/staff_help.js" type="text/javascript"></script>

<style>
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

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">Administrative Tools</a></div><div style="font-size:200%; margin-left:10px; font-weight:bold">System Information</div></td>
<td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(240); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
</tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>
<br />
<div align="center">
<table cellspacing="0" cellpadding="0" border="0" style="font-size:100%; text-align:left">
<tr><td style="vertical-align:top">
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; text-align:left">
<tr><td style="width:120px" class="sechead">Table</td><td class="sechead">Records</td><td class="sechead">Updated<td class="sechead">Engine&nbsp;</td>
</tr>
<?php
  $results = $mysqli->query("SHOW TABLE STATUS");
  while ($row = $results->fetch_assoc()) {
    if (($row['Name'] == 'log_late' or $row['Name'] == 'temp_users') and $row['Rows'] > 0) {
      echo "<tr><td style=\"color:#C00000\">" . $row['Name'] . "&nbsp;<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"!\" /></td><td style=\"text-align:right; color:#C00000\">" . number_format($row['Rows']) . "</td>";
    } else {
      echo "<tr><td>" . $row['Name'] . "</td><td style=\"text-align:right\">" . number_format($row['Rows']) . "</td>";
    }
    if ($row['Engine'] == 'InnoDB') {
      echo "<td>&nbsp;<span style=\"color:#808080\">N/A</span></td>";
    } else {
      echo "<td>&nbsp;" . substr($row['Update_time'], 8, 2) . "/" . substr($row['Update_time'], 5, 2) . "/" . substr($row['Update_time'], 0, 4) .  "</td>";
    }
    echo "<td>" . $row['Engine'] . "</td></tr>\n";
  }
  $results->close();

  echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
  echo "<tr><td colspan=\"2\" class=\"sechead\">MySQL Status</td><td colspan=\"2\"></td></tr>\n";
  $status = explode('  ', $mysqli->stat());
  for ($i=0; $i<=7; $i++) {
    $parts = explode(': ', $status[$i]);
    if ($i < 7) {
      echo "<tr><td>" . $parts[0] . "</td><td style=\"text-align:right\">" . number_format($parts[1]) . "</td><td colspan=\"2\"></td></tr>\n";
    } else {
      echo "<tr><td>" . $parts[0] . "</td><td style=\"text-align:right\">" . $parts[1] . "</td><td colspan=\"2\"></td></tr>\n";
    }
  }
  echo "</table>\n<br />\n";
?>
</td>
<td style="width:50px">&nbsp;</td>
<td style="vertical-align:top">
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; width:400px">
<tr><td colspan="2" class="sechead">TouchStone</td></tr>
<tr><td>Version</td><td><?php echo $ts_version; ?></td></tr>
<tr><td>Web Root</td><td><?php echo $cfg_web_root; ?></td></tr>
<tr><td>Database</td><td><?php echo $cfg_db_database; ?></td></tr>
<?php
if ($cfg_use_ldap == true) {
  echo "<tr><td>Authentication</td><td>LDAP</td></tr>\n";
} else {
  echo "<tr><td>Authentication</td><td>Internal</td></tr>\n";
}
?>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><td colspan="2" class="sechead">Server Information</td></tr>
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
      echo "<tr><td>Processor</td><td>$processor</td></tr>\n";
      echo "<tr><td>Cores</td><td>$core_no</td></tr>\n";
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
      echo "<tr><td>Processor</td><td>" . $processor_parts[0] . "($speed)</td></tr>\n";
      echo "<tr><td>CPUs</td><td>$physical ($virtual virtual)</td></tr>\n";
    }
  } else {
    echo "<tr><td>Processor</td><td>" . php_uname('m') . "</td></tr>\n";
  }
      
  echo "<tr><td style=\"width:90px\">Server name</td><td>" . gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME'])) . "</td></tr>\n";
  echo "<tr><td>Host name</td><td>" . $_SERVER['HTTP_HOST'] . "</td></tr>\n";
  echo "<tr><td>IP Address</td><td>" . apache_getenv("SERVER_ADDR") . "</td></tr>\n";
  echo "<tr><td>Clock</td><td>" . date('d F Y H:i:s') . "</td></tr>\n";;
  echo "<tr><td>OS</td><td>" . php_uname('s') . "</td></tr>\n";;
  echo "<tr><td>Apache</td><td>" . apache_get_version() . "</td></tr>\n";
  echo "<tr><td>PHP</td><td>" . phpversion() . "</td></tr>\n";
  echo "<tr><td>MySQL</td><td>" . $mysqli->server_info . "</td></tr>\n";
  
  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '<tr><td colspan="2" class="sechead">Client Computer</td></tr>';
  echo '<tr><td>IP Address</td><td>' . NetworkUtils::get_ipaddress() . '</td></tr>';
  echo '<tr><td>Clock</td><td><script language="JavaScript">the_date = new Date(); document.write(the_date.toLocaleString()); </script></td></tr>';
  echo '<tr><td>Browser</td><td>' . $_SERVER['HTTP_USER_AGENT'] . '</td></tr>';

  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '<tr><td colspan="2" class="sechead">Partitions</td></tr>';

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
      echo '<tr><td><img src="../artwork/drive_icon.png" width="48" height="48" alt="Drive icon" border="0" /></td><td>' . $master_array[$i][5] . '<br /><span style="border: 1px solid #808080; display:block; height:11px; width:150px">';
      if (intval($master_array[$i][3]) < intval($master_array[$i][1])) {
        if ((intval($master_array[$i][1]) - intval($master_array[$i][3])) > (intval($master_array[$i][1]) * 0.9)) {
          echo '<img src="red_bar.png" width="' . round((1 - (intval($master_array[$i][3]) / intval($master_array[$i][1]))) * 148) . '" height="11" alt="" border="0" />';
        } else {
          echo '<img src="blue_bar.png" width="' . round((1 - (intval($master_array[$i][3]) / intval($master_array[$i][1]))) * 148) . '" height="11" alt="" border="0" />';
        }
      } else {
        echo '<img src="blank_bar.png" width="20" height="11" border="0" />';
      }
      echo '</span><span style="color:#808080">' . $master_array[$i][3] . ' free of ' . $master_array[$i][1] . '</span></td></tr>';
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