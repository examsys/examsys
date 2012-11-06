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
require '../include/icon_display.inc';
require '../config/index.inc';
require '../classes/dateutils.class.php';

// Redirect External Examiners if they are straying
if (strpos($userroles,'External Examiner') !== false) {
  if ($_SERVER['PHP_SELF'] != '/staff/index.php' and $_SERVER['PHP_SELF'] != '/reviews/index.php' and $_SERVER['PHP_SELF'] != '/reviews/start.php' and $_SERVER['PHP_SELF'] != '/reviews/finish.php') {
    header("location: " . $protocol. $_SERVER['HTTP_HOST'] . $cfg_root_path . "/reviews/");
  }
}

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
  while ($stmt->fetch()) {
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
SELECT p.paper_title, p.paper_type, p.labs, p.start_date, p.end_date, max(pa.screen) AS screens, p.calendar_year, p.crypt_name, p.password FROM properties p
INNER JOIN papers pa ON p.property_id = pa.paper
WHERE p.paper_type IN ('0','1','3','6')
AND p.moduleID LIKE ? AND (p.calendar_year = ? OR p.calendar_year = '' OR p.calendar_year IS NULL)
AND p.start_date < NOW() AND p.end_date > NOW()
AND p.deleted IS NULL
GROUP BY p.property_id
ORDER BY p.paper_title
QUERY;

for ($i = 0; $i < count($modules); $i++) {
  $mod_id = $modules[$i]['id'];
	if ($stmt = $mysqli->prepare($papers_query)) {
		$mod_string = '%'.$mod_id.'%';
	  $stmt->bind_param('ss', $mod_string, $modules[$i]['year']);
	  $stmt->execute();
	  $stmt->bind_result($paper_title, $paper_type, $labs, $start_date, $end_date, $screens, $calendar_year, $crypt_name, $password);
	  $stmt->store_result();
	  while ($stmt->fetch()) {
	  	// Check if the user is able to access the paper from their current location
	  	$lab_arr = (empty($labs)) ? array() : explode(',', $labs);
	  	if (empty($lab_arr) or ($lab != -1 and in_array($lab, $lab_arr))) {
	  		$screens = (empty($screens)) ? 0 : $screens;
	  		
	  		// Don't show if 0 screens
	  		if ($screens > 0) {
					$modules[$i]['papers'][] = array('title' =>$paper_title, 'type' => $paper_type, 'start' => $start_date, 'end' => $end_date, 'screens' => $screens, 'crypt_name' => $crypt_name, 'password' => $password);
					$papers++;
					
					if (!in_array($modules[$i]['year'], $sessions_with_papers)) {
						$sessions_with_papers[] = $modules[$i]['year'];
					}
	  		}
	  	}
	  }
    $stmt->close();
  }
}

// Get which papers a student has taken (for feedback purposes).
$papers_taken = array();
$log_query = "SELECT DISTINCT q_paper FROM log2 WHERE userID=?";
$stmt = $mysqli->prepare($log_query);
$stmt->bind_param('i', $userID);
$stmt->execute();
$stmt->bind_result($q_paper);
while ($stmt->fetch()) {
  $papers_taken[] = $q_paper;
}
$stmt->close(); 

// Get any objectives-based feedback released.
$feedback_query = <<< QUERY
SELECT paper_id, calendar_year, paper_title, crypt_name, f.type, p.start_date FROM feedback_release f, properties p
WHERE f.paper_id=p.property_id
AND NOW() > f.date
AND p.paper_type IN ('0','1','2')
AND p.moduleID LIKE ?
AND (p.calendar_year = ? OR p.calendar_year = '' OR p.calendar_year IS NULL)
AND p.end_date < NOW()
ORDER BY p.paper_title
QUERY;

for ($i = 0; $i < count($modules); $i++) {
  $mod_id = $modules[$i]['id'];
	if ($stmt = $mysqli->prepare($feedback_query)) {
		$mod_string = '%' . $mod_id . '%';
	  $stmt->bind_param('ss', $mod_string, $modules[$i]['year']);
	  $stmt->execute();
	  $stmt->bind_result($paper_id, $calendar_year, $paper_title, $crypt_name, $feedback_type, $start_date);
	  $stmt->store_result();
	  while ($stmt->fetch()) {
      if (in_array($paper_id, $papers_taken)) {
        $modules[$i]['papers'][] = array('title' =>$paper_title, 'type' => $feedback_type, 'start' => $start_date, 'end' => 0, 'screens' => 1, 'crypt_name' => $crypt_name);
        $papers++;
        
        if (!in_array($modules[$i]['year'], $sessions_with_papers)) {
          $sessions_with_papers[] = $modules[$i]['year'];
        }
      }
	  }
	
    $stmt->close();
  }
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  
  <title>Rogō<?php echo " $cfg_install_type"; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/announcements.css" />
  <style type="text/css">
    body {padding-left:0px}
  </style>

  <script src="../js/student_help.js" type="text/javascript"></script>
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
<div id="content" class="content">
	<table cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
      <td rowspan="2"style="background-color:#F1F5FB; height:70px; padding-left:10px">
        <img src="../artwork/r_logo.gif" width="56" height="60" alt="logo" border="0" style="float:left; padding-right:8px" />
        <div style="color:#1F497D; font-size:28pt; font-weight:bold">Rogō</div>
        <div style="color:#1F497D; font-size:9pt"><?php echo $string['eassessmentmanagementsystem']; ?></div>
      </td>
      <td style="background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(1); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="Help" border="0" /></a></td>
    </tr>
	  <tr>
	    <td style="background-color:#F1F5FB; text-align:right; vertical-align:bottom">
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
// Check for any news/announcements
$student_msg = '';
$result = $mysqli->prepare("SELECT title, student_msg, icon FROM announcements WHERE NOW() > startdate AND NOW() < enddate AND deleted IS NULL");
$result->execute();
$result->bind_result($news_title, $student_msg, $icon);
$result->fetch();
$result->close();

if ($student_msg != '') {
  $news_icons = array('', 'news_64.png', 'new_64.png', 'tip_64.png', 'software_64.png', 'exclamation_64.png', 'sync_64.png', 'megaphone_64.png');
  echo "<br /><div class=\"announcement\"><div style=\"padding-left:80px; background: transparent url('../artwork/" . $news_icons[$icon] . "') no-repeat top left;\"><strong>$news_title</strong><br />\n<br />\n$student_msg</div></div>\n<br />\n";
}
  
if ($papers > 0) {
	$last_session = '';
	
	foreach($modules as $module) {
	  $mod_id = $module['id'];
		if (!empty($module['papers'])) {
    
    
			if ($module['year'] != $last_session) {
				$visibility = 'style="display: none"';
				if ($module['year'] == $default_session) {
					$visibility = '';
				}
				if ($last_session != '') {
?>
		</div>
<?php
				}
?>
		<div id="papers-<?php echo str_replace('/', '-', $module['year']) ?>"<?php echo $visibility; ?>>
<?php
				$last_session = $module['year'];
			}
?>
			<br clear="all" /><table border="0" style="margin-left:10px; padding-right:2px; padding-bottom:5px; color:#1E3287"><tr><td><nobr><?php echo("<strong>{$mod_id}</strong>: {$module['name']} (".count($module['papers']).")"); ?></nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table>
			<br />
<?php
			foreach($module['papers'] as $paper) {
        if ($paper['type'] == '6') {
          $script_name = '../peer_review/form.php';
        } elseif ($paper['type'] == 'objectives') {
          $script_name = '../mapping/user_feedback.php';
        } elseif ($paper['type'] == 'questions') {
          $script_name = '../paper/feedback.php';
        } else {
          $script_name = '../user_index.php';
        }
?>
			  <div class="file">
			  	<table cellpadding="0" cellspacing="0" border="0">
			  		<tr>
			  			<td style="width:60px" align="center">
								<a href="<?php echo $script_name; ?>?id=<?php echo $paper['crypt_name']; ?>" title="<?php echo htmlentities($paper['title']) ?>" target="_blank"><?php echo(displayIcon($paper['type'], $paper['title'], '', '', '', '')); ?></a>
							</td>
	    				<td>
	    					<a href="<?php echo $script_name; ?>?id=<?php echo $paper['crypt_name']; ?>" title="<?php echo htmlentities($paper['title']) ?>" target="_blank" class="blacklink"><?php echo(htmlentities($paper['title'])); ?></a>
<?php
if (isset($paper['password']) and $paper['password'] != '') {
?>
  <img src="../artwork/key.png" width="16" height="16" alt="Key" /> <span style="color:#C88607; font-weight:bold; font-size:80%"><?php echo $string['passwordRequired'] ?></span><?php
}
?>
                <br />
	    					<span style="color:#808080">
	    						<?php 

                    if ($paper['type'] == 'objectives') {
                      echo $string['objectivesbased'] . ' ' . date(str_replace('%', '', $cfg_long_date_time), strtotime($paper['start']));
                    } elseif ($paper['type'] == 'questions') {
                      echo $string['questionsbased'] . ' ' . date(str_replace('%', '', $cfg_long_date_time), strtotime($paper['start']));
                    } else {
                      echo $paper['screens'] . ' ';
                      if ($paper['screens'] == 1) {
                        echo $string['screen'];
                      } else {
                        echo $string['screens'];
                      }
                      echo '<br />';
                      echo date(str_replace('%', '', $cfg_long_date_time), strtotime($paper['start'])) . ' ' . $string['to'] . ' ' . date(str_replace('%', '', $cfg_long_date_time), strtotime($paper['end']));
                    }
                  ?>
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
	<p style="margin-left:20px"><?php echo $string['nopapers']; ?></p>
<?php
}
?>
</div>
</body>
</html>
