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
require_once '../classes/moduleutils.class.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['keywords'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .qline {line-height:150%;cursor:pointer;color:#000000;background-color:white; -webkit-user-select:none; -moz-user-select:none;}
    .qline:hover {background-color:#eee}
    .qline.highlight {background-color:#B3C8E8}
  </style>
  
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script language="javascript">
    function getLastID(IDs) {
      var id_list = IDs.split(",");
      last_elm = id_list.length - 1;
      
      return id_list[last_elm];
    }

    function addKeyID(keyID, clearall) {
      if (clearall) {
        document.getElementById('keywordID').value = ',' + keyID;
      } else {
        document.getElementById('keywordID').value = document.getElementById('keywordID').value + ',' + keyID;
      }
    }

    function subKeyID(keyID) {
      var tmpq = ',' + keyID;
      document.getElementById('keywordID').value = document.getElementById('keywordID').value.replace(tmpq, '');
    }
    
    function clearAll() {
      $('.highlight').removeClass('highlight');
    }
  
    function selKey(questionID, evt) {

      document.getElementById('menu1a').style.display = 'none';
      document.getElementById('menu1b').style.display = 'block';

      if (evt.ctrlKey == false) {
        clearAll();
        $('#link_' + questionID).addClass('highlight');
        addKeyID(questionID, true);
      } else {
        if ($('#link_' + questionID).hasClass('highlight')) {
          $('#link_' + questionID).removeClass('highlight');
          subKeyID(questionID);
        } else {
          $('#link_' + questionID).addClass('highlight');
          addKeyID(questionID, false);
        }
      }
    }
    
    function selKey2(divID, evt) {
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
  </script>
</head>

<body>
<?php
  $keyword_list = array();
  
  if (isset($_GET['module']) and $_GET['module'] != '') {
    // Get team keywords
    $result = $mysqli->prepare("SELECT id, keyword FROM keywords_user WHERE keyword_type='team' AND userID=? ORDER BY keyword");
    $result->bind_param('i', $_GET['module']);
    $result->execute();
    $result->bind_result($keywordID, $keyword);
    while ($result->fetch()) {
      $keyword_list[$keywordID] = $keyword;
    }
    $result->close();
  } else {
    // Get personal keywords
    $result = $mysqli->prepare("SELECT id, keyword FROM keywords_user WHERE keyword_type='personal' AND userid=? ORDER BY keyword");
    $result->bind_param('i', $userObject->get_user_ID());
    $result->execute();
    $result->bind_result($keywordID, $keyword);
    while ($result->fetch()) {
      $keyword_list[$keywordID] = $keyword;
    }
    $result->close();
  }

  require '../include/folder_keyword_options.inc';
?>
<div id="content" class="content">

<table class="header">
<tr>
<?php
  if (isset($_GET['module']) and $_GET['module'] != '') {
    $module_code = module_utils::get_moduleid_from_id($_GET['module'], $mysqli);
    echo "<th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"./details.php?module=" . $_GET['module'] . "\">" . $module_code . "</a></div><div style=\"margin-left:10px; font-size:200%\">" . sprintf($string['modulekeywords'], $module_code) . "</th>\n";
  } else {
    echo "<th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div style=\"margin-left:10px; font-size:200%\">" . $string['mypersonalkeywords'] . "</th>\n";
  }
?>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(237); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['mypersonalkeywords']; ?>" border="0" /></a></th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>
<?php
foreach ($keyword_list as $keywordID => $keyword) {
  echo "<tr class=\"qline\" id=\"link_$keywordID\" onclick=\"selKey($keywordID, event)\" ondblclick=\"editKeyword($keywordID)\"><td colspan=\"2\">&nbsp;$keyword</td></tr>\n";
}
$mysqli->close();
?>
</table>
</div>

</body>
</html>