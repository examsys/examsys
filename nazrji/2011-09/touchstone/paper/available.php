<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* Display a list of the papers that are currently available to a student
* 
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require '../config/index.inc';
require '../classes/dateutils.class.php';

function drawTabs($tab_array, $current_tab) {
	$html = '<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%; float: right"><tr>';
	foreach($tab_array as $individual_tab) {
		$button_id = 'button-'.str_replace('/', '-', $individual_tab);
		$bg_mod =  ($individual_tab == $current_tab) ? '_on' : '_off';
		$html .= "<td id=\"{$button_id}\" style=\"padding-top:0px; cursor:pointer; width:126px; height:21px; color:white; text-align:center; font-weight:bold; font-size:110%; background-image:url(../artwork/tab{$bg_mod}.gif)\" onclick=\"switchYear('{$individual_tab}'); return false;\">$individual_tab</td>";
	}
	$html .= "</tr></table>\n";
	return $html;
}


// Check if our student is in a lab
$lab_info = $mysqli->prepare("SELECT lab FROM ip_addresses WHERE address=? LIMIT 1");
$lab_info->bind_param('s', $_SERVER['REMOTE_ADDR']);
$lab_info->execute();
$lab_info->bind_result($lab);
$lab_info->store_result();
$lab_info->fetch();
if ($lab_info->num_rows == 0 or empty($lab)) {
	$lab = -1;
}
$lab_info->close();

// Get modules
$modules = array();
$i = 0;
if ($stmt = $mysqli->prepare("SELECT m.ModuleID, m.fullname, sm.calendar_year FROM modules m INNER JOIN student_modules sm on m.moduleID = sm.moduleid WHERE sm.userID = ? AND m.active = 1 ORDER BY sm.calendar_year ASC, m.fullname ASC")) {
  $stmt->bind_param('i', $userID);
  $stmt->execute();
  $stmt->bind_result($moduleID, $module_name, $module_year);
  while($stmt->fetch()) {
		$modules[$i]['id'] = $moduleID;
		$modules[$i]['name'] = $module_name;
		$modules[$i]['year'] = $module_year;
		$i++;
  }
}
$stmt->close();

$sessions_with_papers = array();

// Get papers for this module - types 0,1,3, valid for this date
$papers = 0;
$papers_query = <<< QUERY
SELECT p.paper_title, p.paper_type, p.labs, p.start_date, p.end_date, max(pa.screen) AS screens, p.calendar_year FROM properties p
INNER JOIN papers pa ON p.property_id = pa.paper
WHERE p.paper_type IN ('0','1','3')
AND p.moduleID LIKE ? AND (p.calendar_year = ? OR p.calendar_year = '' OR p.calendar_year IS NULL)
AND p.start_date < NOW() AND p.end_date > NOW()
AND p.deleted IS NULL
GROUP BY p.property_id
ORDER BY p.paper_title
QUERY;

for($i = 0; $i < count($modules); $i++) {
  $mod_id = $modules[$i]['id'];
	if ($stmt = $mysqli->prepare($papers_query)) {
		$mod_string = '%'.$mod_id.'%';
	  $stmt->bind_param('ss', $mod_string, $modules[$i]['year']);
	  $stmt->execute();
	  $stmt->bind_result($paper_title, $paper_type, $labs, $start_date, $end_date, $screens, $calendar_year);
	  $stmt->store_result();
	  while ($stmt->fetch()) {
	  	// Check if the user is able to access the paper from their current location
	  	$lab_arr = (empty($labs)) ? array() : explode(',', $labs);
	  	if (empty($lab_arr) or ($lab != -1 and in_array($lab, $lab_arr))) {
	  		$screens = (empty($screens)) ? 0 : $screens;
	  		
	  		// Don't show if 0 screens
	  		if ($screens > 0) {
					$modules[$i]['papers'][] = array('title' =>$paper_title, 'type' => $paper_type, 'start' => $start_date, 'end' => $end_date, 'screens' => $screens);
					$papers++;
					
					if (!in_array($modules[$i]['year'], $sessions_with_papers)) {
						$sessions_with_papers[] = $modules[$i]['year'];
					}
	  		}
	  	}
	  }
	}
	$stmt->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>TouchStone<?php echo " $cfg_install_type"; ?></title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<style type="text/css">
body { padding-left: 0px; }
</style>

<script src="../javascript/student_help.js" type="text/javascript"></script>
<script type="text/javascript">
function switchYear(toShow) {
	var years = ['<?php echo implode('\',\'', $sessions_with_papers) ?>'];
	for(var i = 0; i < years.length; i++) {
		target = document.getElementById('papers-' + years[i].replace('/', '-'));
		link = document.getElementById('button-' + years[i].replace('/', '-'));
		if (target != null) {
			target.style.display = (years[i] == toShow) ? 'block' : 'none';
			if (link != null) {
				link.style.backgroundImage = (years[i] == toShow) ? 'url(../artwork/tab_on.gif)' : 'url(../artwork/tab_off.gif)';
			}
		}
	}
}
</script>
</head>
<body>
<div id="content" class="content" style="font-size:80%">
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr><td colspan="2" style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td></tr>
	  <tr>
	    <td colspan="2" style="background-color:#F1F5FB"><div style="font-size:22pt; font-weight:bold">&nbsp;TouchStone<?php echo " $cfg_install_type"; ?></div></td>
	  </tr>
	  <tr>
	    <td style="background-color:#F1F5FB"><div style="position:relative; left:12px; top:-3px; font-size:8pt; padding-bottom: 20px;">Assessment Management System</div></td>
	    <td style="background-color:#F1F5FB; text-align:right; vertical-align: bottom">
<?php
$default_session = '';
if (count($sessions_with_papers) > 0) {
	$default_session = $sessions_with_papers[count($sessions_with_papers) - 1];
	echo drawTabs($sessions_with_papers, $default_session);
}
?>
	    </td>
	  </tr>
	  <tr>
	    <td colspan="2" style="height:6px; background-color:#1E3C7B"></td>
	  </tr>
	</table>
<?php
if($papers > 0) {
	$last_session = '';
	
	foreach($modules as $module) {
	  $mod_id = $module['id'];
		if (!empty($module['papers']))	{
			if($module['year'] != $last_session) {
				$visibility = 'style="display: none"';
				if($module['year'] == $default_session) {
					$visibility = '';
				}
				if($last_session != '') {
?>
		</div>
<?php
				}
?>
		<div id="papers-<?php echo str_replace('/', '-', $module['year']) ?>"<?php echo $visibility ?>>
<?php
				$last_session = $module['year'];
			}
?>
			<br clear="all" /><table border="0" style="margin-left:10px; padding-right:2px; padding-bottom:5px; color:#1E3287"><tr><td><nobr><?php echo("<strong>{$mod_id}</strong>: {$module['name']} (".count($module['papers']).")"); ?></nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table>
			<br />
<?php
			foreach($module['papers'] as $paper) {
				$screen_plural = ($paper['screens'] != 1) ? 's' : '';
?>
			  <div class="file">
			  	<table cellpadding="0" cellspacing="0" border="0">
			  		<tr>
			  			<td style="width:60px" align="center">
								<a href="../user_index.php?paper=<?php echo urlencode($paper['title']) ?>" title="<?php echo htmlentities($paper['title']) ?>" target="_blank"><?php echo(displayIcon($paper['type'],$paper['title'],'','','','')) ?></a>
							</td>
	    				<td>
	    					<a href="../user_index.php?paper=<?php echo urlencode($paper['title']) ?>" title="<?php echo htmlentities($paper['title']) ?>" target="_blank" class="blacklink"><?php echo(htmlentities($paper['title'])) ?></a><br />
	    					<span style="color:#808080">
	    						<?php echo($paper['screens']." screen".$screen_plural)?><br />
	    						<?php echo(date('d/m/Y h:i', strtotime($paper['start']))." to ".date('d/m/Y h:i', strtotime($paper['end']))) ?>
	    					</span>
	    				</td>
	    			</tr>
	    		</table>
	    	</div>
<?php
			}
		}
	}
?>
		</div>
<?php
} else {
?>
	<p style="margin-left: 20px">You have no papers available at this time.</p>
<?php
}
?>
</div>
</body>
</html>
<?php
function displayIcon($paper_type,$title,$initials,$surname,$shared,$locked) {
  switch ($paper_type) {
    case 0:
      $html = "<img src=\"../artwork/formative" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Formative Self-Assessment&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
    case 1:
      $html = "<img src=\"../artwork/progress" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Progress Test&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
    case 3:
      $html = "<img src=\"../artwork/survey" . $shared . ".png\" width=\"48\" height=\"48\" alt=\"Type: Survey&#013;Author: $title $initials $surname\" border=\"0\" />";
      break;
  }
  return $html;
}
?>