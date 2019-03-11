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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

if (isset($_POST['formative']) and isset($_POST['progress']) and isset($_POST['summative']) and isset($_POST['survey']) and isset($_POST['osce']) and isset($_POST['offline']) and isset($_POST['peerreview'])) {
  // All types are selected so don't build into query.
  $type = '';
	$type_problem = false;
} else {
  $type = '';
  if (isset($_POST['formative']) and $_POST['formative'] == '1') $type .= " OR paper_type='0'";
  if (isset($_POST['progress']) and $_POST['progress'] == '1') $type .= " OR paper_type='1'";
  if (isset($_POST['summative']) and $_POST['summative'] == '1') $type .= " OR paper_type='2'";
  if (isset($_POST['survey']) and $_POST['survey'] == '1') $type .= " OR paper_type='3'";
  if (isset($_POST['osce']) and $_POST['osce'] == '1') $type .= " OR paper_type='4'";
  if (isset($_POST['offline']) and $_POST['offline'] == '1') $type .= " OR paper_type='5'";
  if (isset($_POST['peerreview']) and $_POST['peerreview'] == '1') $type .= " OR paper_type='6'";
  if (strlen($type) > 0) {
		$type = 'AND (' . substr($type,4) . ')';
		$type_problem = false;
	} else {
		$type_problem = true;
	}
	
}

$params = '';
$variables = array();
if (isset($_POST['searchterm']) and $_POST['searchterm'] != '') {
  $paper = 'AND paper_title LIKE ?';
  $variables[] = '%' . $mysqli->real_escape_string($_POST['searchterm']) . '%';
  $params .= 's';
} else {
  $paper = '';
}
if (isset($_POST['owner']) and $_POST['owner'] != '') {
  $owner = 'AND paper_ownerID = ?';
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
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('Rog&#333;:'); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
  .f a {color:black}
  .f {float:left; width:375px; height:74px; padding-left:12px}
  </style>

  <script src="../js/staff_help.js" type="text/javascript"></script>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/state.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/paper_search_options.inc';

  require '../include/toprightmenu.inc';
	echo draw_toprightmenu();
	
	echo "<div id=\"content\" class=\"content\">\n";
	echo "<div class=\"head_title\">\n";

  if (!isset($_POST['submit'])) {
    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>";
    echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
    if (isset($_REQUEST['module'])  and $_REQUEST['module'] != '') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_REQUEST['module'] . '">' . module_utils::get_moduleid_from_id($_REQUEST['module'], $mysqli) . '</a>';
    }
    echo "</div><div class=\"page_title\">" . $string['papersearch'] . "</div>";
    echo "</div>\n";
  }

  if (isset($_POST['submit'])) {
    if (!$userObject->has_role('SysAdmin') and empty($staff_modules)) {
      // The user cannot see any papers if they are not on any teams and they are not a SysAdmin.
      $title = $string['notonmodules'];
      $message = $string['notonmodulesmessage'];
      $icon = '/artwork/exclamation_red_bg.png';
      $notice->display_notice_and_exit($mysqli, $title, $message, $message, $icon, 'black', false);
    }
	  if ($type_problem) {
      echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" />";
      if (isset($_REQUEST['module']) and $_REQUEST['module'] != '') {
        echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_REQUEST['module'] . '">' . module_utils::get_moduleid_from_id($_REQUEST['module'], $mysqli) . '</a>';
      }
      echo "</div><div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a></div>";
      echo "<div class=\"page_title\">" . $string['papersearch'] . "</div>";
      echo "</div>\n";

			echo $notice->info_strip('No paper types have been selected.', 100);
			echo "</body>\n</html>\n";
			exit;
		}
    $sql = "SELECT properties.property_id, users.title, users.initials, users.surname, GROUP_CONCAT(DISTINCT moduleID SEPARATOR ', '), paper_type, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'%Y%m%d%H%i%s') AS start_date, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, DATE_FORMAT(end_date,'{$configObject->get('cfg_long_date_time')}') AS display_end_date, retired
						FROM (properties, users, properties_modules, modules)
						LEFT JOIN papers ON properties.property_id = papers.paper
						WHERE properties.property_id = properties_modules.property_id
						AND properties_modules.idMod = modules.id
						AND properties.paper_ownerID = users.id $paper $owner $lab $moduleid $date $type
						AND deleted IS NULL
						GROUP BY properties.paper_type, properties.paper_title, properties.property_id, properties.retired, users.surname, users.title, users.initials";
		$results = $mysqli->prepare($sql);
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
    $results->bind_result($property_id, $title, $initials, $surname, $moduleID, $paper_type, $screens, $paper_title, $start_date, $display_start_date, $display_end_date, $retired);

    echo "<div><img src=\"../artwork/toprightmenu.gif\" id=\"toprightmenu_icon\" /></div>";
    echo "<div class=\"breadcrumb\"><a href=\"../index.php\">" . $string['home'] . "</a>";
    if (isset($_REQUEST['module']) and $_REQUEST['module'] != '') {
      echo '<img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=' . $_REQUEST['module'] . '">' . module_utils::get_moduleid_from_id($_REQUEST['module'], $mysqli) . '</a>';
    }
    echo "</div><div class=\"page_title\">" . $string['papersearch'] . " (" . number_format($results->num_rows) . "):&nbsp;<span style=\"font-weight: normal\">'" . $_POST['searchterm'] . "'</span></div>";
    echo "</div>\n";

    if ($results->num_rows > 0) {
      echo '<br />';
      while ($results->fetch()) {
        echo '<div class="f">';
        echo '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="width:60px; text-align:center">';
        $type = $paper_type;
        if ($start_date != '' and date("YmdHis", time()) >= $start_date) {
          $locked = '_locked';
        } else {
          $locked = '';
        }
				echo "<a href=\"../paper/details.php?paperID=$property_id\">" . Paper_utils::displayIcon($type, $title, $initials, $surname, $locked, $retired) . "</a></td>\n";
				echo "</td><td><a href=\"../paper/details.php?paperID=$property_id\">$paper_title</a><br />";
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
			$msg = $string['nothingfound'] . ' "' . $_POST['searchterm'] . '"';
			echo $notice->info_strip($msg, 100);
    }
    $results->close();
  }
  
  $mysqli->close();
?>
</div>
</body>
</html>