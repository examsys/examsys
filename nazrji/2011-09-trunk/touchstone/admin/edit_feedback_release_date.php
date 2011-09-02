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

require '../include/sysadmin_auth.inc';

if (isset($_POST['submit'])) {
  $release_date = $_POST['year'] . $_POST['month'] . $_POST['day'] . $_POST['time'];
  $result = $mysqli->prepare("UPDATE feedback_release SET date=? WHERE paper_id=?");
  $result->bind_param('si', $release_date, $_POST['paperID']);
  $result->execute();
  $result->close();
  $mysqli->close();
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/admin/list_releases.php");
} else {
  $stmt = $mysqli->prepare("SELECT DATE_FORMAT(date,\"%Y%m%d%H%i\"), paper_title FROM (feedback_release, properties) WHERE feedback_release.paper_id=properties.property_id AND feedback_release.paper_id=?");
  $stmt->bind_param('i', $_GET['paperID']);
  $stmt->execute();
  $stmt->bind_result($release_date, $paper);
  $stmt->fetch();
  $stmt->close();
  $mysqli->close();
?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
  <title>Edit Feedback Release Date</title>
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
  </script>
  </head>
  
  <body>
  <?php
    include 'release_options.inc';
  ?>
  <div id="content" class="content" style="font-size:80%">
  <table cellpadding="0" cellspacing="0" border="0" width="100%">
  <tr><td style="background-color:#EBEADB; font-size:200%; font-weight:bold"><a onmouseover="move_in('image1','../artwork/up_folder_icon_on.gif')" onmouseout="move_out('image1','../artwork/up_folder_icon_off.gif')" href="list_releases.php"><img name="image1" src="../artwork/up_folder_icon_off.gif" width="32" height="38" alt="Up" border="0" /></a>&nbsp;Edit Feedback Release Date</td></tr>
  <tr><td style="height:3px"><img src="../artwork/header_horizontal_line.gif" width="100%" height="3" /></td></tr>
  </table>
  <br />
  <div align="center">
  <form name="edit_degree" method="post" onsubmit="return checkForm()" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <table cellpadding="0" cellspacing="2" border="0" style="text-align:left">
    
<?php
    echo "<tr><td><strong>Paper</strong></td><td>$paper</td></tr>\n";
    
    $feedback_year = substr($release_date,0,4);
    $feedback_month = substr($release_date,4,2);
    $feedback_day = substr($release_date,6,2);
    $split_hour = substr($release_date,8,2);
    $split_minute = substr($release_date,10,2);
    $date_array = getdate();
    
    echo "<tr><td align=\"right\"><strong>Release Date&nbsp;</strong></td><td><select name=\"month\">\n";
    // Available to Month
    $months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    for ($i=1; $i<=12; $i++) {
      if ($i < 10) {
        if ($i == $feedback_month) {
          echo "<option value=\"0$i\" selected>" . $months[$i-1] . "</option>\n";
        } else {
          echo "<option value=\"0$i\">" . $months[$i-1] . "</option>\n";
        }
      } else {
        if ($i == $feedback_month) {
          echo "<option value=\"$i\" selected>" . $months[$i-1] . "</option>\n";
        } else {
          echo "<option value=\"$i\">" . $months[$i-1] . "</option>\n";
        }
      }
    }
    echo "</select>\n";
    // Available to Day
    echo "<select name=\"day\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $feedback_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
          echo "<option value=\"0$i\">";
        }
      } else {
        if ($i == $feedback_day) {
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
    echo "<select name=\"year\"\">\n";
    for ($i = $date_array[year]; $i < ($date_array[year]+2); $i++) {
      if ($i == $feedback_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select>\n<select name=\"time\">\n";
    // Available from Hour
    $times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
    foreach ($times as $key => $value) {
      if ($key == $split_hour . $split_minute . '00') {
        echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
      } else {
        echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
      }
    }
    echo "</select>\n</td></tr>\n</table>\n";
    echo "<input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" />\n";
?>
<p><input type="submit" style="width:100px" name="submit" value="Save">&nbsp;&nbsp;<input style="width:100px" type="button" name="home" value="Cancel" onclick="javascript:history.back();" /></p>
  </form>
  </div>
</div>
<?php
}
?>
</body>
</html>