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
  require '../include/errors.inc';
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title><?php echo $string['keywords'] . ' ' . $cfg_install_type; ?></title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
  .l {cursor:pointer}
  </style>
  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script language="javascript">
    function selKey(divID, evt) {
      tmp_ID = document.myform.oldID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
        document.getElementById(tmp_ID).style.color = 'black';
      }

      document.getElementById('menu1a').style.display = 'none';
      document.getElementById('menu1b').style.display = 'block';

      document.myform.oldID.value = divID;
      document.myform.id.value = divID;

      document.getElementById(divID).style.backgroundColor = '#B3C8E8';
      evt.cancelBubble = true;
    }

    function deselKey() {
      tmp_ID = document.myform.oldID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }
      document.myform.oldID.value = '';
      document.getElementById('menu1b').style.display = 'none';
      document.getElementById('menu1a').style.display = 'block';
    }

    function lon(lineID) {
      if (lineID != document.myform.oldID.value) {
        document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function loff(lineID) {
      if (lineID != document.myform.oldID.value) {
        document.getElementById(lineID).style.backgroundColor = '';
      }
    }
  </script>
</head>

<body onclick="deselDeg()">
<?php
  $keyword_list = array();
  
  if (isset($_GET['module']) and $_GET['module'] != '') {
    // Get team keywords
    $result = $mysqli->prepare("SELECT keywords_user.id, keyword FROM keywords_user, modules WHERE keywords_user.userID=modules.id AND keyword_type='team' AND moduleid=? ORDER BY keyword");
    $result->bind_param('s', $_GET['module']);
    $result->execute();
    $result->bind_result($keywordID, $keyword);
    while ($result->fetch()) {
      $keyword_list[$keywordID] = $keyword;
    }
    $result->close();
  } else {
    // Get personal keywords
    $result = $mysqli->prepare("SELECT keywords_user.id, keyword FROM keywords_user WHERE keyword_type='personal' AND userid=? ORDER BY keyword");
    $result->bind_param('s', $userID);
    $result->execute();
    $result->bind_result($keywordID, $keyword);
    while ($result->fetch()) {
      $keyword_list[$keywordID] = $keyword;
    }
    $result->close();
  }

  require '../include/folder_keyword_options.inc';
?>
<div id="content" class="content" style="font-size:80%">

<table class="header">
<tr>
<?php
  if (isset($_GET['module']) and $_GET['module'] != '') {
    echo "<th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./details.php?module=" . $_GET['module'] . "\">" . $_GET['module'] . "</a></div><div style=\"margin-left:10px; font-size:200%\">" . sprintf($string['modulekeywords'], $_GET['module']) . "</th>\n";
  } else {
    echo "<th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div style=\"margin-left:10px; font-size:200%\">" . $string['mypersonalkeywords'] . "</th>\n";
  }
?>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(237); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['mypersonalkeywords']; ?>" border="0" /></a></th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>
<?php
foreach ($keyword_list as $keywordID => $keyword) {
  echo "<tr id=\"$keywordID\" onclick=\"selKey($keywordID,event)\" ondblclick=\"editKeyword($keywordID)\" onmouseover=\"lon($keywordID)\" onmouseout=\"loff($keywordID)\" class=\"l\"><td colspan=\"2\">&nbsp;$keyword</td></tr>\n";
}
$mysqli->close();
?>
</table>
</div>

</body>
</html>