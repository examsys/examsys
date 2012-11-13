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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['newsannouncements'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .l {cursor:pointer}
    .t {color:black; text-decoration:none}
    .col {padding-left:5px}
    .col1 {padding-left:20px}
    .deleted {color:#808080}
  </style>

  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script language="javascript">
    function selAnnounce(divID, announcementID, evt) {
      tmp_ID = document.myform.divID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }

      document.getElementById('menu1a').style.display = 'none';
      document.getElementById('menu1b').style.display = 'block';
      document.myform.divID.value = divID;
      
      document.myform.announcementID.value = announcementID;
      
      document.getElementById(divID).style.backgroundColor = '#B3C8E8';
      evt.cancelBubble = true;
    }
    
    function deselAnnounce() {
      tmp_ID = document.myform.divID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }
      document.myform.divID.value = '';
      document.getElementById('menu1b').style.display = 'none';
      document.getElementById('menu1a').style.display = 'block';
    }

    function lon(lineID) {
      if (lineID != document.myform.divID.value) {
        document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function loff(lineID) {
      if (lineID != document.myform.divID.value) {
        document.getElementById(lineID).style.backgroundColor = '';
      }
    }
    
    function edit(announcementID) {
      document.location.href='./edit_announcement.php?announcementid=' + announcementID;
    }
  </script>
</head>

<body onclick="deselMod()">
<?php
  require '../include/announcement_options.inc';
?>
<div id="content" class="content">

<table class="header">
<tr>
<th colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['newsannouncements']; ?></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(233); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr>
<?php
  if (isset($_GET['sortby'])) {
    $sortby = $_GET['sortby'];
    $ordering = $_GET['ordering'];
  } else {
    $sortby = 'startdate';
    $ordering = 'asc';
  }

  // output table header
  $table_order = array($string['title']=>'title', $string['startdate']=>'startdate', $string['enddate']=>'enddate');
  foreach($table_order as $display => $key) {
    echo '<th>';
    if ($key == 'moduleid') {
      echo '<div style="padding-left:10px">';
    } else {
      echo '<div><img src="../artwork/header_vertical_line.gif" width="2" height="15" alt="line" border="0" />&nbsp;';
    }
    if ($sortby == $key and $ordering == 'asc') {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=desc\">$display</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" />&nbsp;</div></th>";
    } elseif ($sortby == $key and $ordering == 'desc') {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" />&nbsp;</div></th>";
    } else {
      echo "<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>&nbsp;</div></th>";
    }
  }
?>
</tr>
<tr><th colspan="4" class="bevel"></th></tr>
<?php
$announcements_no = 0;
$announcements = array();

$result = $mysqli->prepare("SELECT id, title, icon, startdate, enddate, deleted FROM announcements ORDER BY startdate DESC");
$result->execute();
$result->bind_result($announcementid, $title, $icon, $startdate, $enddate, $deleted);
while ($result->fetch()) {
  $announcements[$announcements_no]['announcementid'] = $announcementid;
  $announcements[$announcements_no]['title'] = $title;
  $announcements[$announcements_no]['icon'] = $icon;
  $announcements[$announcements_no]['startdate'] = $startdate;
  $announcements[$announcements_no]['enddate'] = $enddate;
  $announcements[$announcements_no]['deleted'] = $deleted;
  
  $announcements_no++;
}
$result->close();

for ($i=0; $i<$announcements_no; $i++) {
  /*
  if ($sortby == 'school' and $old_school != $modules[$i]['school']) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $modules[$i]['school'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  } elseif ($sortby == 'moduleid' and $old_moduleid_letter != substr($modules[$i]['moduleid'], 0, 1)) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . substr($modules[$i]['moduleid'], 0, 1) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  } elseif ($sortby == 'name' and $old_name_letter != substr($modules[$i]['name'], 0, 1)) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . substr($modules[$i]['name'], 0, 1) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  } elseif ($sortby == 'school' and $old_school != $modules[$i]['school']) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . substr($modules[$i]['school'], 0, 1) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  } elseif ($sortby == 'active' and $old_active != $modules[$i]['active']) {
    echo "<tr><td colspan=\"5\"><table border=\"0\" style=\"padding-left:10px; padding-right:2px; padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $tmp_active . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
  }
  */
  if ($announcements[$i]['deleted'] != '') {
    $deleted = ' deleted';
  } else {
    $deleted = '';
  }
  echo "<tr id=\"$i\" onclick=\"selAnnounce($i,'" . $announcements[$i]['announcementid'] . "',event)\" ondblclick=\"edit('" . $announcements[$i]['announcementid'] . "')\" onmouseover=\"lon($i)\" onmouseout=\"loff($i)\" class=\"l\"><td><div class=\"col$deleted\">" . $announcements[$i]['title'] . "</div></td><td><div class=\"col$deleted\">" . $announcements[$i]['startdate']  . "</div></td><td><div class=\"col$deleted\">" . $announcements[$i]['enddate']  . "</div></td></tr>\n";
}

$mysqli->close();
?>
</table>
</div>

</body>
</html>