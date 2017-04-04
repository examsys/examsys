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

require_once '../include/sysadmin_auth.inc';
require_once '../include/sidebar_menu.inc';

/**
 * Formats space in human-readable format.
 * @param int $space - Raw bytes to be converted.
 * @return string space in human readable format.
 */
function format_space($space) {
  $units = array('KB', 'MB', 'GB', 'TB');
  $i = -1;
  do {
    $i++;
    $space = $space / 1024;
    $correct_units = $units[$i];
  } while ($space > 1024);

  return round($space, 1) . ' ' . $correct_units;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;: <?php echo $string['systeminformation']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    .sechead {background-color:#295AAD; color:white; text-align:left; font-weight:normal}
    a {color:#215DC6}
    a.heading {color:#215DC6; font-weight:bold}
    a.heading:hover {color:#428EFF; font-weight:bold}
		.on {width:30px; float:left; color:#008000; font-weight:bold}
		.off {width:30px; float:left; color:#C00000; font-weight:bold}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>

<div id="content">

<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['systeminformation'] ?></div>
</div>
  
<br />
<div align="center">
<table cellspacing="0" cellpadding="0" border="0" style="font-size:100%; text-align:left">
<tr><td style="vertical-align:top">
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; text-align:left; width:360px">
<tr><td style="width:180px" class="sechead"><?php echo $string['table']; ?></td><td class="sechead"><?php echo $string['records']; ?></td>
</tr>
<?php
  // Get info about some database tables
  $sub_result = $mysqli->prepare("SELECT COUNT(id) FROM log_late");   // Query to get an accurate figure for log_late.
  $sub_result->execute();
  $sub_result->store_result();
  $sub_result->bind_result($Rows);
  $sub_result->fetch();
  $sub_result->close();
  if ($Rows > 0) {
    echo "<tr><td style=\"color:#C00000\">log_late&nbsp;<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"" . $string['warning'] . "\" />&nbsp;<a href=\"log_late_details.php\">" . $string['More details'] . "</a></td>";
    echo "<td style=\"text-align:right; color:#C00000\">" . number_format($Rows) . "</td>";
  } else {
    echo "<tr><td>log_late</td><td style=\"text-align:right\">" . number_format($Rows) . "</td>";
  }
  $sub_result = $mysqli->prepare("SELECT COUNT(id) FROM temp_users");   // Query to get an accurate figure for temp_users.
  $sub_result->execute();
  $sub_result->store_result();
  $sub_result->bind_result($Rows);
  $sub_result->fetch();
  $sub_result->close();
  if ($Rows > 0) {
    echo "<tr><td style=\"color:#C00000\">temp_users&nbsp;<img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"" . $string['warning'] . "\" />&nbsp;<a href=\"clear_guest_users.php\">" . $string['More details'] . "</a></td>";
    echo "<td style=\"text-align:right; color:#C00000\">" . number_format($Rows) . "</td>";
  } else {
    echo "<tr><td>temp_users</td><td style=\"text-align:right\">" . number_format($Rows) . "</td>";
  }

  echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
  echo "<tr><td colspan=\"3\" class=\"sechead\">" . $string['mysqlstatus'] . "</td><td colspan=\"2\"></td></tr>\n";
  $status = explode('  ', $mysqli->stat());
  for ($i=0; $i<=7; $i++) {
    $parts = explode(': ', $status[$i]);
    if ($i == 0) {
      $hours = ($parts[1] / 60 / 60);
      if ($hours < 1) {
        $hours = ($parts[1] / 60);
        $units = 'minutes';
      } elseif ($hours < 24) {
        $units = 'hours';
      } else {
        $hours = ($hours / 24);
        $units = 'days';
      }
      echo "<tr><td>" . $string[strtolower($parts[0])] . "</td><td style=\"text-align:right\">" . number_format($hours) . "</td><td colspan=\"2\">" . $string[$units] . "</td></tr>\n";
    } else if ($i < 7) {
      echo "<tr><td>" . $string[strtolower($parts[0])] . "</td><td style=\"text-align:right\">" . number_format($parts[1]) . "</td><td colspan=\"2\"></td></tr>\n";
    } else {
      echo "<tr><td>" . $string[strtolower($parts[0])] . "</td><td style=\"text-align:right\">" . $parts[1] . "</td><td colspan=\"2\"></td></tr>\n";
    }
  }
  echo "</table>\n<br />\n";

// Get info on authentication stack
$authinfo = $authentication->version_info(true, false);

// Get info about enhanced calculation plugin
$enhancedc1 = $configObject->get('enhancedcalc_type');
$enhancedc2 = $configObject->get('enhancedcalculation');
if (is_null($enhancedc1)) {
  $enhancedc1 = 'BLANK therefore Rrserve';
}
$enhancedc3 = '';
foreach ($enhancedc2 as $key => $value) {
  $enhancedc3 .= "$key => $value<br />";
}

$enhancedPlugininfo = "Type: $enhancedc1<br />$enhancedc3";
// TODO really need a lookup plugin info section
// Get info on error handling
$ErrorLogSettings = '';
$e1 = $configObject->get('display_auth_debug');
$e2 = $configObject->get('debug_lang_string');
$e3 = $configObject->get('displayerrors');
$e4 = $configObject->get('displayallerrors');
$e5 = $configObject->get('errorshutdownhandling');
$e6 = $configObject->get('errorcontexthandling');
$e7 = $configObject->get('logerrors');

function onoff($status, $html) {
  if ($status) {
	  $html .= '<br /><div class="on">On</div>';
	} else {
	  $html .= '<br /><div class="off">Off</div>';
	}
	return $html;
}

$ErrorLogSettings = onoff(($e1 === true), $ErrorLogSettings);
$ErrorLogSettings .= $string['authdebug'];

$ErrorLogSettings = onoff(($e3 === true), $ErrorLogSettings);
$ErrorLogSettings .= $string['errorsonscreen'];

$ErrorLogSettings = onoff(($e7 === true), $ErrorLogSettings);
$ErrorLogSettings .= $string['errorslogged'];

$ErrorLogSettings = onoff(($e4 === true), $ErrorLogSettings);
$ErrorLogSettings .= $string['phpnotices'];

$ErrorLogSettings = onoff(($e5 === true), $ErrorLogSettings);
$ErrorLogSettings .= $string['errorshutdown'];

$ErrorLogSettings .= '<br />' . $string['varcapturemethod'] . ' ';
if ($e6 == 'improved') {
  $ErrorLogSettings .= $string['improved'];
} elseif ($e6 == 'basic') {
  $ErrorLogSettings .= $string['basic'];
} else {
  $ErrorLogSettings .= $string['none'];
}

$yearutils = new yearutils($mysqli);

?>
</td>
<td style="width:50px">&nbsp;</td>
<td style="vertical-align:top">
<table cellpadding="2" cellspacing="0" border="0" style="font-size:100%; width:550px">
<tr><td colspan="2" class="sechead"><?php echo $string['application']; ?></td></tr>
<tr><td style="width:130px"><?php echo $string['version']; ?></td><td><?php echo $configObject->get('rogo_version'); ?></td></tr>
<tr><td><?php echo $string['webroot']; ?></td><td><?php echo $configObject->get('cfg_web_root'); ?></td></tr>
<tr><td><?php echo $string['database']; ?></td><td><?php echo $configObject->get('cfg_db_database'); ?></td></tr>
<tr><td><?php echo $string['company']; ?></td><td><?php echo $configObject->get('cfg_company'); ?></td></tr>
<tr><td><?php echo $string['lookups']; ?></td><td><?php echo $configObject->get('cfg_client_lookup'); ?></td></tr>
<tr><td><?php echo $string['Session']; ?></td><td><?php echo $yearutils->get_academic_session($yearutils->get_current_session()); ?></td></tr>
<tr><td><?php echo $string['ErrorLogSettings']; ?></td><td><?php echo $ErrorLogSettings ?></td></tr>

<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2" class="sechead"><?php echo $string['rogoplugins']; ?></td></tr>

<tr><td><?php echo $string['authentication']; ?></td><td><?php echo $authinfo; ?> <a href="./detailed_authentication_info.php"><?php echo $string['More details']; ?></a></td></tr>
<tr><td><?php echo $string['EnhancedCalcPlugin']; ?></td><td><?php echo $enhancedPlugininfo ?></td></tr>

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
        $components = explode(':', $individual_line);
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
      $lines = explode('<br />', nl2br($results));
      $physical = 0;
      $virtual = 0;
      $processor = '';
      foreach ($lines as $individual_line) {
        if (strpos($individual_line, 'The physical processor') !== false) {
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
    $results = shell_exec('wmic cpu get name');
    $lines = explode('<br />', nl2br($results));
    echo "<tr><td>" . $string['processor'] . "</td><td>" . $lines[1] . "</td></tr>\n";
  }

  echo "<tr><td style=\"width:90px\">" . $string['servername'] . "</td><td>" . gethostbyaddr(gethostbyname($_SERVER['SERVER_NAME'])) . "</td></tr>\n";
  echo "<tr><td>" . $string['hostname'] . "</td><td>" . $_SERVER['HTTP_HOST'] . "</td></tr>\n";
  echo "<tr><td>" . $string['ipaddress'] . "</td><td>" . NetworkUtils::get_server_address() . "</td></tr>\n";
  echo "<tr><td>" . $string['clock'] . "</td><td>" . date('d F Y H:i:s') . "</td></tr>\n";;
  echo "<tr><td>" . $string['os'] . "</td><td>" . php_uname('s') . "</td></tr>\n";;
  echo "<tr><td>" . $string['webserver'] . "</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>\n";
  echo "<tr><td>" . $string['php'] . "</td><td>" . phpversion() . "</td></tr>\n";
  echo "<tr><td>" . $string['mysql'] . "</td><td>" . $mysqli->server_info . "</td></tr>\n";

  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '<tr><td colspan="2" class="sechead">' . $string['clientcomputer'] . '</td></tr>';
  echo '<tr><td>' . $string['ipaddress'] . '</td><td>' . NetworkUtils::get_client_address() . '</td></tr>';
  echo '<tr><td>' . $string['clock'] . '</td><td><script>the_date = new Date(); document.write(the_date.toLocaleString("' . $language . '")); </script></td></tr>';
  echo '<tr><td>' . $string['browser'] . '</td><td>' . $_SERVER['HTTP_USER_AGENT'] . '</td></tr>';

  echo '<tr><td colspan="2">&nbsp;</td></tr>';
  echo '<tr><td colspan="2" class="sechead">' . $string['partitions'] . '</td></tr>';

  echo '<tr><td colspan="2" rowspan="18" valign="top" align="left"><table cellspacing="0" cellpadding="2" border="0" style="font-size:90%">';

  if (php_uname('s') == 'Windows NT') {
    $disks = array('A:\\', 'B:\\', 'C:\\', 'D:\\', 'E:\\', 'F:\\', 'G:\\', 'H:\\', 'I:\\',
      'J:\\', 'K:\\', 'L:\\', 'M:\\', 'N:\\', 'O:\\', 'P:\\', 'Q:\\', 'R:\\', 'S:\\', 'T:\\',
      'U:\\', 'V:\\', 'W:\\', 'X:\\', 'Y:\\', 'Z:\\');
    $i = 1;
    foreach ($disks as $disk) {
      if (file_exists($disk)) {
        $master_array[$i][3] = @disk_free_space($disk);
        $master_array[$i][1] = @disk_total_space($disk);
        $master_array[$i][5] = $disk;
        $i++;
      }
    }
    $row_no = $i + 1;
  } else {
    $master_array = array();
    // List free disk space, ensuring one file system per line.
    // df -P flag not used as not supported by Solaris.
    $results = shell_exec("df -k | awk 'NF == 1 {printf($1); next}; {print}'");
    $lines = explode('<br />', nl2br($results));
    $row_no = 0;
    foreach ($lines as $individual_line) {
      if ($row_no > 0) {
        $cols = explode(' ', $individual_line);
        foreach ($cols as $individual_col) {
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
      echo '<tr><td><img src="../artwork/drive_icon.png" width="48" height="48" alt="' . $string['driveicon'] . '" /></td><td>' . $master_array[$i][5] . '<br />';
      echo '<span style="border: 1px solid #808080; display:block; height:11px; background-color:#EAEAEA; width:150px">';

      if ($master_array[$i][1] > 0) {
          
        $bar_width = round((1 - (intval($master_array[$i][3]) / intval($master_array[$i][1]))) * 148);
        
        
        $free_percent = ($master_array[$i][3] / $master_array[$i][1]) * 100;
        $used_percent = 100 - $free_percent;
        $bar_width = 1.48 * $used_percent;

        if ($used_percent > 90) {
          echo '<span style="display:block; height:11px; width:' . $bar_width . 'px; background-color:#DA2626"></span>';  // Red bar
        } else {
          echo '<span style="display:block; height:11px; width:' . $bar_width . 'px; background-color:#26A0DA"></span>';  // Blue bar
        }
        
      }
      // linux resutls are in kbyte blocks
      if (php_uname('s') != 'Windows NT') {
        $master_array[$i][3] = $master_array[$i][3] * 1024;
        $master_array[$i][1] = $master_array[$i][1] * 1024;
      }
      echo '</span><span style="color:#808080">' . sprintf($string['freespace'], format_space($master_array[$i][3]), format_space($master_array[$i][1])) . '</span></td></tr>';
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
