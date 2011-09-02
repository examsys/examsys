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
* This script is the homepage of Internet Explorer when GTZEXAM1 logs in.
* It takes the user details of the student together with the IP address
* for the log and redirects to the correct paper.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require './touchstone/include/staff_student_auth.inc';
require_once './touchstone/classes/networkutils.class.php';

  // Redirect External Exminers and Invigilators to their own areas.
  if ($userroles == 'External Examiner') {
    header("location: " . $protocol. $_SERVER['HTTP_HOST'] . "/touchstone/reviews/");
  } elseif ($userroles == 'Invigilator') {
    header("location: " . $protocol. $_SERVER['HTTP_HOST'] . "/touchstone/invigilator/");
  }

  function displayIcon($paper_type) {
    switch ($paper_type) {
      case 0:
        $html = "<img src=\"./touchstone/artwork/formative.png\" width=\"48\" height=\"48\" alt=\"Type: Formative\" border=\"0\" />";
        break;
      case 1:
        $html = "<img src=\"./touchstone/artwork/progress.png\" width=\"48\" height=\"48\" alt=\"Type: Progress\" border=\"0\" />";
        break;
      case 2:
        $html = "<img src=\"./touchstone/artwork/summative.png\" width=\"48\" height=\"48\" alt=\"Type: Summative\" border=\"0\" />";
        break;
      case 3:
        $html = "<img src=\"./touchstone/artwork/survey.png\" width=\"48\" height=\"48\" alt=\"Type: Survey\" border=\"0\" />";
        break;
    }
    return $html;
  }

  $paper_no = 0;
  $paper_display = array();
  $paper_query = $mysqli->query("SELECT paper_type, paper_title, bidirectional, fullscreen, MAX(screen) AS max_screen, labs, moduleID, calendar_year, password FROM (papers, properties) WHERE papers.paper=properties.property_id AND labs != '' AND (paper_type='1' OR paper_type='2') AND deleted IS NULL AND start_date < DATE_ADD(NOW(),interval 15 minute) AND end_date > NOW() GROUP BY paper");
  while ($row = $paper_query->fetch_assoc()) {
    if ($row['labs'] != '') {
      $machineOK = false;
      $labs = str_replace(","," OR lab=",$row['labs']);
      $lab_info = $mysqli->query("SELECT address FROM ip_addresses WHERE address='" . NetworkUtils::get_ipaddress() . "' AND (lab=$labs)");
      if ($lab_info->num_rows > 0) $machineOK = true;
      $lab_info->close();
    } else {
      $machineOK = true;
    }
    if (strpos($_SERVER['PHP_AUTH_USER'], 'user') !== 0) {
      if ($row['moduleID'] != '') {
        $moduleOK = false;
        if($row['calendar_year'] != '') {
          $cal_sql = "AND calendar_year = '" . $row['calendar_year'] . "'";
        } else {
          $cal_sql = '';
        }
        $moduleInfo = $mysqli->query("SELECT userID FROM student_modules WHERE userID=$userID $cal_sql AND moduleID IN ('" . str_replace(",","','",$row['moduleID']) . "')");
        if ($moduleInfo->num_rows > 0) $moduleOK = true;
        $moduleInfo->close();
      } else {
        $moduleOK = true;
      }
    } else {
      $moduleOK = true;
    }
    if ($machineOK == true AND $moduleOK == true) {
      $paper_display[$paper_no]['paper_title'] = $row['paper_title'];
      $paper_display[$paper_no]['paper_type'] = $row['paper_type'];
      $paper_display[$paper_no]['max_screen'] = $row['max_screen'];
      $paper_display[$paper_no]['bidirectional'] = $row['bidirectional'];
      $paper_display[$paper_no]['password'] = $row['password'];
      $paper_no++;
    }
  }
  $paper_query->close();

  if ($paper_no == 1 and $paper_display[0]['password'] == '') {
    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/user_index.php?paper=" . $paper_display[0]['paper_title']);
  } elseif ($paper_no == 0) {
    echo "<html>\n<head>\n<title>Exams</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#4465A2; font-size:140%}\n</style>\n</head>\n<body>\n";
    echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/orange_alert_48.png\" width=\"48\" height=\"48\" /></div>\n";
    echo "<h1 style=\"margin-left:60px\">TouchStone cannot find any Exams</h1>\n";

    if (strpos($userroles,'Staff') !== false) {
      echo "<p style=\"margin-left:60px; color:#C00000\"><strong>Note:</strong> This is the summative exams screen for students, did you want the <img src=\"/touchstone/artwork/small_link.png\" width=\"12\" height=\"12\" /> <a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/\" style=\"color:blue\"><strong>Staff management screens</strong></a>?</p>\n";
    }

    echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Most likely cause is one or more security conflicts with:</p>\n<ul style=\"margin-left:80px\">\n";

    $ip_info = $mysqli->query("SELECT name FROM (labs, ip_addresses) WHERE labs.id=ip_addresses.lab AND address='" . NetworkUtils::get_ipaddress() . "'");
    if ($ip_info->num_rows > 0) {
      $ip_row = $ip_info->fetch_assoc();
      $computer_lab = '(' . $ip_row['name'] . ')';
    } else {
      $computer_lab = '<span style="color:red">(unknown address)</span>';
    }
    $ip_info->close();
    echo "<li>IP address - " . NetworkUtils::get_ipaddress() . " $computer_lab</li>\n";
    echo "<li>Time/Date - " . date('d/m/Y H:i:s') . "</li>\n";
    echo "<li>Academic Year - ";
    if ($year == '') {
      echo '<span style="color:red">no year held for user!</span>';
    } else {
      echo $year;
    }
    echo "</li>\n";
    echo "<li>Modules - \n";
    $info = $mysqli->query("SELECT moduleID, calendar_year FROM student_modules WHERE userID=$userID ORDER BY calendar_year DESC, moduleID");
    $last_cal_year = '';
    $i = 0;
    if ($info->num_rows == 0) {
      echo '<span style="color:red">Warning: no modules registered!</span>';
    } else {
      while ($row = $info->fetch_assoc()) {
        if($last_cal_year != $row['calendar_year']) {
          echo "<br/><b>" . $row['calendar_year'] . "</b><br/>";
        }
        echo $row['moduleID'] . '&nbsp;';
        $last_cal_year = $row['calendar_year'];
        $i++;
      }
    }
    $info->close();
    echo "</li>\n";
    echo "<li>User roles - ";
    if ($userroles != 'Student') {
      echo '<span style="color:red">' . $userroles . '</span>';
    } else {
      echo $userroles;
    }
    echo "</li>\n</ul>\n<p style=\"margin-left:60px\">What you can try:</p>\n<ul style=\"margin-left:80px\">\n<li>If the time is more than 15 minutes before the start of the exam wait until within 15 minutes and press F5.</li>\n<li>Raise your hand for a member of staff.</li>\n</ul>\n</body>\n</html>\n";
    exit;
  } else {
?>
  <html>
  <head>
  <title>Exams</title>
  <script language="JavaScript">
    function enterPassword() {
      var password = prompt("This paper requires a password.","");
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
      echo "<h1>Multiple Exams Found</h1>\n";
      echo "<p><em>Please select the one you are required to take:</em></p>\n";
    }
    echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\">\n";
    for ($i=0; $i<$paper_no; $i++) {
      if ($paper_display[$i]['password'] == '') {
        echo "<tr><td width=\"66\" style=\"text-align:right\"><a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/user_index.php?paper=" . $paper_display[$i]['paper_title'] . "\">" . displayIcon($paper_display[$i]['paper_type']) . "</a></td>\n";
        echo "<td><a href=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/user_index.php?paper=" . $paper_display[$i]['paper_title'] . "\" style=\"color:blue\">" . $paper_display[$i]['paper_title'] . "</a>";
      } else {
        echo "<tr><td width=\"66\" style=\"text-align:right\"><a onclick=\"return enterPassword();\" href=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/user_index.php?paper=" . $paper_display[$i]['paper_title'] . "\">" . displayIcon($paper_display[$i]['paper_type']) . "</a></td>\n";
        echo "<td><a onclick=\"return enterPassword();\" href=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/user_index.php?paper=" . $paper_display[$i]['paper_title'] . "\" style=\"color:blue\">" . $paper_display[$i]['paper_title'] . "</a>";
        echo ' <img src="./touchstone/artwork/key.png" width="16" height="16" alt="Key" /> <span style="color:#C88607; font-weight:bold; font-size:80%">password required</span>';
      }
      echo '<br /><span style="color:#808080; font-size:80%">(' . $paper_display[$i]['max_screen'];
      if ($paper_display[$i]['max_screen'] == 1) {
        echo ' screen, ';
      } else {
        echo ' screens, ';
      }
      if ($paper_display[$i]['bidirectional'] == 1) {
        echo 'Bidirectional navigation';
      } else {
        echo 'Unidirectional navigation';
      }
      echo ")</span></td></tr>\n";
    }
    echo "</table>\n";
  }
  $mysqli->close();
?>
</body>
</html>
