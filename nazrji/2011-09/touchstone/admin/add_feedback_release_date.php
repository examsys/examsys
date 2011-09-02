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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  
  function modulo($n,$b) {
    return $n-$b*floor($n/$b);
  }

  if ($_POST['submit'] == 'Add') {
    if ((modulo($_POST['tyear'],4) == 0 and modulo($_POST['tyear'],100) != 0) or modulo($_POST['tyear'],400) == 0) {
      $leap = true;
    } else {
      $leap = false;
    }   
    if ($leap == true and $_POST['tmonth'] == '02' and ($_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '29';
    if ($leap == false and $_POST['tmonth'] == '02' and ($_POST['tday'] == '29' or $_POST['tday'] == '30' or $_POST['tday'] == '31')) $_POST['tday'] = '28';
    if (($_POST['tmonth'] == '04' or $_POST['tmonth'] == '06' or $_POST['tmonth'] == '09' or $_POST['tmonth'] == '11') and $_POST['tday'] == '31') $_POST['tday'] = '30';
    $release_date = $_POST['tyear'] . $_POST['tmonth'] . $_POST['tday'] . $_POST['ttime'];

    // Insert a record for each paper to be released.
    for ($i=0; $i<$_POST['paper_no']; $i++) {
      if ($_POST["paper$i"] != '') {
        $result = $mysqli->prepare("INSERT INTO feedback_release VALUES (NULL,?,?)");
        $result->bind_param('ss', $_POST["paper$i"], $release_date);
        $result->execute();  
        $result->close();
      }
    }

    header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/admin/list_releases.php");
  } else {
    $calendar_years = array();
    $result = $mysqli->query("SELECT DISTINCT calendar_year FROM properties WHERE (paper_type='2' OR paper_type='4' OR paper_type='5') AND deleted IS NULL AND moduleID != '' AND start_date < NOW() ORDER BY calendar_year");
    while ($row = $result->fetch_assoc()) {
      $calendar_years[] = $row['calendar_year'];
      $most_recent_session = $row['calendar_year'];
    }
    $result->close();
  ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Feedback Release Date</title>
<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
<script language="JavaScript">
  function move_in(img_name) {
    document[img_name].src=onImg.src;
  }
  function move_out(img_name) {
    document[img_name].src=offImg.src;
  }
  onImg = new Image;
  onImg.src = '../artwork/up_folder_icon_on.gif';
  offImg = new Image;
  offImg.src = '../artwork/up_folder_icon_off.gif';

  function toggle(objectID) {
    if (document.getElementById(objectID).style.backgroundColor == 'white') {
      document.getElementById(objectID).style.backgroundColor = 'highlight';
      document.getElementById(objectID).style.color = 'white';
    } else {
      document.getElementById(objectID).style.backgroundColor = 'white';
      document.getElementById(objectID).style.color = 'black';
    }
  }
  
  function switchSession() {
    var active_session = document.myform.session.options[document.myform.session.selectedIndex].value;
    
    <?php
      foreach ($calendar_years as $individual_year) {
        echo "document.getElementById('$individual_year').style.display = 'none';\n";
      }
    ?>
    
    document.getElementById(active_session).style.display = 'block';
  }
</script>
</head>

<body onload="document.getElementById('<?php echo $most_recent_session; ?>').style.display = 'block'">
<?php
  include 'release_options.inc';
?>
<div id="content" class="content" style="font-size:80%">
<form name="myform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

  <?php
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
    echo "<tr><td style=\"background-color:#EBEADB\"><div style=\"font-size:200%; font-weight:bold\"><a onmouseover=\"move_in('image1')\" onmouseout=\"move_out('image1')\" href=\"index.php\" target=\"_top\"><img name=\"image1\" src=\"../artwork/up_folder_icon_off.gif\" style=\"vertical-align: middle\" width=\"32\" height=\"38\" alt=\"Up\" border=\"0\" /></a>&nbsp;Admin/Create new release(s)</div></td>\n";
    echo "<tr><td colspan=\"2\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n</table>\n<br />\n";
  
    echo "<div style=\"margin-left:10px; width:99%\">Session <select name=\"session\" onchange=\"switchSession();\">";
    foreach ($calendar_years as $individual_year) {
      if ($individual_year == $most_recent_session) {
        echo "<option value=\"$individual_year\" selected>$individual_year</option>\n";
      } else {
        echo "<option value=\"$individual_year\">$individual_year</option>\n";
      }
    }
    echo "</select>\n<br />\n";
  
    echo "Release Date: <select name=\"tmonth\">\n";
    // Available to Month
    $months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    for ($i=0; $i<12; $i++) {
      if ($i < 9) {
        if (($i+1) == date('n')) {
          echo "<option value=\"0" . ($i+1) . "\" selected>" . $months[$i] . "</option>\n";
        } else {
          echo "<option value=\"0" . ($i+1) . "\">" . $months[$i] . "</option>\n";
        }
      } else {
        if (($i+1) == date('n')) {
          echo "<option value=\"" . ($i+1) . "\" selected>" . $months[$i] . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">" . $months[$i] . "</option>\n";
        }
      }
    }
    echo "</select>\n";
    // Available to Day
    echo "<select name=\"tday\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
         if ($i == date('j')) {
           echo "<option value=\"0$i\" selected>";
         } else {
           echo "<option value=\"0$i\">";
         }
       } else {
         if ($i == date('j')) {
           echo "<option value=\"$i\" selected>";
         } else {
           echo "<option value=\"$i\">";
         }
       }
       if ($i == 1 or $i == 21 or $i == 31) {
         echo $i . "st</option>\n";
       } elseif ($i == 2 or $i == 22) {
         echo $i . "nd</option>\n";
       } elseif ($i == 3 or $i == 23) {
         echo $i . "rd</option>\n";
       } else {
         echo $i . "th</option>\n";
       }
     }
     echo "</select>\n";
     // Available to Year
     echo "<select name=\"tyear\">\n";
     for ($i = date(Y); $i < (date(Y)+2); $i++) {
       echo "<option value=\"$i\">$i</option>\n";
     }
     echo "</select>&nbsp;<select name=\"ttime\">\n";
     // Available to Hour
     $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
     foreach ($times as $key => $value) {
       echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
     }
     echo "</select><br />\n<br />\n";
  
    // Get lab information.
    $labs = array();
    $results = $mysqli->query("SELECT id, room_no FROM labs");
    while ($row = $results->fetch_assoc()) {
      $lab_id = $row['id'];
      $labs[$lab_id] = $row['room_no'];
    }
    $results->close();

    $paper_no = 0;
    $old_calendar_year = '';
    $query_string = $mysqli->query("SELECT property_id, paper_title, labs, calendar_year FROM properties WHERE (paper_type='2' OR paper_type='4' OR paper_type='5') AND deleted IS NULL AND moduleID != '' AND start_date < NOW() ORDER BY calendar_year, paper_title");
    while ($row = $query_string->fetch_assoc()) {
      if ($row['calendar_year'] != $old_calendar_year) {
        if ($old_calendar_year != '') echo "</div>\n";
        echo "<div id=\"" . $row['calendar_year'] . "\" style=\"display:none; width:100%; height:660px; overflow-y:scroll; border:1px solid highlight; font-size:90%\">\n";
      }
      if (($row['labs'] != '' and $row['paper_type'] == '2') or $row['paper_type'] != '2') {
        echo "<div class=\"p\" id=\"divpaper$paper_no\"><input type=\"checkbox\" onclick=\"toggle('divpaper$paper_no')\" name=\"paper$paper_no\" value=\"" . $row['property_id'] . "\" />&nbsp;" . $row['paper_title'] . " <span style=\"font-size:80%\">";
        $rooms = explode(',',$row['labs']);
        $html = '';
        foreach ($rooms as $individual_room) {
          if ($html == '') {
            if ($labs[$individual_room] != '') $html = '<a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '">' . $labs[$individual_room] . '</a>';
          } else {
            if ($labs[$individual_room] != '') $html .= ', <a style="color:#FF6300" href="lab_details.php?labID=' . $individual_room . '">' . $labs[$individual_room] . '</a>';
          }
        }
        echo "$html</span></div>\n";
       $paper_no++;
      }      
      $old_calendar_year = $row['calendar_year'];
    }
    $query_string->close();
    echo "</div>\n";
    
    echo "<input type=\"hidden\" name=\"paper_no\" value=\"$paper_no\" />\n</div>\n";
  ?>
  </div>
  <br />
  <div style="text-align:center"><input type="submit" name="submit" value="Add" style="width:120px" />&nbsp;&nbsp;<input type="button" name="cancel" value="Cancel" style="width:120px" onclick="window.top.location='index.php'" /></div>
</form>
</div>

</body>
</html>
<?php
  }
  $mysqli->close();
?>