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
* Displays summative exams and OSCEs
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
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

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>TouchStone: Calendar<?php echo " $cfg_install_type"; ?></title>
<script language="JavaScript" src="../javascript/sidebar.js"></script>
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
<style>
  .month {font-size:140%; margin-left:10px; margin-right:10px}
</style>
</head>

<body>

<?php
  include '../include/calendar_options.inc';
  
  //get faculty and school info
  $schools = array("Default"=>array('-1'=>'&lt;All Schools&gt;'));
  $results = $mysqli->query("SELECT id, faculty, school FROM schools ORDER BY faculty,school");
  while ($row = $results->fetch_assoc()) {
    $schools[$row['faculty']][$row['id']] = $row['school'];
  }
  $results->close();
?>

<div id="content" class="content" style="font-size:80%">
<form action="" method="get">
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="text-align:left">
<tr style="background-color:#F1F5FB"><td>
<?php
  if (isset($_GET['module'])) {
    echo '<div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . $_GET['module'] . '</a></div>';
  } else {
    if (strpos($userroles,'SysAdmin') !== false) {
      echo '<div class="breadcrumb"><a href="../index.php">Home</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php">Administrative Tools</a></div>';
    } else {
      echo '<div class="breadcrumb"><a href="../index.php">Home</a></div>';
    }
  }
?>
<div style="font-size:200%; margin-left:10px"><strong>Calendar:</strong> <?php echo $current_year; ?></div></td>
<td style="text-align:right">
<?php
  echo "<select name=\"school\" onchange=\"this.form.submit();\">";
  foreach($schools as $fac => $sch) {
    echo "<optgroup label=\"$fac\">";
    foreach($sch as $id => $title) {
      $selected = '';
      if(isset($_GET['school']) and $id == $_GET['school']) $selected = 'selected '; 
      echo "<option value=\"$id\" $selected>$title</option>";
    }
    echo "</optgroup>";
  }
  echo "</select>&nbsp;";
  echo "<input type=\"hidden\" name=\"calyear\" value=\"$current_year\" /><br/>";
?>
<input style="width:100px" type="button" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>?calyear=<?php echo $current_year-1; ?>&school=<?php if (isset($_GET['school'])) echo $_GET['school']; ?>&module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>'" value="&lt; <?php echo $current_year-1; ?>" />&nbsp;<input style="width:100px" type="button" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>?calyear=<?php echo $current_year+1; ?>&school=<?php if (isset($_GET['school'])) echo $_GET['school']; ?>&module=<?php if (isset($_GET['module'])) echo $_GET['module']; ?>'" value="<?php echo $current_year+1; ?> &gt;" />&nbsp;</td>
</tr>
<tr><td colspan="2" style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" alt="Line" /></td></tr>
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
  $labs = array();
  $results = $mysqli->query("SELECT id, room_no, name FROM labs");
  while ($row = $results->fetch_assoc()) {
    $lab_id = $row['id'];
    $labs[$lab_id]['room_no'] = $row['room_no'];
    $labs[$lab_id]['name'] = $row['name'];
  }
  $results->close();
  
  //show only exams in a particular school
  $schools_sql = '';
  if(isset($_GET['school']) and $_GET['school'] != '') {
    foreach($schools as $fac => $sch) {
      foreach($sch as $id => $title) {
        if($id == $_GET['school']) {
          $school_name = $title;
          break 2;
        }
      }
    }
    //get the module list
    $schools_sql = '';
    $results = $mysqli->query("SELECT moduleid FROM modules WHERE school=\"$school_name\"");
    while ($row = $results->fetch_assoc()) {
      if($schools_sql == '') {
        $schools_sql = ' AND (';
      } else {
        $schools_sql .= ' OR ';
      }
      $schools_sql .= ' moduleID LIKE \'%' . $row['moduleid'] . '%\' ';
    }
    $results->close();
    if($schools_sql != '') $schools_sql .= ')';
  }
  $paper_no = 0;
  $paper_details = array();
  if ($schools_sql != '' OR !isset($_GET['school']) OR (isset($_GET['school']) AND ($_GET['school'] == -1 OR $_GET['school'] == ''))) {
    // Get papers running on various dates.
    $results = $mysqli->query("SELECT DATE_FORMAT(start_date,'%Y/%m/%d') AS date, labs, DATE_FORMAT(start_date,'%H:%i') AS start_time, DATE_FORMAT(end_date,'%H:%i') AS end_time, property_id, paper_title, DATE_FORMAT(start_date,'%c') AS month, DATE_FORMAT(start_date,'%Y') AS cal_year, DATE_FORMAT(start_date,'%e') AS start_day, DATE_FORMAT(end_date,'%e') AS end_date, moduleID, paper_type FROM properties WHERE start_date>=" . $current_year . "0101000000 AND end_date<=" . $current_year . "1231235959 AND (paper_type='2' OR paper_type='4') AND deleted IS NULL $schools_sql ORDER BY start_date");
    while ($row = $results->fetch_assoc()) {
      $paper_details[$paper_no]['labs'] = $row['labs'];
      $paper_details[$paper_no]['date'] = $row['date'];
      $paper_details[$paper_no]['start_day'] = $row['start_day'];
      $paper_details[$paper_no]['end_date'] = $row['end_date'];
      $paper_details[$paper_no]['paper_title'] = $row['paper_title'];
      $paper_details[$paper_no]['property_id'] = $row['property_id'];
      $paper_details[$paper_no]['month'] = $row['month'];
      $paper_details[$paper_no]['cal_year'] = $row['cal_year'];
      $paper_details[$paper_no]['start_time'] = $row['start_time'];
      $paper_details[$paper_no]['end_time'] = $row['end_time'];
      $paper_details[$paper_no]['paper_type'] = $row['paper_type'];
      $tmp_modules = explode(',',$row['moduleID']);
      $paper_details[$paper_no]['moduleID'] = $tmp_modules[0];
      $paper_no++;
    }
    $results->close();
  }

  // Sort all papers correctly by start time
  $sortby = 'start_time';
  $ordering = 'asc';
  $paper_details = array_csort($paper_details,$sortby,$ordering);
  
  for ($i=1; $i<=12; $i++) {
    $current_full_month = date("m", mktime(0, 0, 0, $current_month, 1, $current_year));
    $days_in_month = date("t", mktime(0, 0, 0, $current_month, 1, $current_year));
    $paper_no = 0;
    $first_day = true;

    echo "<div>";
    echo "<table cellpadding=\"0\" cellspacing=\"8\" border=\"0\" style=\"background-color:#E3EFFF; width:96%; margin-left:auto; margin-right:auto; text-align:left\">\n";
    echo "<tr><td class=\"month\"><a name=\"$i\"></a>" . date("F", mktime(0, 0, 0, $current_month, 1, $current_year)) . "</td></tr>\n";
    echo "<tr><td>";
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:85%; margin-left:auto; margin-right:auto\">\n";
    echo "<tr style=\"text-align:center; color:#6593CF; background-color:#E3EFFF\"><td style=\"border-left:1px solid #5D8CC9\">Monday</td><td style=\"border-left:1px solid #5D8CC9\">Tuesday</td><td style=\"border-left:1px solid #5D8CC9\">Wednesday</td><td style=\"border-left:1px solid #5D8CC9\">Thursday</td><td style=\"border-left:1px solid #5D8CC9; border-right:1px solid #5D8CC9\">Friday</td></tr>";
   
    $day_no = 1;
    $cell_no = 1;
    $subtract = 0;
    $start_day = getDayOfWeek(1,$current_month,$current_year,1);
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
                echo ($day_no-$subtract) . ' ' . date("M", mktime(0, 0, 0, $current_month, 1, $current_year));
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
              $rooms = explode(',',$individual_paper['labs']);
              $html = '';
              foreach ($rooms as $individual_room) {
                if ($html == '') {
                  if (isset($labs[$individual_room]['room_no']) and $labs[$individual_room]['room_no'] != '') $html = '<a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '" title="' . $labs[$individual_room]['name'] . '">' . $labs[$individual_room]['room_no'] . '</a>';
                } else {
                  if (isset($labs[$individual_room]['room_no'])  and $labs[$individual_room]['room_no'] != '') $html .= ', <a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '" title="' . $labs[$individual_room]['name'] . '">' . $labs[$individual_room]['room_no'] . '</a>';
                }
              }
              if ($individual_paper['labs'] == '' and $individual_paper['paper_type'] == '2') echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" align="texttop" alt="Warning: no labs set!" />';
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
              echo "<br ><table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"font-size:100%; width:100%\"><td style=\"height:17px; background-image:url('../artwork/cal_box_gradient.png'); background-repeat:repeat-x; border-top:1px solid #5D8CC9; border-bottom:1px solid #5D8CC9\"><strong>&nbsp;&nbsp;$day_number</strong> - Saturday</td></tr></table>";

              echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"padding-top:5px; font-size:100%; width:100%\">";            
              foreach ($paper_details as $individual_paper) {
                if ($individual_paper['start_day'] == (($day_no + 1) - $subtract) and $individual_paper['cal_year'] == $current_year and $individual_paper['month'] == $current_month) {
                  echo "<tr><td style=\"color:#294C7A; text-align:right\" valign=\"top\">&nbsp;";
                  if ($individual_paper['start_time'] == $individual_paper['end_time']) echo '<img src="../artwork/small_yellow_warning_icon.gif" width="16" height="16" align="texttop" alt="Warning: problem with times!" />';
                  echo $individual_paper['start_time'] . "&nbsp;&nbsp;</td><td style=\"color:#294C7A\" valign=\"top\">" . $individual_paper['end_time'] . "&nbsp;</td><td><a href=\"../paper/details.php?paperID=" . $individual_paper['property_id'] . "&module=" . $individual_paper['moduleID'] . "&folder=\">" . $individual_paper['paper_title'] . "</a>&nbsp;";
                  $rooms = explode(',',$individual_paper['labs']);
                  $html = '';
                  foreach ($rooms as $individual_room) {
                    if (isset($labs[$individual_room]['name'])) {
                      if ($html == '') {
                        if ($labs[$individual_room]['room_no'] != '') $html = '<a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '" title="' . $labs[$individual_room]['name'] . '">' . $labs[$individual_room]['room_no'] . '</a>';
                      } else {
                        if ($labs[$individual_room]['room_no'] != '') $html .= ', <a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '" title="' . $labs[$individual_room]['name'] . '">' . $labs[$individual_room]['room_no'] . '</a>';
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
  
  $prev_param = 'calyear=' . $current_year-1;
  $next_param = 'calyear=' . $current_year+1;
  $module = (isset($_GET['module'])) ? $_GET['module'] : '';
  if ($module != '') {
    $prev_param .= '&module=' . $_GET['module'];
    $next_param .= '&module=' . $_GET['module'];
  }
?>
<div style="text-align:right"><input style="width:100px" type="button" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>?calyear=<?php echo $current_year-1; ?>&module=<?php echo $module; ?>'" value="&lt; <?php echo $current_year-1; ?>" />&nbsp;<input style="width:100px" type="button" onclick="window.location='<?php echo $_SERVER['PHP_SELF']; ?>?calyear=<?php echo $current_year+1; ?>&module=<?php echo $module; ?>'" value="<?php echo $current_year+1; ?> &gt;" />&nbsp;</div>
</form>
</div>

</body>
</html>
