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
* @author Rob Ingram, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require_once '../include/icon_display.inc';
require_once '../config/index.inc';

// Redirect special users if they are straying
if ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer')) {
  $cfg_root_path = $configObject->get('cfg_root_path');
  header("location: ../reviews/");
  exit();
}

function drawTabs($tab_array, $current_tab) {
	$html = '<table cellpadding="0" cellspacing="0" border="0" style="font-size:100%; float: right"><tr>';
	foreach($tab_array as $individual_tab) {
		$button_id = 'button-'.str_replace('/', '-', $individual_tab);
		if ($individual_tab == $current_tab) {
			$html .= "<td id=\"{$button_id}\" class=\"tabon\" onclick=\"switchYear('{$individual_tab}'); return false;\">$individual_tab</td>";
		} else {
			$html .= "<td id=\"{$button_id}\" class=\"taboff\" onclick=\"switchYear('{$individual_tab}'); return false;\">$individual_tab</td>";
		}
	}
	$html .= "</tr></table>\n";
	
	return $html;
}

$sessions_with_papers = array();

$performance_summary_years = array();

if ($userObject->has_role('Student')) {
  $logger = new Logger($mysqli);
  $logger->record_access($userObject->get_user_ID(), 'Student homepage', '/students/');

  $current_address = NetworkUtils::get_client_address();

  // Check if our student is in a lab
  $lab_info = $mysqli->prepare("SELECT lab FROM client_identifiers WHERE address = ? LIMIT 1");
  $lab_info->bind_param('s', $current_address);
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
  if ($stmt = $mysqli->prepare("SELECT DISTINCT idMod, m.moduleid, m.fullname, sm.calendar_year FROM modules m INNER JOIN modules_student sm ON m.id = sm.idMod WHERE sm.userID = ? AND m.active = 1 AND mod_deleted IS NULL AND calendar_year != '' ORDER BY sm.calendar_year ASC, m.moduleid ASC")) {
    $stmt->bind_param('i', $userObject->get_user_ID());
    $stmt->execute();
    $stmt->bind_result($idMod, $moduleID, $module_name, $module_year);
    while ($stmt->fetch()) {
      $modules[$i]['idMod'] = $idMod;
      $modules[$i]['id']    = $moduleID;
      $modules[$i]['name']  = $module_name;
      $modules[$i]['year']  = $module_year;
      $i++;
    }
  }
  $stmt->close();

  // Get papers for this module - types 0, 1, 3, 6 valid for this date
  $papers = 0;
  $papers_query = <<< QUERY
  SELECT p.paper_title, p.paper_type, p.labs, p.start_date, p.end_date, max(pa.screen) AS screens, p.calendar_year, p.crypt_name, p.password FROM (properties p, properties_modules pm)
  INNER JOIN papers pa ON p.property_id = pa.paper
  WHERE p.paper_type IN ('0', '1', '3', '6')
  AND p.property_id = pm.property_id
  AND idMod = ?
  AND (p.calendar_year = ? OR p.calendar_year = '' OR p.calendar_year IS NULL)
  AND p.start_date < NOW() AND p.end_date > NOW()
  AND p.deleted IS NULL
  GROUP BY p.property_id
  ORDER BY p.paper_title
QUERY;

  for ($i = 0; $i < count($modules); $i++) {

    if ($stmt = $mysqli->prepare($papers_query)) {

      $stmt->bind_param('is', $modules[$i]['idMod'], $modules[$i]['year']);
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
            $modules[$i]['papers'][] = array('title' =>$paper_title, 'type' => $paper_type, 'original_type' => $paper_type, 'start' => $start_date, 'end' => $end_date, 'screens' => $screens, 'crypt_name' => $crypt_name, 'password' => $password);
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
  $types = array(0, 1, 2, 5);
  foreach ($types as $type) {
    $log_query = "SELECT DISTINCT paperID FROM log$type, log_metadata WHERE log$type.metadataID = log_metadata.id AND userID = ?";
    $stmt = $mysqli->prepare($log_query);
    $stmt->bind_param('i', $userObject->get_user_ID());
    $stmt->execute();
    $stmt->bind_result($paperID);
    while ($stmt->fetch()) {
      $papers_taken[] = $paperID;
    }
    $stmt->close();
  }

  $log_query = "SELECT DISTINCT q_paper FROM log4_overall WHERE userID = ?";
  $stmt = $mysqli->prepare($log_query);
  $stmt->bind_param('i', $userObject->get_user_ID());
  $stmt->execute();
  $stmt->bind_result($q_paper);
  while ($stmt->fetch()) {
    $papers_taken[] = $q_paper;
  }
  $stmt->close();

  // Get any question-based or objectives-based feedback released.
  $feedback_query = <<< QUERY
  SELECT paper_id, calendar_year, paper_title, crypt_name, f.type, paper_type, p.start_date, p.password FROM (feedback_release f, properties p, properties_modules pm)
  WHERE f.paper_id = p.property_id
  AND p.property_id = pm.property_id
  AND idMod = ?
  AND NOW() > f.date
  AND p.paper_type IN ('0', '1', '2', '4', '5')
  AND (p.calendar_year = ? OR p.calendar_year = '' OR p.calendar_year IS NULL)
  AND p.end_date < NOW()
  ORDER BY p.paper_title
QUERY;

  for ($i = 0; $i < count($modules); $i++) {
    if ($stmt = $mysqli->prepare($feedback_query)) {
      $stmt->bind_param('is', $modules[$i]['idMod'], $modules[$i]['year']);
      $stmt->execute();
      $stmt->bind_result($paper_id, $calendar_year, $paper_title, $crypt_name, $feedback_type, $paper_type, $start_date, $password);
      $stmt->store_result();
      while ($stmt->fetch()) {
        if (in_array($paper_id, $papers_taken)) {
          if ($feedback_type == 'objectives' or $feedback_type == 'questions') {
            $modules[$i]['papers'][] = array('title' =>$paper_title, 'type' => $feedback_type, 'original_type' => $paper_type, 'start' => $start_date, 'end' => 0, 'screens' => 1, 'crypt_name' => $crypt_name, 'password' => $password);
            $papers++;
          } elseif ($feedback_type == 'cohort_performance') {
            $performance_summary_years[$calendar_year] = true;
          }

          if (!in_array($modules[$i]['year'], $sessions_with_papers)) {
            $sessions_with_papers[] = $modules[$i]['year'];
          }
        }
      }

      $stmt->close();
    }
  }
}

$paper_utils = Paper_utils::get_instance();

$textsize = 100;
$font = 'Arial';
if ($userObject->is_special_needs()) {
  // Look up special_needs data
  $textsize = $userObject->get_textsize($textsize);
  $font = $userObject->get_font($font);
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Rog&#333;<?php echo ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css"/>
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/tabs.css" />
  <link rel="stylesheet" type="text/css" href="../css/announcements.css" />
  <style type="text/css">
    body {padding-left:0; font-size:<?php echo $textsize ?>%; font-family:<?php echo $font ?>}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script type="text/javascript" src="../js/student_help.js"></script>
  <script>
		function switchYear(toShow) {
			var years = ['<?php echo implode('\',\'', $sessions_with_papers) ?>'];
			for (var i = 0; i < years.length; i++) {
				target = document.getElementById('papers-' + years[i].replace('/', '-'));
				link = document.getElementById('button-' + years[i].replace('/', '-'));
				if (target != null) {
					target.style.display = (years[i] == toShow) ? 'block' : 'none';
					if (link != null) {
						link.style.backgroundColor = (years[i] == toShow) ? '#1E3C7B' : '#517DBF';
					}
				}
			}
		}
  </script>
  <?php require_once '../include/toprightmenu.inc'; ?>
</head>
<body>
<?php
	echo draw_toprightmenu();
?>
<div id="content">
	<table cellpadding="0" cellspacing="0" border="0" class="header">
		<tr>
      <th style="padding-left:16px; padding-top:5px">
        <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />
				<div class="logo_lrg_txt">Rog&#333;</div>
				<div class="logo_small_txt"><?php echo $string['eassessmentmanagementsystem']; ?></div>
      </th>
      <th style="text-align:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></th>
    </tr>
	  <tr>
	    <td colspan="2" style="background-color:#EEF4FF; text-align:right; vertical-align:bottom">
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
if (!$userObject->has_role('Student')) {
?>
   <p style="margin-left:20px"><?php echo $string['staffmsg']; ?></p>
<?php
} else {
  // Check for any news/announcements
  $announcements = announcement_utils::get_student_announcements($mysqli);  
  foreach ($announcements as $announcement) {
    echo "<div class=\"announcement\"><div style=\"min-height:64px; padding-left:80px; padding-top:5px; background: transparent url('../artwork/" . $announcement['icon'] . "') no-repeat 5px 5px;\"><strong>" . $announcement['title'] . "</strong><br />\n<br />\n" . $announcement['msg'] . "</div></div>\n";
  }

  if ($papers > 0) {
  	$last_session = '';

  	foreach ($modules as $module) {
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
		<div id="papers-<?php echo str_replace('/', '-', $module['year']) ?>"<?php echo $visibility ?>>
<?php
  				$last_session = $module['year'];

          if (isset($performance_summary_years[$module['year']])) {
            echo "<div style=\"margin-top:4px; margin-left:10px\"><input type=\"button\" onclick=\"window.location='performance_summary.php#" . $module['year'] . "'\" value=\"" . $string['performsummary'] . "\" /></div>";
          }
        }
?>

      <br clear="all" /><table border="0" style="margin-left:10px; padding-right:2px; padding-bottom:5px; color:#1E3287"><tr><td><nobr><?php echo("<strong>{$mod_id}</strong>: {$module['name']}"); ?></nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table>
			<br />
<?php
  			foreach ($module['papers'] as $paper) {
          if ($paper['type'] == '6') {
            $script_name = '../peer_review/form.php?id=' . $paper['crypt_name'];
          } elseif ($paper['type'] == 'objectives') {
            $script_name = 'objectives_feedback.php?id=' . $paper['crypt_name'];
          } elseif ($paper['type'] == 'questions') {
            if ($paper['original_type'] == '4') {
              $script_name = '../osce/view_form.php?id=' . $paper['crypt_name'];
            } else {
              $script_name = '../students/question_feedback.php?id=' . $paper['crypt_name'];
            }
          } else {
            $script_name = '../paper/user_index.php?id=' . $paper['crypt_name'];
          }
?>
			  <div class="file">
			  	<table cellpadding="0" cellspacing="0" border="0">
			  		<tr>
			  			<td style="width:60px" align="center">
								<a href="<?php echo $script_name; ?>" title="<?php echo htmlentities($paper['title']) ?>" target="_blank"><?php echo($paper_utils->displayIcon($paper['type'], $paper['title'], '', '', '', '')); ?></a>
							</td>
	    				<td>
	    					<a href="<?php echo $script_name; ?>" title="<?php echo htmlentities($paper['title']) ?>" target="_blank" class="blacklink"><?php echo(htmlentities($paper['title'])); ?></a>
<?php
  if (isset($paper['password']) and $paper['password'] != '') {
?>
  <img src="../artwork/key.png" width="16" height="16" alt="Key" /> <span style="color:#C88607; font-weight:bold; font-size:80%"><?php echo $string['passwordRequired'] ?></span>
<?php
  }
?>
                <br />
	    					<span style="color:#808080">
	    						<?php

                    if ($paper['type'] == 'objectives') {
                      echo $string['objectivesbased'] . ' ' . date(str_replace('%', '', $configObject->get('cfg_long_date_time')), strtotime($paper['start']));
                    } elseif ($paper['type'] == 'questions') {
                      echo $string['questionsbased'] . ' ' . date(str_replace('%', '', $configObject->get('cfg_long_date_time')), strtotime($paper['start']));
                    } else {
                      echo $paper['screens'] . ' ';
                      if ($paper['screens'] == 1) {
                        echo $string['screen'];
                      } else {
                        echo $string['screens'];
                      }
                      echo '<br />';
                      echo date(str_replace('%', '', $configObject->get('cfg_long_date_time')), strtotime($paper['start'])) . ' ' . $string['to'] . ' ' . date(str_replace('%', '', $configObject->get('cfg_long_date_time')), strtotime($paper['end']));
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
	 <p style="margin-left:20px"><?php echo $string['nopapers'] ?></p>
<?php
  }
}
?>
</div>
</body>
</html>
