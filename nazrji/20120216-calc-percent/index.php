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
* This script is the homepage of Internet Explorer when GTZEXAM1 logs in.
* It takes the user details of the student together with the IP address
* for the log and redirects to the correct paper.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require './include/staff_student_auth.inc';
require_once './classes/networkutils.class.php';

  // Redirect External Exminers and Invigilators to their own areas.
  if ($userroles == 'External Examiner') {
    header("location: reviews/");
  } elseif ($userroles == 'Invigilator') {
    header("location: invigilator/");
  }

  function displayIcon($paper_type) {
    switch ($paper_type) {
      case 0:
        $html = "<img src=\"./artwork/formative.png\" width=\"48\" height=\"48\" alt=\"Type: Formative\" border=\"0\" />";
        break;
      case 1:
        $html = "<img src=\"./artwork/progress.png\" width=\"48\" height=\"48\" alt=\"Type: Progress\" border=\"0\" />";
        break;
      case 2:
        $html = "<img src=\"./artwork/summative.png\" width=\"48\" height=\"48\" alt=\"Type: Summative\" border=\"0\" />";
        break;
      case 3:
        $html = "<img src=\"./artwork/survey.png\" width=\"48\" height=\"48\" alt=\"Type: Survey\" border=\"0\" />";
        break;
    }
    return $html;
  }

  $paper_no = 0;
  $paper_display = array();
  
  $paper_query = $mysqli->prepare("SELECT paper_type, crypt_name, paper_title, bidirectional, fullscreen, MAX(screen) AS max_screen, labs, moduleID, calendar_year, password FROM (papers, properties) WHERE papers.paper=properties.property_id AND labs != '' AND (paper_type='1' OR paper_type='2') AND deleted IS NULL AND start_date < DATE_ADD(NOW(),interval 15 minute) AND end_date > NOW() GROUP BY paper");
  $paper_query->execute();
  $paper_query->store_result();
  $paper_query->bind_result($paper_type, $crypt_name, $paper_title, $bidirectional, $fullscreen, $max_screen, $labs, $moduleID, $calendar_year, $password);
  while ($paper_query->fetch()) {
    if ($labs != '') {
      $machineOK = false;
      $labs = str_replace(","," OR lab=",$labs);
      $lab_info = $mysqli->query("SELECT address FROM ip_addresses WHERE address='" . NetworkUtils::get_ipaddress() . "' AND (lab=$labs)");
      if ($lab_info->num_rows > 0) $machineOK = true;
      $lab_info->close();
    } else {
      $machineOK = true;
    }
    if (strpos($_SERVER['PHP_AUTH_USER'], 'user') !== 0) {
      if ($moduleID != '') {
        $moduleOK = false;
        if ($calendar_year != '') {
          $cal_sql = "AND calendar_year = '" . $calendar_year . "'";
        } else {
          $cal_sql = '';
        }
        $moduleInfo = $mysqli->prepare("SELECT userID FROM student_modules WHERE userID=? $cal_sql AND moduleID IN ('" . str_replace(",","','",$moduleID) . "')");
        $moduleInfo->bind_param('i', $userID);
        $moduleInfo->execute();
        $moduleInfo->store_result();
        $moduleInfo->bind_result($tmp_userID);
        $moduleInfo->fetch();
        //$moduleInfo = $mysqli->query("SELECT userID FROM student_modules WHERE userID=$userID $cal_sql AND moduleID IN ('" . str_replace(",","','",$moduleID) . "')");
        if ($moduleInfo->num_rows() > 0) $moduleOK = true;
        $moduleInfo->close();
      } else {
        $moduleOK = true;
      }
    } else {
      $moduleOK = true;
    }
    if ($machineOK == true AND $moduleOK == true) {
      $paper_display[$paper_no]['paper_title'] = $paper_title;
      $paper_display[$paper_no]['crypt_name'] = $crypt_name;
      $paper_display[$paper_no]['paper_type'] = $paper_type;
      $paper_display[$paper_no]['max_screen'] = $max_screen;
      $paper_display[$paper_no]['bidirectional'] = $bidirectional;
      $paper_display[$paper_no]['password'] = $password;
      $paper_no++;
    }
  }
  $paper_query->close();

  if ($paper_no == 1 and $paper_display[0]['password'] == '') {
    header("location: user_index.php?id=" . $paper_display[0]['crypt_name']);
  } elseif ($paper_no == 0) {
    echo "<html>\n<head>\n<title>" . $string['exams']. "</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#4465A2; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/artwork/orange_alert_48.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">" . $string['cannotfindexams'] . "</h1>\n"; 

    if (strpos($userroles,'Staff') !== false) {
      echo "<p style=\"margin-left:60px; color:#C00000\">" . $string['note1'] . " <img src=\"/artwork/small_link.png\" width=\"12\" height=\"12\" /> <a href=\"staff/index.php\" style=\"color:blue\"><strong>" . $string['staffmangscreens'] . "</strong></a>?</p>\n";
    }

    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">" . $string['mostLikely'] . "</p>\n<ul style=\"margin-left:80px\">\n";

    $current_ip_address = NetworkUtils::get_ipaddress();
    $ip_info = $mysqli->prepare("SELECT name FROM (labs, ip_addresses) WHERE labs.id=ip_addresses.lab AND address=?");
    $ip_info->bind_param('s', $current_ip_address);
    $ip_info->execute();
    $ip_info->store_result();
    $ip_info->bind_result($computer_lab);
    $ip_info->fetch();
    if ($ip_info->num_rows() == 0) {
      $computer_lab = '<span style="color:red">' . $string['unknownIp'] . '</span>';
    }
    $ip_info->close();
    echo "<li>" . $string['IPaddress'] . " - " . NetworkUtils::get_ipaddress() . " $computer_lab</li>\n";
    echo "<li>" . $string['Time/Date'] . " - " . date('d/m/Y H:i:s') . "</li>\n";
    echo "<li>" . $string['yearofstudy'] . " - ";
    if ($year == '') {
      echo '<span style="color:red">' . $string['noyear'] . '</span>';
    } else {
      echo $year;
    }
    echo "</li>\n";
    echo "<li>" . $string['Modules'] . " - \n";
    
    
    $last_cal_year = '';
    $i = 0;
    $info = $mysqli->prepare("SELECT moduleID, calendar_year FROM student_modules WHERE userID=? ORDER BY calendar_year DESC, moduleID");
    $info->bind_param('i', $userID);
    $info->execute();
    $info->bind_result($user_moduleID, $user_calendar_year);
    $info->store_result();
    if ($info->num_rows() == 0) {
      echo '<span style="color:red">' . $string['nomodules'] . '</span>';
    } else {
      while ($info->fetch()) {
        if ($last_cal_year != $user_calendar_year) {
          echo "<br/><strong>" . $user_calendar_year . "</strong><br/>";
        }
        echo $user_moduleID . '&nbsp;';
        $last_cal_year = $user_calendar_year;
        $i++;
      }
    }
    $info->close();
    echo "</li>\n";
    echo "<li>" . $string['UserRoles'] . " - ";
    $userRolesArray = explode(',', $userroles);
    foreach ($userRolesArray as $ur) {
      if ($ur != 'Student') {
        echo '<span style="color:red">' . $string[strtolower($ur)] . '</span>,';
      } else {
        echo $string[strtolower($ur)] . ',';
      }
    }
    echo "</li>\n</ul>\n<p style=\"margin-left:60px\">" . $string['try'] . ":</p>\n<ul style=\"margin-left:80px\">\n<li>" . $string['f5'] . "</li>\n<li>" . $string['RaiseYourHand '] . "</li>\n</ul>\n</body>\n</html>\n";
    exit;
  } else {
?>
  <html>
  <head>
  <title><?php echo $string['exams']; ?></title>
  <script language="JavaScript">
    function enterPassword() {
      var password = prompt("<?php echo $string['requirespassword'] ?>","");
      if (password == '' || password == null) {
        return false;
      } else {
        document.cookie = "paperpwd=" + password + "; secure";
        return true;
      }
    }
  </script>
  </head>
  <body style="font-family:Arial,sans-serif">
<?php
    if ($paper_no > 1) {
      echo "<h1>" . $string['multipleExams'] . "</h1>\n";
      echo "<p><em>" . $string['selectOne'] . "</em></p>\n";
    }
    echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
    for ($i=0; $i<$paper_no; $i++) {
      if ($paper_display[$i]['password'] == '') {
        echo "<tr><td width=\"66\" style=\"text-align:right\"><a href=\"user_index.php?id=" . $paper_display[$i]['crypt_name'] . "\">" . displayIcon($paper_display[$i]['paper_type']) . "</a></td>\n";
        echo "<td><a href=\"user_index.php?id=" . $paper_display[$i]['crypt_name'] . "\" style=\"color:blue\">" . $paper_display[$i]['paper_title'] . "</a>";
      } else {
        echo "<tr><td width=\"66\" style=\"text-align:right\"><a onclick=\"return enterPassword();\" href=\"user_index.php?id=" . $paper_display[$i]['crypt_name'] . "\">" . displayIcon($paper_display[$i]['paper_type']) . "</a></td>\n";
        echo "<td><a onclick=\"return enterPassword();\" href=\"user_index.php?id=" . $paper_display[$i]['crypt_name'] . "\" style=\"color:blue\">" . $paper_display[$i]['paper_title'] . "</a>";
        echo ' <img src="./artwork/key.png" width="16" height="16" alt="Key" /> <span style="color:#C88607; font-weight:bold; font-size:80%">' . $string['passwordRequired'] . '</span>';
      }
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
      echo ")</span></td></tr>\n";
    }
    echo "</table>\n";
  }
  $mysqli->close();
?>
</body>
</html>
