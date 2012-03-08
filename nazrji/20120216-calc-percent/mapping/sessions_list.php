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
* @author Simon Wilkinson, Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require_once '../include/staff_auth.inc';
  require_once '../include/errors.inc';
  check_var('module', 'GET', true, false);

  if (in_array($_GET['module'], $teams) === false and strpos($userroles,'SysAdmin') === false) {
    exit;
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html onclick="hideSessCopyMenu(event);">
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō: <?php echo $string['manageobjectives'] . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
  .obj_no {text-align:right; padding-right:6px}
  .zero_obj_no {text-align:right; padding-right:6px; color:#C00000}
  .title {padding-left:6px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script type="text/javascript">
    function selSession(divID, identifier, session, VLE, evt) {
      hideSessCopyMenu(evt);
      tmp_ID = document.myform.oldDivID.value;
      if (tmp_ID != '') {
        document.getElementById(tmp_ID).style.backgroundColor = 'white';
      }

      if (VLE == 'NLE') {
        document.getElementById('menu1a').style.display = 'none';
        document.getElementById('menu1c').style.display = 'block';
      } else {
        document.getElementById('menu1a').style.display = 'none';
        document.getElementById('menu1b').style.display = 'block';
      }

      document.myform.oldDivID.value = divID;
      document.myform.divID.value = divID;

      document.myform.identifier.value = identifier;
      document.myform.session.value = session;
      document.myform.VLE.value = VLE;

      document.getElementById(divID).style.backgroundColor = '#B3C8E8';
      evt.cancelBubble = true;
    }

    function editSession(identifier, calendar_year) {
      window.location.href="./edit_session.php?identifier=" + identifier + "&module=<?php echo $_GET['module']; ?>&calendar_year=" + calendar_year;
    }

    function editNLESession(calendar_year) {
      alert("<?php echo $string['thisisnle']; ?>");
    }

    function highlight(lineID) {
      if (lineID != document.myform.oldDivID.value) {
        document.getElementById(lineID).style.backgroundColor = '#EEEEEE';
      }
    }

    function unhighlight(lineID) {
      if (lineID != document.myform.oldDivID.value) {
        document.getElementById(lineID).style.backgroundColor = '';
      }
    }

  </script>
</head>

<body onclick="hideSessCopyMenu(event);">
<?php
  require '../include/sessions_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<?php
  if (isset($_GET['module'])) {
    $module = $_GET['module'];
  } else {
    $module = '';
  }

  echo "<table class=\"header\">\n";
  echo "<tr><th colspan=\"3\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../folder/details.php?module=$module\">$module</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['manageobjectives'] . "</strong></div></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(0); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";
  echo "<tr><th>&nbsp;" . $string['date'] . "&nbsp;</th>\n";
  echo "<th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;" . $string['name'] . "&nbsp;</th>\n";
  echo "<th><img src=\"../artwork/header_vertical_line.gif\" width=\"2\" height=\"15\" alt=\"line\" border=\"0\" />&nbsp;" . $string['objectives'] . "&nbsp;</th><th>&nbsp;</th></tr>\n";
  echo "<tr><th colspan=\"4\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></th></tr>\n";
  $objectives = getObjectives($module, $session, '', '', $mysqli, 'all');
  $old_session = '';
  $id = 0;
	$first = true;

  if (count($objectives) > 0) {
    foreach ($objectives[$_GET['module']] as $session) {
      if (isset($session['objectives'])) {
        $objectives_no = count($session['objectives']);
      } else {
        $objectives_no = 0;
      }
      if ($old_session != $session['calendar_year']) {
      	if (!$first) {
	      	echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
      	}
	      $first = false;
      	echo "<tr><td colspan=\"4\"><table border=\"0\" style=\"padding-bottom:5px; width:100%; color:#1E3287\"><tr><td><nobr>" . $session['calendar_year'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
      }
      if (isset($session['identifier'])) {
        $identifier = $session['identifier'];
      } else {
        $identifier = '';
      }
      if ($session['VLE'] == 'NLE') {
        echo "<tr style=\"cursor:pointer\" id=\"$id\" onmouseover=\"highlight($id)\" onmouseout=\"unhighlight($id)\" onclick=\"selSession('$id','$identifier','" . $session['calendar_year'] . "','" . $session['VLE'] . "',event);\" ondblclick=\"editNLESession('" . $session['calendar_year'] . "');\">";
      } else {
        echo "<tr style=\"cursor:pointer\" id=\"$id\" onmouseover=\"highlight($id)\" onmouseout=\"unhighlight($id)\" onclick=\"selSession('$id','$identifier','" . $session['calendar_year'] . "','" . $session['VLE'] . "',event);\" ondblclick=\"editSession('" . $session['identifier'] . "','" . $session['calendar_year'] . "');\">";
      }
      echo "<td>&nbsp;" . $session['occurrance'] . "</td><td class=\"title\">" . $session['title'] . "</td>";
      if ($objectives_no == 0) {
        echo "<td class=\"zero_obj_no\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"16\" height=\"16\" alt=\"Warning\" border=\"0\" />&nbsp;$objectives_no</td>";
      } else {
        echo "<td class=\"obj_no\">$objectives_no</td>";
      }
      echo "<td>&nbsp;</td></tr>\n";
      $old_session = $session['calendar_year'];
      $id++;
    }
  }

  $mysqli->close();
?>
</table>
</div>

</body>
</html>