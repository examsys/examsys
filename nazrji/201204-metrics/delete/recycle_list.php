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

function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
  foreach ($marray as $row) {
    $sortarr[] = $row[$column];
  }
  if ($sort_order == 'asc') {
    array_multisort($sortarr, SORT_ASC, $marray);
  } else {
    array_multisort($sortarr, SORT_DESC, $marray);
  }
  return $marray;
}

function dateDisplay($tmp_date) {
  return substr($tmp_date,6,2) . '/' . substr($tmp_date,4,2) . '/' . substr($tmp_date,0,4) . ' ' . substr($tmp_date,8,2) . ':' . substr($tmp_date,10,2);
}

if (isset($_GET['module'])) {
  $module = $_GET['module'];
} else {
  $module = '';
}

if (isset($_GET['folder'])) {
  $folder = $_GET['folder'];
} else {
  $folder = '';  
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō: <?php echo $string['recyclebin'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    .icon {width:20px; text-align:right; padding-right:8px}
    .f {float:left; width:375px; height:74px; padding-left:12px}
  </style>

  <script type="text/javascript">
    function selQ(lineNo,itemID,itemType) {
      tmp_ID = document.PapersMenu.oldLineNo.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
        document.getElementById(tmp_ID).style.color = 'black';
      }
      document.getElementById('menu1a').style.display = 'none';
      document.getElementById('menu1b').style.display = 'block';

      document.getElementById(lineNo).style.backgroundColor = '#B3C8E8';

      document.PapersMenu.oldLineNo.value = lineNo;
      document.PapersMenu.lineNo.value = lineNo;
      document.PapersMenu.itemType.value = itemType;
      document.PapersMenu.itemID.value = itemID;
    }

    function qOff() {
      document.getElementById('menu1a').style.display = 'block';
      document.getElementById('menu1b').style.display = 'none';
      tmp_ID = document.PapersMenu.oldLineNo.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }
    }

    function lon(lineID) {
      if (lineID != document.PapersMenu.oldLineNo.value) {
        document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function loff(lineID) {
      if (lineID != document.PapersMenu.oldLineNo.value) {
        document.getElementById(lineID).style.backgroundColor = '';
      }
    }
  </script>
</head>

<body onclick="qOff();">
<?php
  require '../include/recycle_options_menu.inc';
?>
<div id="content" class="content" style="font-size:80%">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<table class="header">
<?php
echo '<tr><th colspan="4"><div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a></div><div style="font-size:200%; margin-left:10px; font-weight:bold">' . $string['recyclebin'] . '</div>';

$recycle_bin = array();

// Query the TouchStone papers tables.
$i = 0;
$stmt = $mysqli->prepare("SELECT property_id AS id, paper_type, paper_title, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM properties WHERE (paper_ownerID=? OR moduleID IN ('" . implode("','",$teams) . "')) AND deleted IS NOT NULL");
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->bind_result($id, $paper_type, $paper_title, $deleted);
while ($stmt->fetch()) {
  $recycle_bin[$i]['id'] = $id;
  $recycle_bin[$i]['type'] = 'paper';
  $recycle_bin[$i]['name'] = $paper_title;
  $recycle_bin[$i]['deleted'] = $deleted;
  $recycle_bin[$i]['subtype'] = $paper_type;
  $i++;
}
$stmt->close();

// Query the TouchStone questions tables.
$stmt = $mysqli->prepare("SELECT q_id AS id, q_type, leadin_plain, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM questions WHERE (ownerID=? OR q_group IN ('" . implode("','",$teams) . "')) AND deleted IS NOT NULL");
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->bind_result($id, $q_type, $leadin_plain, $deleted);
while ($stmt->fetch()) {
  $recycle_bin[$i]['id'] = $id;
  $recycle_bin[$i]['type'] = 'question';
  $recycle_bin[$i]['name'] = $leadin_plain;
  $recycle_bin[$i]['deleted'] = $deleted;
  $recycle_bin[$i]['subtype'] = $q_type;
  $i++;
}
$stmt->close();

// Query the TouchStone folder tables.
$stmt = $mysqli->prepare("SELECT id, name, DATE_FORMAT(deleted,'%Y%m%d%H%i') AS deleted FROM folders WHERE (ownerID=? OR team_name IN ('" . implode("','",$teams) . "')) AND deleted IS NOT NULL");
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->bind_result($id, $name, $deleted);
while ($stmt->fetch()) {
  $recycle_bin[$i]['id'] = $id;
  $recycle_bin[$i]['type'] = 'folder';
  $recycle_bin[$i]['name'] = str_replace(';','\\',$name);
  $recycle_bin[$i]['deleted'] = $deleted;
  $recycle_bin[$i]['subtype'] = '';
  $i++;
}
$stmt->close();

$mysqli->close();

$sortby = 'name';
if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];

$ordering = 'asc';
if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

if ($i > 0) $recycle_bin = array_csort($recycle_bin,$sortby,$ordering);

echo "</td></tr>\n";
if ($sortby == 'name') {
  if ($ordering == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=name&ordering=desc\">" . $string['name'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=deleted&ordering=asc\">" . $string['datedeleted'] . "</a>&nbsp;</th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=subtype&ordering=asc\">" . $string['type'] . "</a></th></tr>\n";
  } else {
    echo "<tr><th colspan=\"2\">&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=name&ordering=asc\">" . $string['name'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=deletede&ordering=asc\">" . $string['datedeleted'] . "</a>&nbsp;</th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=subtype&ordering=asc\">" . $string['type'] . "</a></th></tr>\n";
  }
} elseif ($sortby == 'deleted') {
  if ($ordering == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=name&ordering=asc\">" . $string['name'] . "</a>&nbsp;</th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=deleted&ordering=desc\">" . $string['datedeleted'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=subtype&ordering=asc\">" . $string['type'] . "</a></th></tr>\n";
  } else {
    echo "<tr><th colspan=\"2\">&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=name&ordering=asc\">" . $string['name'] . "</a>&nbsp;</th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=deleted&ordering=asc\">" . $string['datedeleted'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=subtype&ordering=asc\">" . $string['type'] . "</a></th></tr>\n";
  }
} elseif ($sortby == 'subtype') {
  if ($ordering == 'asc') {
    echo "<tr><th colspan=\"2\">&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=name&ordering=asc\">" . $string['name'] . "</a>&nbsp;</th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=deleted&ordering=asc\">" . $string['datedeleted'] . "</a></th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=subtype&ordering=desc\">" . $string['type'] . "</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th></tr>\n";
  } else {
    echo "<tr><th colspan=\"2\">&nbsp;&nbsp;&nbsp;&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=name&ordering=asc\">" . $string['name'] . "</a>&nbsp;</th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color: black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=deleted&ordering=asc\">" . $string['datedeleted'] . "</a></th><th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;<a style=\"color:black\" href=\"" . $_SERVER['PHP_SELF'] . "?sortby=subtype&ordering=asc\">" . $string['type'] . "</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" border=\"0\" /></th></tr>\n";
  }
}
echo "<tr><th colspan=\"4\" class=\"bevel\"></th></tr>\n";

$paper_types = array('Formative Self-Assessment','Progress Test','Summative Exam','Survey','OSCE Station','Offline Paper');
$paper_icons = array('formative_16.gif','progress_16.gif','summative_16.gif','survey_16.gif','osce_16.gif','offline_16.gif');
for ($item=0; $item<$i; $item++) {
  if ($recycle_bin[$item]['type'] == 'paper') {
    $temp_type = $recycle_bin[$item]['subtype'];
    $split_name = explode('[deleted',$recycle_bin[$item]['name']);
    echo "<tr id=\"line$item\" onmouseover=\"lon('line$item')\" onmouseout=\"loff('line$item')\" style=\"cursor:pointer\" onclick=\"selQ('line$item'," . $recycle_bin[$item]['id'] . ",'paper'); event.cancelBubble=true;\"><td class=\"icon\"><img src=\"../artwork/" . $paper_icons[$temp_type] . "\" width=\"16\" height=\"16\" border=\"0\" /></td><td>" . $split_name[0] . "</td><td><nobr>&nbsp;" . dateDisplay($recycle_bin[$item]['deleted']) . "</nobr></td><td><nobr>&nbsp;" . $string[strtolower($paper_types[$temp_type])] . "</nobr></td></tr>\n";
  } elseif ($recycle_bin[$item]['type'] == 'folder') {
    echo "<tr id=\"line$item\" onmouseover=\"lon('line$item')\" onmouseout=\"loff('line$item')\" style=\"cursor:pointer\" onclick=\"selQ('line$item'," . $recycle_bin[$item]['id'] . ",'folder'); event.cancelBubble=true;\"><td class=\"icon\"><img src=\"../artwork/yellow_folder.png\" width=\"16\" height=\"16\" border=\"0\" /></td><td>" . $recycle_bin[$item]['name'] . "</td><td><nobr>&nbsp;" . dateDisplay($recycle_bin[$item]['deleted']) . "</nobr></td><td><nobr>&nbsp;" . $string['folder'] . "</nobr></td></tr>\n";
  } else {
    echo "<tr id=\"line$item\" onmouseover=\"lon('line$item')\" onmouseout=\"loff('line$item')\" style=\"cursor:pointer\" onclick=\"selQ('line$item'," . $recycle_bin[$item]['id'] . ",'question'); event.cancelBubble=true;\"><td class=\"icon\"><img src=\"../artwork/question_item_icon.gif\" width=\"16\" height=\"16\" border=\"0\" /></td><td>" . $recycle_bin[$item]['name'] . "</td><td><nobr>&nbsp;" . dateDisplay($recycle_bin[$item]['deleted']) . "</nobr></td><td><nobr>&nbsp;" . $string[strtolower($recycle_bin[$item]['subtype'])] . "</nobr></td></tr>\n";
  }
}
echo "</table>\n";
?>
</form>
</div>
</body>
</html>