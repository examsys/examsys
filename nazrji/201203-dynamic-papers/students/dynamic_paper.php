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
* Display a list of the papers that are currently available to a student
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require '../config/index.inc';
require '../include/mapping.inc';
require_once '../classes/dateutils.class.php';
require '../include/errors.inc';

check_var('module', 'GET', true, false);
check_var('session', 'GET', true, false);

function drawTabs($tab_array, $current_tab) {
	$html = '<table class="year-tabs"><tr>';
	foreach($tab_array as $individual_tab) {
		$bg_mod =  ($individual_tab == $current_tab) ? ' on' : '';
		$html .= "<td class=\"tab{$bg_mod}\"><a href=\"{$_SERVER['PHP_SELF']}?module={$_GET['module']}&amp;session={$individual_tab}\">$individual_tab</a></td>";
	}
	$html .= "</tr></table>\n";
	return $html;
}

$module = $_GET['module'];
$session = $_GET['session'];
$module_name = '';

// Get modules
if ($stmt = $mysqli->prepare("SELECT m.fullname FROM modules m INNER JOIN student_modules sm on m.moduleID = sm.moduleid WHERE m.moduleid=? AND sm.userID = ? AND sm.calendar_year=? AND m.active = 1")) {
  $stmt->bind_param('sis', $module, $userID, $session);
  $stmt->execute();
  $stmt->bind_result($module_name);
  while ($stmt->fetch()) {
		$module_name = $module_name;
  }
}
$stmt->close();

$years = get_years($module, $mysqli, 'all');
$years = array_reverse(array_slice($years, 0, 5));
$session = (isset($_REQUEST['session'])) ? $_REQUEST['session'] : $years[0];

$temp_array = array();
$questionID_list = get_mapped_questions($module, $session, $temp_array, $mysqli);

$objsBySession = getObjectives($module, $session, null, $questionID_list, $mysqli);
unset($objsBySession['none_of_the_above']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />

  <title>Rogō<?php echo " $cfg_install_type {$string['objectivebasedquiz']}"; ?> </title>

  <link rel="stylesheet" type="text/css" href="../css/objective_based.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/tipTip.css" />
  <link rel="stylesheet" type="text/css" href="../css/dynamic_papers.css" />

  <script src="../js/jquery-1.6.1.min.js" type="text/javascript"></script>
  <script src="../js/jquery.tipTip.minified.js" type="text/javascript"></script>
  <script src="../js/jquery.objectivebased.js" type="text/javascript"></script>
  <script src="../js/student_help.js" type="text/javascript"></script>
<?php
echo $cfg_js_root;
$langstrings = array('mustselectobjectives');
echo LangUtils::render_JS_strings($langstrings, $string);
?>
</head>
<body>
  <div id="content" class="content">
    <table id="header">
   		<tr>
         <td rowspan="2"style="background-color:#F1F5FB"><h1><?php echo $module ?> &mdash; <?php echo $string['objectivebasedquiz'] ?></h1></td>
         <td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
       </tr>
   	  <tr>
   	    <td style="background-color:#F1F5FB; text-align:right; vertical-align: bottom">
<?php
if (count($years) > 0) {
  echo drawTabs($years, $session);
}
?>
   	    </td>
   	  </tr>
   	  <tr>
   	    <td colspan="2" style="height:6px; background-color:#1E3C7B"></td>
   	  </tr>
   	</table>

<?php

if (count($objsBySession[$module]) > 0) {
  $qn_count = count(array_unique(explode(',', $questionID_list)));
?>
    <p class="intro"><?php echo $string['selectobjectives'] ?></p>
    <form action="<?php echo $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] ?>" method="post" id="obj_form">
<?php
  foreach($objsBySession[$module] as $identifier => $sessionData) {
    if ($sessionData['mapped'] == '1') {
      echo "<table class=\"map-session\"><tr><td>";
      if ($sessionData['class_code'] != '') {
        echo $sessionData['class_code'] . ': ';
      }
      echo $sessionData['title'] . ' <a href="' . urlencode($sessionData['source_url']) . '"><img src="../artwork/small_link.png" width="12" height="12" alt="" /></a> ';

      echo "</td><td style=\"width:98%\"><hr class=\"head-line\" /></td></tr></table>\n";
      if (isset($sessionData["objectives"]) and is_array($sessionData["objectives"])) {
        echo "<ul class=\"map-objectives\">\n";
        foreach ($sessionData["objectives"] as $id => $objectives) {
          if (is_array($objectives['mapped'])) {
            echo '<li class="mapped"><input type="checkbox" id="obj-mapped' . $objectives['id'] . '" name="obj-mapped[]" value="' . $identifier . '_' . $objectives['id'] . '" class="sel-objective offscreen" /> <label for="obj-mapped' . $objectives['id'] . '" class="map-item map-objective">' . htmlentities($objectives['content']) . "</label></li>\n";
          }
        }
        echo "</ul>\n";
      }
    }
  }
  
  if ($qn_count > 2) {
?>
  <p><a id="clear_all" href="#"><?php echo $string['clearall'] ?></a></p>
  <p>
    <label for="num_qns"><?php echo $string['numberquestions'] ?></label>
    <select id="num_qns" name="num_qns">
<?php
      for ($i = 1; $i <= $qn_count; $i++) {
?>
      <option value="<?php echo $i ?>"><?php echo $i ?></option>              
<?php
      }
?>      
    </select>
    &nbsp;<img src="../artwork/information_icon.gif" width="16" height="16" alt="Information icon" title="<?php echo $string['numbermessage'] ?>" class="tip-right" />
  </p>
  <p><input type="submit" name="submit" value="<?php echo $string['createpaper'] ?>" /></p>
<?php
  } else {
?>
  <p><?php echo $string['toofewquestions'] ?></p>
<?php
  }
} else {
?>
     <p><?php printf($string['nosessions'], $session) ?></p>
<?php
}
?>
      <input type="hidden" name="module" id="module" value="<?php echo $module ?>" />
      <input type="hidden" name="session" id="session" value="<?php echo $session ?>" />
    </form>
</div>
</body>
</html>