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
* Displays the results of a paper search.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../classes/moduleutils.class.php';
require_once '../classes/paperutils.class.php';

if (isset($_POST['formative']) and isset($_POST['progress']) and isset($_POST['summative']) and isset($_POST['survey']) and isset($_POST['osce']) and isset($_POST['offline'])) {
  // All types are selected so don't build into query.
  $type = '';
} else {
  $type = '';
  if (isset($_POST['formative']) and $_POST['formative'] == '1') $type .= " OR paper_type='0'";
  if (isset($_POST['progress']) and $_POST['progress'] == '1') $type .= " OR paper_type='1'";
  if (isset($_POST['summative']) and $_POST['summative'] == '1') $type .= " OR paper_type='2'";
  if (isset($_POST['survey']) and $_POST['survey'] == '1') $type .= " OR paper_type='3'";
  if (isset($_POST['osce']) and $_POST['osce'] == '1') $type .= " OR paper_type='4'";
  if (isset($_POST['offline']) and $_POST['offline'] == '1') $type .= " OR paper_type='5'";
  if (isset($_POST['peerreview']) and $_POST['peerreview'] == '1') $type .= " OR paper_type='6'";
  if (strlen($type) > 0) $type = 'AND (' . substr($type,4) . ')';
}

$params = '';
$variables = array();
if (isset($_POST['searchterm']) and $_POST['searchterm'] != '') {
  $paper = 'AND paper_title LIKE ?';
  $variables[] = '%' . $_POST['searchterm'] . '%';
  $params .= 's';
} else {
  $paper = '';
}
if (isset($_POST['owner']) and $_POST['owner'] != '') {
  $owner = 'AND paper_ownerID=?';
  $variables[] = $_POST['owner'];
  $params .= 'i';
  setcookie("papersearch[2]", $_POST['owner'], time()+60*60*24*365);
} else {
  $owner = '';
  setcookie("papersearch[2]", '', time()+60*60*24*365);
}
if (isset($_POST['lab']) and $_POST['lab'] != '') {
  $lab = 'AND labs LIKE ?';
  $variables[] = '%' . $_POST['lab'] . '%';
  $params .= 's';
} else {
  $lab = '';
}
$moduleid = '';
if (isset($_POST['module']) and $_POST['module'] != '') {
  $moduleid = 'AND idMod = ?';
  $variables[] = $_POST['module'];
  $params .= 'i';
} else {
  if (!$userObject->has_role('SysAdmin')) {
    $moduleid = 'AND idMod IN (' . implode(',', array_keys($staff_modules)) . ')';
  }
}
if (isset($_POST['day']) and $_POST['day'] != '') {
  $date = 'AND start_date <= ? AND end_date >= ?';
  $variables[] = $_POST['year'] . $_POST['month'] . $_POST['day'] . '000000';
  $variables[] = $_POST['year'] . $_POST['month'] . $_POST['day'] . '235959';
  $params .= 'ss';
} else {
  $date = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rogō<?php echo " {$configObject->get('cfg_install_type')}"; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
  .f a {color:black}
  .f {float:left; width:375px; height:74px; padding-left:12px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
</head>

<?php
  if (isset($_POST['submit'])) {
    echo "<body>\n";

    require '../include/paper_search_options.inc';

    echo "<div id=\"content\" class=\"content\">\n";
    echo "<table class=\"header\">\n";
  } else {
    echo "<body style=\"margin:0px; background-color:white; color:black\">\n";

    require '../include/paper_search_options.inc';

    echo "<div id=\"content\" class=\"content\">\n";
    echo "<table class=\"header\">\n";
    echo "<tr><th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['papersearch'] . "</strong></div></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(0); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>";
    echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";
  }

  if (isset($_POST['submit'])) {
    $results = $mysqli->prepare("SELECT properties.property_id, title, initials, surname, GROUP_CONCAT(DISTINCT moduleID SEPARATOR ', '), paper_ownerID, paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'{$configObject->get('cfg_long_date_time')}') AS display_end_date, retired FROM (properties, users, properties_modules, modules) LEFT JOIN papers ON properties.property_id=papers.paper WHERE properties.property_id = properties_modules.property_id AND properties_modules.idMod = modules.id AND properties.paper_ownerID=users.id $paper $owner $lab $moduleid $date $type AND deleted IS NULL GROUP BY paper_title");
    if (count($variables) > 0) {
	    array_unshift($variables, $params);
	    $vars = array();
	    foreach ($variables as &$individual_variable) {
	      $vars[] = &$individual_variable;
	    }
	    call_user_func_array(array($results,'bind_param'), $vars);
    }
    $results->execute();
    $results->store_result();
    $results->bind_result($property_id, $title, $initials, $surname, $moduleID, $paper_ownerID, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $retired);

    echo "<tr><th><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a></div><div onclick=\"qOff()\" style=\"font-size:200%; margin-left:10px\"><strong>" . $string['papers'] . " (" . number_format($results->num_rows) . "):&nbsp;</strong>" . $_POST['searchterm'] . "</div></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(0); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></th></tr>\n";
    echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n</table>\n";
    if ($results->num_rows > 0) {
      echo '<br />';
      while ($results->fetch()) {
        echo '<div class="f">';
        echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:60px; text-align:center">';
        $type = $paper_type;
        if (date("YmdHis", time()) >= $start_date) {
          $locked = '_locked';
        } else {
          $locked = '';
        }
        if ($paper_ownerID == $userObject->get_user_ID() or $userObject->has_role(array('SysAdmin','Admin'))or $type != 2) {
          $codes = module_utils::get_idMod(explode(',', $moduleID), $mysqli);
          $html = implode(',', $codes);
          echo "<a href=\"../paper/details.php?paperID=$property_id&module=$html\">" . Paper_utils::displayIcon($type, $title, $initials, $surname, $locked, $retired) . "</a></td>\n";
          echo "</td><td><a href=\"../paper/details.php?paperID=$property_id&module=$html\">$paper_title</a><br />";
        } else {
          if ($userObject->is_staff_user_on_module($moduleID)) {
            $codes = module_utils::get_idMod(explode(',', $moduleID), $mysqli);
            $html = implode(',', $codes);
            echo "<a href=\"../paper/details.php?paperID=$property_id&module=$html\">" . Paper_utils::displayIcon(2, $title, $initials, $surname, $locked, $retired) . "</a></td>\n";
            echo "</td><td><a href=\"../paper/details.php?paperID=$property_id&module=$html\">$paper_title</a><br />";
          } else {
            echo "<img src=\"../artwork/noentry_question_icon_48.png\" width=\"48\" height=\"48\" alt=\"Type: Summative Exam (Restricted Access)&#013;Author: $title $initials $surname\" border=\"0\" /></td>\n";
            echo "</td><td>$paper_title<br />";
          }
        }
        echo '  <span style="color:#808080">' . $screens;
        if ($screens == 1) {
          echo ' ' . $string['screen'] . ', ';
        } else {
          echo ' ' . $string['screens'] . ', ';
        }
        echo $moduleID . '<br />';
        echo '  ' . $display_start_date. ' ' . $string['to'] . ' ' . $display_end_date .  '</td></tr></table>';
        echo "</div>\n";
      }
    } else {
    ?>
    <table cellpadding="1" cellspacing="1" border="0" style="margin: 0px auto; width:75%; border:1px solid #C0C0C0; text-align:left">
    <tr><td colspan="2" style="background-color:#F2B100; height:3px"> </td></tr>
    <tr><td style="width:16px; padding-top:5px; padding-bottom:5px"><img src="../artwork/information_icon.gif" width="16" height="16" alt="i" border="0" /></td><td style="padding-top:5px; padding-bottom:5px">&nbsp;<?php echo $string['nothingfound']; ?> "<?php echo $_POST['searchterm']; ?>"</td></tr>
    </table>
    <?php
    }
    $results->close();
    $mysqli->close();
  }
?>
</div>
</body>
</html>