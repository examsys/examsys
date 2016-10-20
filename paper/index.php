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
 * This script is the summative exam homepage of Rogo.
 * It takes the user details of the student together with the IP address
 * for the log and redirects to the correct paper.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_student_auth.inc';

// Redirect special users to their own areas.
if ($userObject->has_role('External Examiner') or $userObject->has_role('Internal Reviewer')) {
  header("location: ../reviews/");
  exit();
} elseif ($userObject->has_role('Invigilator')) {
  header("location: ../invigilator/");
  exit();
}

function displayHead($string) {
	$html = '';
	
	$html .= '<table cellpadding="0" cellspacing="0" border="0" class="header">';
  $html .= '<tr>';
  $html .= '  <th style="padding-left:16px; padding-top:5px">';
  $html .= '  <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />';
  $html .= '  <div class="logo_lrg_txt">Rog&#333;</div>';
  $html .= '  <div class="logo_small_txt">' . $string['eassessmentmanagementsystem'] . '</div>';
  $html .= '  </th>';
  $html .= '  <th style="text-align:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></th>';
  $html .= '</tr>';
	$html .= '</table>';
	
	return $html;
}

function display_duration($duration, $string, &$warnings) {
  if ($duration == '' or $duration == 0) {
		$warnings[] = $string['nodurationwarning'];
		$html = '';
  } else {
    $html = $duration . $string['mins'];
  }

  return $html;
}

function display_warning($text) {
  return '<img class="warning-img" alt="' . $text . '" title="' . $text . '" src="../artwork/small_yellow_warning_icon.gif" />';
}

function get_labs($mysqli, $lablist) {
  $lab_list = array();
  if ($lablist != '') {
    $stmt = $mysqli->prepare("SELECT room_no, name FROM labs WHERE id IN ({$lablist})");
    $stmt->execute();
    $stmt->bind_result($room_no, $name);
    while ($stmt->fetch()) {
      $lab_list[] = ($room_no == '') ? $name : $room_no;
    }
    $stmt->close();
  }

  return $lab_list;
}

function display_labs($labs, $computer_lab, $string, &$warnings) {
  if (count($labs) == 0) {
		$warnings[] = $string['nolabswarning'];
		$html = '';
  } else {
    $html = ', <span class="labs">';
    $first = true;
    foreach ($labs as $lab) {
      if ($first) {
        $first = false;
      } else {
        $html .= ', ';
      }
      $html .= ($lab == $computer_lab) ? '<span class="current">' . $lab . '</span>' : $lab;
    }
    $html .= '</span>';
  }

  return $html;
}

$logger = new Logger($mysqli);
$logger->record_access($userObject->get_user_ID(), 'Summative homepage', '/paper/');

$paper_utils = Paper_utils::get_instance();
$paper_display = array();
$paper_no = $paper_utils->get_active_papers($paper_display, array('1', '2'), $userObject, $mysqli);		// Get active Progress Tests and Summative Exams.

if ($paper_no == 1 and $paper_display[0]['password'] == '') {
  header("location: user_index.php?id=" . $paper_display[0]['crypt_name']);
  exit();
} elseif ($paper_no == 0) {
  echo "<html>\n<head>\n<meta http-equiv=\"content-type\" content=\"text/html;charset={$configObject->get('cfg_page_charset')}\" />\n<title>{$string['exams']}</title>\n";
  ?>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset'); ?>"/>

  <title><?php echo $string['exams']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css"/>
  <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css"/>
  <link rel="stylesheet" type="text/css" href="../css/index.css"/>
	
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
<?php
	if ($userObject->has_role('Staff')) {
		echo '<script type="text/javascript" src="../js/staff_help.js"></script>';
	} else {
		echo '<script type="text/javascript" src="../js/student_help.js"></script>';
	}
  
  require_once '../include/toprightmenu.inc';
?>
</head>
<body>
  <?php
	echo draw_toprightmenu();
	
	echo displayHead($string);
	
	echo "<div style=\"font-size:90%; padding-top:20px\">\n";
  echo "<div style=\"float:left; padding-left:16px; padding-top:16px\"><img src=\"{$configObject->get('cfg_root_path')}/artwork/exclamation_48.png\" width=\"48\" height=\"48\" /></div>\n";
  echo "<h1 style=\"margin-left:90px; color:#C00000; font-weight:bold\">" . $string['noexamsfound'] . "</h1>\n";

  if ($userObject->has_role('Staff')) {
    echo "<p style=\"margin-left:90px; color:#C00000\">" . $string['note1'] . " <img src=\"{$configObject->get('cfg_root_path')}/artwork/small_link.png\" width=\"11\" height=\"11\" /> <a href=\"../index.php\"><strong>" . $string['staffmangscreens'] . "</strong></a>?</p>\n";
  }

  echo "<div class=\"hr_line\"></div>\n<p style=\"margin-left:90px\">" . $string['mostLikely'] . "</p>\n<ul style=\"margin-left:80px\">\n";

  $current_address = NetworkUtils::get_client_address();
  $ip_info = $mysqli->prepare("SELECT name, room_no FROM (labs, client_identifiers) WHERE labs.id = client_identifiers.lab AND address = ?");
  $ip_info->bind_param('s', $current_address);
  $ip_info->execute();
  $ip_info->store_result();
  $ip_info->bind_result($computer_lab, $computer_lab_short);
  $ip_info->fetch();
  if ($ip_info->num_rows() == 0) {
    $computer_lab = $computer_lab_short = '<span style="color:#C00000">' . $string['unknownip'] . '</span>';
  }
  $computer_lab_short = ($computer_lab_short == '') ? $computer_lab : $computer_lab_short;
  $ip_info->close();
  echo "<li>" . $string['IPaddress'] . " - " . NetworkUtils::get_client_address() . " $computer_lab</li>\n";
  echo "<li>" . $string['Time/Date'] . " - " . date('d/m/Y H:i:s') . "</li>\n";
  echo "<li>" . $string['yearofstudy'] . " - ";

  if ($userObject->get_year() == '') {
    echo '<span style="color:#C00000">' . $string['noyear'] . '</span>';
  } else {
    echo $userObject->get_year();
  }
  echo "</li>\n";
  echo "<li>" . $string['Modules'] . " - \n";


  $last_cal_year = '';
  $info = $mysqli->prepare("SELECT moduleID, calendar_year FROM modules_student, modules WHERE modules.id = modules_student.idMod AND userID = ? ORDER BY calendar_year DESC, moduleID");
  $info->bind_param('i', $userObject->get_user_ID());
  $info->execute();
  $info->bind_result($user_moduleID, $user_calendar_year);
  $info->store_result();
  if ($info->num_rows() == 0) {
    echo '<span style="background-color:#C00000; color:white">&nbsp;' . $string['nomodules'] . '&nbsp;</span>';
  } else {
    while ($info->fetch()) {
      if ($last_cal_year != $user_calendar_year) {
				$i = 0;
        echo "<br /><strong>" . $user_calendar_year . ":</strong>&nbsp;";
      }
			if ($i > 0) echo ', ';
      echo $user_moduleID;
      $last_cal_year = $user_calendar_year;
      $i++;
    }
  }
  $info->close();
  echo "</li>\n";
  echo '<li>' . $string['UserRoles'] . ' - ';
  $userRolesArray = $userObject->list_user_roles();

  foreach ($userRolesArray as $key => $ur) {
    if ($ur != 'Student') {
      $ur = str_replace('Demo', '', $ur);
      if ($ur != '') {
        echo '<span style="color:#C00000">' . $string[strtolower($ur)] . '</span>';
        if ($key < count($userRolesArray) - 1) {
          echo ', ';
        }
      }
    } else {
      echo $string[strtolower($ur)];
      if ($key < count($userRolesArray) - 1) {
        echo ', ';
      }
    }
  }
  echo "</li>\n</ul>\n<p style=\"margin-left:90px\">" . $string['try'] . ":</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['f5'] . "</li>\n<li>" . $string['RaiseYourHand '] . "</li>\n</ul>\n";

  // Show staff a list of summative papers in the next 6 weeks with a link to test & preview
  if ($userObject->has_role('Staff')) {
    if (!isset($staff_modules)) {
      $staff_modules = $userObject->get_staff_modules();
    }
    $papers = array();
    foreach ($staff_modules as $idMod => $moduleID) {
      $paper_q = $mysqli->prepare("SELECT DISTINCT properties.property_id, MAX(screen) AS screens, paper_title, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}') AS display_start_date, exam_duration, crypt_name, fullscreen, labs FROM properties LEFT JOIN papers ON properties.property_id = papers.paper LEFT JOIN properties_modules ON properties.property_id = properties_modules.property_id WHERE paper_type='2' AND start_date > NOW() AND start_date < DATE_ADD(NOW(), INTERVAL 42 DAY) AND idMod = ?  AND deleted IS NULL AND retired IS NULL GROUP BY paper_title HAVING MAX(screen) > 0 ORDER BY paper_type, paper_title");
      $paper_q->bind_param('i', $idMod);
      $paper_q->execute();
      $paper_q->store_result();
      $paper_q->bind_result($property_id, $screens, $paper_title, $start_date, $exam_duration, $crypt_name, $fullscreen, $labs);
      while ($paper_q->fetch()) {
        $papers[$moduleID][] = array('id' => $property_id, 'screens' => $screens, 'title' => $paper_title, 'start_date' => $start_date, 'duration' => $exam_duration, 'crypt_name' => $crypt_name, 'fullscreen' => $fullscreen, 'labs' => $labs);
      }
      $paper_q->close();
    }
    if (count($papers) > 0) {
      ?>
    <div id="summ_test">
        <h2 class="dkblue_header"><?php echo $string['summativetesting'] ?></h2>

        <p><?php echo $string['summativetestmsg'] ?></p>
      <?php
      $staff_module = '';
      foreach ($papers as $moduleID => $paper_list) {
        if ($moduleID != $staff_module) {
          $staff_module = $moduleID;
          echo "<table style=\"clear:both; font-size:100%\"><tr><td class=\"subsect\"><nobr>$moduleID</nobr></td><td style=\"width:98%\"><hr class=\"head-line\" /></td></tr></table>\n";
        }
        foreach ($paper_list as $paper) {
					$warnings = array();
					
          $screen_plural = ($paper['screens'] > 1) ? 'screens' : 'screen';
          $start_hour = substr($paper['start_date'], 11, 2);
					if (intval($start_hour) < $configObject->get('cfg_hour_warning')) {
					  $warnings[] = sprintf($string['startwarning'], $configObject->get('cfg_hour_warning'));
					}

          $labs = get_labs($mysqli, $paper['labs']);
          $lab_html = display_labs($labs, $computer_lab_short, $string, $warnings);
          ?>
            <div class="file">
                <table cellpadding="0" cellspacing="0" border="0" style="font-size:100%">
                    <tr>
                        <td style="width:60px; vertical-align:top"><a class="blacklink" href="user_index.php?id=<?php echo $paper['crypt_name'] ?>&mode=preview" rel="<?php echo $paper['fullscreen'] ?>"><img src="../artwork/summative.png" width="48" height="48" alt="Type: Summative Exam" border="0"/></a></td>
                        <td>
                            <a href="user_index.php?id=<?php echo $paper['crypt_name'] ?>&mode=preview" class="blacklink" rel="<?php echo $paper['fullscreen'] ?>"><?php echo $paper['title'] ?></a><br />
                            <span class="subtext"><?php echo $paper['screens'] . ' ' . ucfirst($string[$screen_plural]) . '<br />' . $paper['start_date'] . ', ' . display_duration($paper['duration'], $string, $warnings) ?></span><?php
														echo $lab_html;
														foreach ($warnings as $warning) {
														  echo "<div class=\"warning\">" . display_warning($warning) . "$warning</div>\n";
														}
														?>
                        </td>
                    </tr>
                </table>
            </div>
          <?php
        }
      }
      ?>
    </div>
      <?php
    }
  }

  echo "</div>\n</body>\n</html>\n";
  exit;
} else {
  ?>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
	<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset'); ?>"/>

	<title><?php echo $string['exams']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css"/>
  <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css"/>
  <link rel="stylesheet" type="text/css" href="../css/index.css"/>
	
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <?php

    if ($userObject->has_role('Staff')) {
        echo '<script type="text/javascript" src="../js/staff_help.js"></script>';
    } else {
        echo '<script type="text/javascript" src="../js/student_help.js"></script>';
    }

    require_once '../include/toprightmenu.inc';
  ?>
</head>
<body>
<?php
	echo draw_toprightmenu();

  if ($paper_no > 1) {
		echo displayHead($string);

		echo "<div style=\"margin:16px\">";
    echo "<h1>" . $string['multipleExams'] . "</h1>\n";
    echo "<p>" . $string['selectOne'] . "</p>\n";
  }
  echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
  for ($i = 0; $i < $paper_no; $i++) {
    if ($paper_display[$i]['password'] == '') {
      echo "<tr><td width=\"66\" style=\"text-align:right\"><a href=\"user_index.php?id=" . $paper_display[$i]['crypt_name'] . "\">" . Paper_utils::displayIcon($paper_display[$i]['paper_type'], '', '', '', '', '') . "</a></td>\n";
      echo "<td><a href=\"user_index.php?id=" . $paper_display[$i]['crypt_name'] . "\">" . $paper_display[$i]['paper_title'] . "</a>";
    } else {
      echo "<tr><td width=\"66\" style=\"text-align:right\"><a href=\"user_index.php?id=" . $paper_display[$i]['crypt_name'] . "\">" . Paper_utils::displayIcon($paper_display[$i]['paper_type'], '', '', '', '', '') . "</a></td>\n";
      echo "<td><a href=\"user_index.php?id=" . $paper_display[$i]['crypt_name'] . "\">" . $paper_display[$i]['paper_title'] . "</a>";
      echo ' <img src="../artwork/key.png" width="16" height="16" alt="Key" /> <span style="color:#C88607; font-weight:bold; font-size:80%">' . $string['passwordRequired'] . '</span>';
    }
    if ($paper_display[$i]['completed'] == '') {
      echo '<br /><span style="color:#808080; font-size:80%">(' . $paper_display[$i]['max_screen'];
      if ($paper_display[$i]['max_screen'] == 1) {
        echo ' ' . $string['screen'] . ', ';
      } else {
        echo ' ' . $string['screens'] . ', ';
      }
      if ($paper_display[$i]['bidirectional'] == 1) {
        echo $string['Bidirectional'];
      } else {
        echo $string['Unidirectional'];
      }
      echo ")</span>";
    } else {
      echo '<br /><span class="finished">' . $string['finished'] . '</span>';
    }
    echo "</td></tr>\n";
  }
  echo "</table>\n</div>\n";
}
$mysqli->close();
?>
</body>
</html>
