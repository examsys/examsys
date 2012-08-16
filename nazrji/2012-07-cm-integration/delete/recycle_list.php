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
require '../include/sort.inc';
require '../classes/recyclebin.class.php';

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
    .qline {line-height:150%;cursor:pointer;color:#000000;background-color:white; -webkit-user-select:none; -moz-user-select:none;}
    .qline:hover {background-color:#eee}
    .qline.highlight {background-color:#B3C8E8}
  </style>

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript">
    function addQID(qID, clearall) {
      if (clearall) {
        document.PapersMenu.itemID.value = ',' + qID;
      } else {
        document.PapersMenu.itemID.value = document.PapersMenu.itemID.value + ',' + qID;
      }
    }

    function subQID(qID) {
      var tmpq = ',' + qID;
      document.PapersMenu.itemID.value = document.PapersMenu.itemID.value.replace(tmpq, '');
    }

    function clearAll() {
      $('.highlight').removeClass('highlight');
    }
  
    function selQ(lineID, itemID, evt) {
      document.getElementById('menu1a').style.display = 'none';
      document.getElementById('menu1b').style.display = 'block';

      if (evt.ctrlKey == false) {
        clearAll();
        $('#link_' + lineID).addClass('highlight');
        addQID(itemID, true);
      } else {
        if ($('#link_' + lineID).hasClass('highlight')) {
          $('#link_' + lineID).removeClass('highlight');
          subQID(itemID);
        } else {
          $('#link_' + lineID).addClass('highlight');
          addQID(itemID, false);
        }
      }

      document.PapersMenu.oldLineNo.value = lineID;
      document.PapersMenu.lineNo.value = lineID;

      if (evt != null) {
        evt.cancelBubble = true;
      }
    }

    function qOff() {
      document.getElementById('menu1a').style.display = 'block';
      document.getElementById('menu1b').style.display = 'none';
      clearAll();
      document.PapersMenu.itemID.value = '';
      document.PapersMenu.lineNo.value = '';
    }
  </script>
</head>

<body onselectstart="return false">
<?php
  require '../include/recycle_options_menu.inc';
?>
<div id="content" class="content">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get">
<table class="header">
<?php
echo '<tr onclick="qOff();"><th colspan="4"><div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a></div><div style="font-size:200%; margin-left:10px; font-weight:bold">' . $string['recyclebin'] . '</div>';

$recycle_bin = RecycleBin::get_recyclebin_contents($userID, $teams, $mysqli);

$mysqli->close();

$sortby = 'name';
if (isset($_GET['sortby'])) $sortby = $_GET['sortby'];

$ordering = 'asc';
if (isset($_GET['ordering'])) $ordering = $_GET['ordering'];

if (count($recycle_bin) > 0) {
  $recycle_bin = array_csort($recycle_bin, $sortby, $ordering);
}

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

$paper_types = array('Formative Self-Assessment', 'Progress Test', 'Summative Exam', 'Survey', 'OSCE Station', 'Offline Paper', 'Peer Review');
$paper_icons = array('formative_16.gif', 'progress_16.gif', 'summative_16.gif', 'survey_16.gif', 'osce_16.gif', 'offline_16.gif', 'peer_review_16.gif');
$list_size = count($recycle_bin);
for ($item=0; $item<$list_size; $item++) {
  if ($recycle_bin[$item]['type'] == 'paper') {
    $temp_type = $recycle_bin[$item]['subtype'];
    $split_name = explode('[deleted',$recycle_bin[$item]['name']);
    echo "<tr class=\"qline\" id=\"link_$item\" onclick=\"selQ($item,'p" . $recycle_bin[$item]['id'] . "',event)\"><td class=\"icon\"><img src=\"../artwork/" . $paper_icons[$temp_type] . "\" width=\"16\" height=\"16\" border=\"0\" /></td><td>" . $split_name[0] . "</td><td><nobr>&nbsp;" . dateDisplay($recycle_bin[$item]['deleted']) . "</nobr></td><td><nobr>&nbsp;" . $string[strtolower($paper_types[$temp_type])] . "</nobr></td></tr>\n";
  } elseif ($recycle_bin[$item]['type'] == 'folder') {
    echo "<tr class=\"qline\" id=\"link_$item\" onclick=\"selQ($item,'f" . $recycle_bin[$item]['id'] . "',event)\"><td class=\"icon\"><img src=\"../artwork/yellow_folder.png\" width=\"16\" height=\"16\" border=\"0\" /></td><td>" . $recycle_bin[$item]['name'] . "</td><td><nobr>&nbsp;" . dateDisplay($recycle_bin[$item]['deleted']) . "</nobr></td><td><nobr>&nbsp;" . $string['folder'] . "</nobr></td></tr>\n";
  } else {
    echo "<tr class=\"qline\" id=\"link_$item\" onclick=\"selQ($item,'q" . $recycle_bin[$item]['id'] . "',event)\"><td class=\"icon\"><img src=\"../artwork/question_item_icon.gif\" width=\"16\" height=\"16\" border=\"0\" /></td><td>" . $recycle_bin[$item]['name'] . "</td><td><nobr>&nbsp;" . dateDisplay($recycle_bin[$item]['deleted']) . "</nobr></td><td><nobr>&nbsp;" . $string[strtolower($recycle_bin[$item]['subtype'])] . "</nobr></td></tr>\n";
  }
}
echo "</table>\n";
?>
</form>
</div>
</body>
</html>