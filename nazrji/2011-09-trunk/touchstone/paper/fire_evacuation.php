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
* This script can only be called from a paper in ‘summative’ mode from one of the four green fire exit icons displayed in ‘start.php’.
*  It does three main things: 
*        1) record the current screen data to the ‘log’ table, 
*        2) blank the screen to prevent plagiarism among evacuating examinees, and 
*        3) has a ‘continue’ button at the bottom of the screen with passes the correct parameters back to ‘start.php’ if the 
*           examinees are allowed to re-enter the building.
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/staff_student_auth.inc';
require '../include/marking_functions.inc';
  
if ($stmt = $mysqli->prepare("SELECT background, foreground, textsize, marks_color, themecolor, labelcolor, font FROM special_needs WHERE userid=?")) {
  $stmt->bind_param('i',$userID);
  $stmt->execute();
  $stmt->store_result();
  $stmt->bind_result($bgcolor, $fgcolor, $textsize, $marks_color, $themecolor, $labelcolor, $font);
  $stmt->fetch();
}
$stmt->close();

$stmt = $mysqli->prepare("SELECT paper_type, labs, UNIX_TIMESTAMP(start_date), UNIX_TIMESTAMP(end_date), moduleID, calendar_year FROM properties WHERE property_id=?");
$stmt->bind_param('i', $_GET['paperID']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($paper_type, $labs, $start_date, $end_date, $moduleID, $calendar_year);
while ($row = $stmt->fetch()) {
  $attempt = 1; //default attempt to 1 overwritten if the student is resit candidate
  if (strpos($userroles,'Student') !== false) {
    // Check for additional password on the paper
    if (!empty($password)) {
      if (!isset($_COOKIE['paperpwd']) or $password != $_COOKIE['paperpwd']) {
        echo "<html><head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%;font-family:$font,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#4465A2;font-size:140%}\n</style></head>\n<body style=\"font-family:$font,sans-serif\"><div style=\"position:absolute;left:10px;top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
        echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
        echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">There is a specific password assigned to this paper.</p>\n<p style=\"margin-left:60px\"v><form><input type=\"button\" value=\"OK\" style=\"width:100px\" name=\"ok\" onclick=\"window.close();\"></form></p>\n</body>\n</html>";
        $mysqli->close();
        exit;
      }
    }
    
    // Check time security
    if ((time()+120) < $start_date or (time()-3600) > $end_date) {
      echo "<html><head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%; font-family:$font,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#4465A2; font-size:140%}\n</style></head>\n<body style=\"font-family:$font,sans-serif\"><div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/clock_48.png\" width=\"48\" height=\"48\" /></div>\n";
      echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
      echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">The paper you are attempting to access is only available between the following times:</p>\n<ul style=\"margin-left:80px\">\n<li>From - " . date('d/m/Y H:i',$start_date) . "</li>\n<li>To - " . date('d/m/Y H:i',$end_date) . "</li>\n</ul>\n<br /><p style=\"margin-left:60px\"v><form><input type=\"button\" value=\"OK\" style=\"width:100px\" name=\"ok\" onclick=\"window.close();\"></form></p>\n</body>\n</html>";
      $mysqli->close();
      exit;
    }
    //Check room security
    if ($labs != '') {
      $lab_info = $mysqli->prepare("SELECT address, low_bandwidth FROM ip_addresses WHERE address=? AND lab IN ($labs)");
      $lab_info->bind_param('s',$_SERVER['REMOTE_ADDR']);
      $lab_info->execute();
      $lab_info->bind_result($address, $low_bandwidth);
      $lab_info->store_result();
      $lab_info->fetch();
      if ($lab_info->num_rows == 0) {
        echo "<html><head>\n<title>Access Denied</title>\n<style>\nbody {font-size:90%;font-family:$font,sans-serif;background-color:#FCFCFC;color:#575757}\nh1 {font-weight:normal;color:#4465A2;font-size:140%}\n</style></head>\n<body style=\"font-family:$font,sans-serif\"><div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
        echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
        echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px;color:#C0C0C0;background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">Access to this paper is not permitted from your current location.</p>\n</body>\n</html>";
        exit;
      }
      $lab_info->close();
    } else {
      // Exit if a summative exam is on no labs.
      if ($paper_type == '2') exit;
    }
    
    //get modules if the user is a student and the paper is not formative
    if (strpos($_SERVER['PHP_AUTH_USER'], 'user') !== 0) {
      if ($moduleID != '') {
        $cal_year_sql = '';
        if($calendar_year != '') $cal_year_sql = "AND calendar_year = '$calendar_year'";
        $module_info = $mysqli->query("SELECT moduleid,MAX(attempt) as attempt FROM student_modules WHERE userID=$userID AND moduleid IN ('" . str_replace(",","','",$moduleID) . "') $cal_year_sql GROUP BY moduleid");
        if ($module_info->num_rows == 0) {
          echo "<html>\n<head>\n<title>Access Denied - Title</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
          echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
          echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
          echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">$title $surname ($username) is not registered on <strong>$moduleID</strong> in <strong>$calendar_year</strong>.</p>\n</body>\n</html>";
          exit;
        } else {
          $row = $module_info->fetch_array(MYSQLI_ASSOC);
          if(is_array($row)) {
            $attempt = $row['attempt'];
          }
        }
        $module_info->close();
      } else {
        echo "<html>\n<head>\n<title>Access Denied - Year</title>\n<style>\nbody {font-size:90%; font-family:Arial,sans-serif; background-color:#FCFCFC; color:#575757}\nh1 {font-weight:normal; color:#BF0000; font-size:140%}\n</style>\n</head>\n<body>\n";
        echo "<div style=\"position:absolute; left:10px; top:10px\"><img src=\"/touchstone/artwork/access_denied.png\" width=\"48\" height=\"48\" /></div>\n";
        echo "<h1 style=\"margin-left:60px\">Access Denied</h1>\n";
        echo "<hr size=\"1\" align=\"left\" width=\"500\" style=\"margin-left:60px; color:#C0C0C0; background-color:#C0C0C0\" />\n<p style=\"margin-left:60px\">This paper is not on any module.</p>\n</body>\n</html>";
        exit;
      }
    }
    if (time() > $end_date and ($paper_type == '1' or $paper_type == '2')) {
      $paper_type = '_late';
    }
  }
}
$stmt->free_result();
$stmt->close();

echo "<html>\n<head>\n<title></title>\n</head>\n<body style=\"font-family:Arial,sans-serif; color:black\">\n";
echo "<form method=\"post\" name=\"questions\" action=\"start.php?paperID=" . $_GET['paperID'] . "&dont_record=true\">\n";

record_marks($_GET['paperID'], $mysqli, $userID, $paper_type, $grade, $year, $attempt, $userroles);
?>
  <p style="text-align:center; font-size:200%; color:#008000"><?php echo $string['top_msg']; ?></p>
  <p style="text-align:center; font-weight:bold"><?php echo $string['donotrun']; ?></p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p style="text-align:center"><strong><?php echo $string['bottom_msg']; ?> </strong><input type="submit" name="next" value="<?php echo $string['continue']; ?>" /></p>
<?php
  echo "<input type=\"hidden\" name=\"current_screen\" value=\"" . ($_POST['current_screen'] - 1) . "\" />\n";
  if (!$_POST['sessionid']) {
    echo "<input type=\"hidden\" name=\"sessionid\" value=\"" . date("YmdHis", time()) . "\" />\n";
  } else {
    echo "<input type=\"hidden\" name=\"sessionid\" value=\"" . $_POST['sessionid'] . "\" />\n";
  }
  echo "<input type=\"hidden\" name=\"page_start\" value=\"" . date("YmdHis", time()) . "\" />\n";
  echo "<input type=\"hidden\" name=\"old_screen\" value=\"" . ($_POST['current_screen'] - 1) . "\" />\n";
  echo "<input type=\"hidden\" name=\"previous_duration\" value=\"" . $_POST['previous_duration'] . "\" />\n";
  echo "<input type=\"hidden\" name=\"button_pressed\" value=\"\" />\n";
  echo "<input type=\"hidden\" name=\"fire_alarm\" value=\"1\" />\n";

  $mysqli->close();
?>
</form>
</body>
</html>