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
* @author Anthony Brown, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/
  require '../include/staff_auth.inc';

  if (in_array($_GET['module'], $teams) === false and strpos($userroles,'SysAdmin') === false) {
      exit;
  }

  $moduleID = $_GET['module'];
  if (isset($_POST['Save'])) {
    //save session

    $identifier = time();

    $occurrence = $_POST['session_year'] . '-' . $_POST['session_month'] . '-' . $_POST['session_day'] . ' ' . $_POST['session_time'];

    $stmt = $mysqli->prepare("INSERT INTO sessions VALUES (NULL, ?, ?, ?, ?, ?, ?)");
    $identifier = intVal($identifier);
    $stmt->bind_param('ssssss', $identifier, $moduleID, $_POST['session_title'], $_POST['url'], $_POST['session'], $occurrence);
    $stmt->execute();
    $stmt->close();

    $result = $mysqli->prepare("SELECT MAX(obj_id) AS largest FROM objectives");
    $result->execute();
    $result->bind_result($largest);
    $i = 0;
    while ($row = $result->fetch()) {
      $obj_id = $largest + 1;
    }
    if($obj_id < 10) {
      $obj_id = 123;
    }
    $result->close();
    while (isset($_POST["obj_$i"])) {
      if ($_POST["obj_$i"] != $string['msg1']) {
        $stmt = $mysqli->prepare("INSERT INTO objectives VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('issssi', $obj_id, $_POST["obj_$i"], $moduleID, $identifier, $_POST['session'], $i);
        $stmt->execute();
        $stmt->close();
      }
      $obj_id++;
      $i++;
    }

    //redirect to list sessions
    header("Location: ./sessions_list.php?module=" . $_GET['module'] . "&folder=" . $_GET['folder']);

  } else if(isset($_POST['cancel']) and $_POST['cancel'] == 'Cancel') {
    header("Location: ./sessions_list.php?module=" . $_GET['module'] . "&folder=" . $_GET['folder']);
  } else {
    $stmt = $mysqli->prepare("SELECT calendar_year FROM student_modules, modules WHERE student_modules.moduleid=modules.moduleid AND student_modules.moduleid=? ORDER BY calendar_year DESC LIMIT 1");
    $stmt->bind_param('s', $_GET['module']);
    $stmt->execute();
    $stmt->bind_result($session);
    $stmt->fetch();
    $stmt->close();
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
    <head>
    <title>Rogo: <?php echo $string['manageobjectives'] . ' ' . $cfg_install_type; ?></title>
    <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
    <style style="text/css">
      img {border:none;}
      .editBox {width:90%}
      .field {text-align:right; font-weight:bold}
      .note {width:90%}
    </style>
    <script src="../javascript/staff_help.js" type="text/javascript"></script>
    <script>
        function checkForm() {
          if (document.getElementById('session_title').value == '' || document.getElementById('session_title').value == ' ') {
            alert("<?php echo $string['msg2'];?>");
            return false;
          }
        }
        
      function clearTextbox(objectName) {
        if (document.getElementById(objectName).value == '<?php echo $string['msg1']; ?>') {
          document.getElementById(objectName).value = '';
          document.getElementById(objectName).style.color = 'black';
        }
      }

      var ObjCount = 0;
      function addNew(ulId) {
        ul = document.getElementById( ulId );
        li = document.createElement("li");
        li.id = 'li_' + ulId + ObjCount;
        li.style.margin = '0.5em';
        li.style.marginLeft = '3.5em';
        li.innerHTML = '<img src="./up_on.png" onclick="promote( \'' + li.id + '\' )" />&nbsp<img src="./down_on.png" onclick="demote( \'' + li.id + '\' )" />&nbsp<input class="editBox" name="obj_' + ObjCount + '" id="obj_' + ObjCount + '" type="text" style="color:#808080" onfocus="clearTextbox(\'obj_' + ObjCount + '\');" value="<?php echo $string['msg1']; ?>" /></li>';
        ul.insertBefore(li,ul.lastChild);
        updateButtons();
      }

      function demote(liId) {
        li = document.getElementById( liId );
        ul = li.parentNode;
        var i = 0;
        while(ul.childNodes[i].id != liId) {
          i++;
        }
        if ( i > 0 && i < (ul.childNodes.length - 2) ) {
          temp = ul.removeChild(ul.childNodes[i]);
          ul.insertBefore(temp,ul.childNodes[i+1]);
        }
        updateButtons();
      }


      function promote( liId ) {
        li = document.getElementById( liId );
        ul = li.parentNode;
        var i = 0;
        while(ul.childNodes[i].id != liId) {
          i++;
        }
        if( i > 1 ) {
          temp = ul.removeChild(ul.childNodes[i]);
          ul.insertBefore(temp,ul.childNodes[i-1]);
        }
        updateButtons();
      }

      function updateButtons() {
        lis = document.getElementsByTagName('li');
        ObjCount = 0;
        for (var i = 1; i < (lis.length - 1) ; i++ ) {
          if (lis[i].id != '') {
            ObjCount++;
            if (lis[i - 1].id == '') {
              //disable up
              lis[i].childNodes[0].src = './up_off.png';
            } else {
              lis[i].childNodes[0].src = './up_on.png';
            }
            if (lis[i+ 1].id == '') {
              //disable down
              lis[i].childNodes[2].src = './down_off.png';
            } else {
              lis[i].childNodes[2].src = './down_on.png';
            }
          }
        }
      }

      function cancelForm() {
        window.location = "./sessions_list.php?module=<?php  if(isset($_GET['module'])) echo $_GET['module']; ?>&folder=<?php if(isset($_GET['folder'])) echo $_GET['folder']; ?>";
      }
    </script>
    </head>
    <body onclick="hideSessCopyMenu(event);">
    <?php
    require '../include/sessions_options.inc';
    if (isset($_GET['module'])) {
      $module = $_GET['module'];
    } else {
      $module = '';
    }
    if (isset($_GET['folder'])) {
      $folder = $_GET['folder'];
    } else {
      $folder = '';
    }
    echo '<div id="content" class="content" style="font-size:80%">';
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
    echo "<tr><td colspan=\"3\" style=\"background-color:#F1F5FB\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../folder/details.php?module=$module\">$module</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"sessions_list.php?module=$module&folder=$folder\">" . $string['manageobjectives'] . "</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['newsession'] . "</strong></div></td><td style=\"background-color:#F1F5FB; text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(0); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"Help\" border=\"0\" /></a></td></tr>\n";
    echo "<tr><td colspan=\"4\" style=\"height:3px\"><img src=\"../artwork/header_horizontal_line.gif\" width=\"100%\" height=\"3\" alt=\"Line\" /></td></tr>\n";
    echo '</table><br/>';

    echo "<form name=\"editObj\" action=\"" . $_SERVER['PHP_SELF'] . "?module=" . $_GET['module'] . "&folder=\" method=\"post\" onsubmit=\"return checkForm();\">\n<div align=\"center\"><table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:80%; text-align:left\">\n";
    echo "<tr><td style=\"width:92px\" class=\"field\">" . $string['title'] . "</td><td><input type=\"text\" name=\"session_title\" id=\"session_title\" size=\"60\" value=\"\" /></td></tr>\n";

    echo '<tr><td class="field">' . $string['session'] . '</td><td>';
      $validfrom = '<select name="session">'."\n";
      $startyear = ( date('Y') - 1 );
      for ($i = 0; $i < 2; $i++){
        $tmp_session = ($startyear + $i) . '/' . substr(($startyear + $i + 1),2);
        if ($tmp_session == $session) {
          $validfrom .= '<option value="' . $tmp_session . '" selected>' . $tmp_session . '</option>';
        } else {
          $validfrom .= '<option value="' . $tmp_session . '">' . $tmp_session . '</option>';
        }
      }
      $validfrom .= "</select></td></tr>\n";
      echo $validfrom;

      echo '<tr><td class="field">' . $string['date'] . '</td><td>';
      if (isset($_POST['month'])) {
        $currentmonth = $_POST['month'];
      } else {
        $currentmonth   = date('m');
      }
      
      // Day
      if (isset($_POST['day'])) {
        $currentday = $_POST['day'];
      } else {
        $currentday = date('j');
      }
      $validfrom = '<select name="session_day">'."\n";
      foreach ( range(1,31) as $day ){
          $selected = ($day == $currentday ) ? ' selected="selected"' : '';
          if ($day < 10) $day = '0' . $day;
          $validfrom .= '<option value="'. $day .'"'. $selected .'>' . $day . '</option>'."\n";
      }
      $validfrom .= '</select>&nbsp;';
      echo $validfrom;

      // Month
      $validfrom = '<select name="session_month">'."\n";
      $month_names = array(1=>'january', 2=>'february', 3=>'march', 4=>'april', 5=>'may', 6=>'june', 7=>'july', 8=>'august', 9=>'september', 10=>'october', 11=>'november', 12=>'december');
      for ($month = 1; $month <= 12; $month++) {
        $selected = ($month == $currentmonth ) ? ' selected="selected"' : '';
        $validfrom .= '<option value="'. $month .'"'. $selected .'>' . mb_substr($string[$month_names[$month]],0,3,'UTF-8') . '</option>'."\n";
      }
      $validfrom .= '</select>&nbsp;';
      echo $validfrom;

      // Year
      $startyear = ( date('Y') - 1 );
      if (isset($_POST['year'])) {
        $currentyear = $_POST['year'];
      } else {
        $currentyear = date('Y');
      }
      $maxyear  = ( date('Y') + 1 );
      $validfrom = '<select name="session_year">'."\n";
      foreach ( range($startyear,$maxyear) as $years ){
        $selected = ($years == $currentyear ) ? ' selected="selected"' : '';
        $validfrom .= '<option value="'. $years .'"'. $selected .'>'. $years .'</option>'."\n";
      }
      $validfrom .= '</select>';
      echo $validfrom;

      echo "</select>\n";
      
      echo "<select name=\"session_time\">\n";
      
	    // Available from Hour
	    $now = date('H') . ':00' . ':00';
	    $times = array('00:00:00'=>'00:00','00:30:00'=>'00:30','01:00:00'=>'01:00','01:30:00'=>'01:30','02:00:00'=>'02:00','02:30:00'=>'02:30','03:00:00'=>'03:00','03:30:00'=>'03:30','04:00:00'=>'04:00','04:30:00'=>'04:30','05:00:00'=>'05:00','05:30:00'=>'05:30','06:00:00'=>'06:00','06:30:00'=>'06:30','07:00:00'=>'07:00','07:30:00'=>'07:30','08:00:00'=>'08:00','08:30:00'=>'08:30','09:00:00'=>'09:00','09:30:00'=>'09:30','10:00:00'=>'10:00','10:30:00'=>'10:30','11:00:00'=>'11:00','11:30:00'=>'11:30','12:00:00'=>'12:00','12:30:00'=>'12:30','13:00:00'=>'13:00','13:30:00'=>'13:30','140000'=>'14:00','14:30:00'=>'14:30','15:00:00'=>'15:00','15:30:00'=>'15:30','16:00:00'=>'16:00','16:30:00'=>'16:30','17:00:00'=>'17:00','17:30:00'=>'17:30','18:00:00'=>'18:00','18:30:00'=>'18:30','19:00:00'=>'19:00','19:30:00'=>'19:30','20:00:00'=>'20:00','20:30:00'=>'20:30','21:00:00'=>'21:00','21:30:00'=>'21:30','22:00:00'=>'22:00','22:30:00'=>'22:30','23:00:00'=>'23:00','23:30:00'=>'23:30');
	    foreach ($times as $key => $value) {
        if ($key == $now) {
          echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
        } else {
          echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
        }
      }
      echo "</select></td></tr>\n";
      echo '<tr><td class="field">' . $string['url'] . '</td><td><input name="url" class="editBox" type="text" value="" /></td></tr>';
      echo "\n<tr><td colspan=\"2\"><ul id=\"objList\" style=\"margin-left:0px; list-style-type: none; width: 100%\">\t<li>\n\t<table callpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:93%; font-size:100%\">\n<tr>\n\t<td class=\"subheading\"></td>\n";
    	echo "\t<td valign=\"center\" style=\"color:gray; padding-left:1em; font-size:75%; width:100%\"></td>\t";
      echo "\t<td></td></tr></table></li>\n";
      for($i = 0; $i < 3; $i++) {
        $id = $i;
        echo "\t<li id=\"li_$id\" style=\"margin:0.5em; margin-left:3.5em\">";
        echo '<img src="./up_on.png" onclick="promote( \'li_' . $id . '\' )" />&nbsp<img src="./down_on.png" onclick="demote( \'li_' . $id . '\' )" />&nbsp';
        echo "<input class='editBox' onfocus=\"clearTextbox('obj_" . $id . "');\" id=\"obj_" . $id . "\" name=\"obj_" . $id . "\" type=\"text\" value=\"" . $string['msg1'] . "\" />";
        echo "</li>\n";
      }
      echo '<li style="margin:0.5em; margin-left:6em"><input style="width: 80px" type="button" value="' . $string['new'] . '"  onclick="addNew(\'objList\')"></li>';
      echo '</ul>';

      //add the save buttens
      echo '<ul style="margin-left:0px; list-style-type:none; width:100%">';
      echo '<li style="margin:0.5em; margin-left:0.5em; text-align:center">';
      echo '<input name="Save" style="width:120px" type="submit" value="' . $string['save'] . '" />&nbsp;&nbsp;';
      echo '<input name="cancel" style="width:120px" type="button" value="' . $string['cancel'] . '" onclick="cancelForm();" />';
      echo '</li>';
      echo "</ul>\n";
	
      echo "</td></tr>\n</table>\n</div>\n</form>\n";
      echo '<script language="Javascript">updateButtons();</script>';
?>
      </div>
    </body>
    </html>
<?php
  }
?>