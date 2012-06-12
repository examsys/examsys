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
* Displays summative exams and OSCEs
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/sidebar_menu.inc';

if (isset($_GET['calyear'])) {
  $current_year = $_GET['calyear'];
} else {
  $current_year = date("Y");
}

function array_csort($marray, $column, $sort_order) {   //coded by Ichier2003
  $sortarr = array();
  foreach ($marray as $row) {
    $sortarr[] = $row[$column];
  }
  $sortarr = array_map('strtolower',$sortarr);
  $sort_method = SORT_STRING;
  if ($column == 'mark' or $column == 'duration') $sort_method = SORT_NUMERIC;
  if ($sort_order == 'asc') {
    array_multisort($sortarr, SORT_ASC, $sort_method, $marray);
  } else {
    array_multisort($sortarr, SORT_DESC, $sort_method, $marray);
  }
  return $marray;
}

function echoButtons($year) {
  $html = '';
  
  $prev_param = 'calyear=' . ($year - 1);
  $next_param = 'calyear=' . ($year + 1);
  $module = (isset($_GET['module'])) ? $_GET['module'] : '';
  
  if ($module != '') {
    $prev_param .= '&module=' . $_GET['module'];
    $next_param .= '&module=' . $_GET['module'];
  }
  
  $html = '<input style="width:100px" type="button" onclick="window.location=\'' . $_SERVER['PHP_SELF'] . '?' . $prev_param . '\'" value="&lt; ' . ($year - 1) . '" />';
  $html .= '&nbsp;';
  $html .= '<input style="width:100px" type="button" onclick="window.location=\'' . $_SERVER['PHP_SELF'] . '?' . $next_param . '\'" value="' . ($year + 1) . ' &gt;" />';

  return $html;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
<title>Rogō: <?php echo $string['calendar'] . ' ' . $cfg_install_type; ?></title>
<?php echo $cfg_js_root ?>
<script language="JavaScript" src="../js/sidebar.js"></script>
<script language="JavaScript">
  function go() {
    box = document.forms[0].navi;
    destination = box.options[box.selectedIndex].value;
    if (destination) {
      location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?year=" + destination;
    }
  }
</script>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<style type="text/css">
  .month {font-size:140%; margin-left:10px; margin-right:10px}
</style>
</head>

<body>

<?php
  include '../include/calendar_options.inc';
  
  //get faculty and school info
  $schools = array($string['default']=>array('-1'=>$string['allschools']));
  $stmt = $mysqli->prepare("SELECT schools.id, faculty.name, school FROM schools, faculty WHERE faculty.id=schools.facultyID ORDER BY faculty.name, school");
  $stmt->execute();
  $stmt->bind_result($id, $faculty, $school);
  while ($stmt->fetch()) {
    $schools[$faculty][$id] = $school;
  }
  $stmt->close();

  //get computer lab info
  $lab_details = array($string['default']=>array('-1'=>$string['alllabs']));
  $stmt = $mysqli->prepare("SELECT id, building, room_no, campus FROM labs ORDER BY campus, building, room_no");
  $stmt->execute();
  $stmt->bind_result($id, $building, $room_no, $campus);
  while ($stmt->fetch()) {
    $lab_details[$campus][$id] = $building . ' - ' . $room_no;
  }
  $stmt->close();
?>

<div id="content" class="content" style="font-size:80%">
<form action="" method="get">
<table class="header">
<tr><th>
<?php
  if (isset($_GET['module'])) {
    echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a></div>';
  } else {
    if (strpos($userroles,'SysAdmin') !== false) {
      echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">' . $string['administrativetools'] . '</a></div>';
    } else {
      echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a></div>';
    }
  }
?>
<div style="font-size:200%; margin-left:10px"><strong><?php echo $string['calendar']; ?>:</strong> <?php echo $current_year; ?></div></th>
<th style="text-align:right">
<?php

  echo "<select name=\"lab\" onchange=\"this.form.submit();\">";
  foreach ($lab_details as $campus => $lab) {
    echo "<optgroup label=\"$campus\">";
    foreach ($lab as $id => $title) {
      $selected = '';
      if (isset($_GET['lab']) and $id == $_GET['lab']) $selected = 'selected '; 
      echo "<option value=\"$id\" $selected>$title</option>";
    }
    echo "</optgroup>";
  }
  echo "</select>&nbsp;";

  echo "<select name=\"school\" onchange=\"this.form.submit();\">";
  foreach ($schools as $fac => $sch) {
    echo "<optgroup label=\"$fac\">";
    foreach ($sch as $id => $title) {
      $selected = '';
      if (isset($_GET['school']) and $id == $_GET['school']) $selected = 'selected '; 
      echo "<option value=\"$id\" $selected>$title</option>";
    }
    echo "</optgroup>";
  }
  echo "</select>&nbsp;";
  echo "<input type=\"hidden\" name=\"calyear\" value=\"$current_year\" /><br />";
  
?>
<div style="text-align:right"><?php echo echoButtons($current_year); ?>&nbsp;</div>
</th>
</tr>
<tr><th colspan="2" class="bevel"></th></tr>
</table>
<br />
<?php
  function getDayOfWeek($day, $month, $year, $CalendarSystem) {
    // CalendarSystem = 1 for Gregorian Calendar
    if ($month < 3) {
      $month = $month + 12;
      $year = $year - 1;
    }
    return ($day + (2 * $month) + intval(6 * ($month + 1) / 10) + $year + intval($year/4) - intval($year/100) + intval($year/400) + $CalendarSystem) % 7;
  }

  $current_month = 1;

  // Get lab information.
  $lab_list = array();
  $stmt = $mysqli->prepare("SELECT id, room_no, name FROM labs");
  $stmt->execute();
  $stmt->bind_result($lab_id, $room_no, $name);
  while ($stmt->fetch()) {
    $lab_list[$lab_id]['room_no'] = $room_no;
    $lab_list[$lab_id]['name'] = $name;
  }
  $stmt->close();
  
  //show only exams in a particular school
  $schools_sql = '';
  if (isset($_GET['school']) and $_GET['school'] != '') {
    foreach ($schools as $fac => $sch) {
      foreach ($sch as $id => $title) {
        if ($id == $_GET['school']) {
          $school_name = $title;
          break 2;
        }
      }
    }
    //get the module list
    $schools_sql = '';
    $stmt = $mysqli->prepare("SELECT moduleid FROM modules WHERE schoolid=?");
    $stmt->bind_param('i', $_GET['school']);
    $stmt->execute();
    $stmt->bind_result($moduleid);
    while ($stmt->fetch()) {
      if($schools_sql == '') {
        $schools_sql = ' AND (';
      } else {
        $schools_sql .= ' OR ';
      }
      $schools_sql .= " moduleID LIKE '%$moduleid%' ";
    }
    $stmt->close();
    if ($schools_sql != '') $schools_sql .= ')';
  }
  
  if (isset($_GET['lab']) and $_GET['lab'] != -1) {
    $lab_sql = " AND (labs='" . $_GET['lab'] . "' OR labs LIKE '%," . $_GET['lab'] . ",%' OR labs LIKE '" . $_GET['lab'] . ",%' OR labs LIKE '%," . $_GET['lab'] . "')";
  } else {
    $lab_sql = '';
  }
  
  $paper_no = 0;
  $paper_details = array();
  if ($schools_sql != '' OR !isset($_GET['school']) OR (isset($_GET['school']) AND ($_GET['school'] == -1 OR $_GET['school'] == ''))) {
    // Get papers running on various dates.
    $result = $mysqli->prepare("SELECT password, DATE_FORMAT(start_date,'%Y/%m/%d') AS date, labs, DATE_FORMAT(start_date,'%H:%i') AS start_time, DATE_FORMAT(end_date,'%H:%i') AS end_time, property_id, paper_title, DATE_FORMAT(start_date,'%c') AS month, DATE_FORMAT(start_date,'%Y') AS cal_year, DATE_FORMAT(start_date,'%e') AS start_day, DATE_FORMAT(end_date,'%e') AS end_date, moduleID, paper_type FROM properties WHERE start_date>=" . $current_year . "0101000000 AND end_date<=" . $current_year . "1231235959 AND (paper_type='2' OR paper_type='4') AND deleted IS NULL $schools_sql $lab_sql ORDER BY start_date");
    //$result->bind_param('s', $current_ip_address);
    $result->execute();
    //$result->store_result();
    $result->bind_result($password, $main_date, $labs, $start_time, $end_time, $property_id, $paper_title, $month, $cal_year, $start_day, $end_date, $moduleID, $paper_type);

    //$results = $mysqli->query("SELECT DATE_FORMAT(start_date,'%Y/%m/%d') AS date, labs, DATE_FORMAT(start_date,'%H:%i') AS start_time, DATE_FORMAT(end_date,'%H:%i') AS end_time, property_id, paper_title, DATE_FORMAT(start_date,'%c') AS month, DATE_FORMAT(start_date,'%Y') AS cal_year, DATE_FORMAT(start_date,'%e') AS start_day, DATE_FORMAT(end_date,'%e') AS end_date, moduleID, paper_type FROM properties WHERE start_date>=" . $current_year . "0101000000 AND end_date<=" . $current_year . "1231235959 AND (paper_type='2' OR paper_type='4') AND deleted IS NULL $schools_sql $lab_sql ORDER BY start_date");
    while ($result->fetch()) {
      $paper_details[$paper_no]['labs'] = $labs;
      $paper_details[$paper_no]['date'] = $main_date;
      $paper_details[$paper_no]['start_day'] = $start_day;
      $paper_details[$paper_no]['end_date'] = $end_date;
      $paper_details[$paper_no]['paper_title'] = $paper_title;
      $paper_details[$paper_no]['property_id'] = $property_id;
      $paper_details[$paper_no]['month'] = $month;
      $paper_details[$paper_no]['cal_year'] = $cal_year;
      $paper_details[$paper_no]['start_time'] = $start_time;
      $paper_details[$paper_no]['end_time'] = $end_time;
      $paper_details[$paper_no]['paper_type'] = $paper_type;
      $tmp_modules = explode(',', $moduleID);
      $paper_details[$paper_no]['moduleID'] = $tmp_modules[0];
      $paper_details[$paper_no]['password'] = $password;
      $paper_no++;
    }
    $result->close();
  }

  // Sort all papers correctly by start time
  $sortby = 'start_time';
  $ordering = 'asc';
  $paper_details = array_csort($paper_details, $sortby, $ordering);
  
  for ($i=1; $i<=12; $i++) {
    $current_full_month = date("m", mktime(0, 0, 0, $current_month, 1, $current_year));
    $days_in_month = date("t", mktime(0, 0, 0, $current_month, 1, $current_year));
    $paper_no = 0;
    $first_day = true;

    echo "<div>";
    echo "<table cellpadding=\"0\" cellspacing=\"8\" border=\"0\" style=\"background-color:#E3EFFF; width:96%; margin-left:auto; margin-right:auto; text-align:left\">\n";
    $tmp_month = strtolower(date("F", mktime(0, 0, 0, $current_month, 1, $current_year)));
    echo "<tr><td class=\"month\"><a name=\"$i\"></a>" . $string[$tmp_month] . "</td></tr>\n";
    echo "<tr><td>";
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:85%; margin-left:auto; margin-right:auto\">\n";
    echo "<tr style=\"text-align:center; color:#6593CF; background-color:#E3EFFF\"><td style=\"border-left:1px solid #5D8CC9\">" . $string['monday'] . "</td><td style=\"border-left:1px solid #5D8CC9\">" . $string['tuesday'] . "</td><td style=\"border-left:1px solid #5D8CC9\">" . $string['wednesday'] . "</td><td style=\"border-left:1px solid #5D8CC9\">" . $string['thursday'] . "</td><td style=\"border-left:1px solid #5D8CC9; border-right:1px solid #5D8CC9\">" . $string['friday'] . "</td></tr>";
   
    $day_no = 1;
    $cell_no = 1;
    $subtract = 0;
    $start_day = getDayOfWeek(1, $current_month, $current_year,1);
    if ($start_day == 6) {
      $start_day = 1;
      $day_no = 3;
    } elseif ($start_day == 0) {
      $start_day = 1;
      $day_no = 2;
    }
  
    do {
      echo '<tr>';
      for ($col=1; $col<=5; $col++) {
        if (($day_no - $subtract) <= $days_in_month) {
          if (($day_no - $subtract) != date("j") or $current_month != date("n") or $current_year != date("Y")) {
            // Day in month but not today
            echo "<td style=\"height:17px; background-image:url('../artwork/cal_box_gradient.png'); background-repeat:repeat-x; border-top:1px solid #5D8CC9; border-left:1px solid #5D8CC9; border-bottom:1px solid #5D8CC9; padding-left:5px";
            if ($col == 5) echo '; border-right:1px solid #5D8CC9';
            echo '"><strong>';
            if ($day_no >= $start_day) {
              if ($first_day == true) {
                $tmp_month = strtolower(date("F", mktime(0, 0, 0, $current_month, 1, $current_year)));
                echo ($day_no-$subtract) . ' ' . mb_substr($string[$tmp_month],0,3,'UTF-8');
                $first_day = false;
              } else {
                echo ($day_no-$subtract);
              }
            } else {
              echo '&nbsp;';
              $subtract++;
            }
            echo '</strong></td>';
          } elseif ($day_no >= $start_day) {
            // Today
            echo "<td style=\"height:17px; background-image:url('../artwork/cal_box_gradient_on.png'); background-repeat:repeat-x; border-top:1px solid #EE9311; border-left:1px solid #EE9311; border-bottom:1px solid #EE9311; padding-left:5px";
            if ($col == 5) echo "; border-right:1px solid #EE9311";
            if ($first_day == true) {
              echo "\"><strong>" . ($day_no-$subtract) . " " . date("M", mktime(0, 0, 0, $current_month, 1, $current_year)) . "</strong></td>\n";
              $first_day = false;
            } else {
              echo "\"><strong>" . ($day_no-$subtract) . "</strong></td>\n";
            }
          } else {
            // Day not in month
            echo "<td style=\"height:17px; background-image:url('../artwork/cal_box_gradient.png'); background-repeat:repeat-x; border-top:1px solid #5D8CC9; border-left:1px solid #5D8CC9; border-bottom:1px solid #5D8CC9";
            if ($col == 5) echo "; border-right:1px solid #5D8CC9";
            echo "\">&nbsp;</td>\n";
            $subtract++;
          }
        } else {
          // Day not in month
          echo "<td style=\"height:17px; background-image:url('../artwork/cal_box_gradient.png'); background-repeat:repeat-x; border-top:1px solid #5D8CC9; border-left:1px solid #5D8CC9; border-bottom:1px solid #5D8CC9";
          if ($col == 5) echo "; border-right:1px solid #5D8CC9";
          echo "\">&nbsp;</td>\n";
        }
        $day_no++;
      }
      echo '</tr>';
      $day_no -= 5;  // reset day number.
      
      echo '<tr style="height:80px">';
      for ($col=1; $col<=5; $col++) {
        if (($day_no - $subtract) < 1 or $day_no < $start_day) {    // Day on grid before start of month.
          echo "<td style=\"width:20%; background-color:#A5BFE1; border-left:1px solid #5D8CC9; border-bottom:1px solid #5D8CC9";
          if ($col == 5) echo "; border-right:1px solid #5D8CC9";
          echo "\">&nbsp</td>";
        } elseif (($day_no - $subtract) <= $days_in_month) {
          if (($day_no - $subtract) == date("j") and $current_month == date("n") and $current_year == date("Y")) {  // Current day
            echo "<td style=\"text-align:left; vertical-align:top; width:20%; padding_top:5px; padding-bottom:5px; background-color:white; border-left:1px solid #EE9311; border-bottom:1px solid #EE9311";
            if ($col == 5) echo "; border-right:1px solid #EE9311";
            echo "\">";
          } else {
            echo "<td style=\"text-align:left; vertical-align:top; width:20%; padding-top:5px; padding-bottom:5px; background-color:white; border-left:1px solid #5D8CC9; border-bottom:1px solid #5D8CC9";
            if ($col == 5) echo "; border-right:1px solid #5D8CC9";
            echo "\">";
          }
          $papers = 0;
          echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:100%\">\n";
          foreach ($paper_details as $individual_paper) {
            if ($individual_paper['start_day'] == ($day_no - $subtract) and $individual_paper['cal_year'] == $current_year and $individual_paper['month'] == $current_month) {
              $papers++;
              echo "<tr><td style=\"color:#294C7A; text-align:right; width:50px\" valign=\"top\">";
              if ($individual_paper['start_time'] == $individual_paper['end_time']) echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" align="texttop" alt="Warning: problem with times!" />';
              echo $individual_paper['start_time'] . "&nbsp;&nbsp;</td><td style=\"color:#294C7A; width:38px\" valign=\"top\">" . $individual_paper['end_time'] . "&nbsp;</td><td><a href=\"../paper/details.php?paperID=" . $individual_paper['property_id'] . "&module=" . $individual_paper['moduleID'] . "&folder=\">" . $individual_paper['paper_title'] . "</a>&nbsp;";
              $rooms = explode(',', $individual_paper['labs']);
              $html = '';
              foreach ($rooms as $individual_room) {
                if ($html == '') {
                  if (isset($lab_list[$individual_room]['room_no']) and $lab_list[$individual_room]['room_no'] != '') $html = '<a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '" title="' . $lab_list[$individual_room]['name'] . '">' . $lab_list[$individual_room]['room_no'] . '</a>';
                } else {
                  if (isset($lab_list[$individual_room]['room_no'])  and $lab_list[$individual_room]['room_no'] != '') $html .= ', <a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '" title="' . $lab_list[$individual_room]['name'] . '">' . $lab_list[$individual_room]['room_no'] . '</a>';
                }
              }
              if ($individual_paper['labs'] == '' and $individual_paper['password'] == '' and $individual_paper['paper_type'] == '2') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" align="texttop" alt="Warning: no labs set!" />';
              if ($individual_paper['password'] != '') {
                echo '&nbsp;<img src="../artwork/key_12.png" width="12" height="12" alt="password" border="0" style="vertical-align:text-top" />';
              }
              if ($individual_paper['paper_type'] == '4') echo '<img src="../artwork/small_osce_icon.png" width="16" height="13" border="0" alt="OSCE" />';
              echo "$html</td></tr>";
            }
          }
          echo "</table>\n";
          if ($papers == 0) echo '&nbsp;';
          
          if ($col == 5) {  // Check for Saturday exams.
            $saturday_exams = false;
            $day_number = '';
            foreach ($paper_details as $individual_paper) {
              if ($individual_paper['start_day'] == (($day_no + 1) - $subtract) and $individual_paper['cal_year'] == $current_year and $individual_paper['month'] == $current_month) {
                $saturday_exams = true;
                $day_number = $individual_paper['start_day'];
              }
            }
          
            if ($saturday_exams == true) {
              echo "<br ><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; width:100%\"><td style=\"height:17px; background-image:url('../artwork/cal_box_gradient.png'); background-repeat:repeat-x; border-top:1px solid #5D8CC9; border-bottom:1px solid #5D8CC9\"><strong>&nbsp;&nbsp;$day_number</strong> - " . $string['saturday'] . "</td></tr></table>";

              echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"padding-top:5px; font-size:100%; width:100%\">";            
              foreach ($paper_details as $individual_paper) {
                if ($individual_paper['start_day'] == (($day_no + 1) - $subtract) and $individual_paper['cal_year'] == $current_year and $individual_paper['month'] == $current_month) {
                  echo "<tr><td style=\"color:#294C7A; text-align:right\" valign=\"top\">&nbsp;";
                  if ($individual_paper['start_time'] == $individual_paper['end_time']) echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" align="texttop" alt="Warning: problem with times!" />';
                  echo $individual_paper['start_time'] . "&nbsp;&nbsp;</td><td style=\"color:#294C7A\" valign=\"top\">" . $individual_paper['end_time'] . "&nbsp;</td><td><a href=\"../paper/details.php?paperID=" . $individual_paper['property_id'] . "&module=" . $individual_paper['moduleID'] . "&folder=\">" . $individual_paper['paper_title'] . "</a>&nbsp;";
                  $rooms = explode(',',$individual_paper['labs']);
                  $html = '';
                  foreach ($rooms as $individual_room) {
                    if (isset($lab_list[$individual_room]['name'])) {
                      if ($html == '') {
                        if ($lab_list[$individual_room]['room_no'] != '') $html = '<a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '" title="' . $lab_list[$individual_room]['name'] . '">' . $lab_list[$individual_room]['room_no'] . '</a>';
                      } else {
                        if ($lab_list[$individual_room]['room_no'] != '') $html .= ', <a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '" title="' . $lab_list[$individual_room]['name'] . '">' . $lab_list[$individual_room]['room_no'] . '</a>';
                      }
                    } 
                  }
                  if ($individual_paper['labs'] == '' and $individual_paper['paper_type'] == '2') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" align="texttop" alt="Warning: no labs set!" />';
                  if ($individual_paper['paper_type'] == '4') echo '<img src="../artwork/small_osce_icon.png" width="16" height="13" border="0" alt="OSCE" />';
                  echo "$html</td></tr>";
                }
              }
              echo "</table>";
            }
          }
          
          echo "</td>";
        } else {        // Day on grid after end of month.
          echo "<td style=\"width:20%; background-color:#A5BFE1; border-left:1px solid #5D8CC9; border-bottom:1px solid #5D8CC9";
          if ($col == 5) echo "; border-right:1px solid #5D8CC9";
          echo "\">&nbsp</td>";
        }
        $day_no++;
      }
      echo "</tr>\n";
      
      $day_no += 2;  // Skip the weekend.
    } while (($day_no-$subtract) <= $days_in_month);
    echo "</table>\n</td></tr>\n</table></div><br />\n";
    
    $current_month++;
  }
  $mysqli->close();
?>
<div style="text-align:right"><?php echo echoButtons($current_year); ?>&nbsp;</div>
</form>
</div>

</body>
</html>
