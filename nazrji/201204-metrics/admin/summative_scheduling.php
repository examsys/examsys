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
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['summativescheduling'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  
  <style>
  .s {padding-left:6px}
  </style>
  
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script language="javascript">
  $(function () {
    $('body').click(desel);
  });

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
<table class="header" style="font-size:80%">
<tr>
<th colspan="4"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['summativescheduling']; ?></th>
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
<?php
  $rowID = 0;
  $months = array('january','february','march','april','may','june','july','august','september','october','november','december');

  $results = $mysqli->prepare("SELECT property_id, paper_title, moduleID, period, barriers_needed, cohort_size, campus FROM (properties, scheduling) WHERE (start_date IS NULL OR end_date IS NULL) AND properties.property_id=scheduling.paperID");
  $results->execute();
  $results->store_result();
  $results->bind_result($property_id, $paper_title, $moduleID, $period, $barriers_needed, $cohort_size, $campus);
  while ($results->fetch()) {
    $rowID++;
    $cohort_size = str_replace('<', '&lt;', $cohort_size);
    $cohort_size = str_replace('>', '&gt;', $cohort_size);
    echo "<tr onclick=\"sel($property_id)\" onmouseover=\"lon($property_id)\" onmouseout=\"loff($property_id)\" ondblclick=\"viewDetails()\" id=\"$property_id\"><td class=\"s\">$paper_title</td><td class=\"s\">" . $string[$months[$period]] . "</td><td class=\"s\">$campus</td><td class=\"s\">$moduleID</td><td class=\"s\">$cohort_size</td></tr>\n";
  }
  $results->close();
?>
</table>
</div>

</body>
</html>