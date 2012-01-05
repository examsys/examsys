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

  require '../include/staff_auth.inc';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title><?php echo $string['computerlabs']; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style>
.foldername {float:left; width:380px; height:60px; padding-left:12px; font-size:90%}
</style>

<script src="../js/staff_help.js" type="text/javascript"></script>
<script language="javascript">
  function selLab(labID,labNo,evt) {
    tmp_ID = document.labform.oldLabNo.value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
      document.getElementById(tmp_ID).style.color = 'black';
    }
    document.getElementById(labNo).style.backgroundColor = '#EEEEEE';
    document.getElementById(labNo).style.color = 'white';

    document.getElementById('menu1a').style.display = 'none';
    document.getElementById('menu1b').style.display = 'block';

  
    document.getElementById('labID').value = labID;
    document.getElementById('labNo').value = labNo;
    document.getElementById('oldLabNo').value = labNo;
  
    evt.cancelBubble = true;
  }
  
  function deselLab() {
    tmp_ID = document.getElementById('oldLabNo').value;
    if (tmp_ID != '') {
      document.getElementById(tmp_ID).style.backgroundColor = 'white';
      document.getElementById(tmp_ID).style.color = 'black';
    }  
    document.getElementById('menu1a').style.display = 'block';
    document.getElementById('menu1b').style.display = 'none';

  }
</script>
</head>

<body onclick="deselLab();">
<?php
  require '../include/lab_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td style="background-color:#F1F5FB"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><?php echo $string['computerlabs']; ?></div></td>
<td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(231); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></td>
</tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
</table>
<br />
<?php
$labs = array();
$campus_sizes = array();

$lab_data = $mysqli->prepare("SELECT labs.id, name, count(address) AS pc_number, campus, building, low_bandwidth FROM (ip_addresses, labs) WHERE ip_addresses.lab=labs.id GROUP BY labs.id ORDER BY campus, name");
$lab_data->execute();
$lab_data->store_result();
$lab_data->bind_result($id, $name, $pc_number, $campus, $building, $low_bandwidth);
while ($lab_data->fetch()) {
  $labs[] = array('id'=>$id, 'name'=>$name, 'pc_number'=>$pc_number, 'campus'=>$campus, 'building'=>$building, 'low_bandwidth'=>$low_bandwidth);
}
$lab_data->close();

$old_campus = '';
$lab_no = 0;
if (count($labs) > 0) {
  foreach($labs as $lab) {
    if (isset($campus_sizes[$lab['campus']])) {
      $campus_sizes[$lab['campus']]++;
    } else {
      $campus_sizes[$lab['campus']] = 1;
    }
  }

  foreach($labs as $lab) {
    if ($old_campus != $lab['campus']) {
      echo "<table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $lab['campus'] . " (" . $campus_sizes[$lab['campus']] . ")</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#CCCCCC; background-color:#CCCCCC; width:100%\" /></td></tr></table>\n";
    }
    echo '<div class="foldername">';
    echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:66px; cursor:pointer" align="center">';
    echo "  <img onclick=\"selLab('" . $lab['id'] . "','lab$lab_no',event)\" ondblclick=\"viewDetails('" . $lab['id'] . "')\" src=\"../artwork/computer_lab_48.png\" width=\"48\" height=\"48\" alt=\"" . $lab['name'] . "\" border=\"0\" /><td>\n";
    echo "  <td style=\"width:290px; cursor:pointer\"><span id=\"lab$lab_no\" onclick=\"selLab('" . $lab['id'] . "','lab$lab_no',event)\" ondblclick=\"viewDetails('" . $lab['id'] . "')\">" . $lab['name'] . "</span><br />";
    echo '  <span style="color:#808080">' . $lab['pc_number'];
    if ($lab['pc_number'] == 1) {
      echo ' ' . $string['machine'];
    } else {
      echo ' '. $string['machines'];
    }  
    if ($lab['building'] != '') echo ', ' . $lab['building']; 
    echo '</span>';
    if ($lab['low_bandwidth'] == 1) {
      echo '<br /><span style="background-color:#C00000; color:white">&nbsp;' . $string['lowbandwidth'] . '&nbsp;</span>';
    }
    echo '</td></tr></table>';
    echo "</div>\n";
    $old_campus = $lab['campus'];
    $lab_no++;
  }
}

$mysqli->close();
?>
</div>
</body>
</html>