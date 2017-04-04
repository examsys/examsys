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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

$modID = check_var('module', 'GET', true, false, true);

$module = module_utils::get_moduleid_from_id($modID, $mysqli);

if (!$module) {
   $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
   $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
?>
<!DOCTYPE html>
<html onclick="hideSessCopyMenu(event);">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['manageobjectives'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
  <style type="text/css">
    .obj_no {text-align:right; padding-right:6px}
    .zero_obj_no {text-align:right; padding-right:6px; color:#C00000}
    .title {padding-left:6px}
    .indent {padding-left:24px}
  </style>

  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    function selSession(divID, identifier, session, VLE, evt) {
      hideSessCopyMenu(evt);
      tmp_ID = $('#oldDivID').val();
      if (tmp_ID != '') {
        $('#' + tmp_ID).css('background-color', 'white');
      }

      if (VLE != '') {
        $('#menu1a').hide();
        $('#menu1c').show();
      } else {
        $('#menu1a').hide();
        $('#menu1b').show();
      }

      $('#oldDivID').val(divID);
      $('#divID').val(divID);

      $('#identifier').val(identifier);
      $('#session').val(session);
      $('#VLE').val(VLE);

      $('#' + divID).css('background-color', '#FFBD69');
      evt.cancelBubble = true;
    }

    function editSession(identifier, calendar_year) {
      window.location.href="./edit_session.php?identifier=" + identifier + "&module=<?php echo $modID ?>&calendar_year=" + calendar_year;
    }

    function highlight(lineID) {
      if (lineID != $('#oldDivID').val()) {
        $('#' + lineID).css('background-color', '#FFE7A2');
      }
    }

    function unhighlight(lineID) {
      if (lineID != $('#oldDivID').val()) {
        $('#' + lineID).css('background-color', '');
      }
    }

    $(function () {
		  $('html').click(function() {
			  hideSessCopyMenu(event);
      });
		});
  </script>
</head>

<body>
<?php
  require '../include/sessions_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">
  
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=<?php echo $modID ?>"><?php echo $module ?></a></div>
  <div class="page_title"><?php echo $string['manageobjectives'] ?></div>
</div>
<?php  
  echo "<table class=\"header\">\n";
  echo "<tr><th class=\"col10\">" . $string['date'] . "</th>\n";
  echo "<th class=\"col\">" . $string['name'] . "</th>\n";
  echo "<th class=\"col\">" . $string['objectives'] . "</th><th>&nbsp;</th></tr>\n";

  $old_session = '';
  $id = 0;
  $first = true;
  if (count($objsBySession) > 0 and isset($objsBySession[$module])) {
    foreach ($objsBySession[$module] as $session) {
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
      	echo "<tr><td colspan=\"4\"><table border=\"0\" class=\"subsect\" style=\"margin-left:10px; width:99%\"><tr><td><nobr>" . $session['calendar_year'] . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table></td></tr>\n";
      }
      if (isset($session['identifier'])) {
        $identifier = $session['identifier'];
      } else {
        $identifier = '';
      }
      if ($session['VLE'] != '') {
        echo "<tr class=\"l\" id=\"$id\" onclick=\"selSession($id,'$identifier','" . $session['calendar_year'] . "','" . $session['VLE'] . "',event);\" ondblclick=\"editVLESession('" . $session['calendar_year'] . "');\">";
      } else {
        echo "<tr class=\"l\" id=\"$id\" onclick=\"selSession($id,'$identifier','" . $session['calendar_year'] . "','" . $session['VLE'] . "',event);\" ondblclick=\"editSession('" . $session['identifier'] . "','" . $session['calendar_year'] . "');\">";
      }
      echo "<td class=\"indent\">" . $session['occurrance'] . "</td><td class=\"title\">" . $session['title'] . "</td>";
      if ($objectives_no == 0) {
        echo "<td class=\"zero_obj_no\"><img src=\"../artwork/small_yellow_warning_icon.gif\" width=\"12\" height=\"11\" alt=\"Warning\" />&nbsp;$objectives_no</td>";
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