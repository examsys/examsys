<?php
// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';

function getLabs($labs, $mysqlidb) {
  global $string;
  
  $html = '';
  
  if ($labs != '') {
    $results = $mysqlidb->prepare("SELECT room_no FROM labs WHERE id IN ($labs)");
    $results->execute();
    $results->bind_result($room_no);
    while ($results->fetch()) {
      if ($html == '') {
        $html = $room_no;
      } else {
        $html .= ', ' . $room_no;
      }
    }
    $results->close();
    
    $html = '<span style="color:#FF6300">' . $html . '</span>';
  } else {
    $html = '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" /> <span style="color:#C00000">' . $string['nolabsset'] . '</span>';
  }
  
  return $html;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title><?php echo $string['summativescheduling'] . ' ' . $cfg_install_type; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .s {padding-left:6px}
    .grey {color:#808080}
  </style>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script language="javascript">
  
    $('body').click(desel);

    function sel(divID, evt) {
      tmp_ID = document.myform.divID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }

      document.getElementById('menu1a').style.display = 'none';
      document.getElementById('menu1b').style.display = 'block';
      document.myform.divID.value = divID;
         
      document.getElementById(divID).style.backgroundColor = '#B3C8E8';
      evt.cancelBubble = true;
    }
    
    function desel() {
      tmp_ID = document.getElementById('divID').value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }
      document.getElementById('menu1b').style.display = 'none';
      document.getElementById('menu1a').style.display = 'block';
    }

    function lon(lineID) {
      if (lineID != document.getElementById('divID').value) {
        document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function loff(lineID) {
      if (lineID != document.getElementById('divID').value) {
        document.getElementById(lineID).style.backgroundColor = '';
      }
    }
  </script>
</head>

<body>
<?php
  require '../include/scheduling_options.inc';
?>
<div id="content" class="content">
<table class="header">
<tr>
<th colspan="4"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['summativescheduling']; ?></div></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(0); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr>
<th><div class="mid s"><?php echo $string['title']; ?>&nbsp;</div></th>
<th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['month']; ?>&nbsp;</th>
<th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['campus']; ?>&nbsp;</th>
<th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['modules']; ?>&nbsp;</th>
<th><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;<?php echo $string['cohortsize']; ?>&nbsp;</th>

</tr>
<tr><th colspan="5" class="bevel"></th></tr>
  <tr><td colspan="5"><table border="0" class="subsect" style="width:98%"><tr><td><nobr><?php echo $string['unscheduled']; ?></nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>
<?php
  $rowID = 0;
  $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
  
  $papers = array();
  
  $results = $mysqli->prepare("SELECT properties.property_id, paper_title, moduleid, period, barriers_needed, cohort_size, campus FROM (properties, properties_modules, modules, scheduling) WHERE start_date IS NULL AND properties.property_id=scheduling.paperID AND properties.property_id=properties_modules.property_id AND properties_modules.idMod=modules.id AND deleted IS NULL ORDER BY period");
  $results->execute();
  $results->store_result();
  $results->bind_result($property_id, $paper_title, $moduleID, $period, $barriers_needed, $cohort_size, $campus);
  while ($results->fetch()) {
    if (!isset($papers[$property_id])) {
      $papers[$property_id] = array('paper_title'=>$paper_title, 'period'=>$period, 'barriers_needed'=>$barriers_needed, 'cohort_size'=>$cohort_size, 'campus'=>$campus);
    }
    $papers[$property_id]['modules'][] = $moduleID;
  }
  $results->close();
  
  foreach ($papers as $property_id=>$paper_details) {
    $cohort_size = str_replace('<', '&lt;', $paper_details['cohort_size']);
    $cohort_size = str_replace('>', '&gt;', $cohort_size);
    
    if ($paper_details['period'] != '') {
      $display_month = $string[$months[$paper_details['period']]];
    } else {
      $display_month = '&lt;unknown&gt;';
    }
    
    echo "<tr onclick=\"sel($property_id)\" onmouseover=\"lon($property_id)\" onmouseout=\"loff($property_id)\" ondblclick=\"viewDetails()\" id=\"$property_id\">";
    echo "<td class=\"s\" style=\"padding-left:24px\">" . $paper_details['paper_title'] . "</td><td class=\"s\">$display_month</td><td class=\"s\">". $paper_details['campus'] . "</td><td class=\"s\">";
    $html = '';
    foreach ($paper_details['modules'] as $individual_module) {
      if ($html == '') {
        $html = $individual_module;
      } else {
        $html .= ', ' . $individual_module;
      }
    }
    echo "$html</td><td class=\"s\">$cohort_size</td></tr>\n";
  }
?>
  <tr><td colspan="5">&nbsp;</td></tr>
  <tr><td colspan="5"><table border="0" class="subsect" style="width:98%"><tr><td><nobr><?php echo $string['scheduled']; ?></nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>
<?php
  $papers = array();
  
  $results = $mysqli->prepare("SELECT properties.property_id, paper_title, moduleid, period, barriers_needed, cohort_size, campus, DATE_FORMAT(start_date,'$cfg_long_date_time'), end_date, labs FROM (properties, properties_modules, modules, scheduling) WHERE start_date > NOW() AND properties.property_id=scheduling.paperID AND properties.property_id=properties_modules.property_id AND properties_modules.idMod=modules.id AND deleted IS NULL ORDER BY period");
  $results->execute();
  $results->store_result();
  $results->bind_result($property_id, $paper_title, $moduleID, $period, $barriers_needed, $cohort_size, $campus, $start_date, $end_date, $labs);
  while ($results->fetch()) {
    if (!isset($papers[$property_id])) {
      $papers[$property_id] = array('paper_title'=>$paper_title, 'period'=>$period, 'barriers_needed'=>$barriers_needed, 'cohort_size'=>$cohort_size, 'campus'=>$campus, 'start_date'=>$start_date, 'end_date'=>$end_date, 'labs'=>$labs);
    }
    $papers[$property_id]['modules'][] = $moduleID;
  }
  $results->close();

  foreach ($papers as $property_id=>$paper_details) {
    $cohort_size = str_replace('<', '&lt;', $paper_details['cohort_size']);
    $cohort_size = str_replace('>', '&gt;', $cohort_size);

    echo "<tr onclick=\"sel($property_id)\" onmouseover=\"lon($property_id)\" onmouseout=\"loff($property_id)\" ondblclick=\"viewDetails()\" id=\"$property_id\">";
    echo "<td class=\"s\"><img src=\"../artwork/shortcut_calendar_icon.png\" width=\"16\" height=\"14\" border=\"0\" />&nbsp;" . $paper_details['paper_title'] . "</td><td class=\"s\">" . $paper_details['start_date'] . "</td><td class=\"s\">$campus " . getLabs($paper_details['labs'], $mysqli) . "</td><td class=\"s\">";
    $html = '';
    foreach ($paper_details['modules'] as $individual_module) {
      if ($html == '') {
        $html = $individual_module;
      } else {
        $html .= ', ' . $individual_module;
      }
    }
    echo "$html</td><td class=\"s\">$cohort_size</td></tr>\n";
  }
?>
</table>
</div>

</body>
</html>