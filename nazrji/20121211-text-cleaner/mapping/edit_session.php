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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

$moduleID = '';
$identifier = '';
$session = '';
$folder = '';

if (isset($_GET['module'])) $moduleID = $_GET['module'];
if (isset($_GET['folder'])) $folder = $_GET['folder'];
if (isset($_GET['identifier'])) $identifier = $_GET['identifier'];
if (isset($_GET['calendar_year'])) $session = $_GET['calendar_year'];

if (isset($_POST['Edit'])) {
  //save session
  $identifier = $_POST['identifier'];
  $occurrence = $_POST['year'] . $_POST['month'] . $_POST['day'] . $_POST['time'];
 
  //update session
  $stmt = $mysqli->prepare("UPDATE sessions SET title = ?,source_url = ?, occurrence = ? WHERE identifier = ? AND moduleID = ? AND identifier = ? AND calendar_year = ?");
  $stmt->bind_param('sssssss', $_POST['session_title'], $_POST['url'], $occurrence, $identifier, $moduleID, $identifier, $_POST['session']);
  $stmt->execute();
  $stmt->close();

  $maxID = 0;
  $sequence = 0;
  foreach($_POST as $key => $value) {
    $tmp = explode('_',$key);
    if(count($tmp) > 1) {
      $type = $tmp[0];
      $objId = $tmp[1];
    } else {
      $type = $tmp[0];
      $objId = '';
    }
    switch($type) {
      //deal with old objs
      case 'obj':
        if ($value == '') {
          //delete objs and mappings
          $stmt = $mysqli->prepare("DELETE FROM objectives WHERE obj_id = ? AND moduleID = ? AND identifier = ? AND calendar_year = ?");
          $stmt->bind_param('isss', $objId, $moduleID, $identifier, $_POST['session']);
          $stmt->execute();
          $stmt->close();

          $stmt = $mysqli->prepare("DELETE FROM relationships WHERE obj_id = ? AND module_id = ? AND calendar_year = ? AND vle_api=''");
          $stmt->bind_param('iss', $objId, $moduleID, $_POST['session']);
          $stmt->execute();
          $stmt->close();
        } else {
          $sequence++;
          //update obj
          $stmt = $mysqli->prepare("UPDATE objectives SET objective = ?, sequence = ? WHERE obj_id = ? AND moduleID = ? AND identifier = ? AND calendar_year = ?");
          $stmt->bind_param('sissss', $value, $sequence, $objId, $moduleID, $identifier, $_POST['session']);
          $stmt->execute();
          $stmt->close();
        }
        break;
      //deal with new objs
      case 'objnew':
        if ($maxID == 0) {
          $result = $mysqli->prepare("SELECT MAX(obj_id) AS largest FROM objectives");
          $result->execute();
          $result->bind_result($largest);
          while ($row = $result->fetch()) {
            $maxID = $largest + 1;
          }
        }
        if ($value != '' AND $value != 'Type New Objective here...') {
          $sequence++;
          //insert new obj
          $stmt = $mysqli->prepare("INSERT INTO objectives VALUES (?,?,?,?,?,?)");
          $stmt->bind_param('issssi', $maxID, $value, $moduleID, $identifier, $_POST['session'], $sequence);
          $stmt->execute();
          $stmt->close();
          $maxID++;
        }
        break;
    }
  }

  //redirect to list sessions
  header("Location: ./sessions_list.php?module=" . $moduleID . "&folder=" . $folder);

} else if(isset($_POST['cancel'])) {
  header("Location: ./sessions_list.php?module=" .  $moduleID . "&folder=" . $folder);
} else {
  //display form
  ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
    
    <title>Rogō: <?php echo $string['manageobjectives'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
    
    <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css" />
    <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
    <style type="text/css">
      .editBox {width:90%}
      .field {text-align:right; font-weight:bold}
      .note {width:90%}
    </style>
    <script src="../js/staff_help.js" type="text/javascript"></script>
    <script type="text/javascript">
      function checkForm() {
        if (document.getElementById('session_title').value == '' || document.getElementById('session_title').value == ' ') {
          alert("<?php echo $string['entertitle']; ?>");
          return false;
        }
      }

      function clearTextbox(objectName) {
        if (document.getElementById(objectName).value == '<?php echo $string['msg1']; ?>') {
          document.getElementById(objectName).value = '';
          document.getElementById(objectName).style.color = 'black';
        }
      }

      var ObjNewCount = 0;
      var ObjCount = 0;
      function addNew(ulId) {
        ul = document.getElementById( ulId );
        li = document.createElement("li");
        li.id = 'li_' + ulId + ObjNewCount;
        li.style.margin = '0.5em';
        li.style.marginLeft = '3.5em';
        li.innerHTML = '<img src="./up_on.png" onclick="promote( \'' + li.id + '\' )" />&nbsp<img src="./down_on.png" onclick="demote( \'' + li.id + '\' )" />&nbsp<input class="editBox" name="objnew_' + ObjNewCount + '" id="objnew_' + ObjNewCount + '" type="text" style="color:#808080" onfocus="clearTextbox(\'objnew_' + ObjNewCount + '\');" value="<?php echo $string['msg1']; ?>" /></li>';
        ul.insertBefore(li,ul.lastChild);
        ObjNewCount++;
        updateButtons();
      }

      function demote( liId ) {
        li = document.getElementById( liId );
        ul = li.parentNode;
        var i = 0;
        while(ul.childNodes[i].id != liId) {
           i++;
        }
        if( i > 0 && i < (ul.childNodes.length - 2) ) {
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
        if ( i > 1 ) {
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
            if(lis[i - 1].id == '') {
               //disable up
               lis[i].childNodes[0].src = './up_off.png';
            } else {
               lis[i].childNodes[0].src = './up_on.png';
            }
            if(lis[i+ 1].id == '') {
               //disable down
               lis[i].childNodes[2].src = './down_off.png';
            } else {
                lis[i].childNodes[2].src = './down_on.png';
            }
          }
        }
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

if (isset($_GET['calendar_year'])) {
  $session = $_GET['calendar_year'];
}
echo '<div id="content" class="content" style="font-size:80%">';
echo "<table class=\"header\">\n";
echo "<tr><th colspan=\"3\"><div class=\"breadcrumb\"><a href=\"../staff/index.php\">" . $string['home'] . "</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"../folder/details.php?module=$module\">$module</a>&nbsp;&nbsp;<img src=\"../artwork/breadcrumb_arrow.png\" width=\"4\" height=\"7\" alt=\"-\" />&nbsp;&nbsp;<a href=\"sessions_list.php?module=$module&folder=$folder\">" . $string['manageobjectives'] . "</a></div><div style=\"font-size:200%; margin-left:10px\"><strong>" . $string['editsession'] . "</div></th><th style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(0); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" border=\"0\" /></a></th></tr>\n";
echo "<tr><th colspan=\"4\" class=\"bevel\"></td></tr>\n";
echo '</table><br />';

//get session information
$result = $mysqli->prepare("SELECT sessions.title, source_url, sessions.calendar_year, sessions.occurrence, obj_id, objective FROM sessions LEFT JOIN objectives ON sessions.identifier=objectives.identifier AND sessions.calendar_year = objectives.calendar_year AND sessions.idMod = objectives.idMod WHERE sessions.idMod = ? and sessions.identifier = ? AND sessions.calendar_year = ? ORDER BY sequence");
echo "SELECT sessions.title, source_url, sessions.calendar_year, sessions.occurrence, obj_id, objective FROM sessions LEFT JOIN objectives ON sessions.identifier=objectives.identifier AND sessions.calendar_year = objectives.calendar_year AND sessions.idMod = objectives.idMod WHERE sessions.idMod = $moduleID and sessions.identifier = $identifier AND sessions.calendar_year = '$session' ORDER BY sequence";
echo $mysqli->error;
$result->bind_param('iis', $moduleID, $identifier, $session);
$result->execute();
$result->bind_result($title, $source_url, $calendar_year, $occurrence, $obj_id, $objective);
$sess = array();
while ($result->fetch()) {
  if( !isset($sess['identifier']) ) {
    $sess['identifier'] = $identifier;
    $sess['moduleID'] = $moduleID;
    $sess['title'] = $title;
    $sess['source_url'] = $source_url;
    $sess['calendar_year'] = $calendar_year;
    $sess['occurrence'] = $occurrence;
  }
  if ($obj_id != '') {
   $sess['objectives'][$obj_id] = $objective;
   $sess['objectives'][$obj_id] = $objective;
   $sess['objectives'][$obj_id] = $objective;
  }
}
$result->close();

var_dump($sess);

echo "<form name=\"editObj\" action=\"" . $_SERVER['PHP_SELF'] . "?module=" . $_GET['module'] . "\" method=\"post\" onsubmit=\"return checkForm();\">\n<div align=\"center\"><table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:80%; text-align:left\">\n";

echo "<tr><td style=\"width:92px\" class=\"field\">" . $string['title'] . "</td><td><input type=\"text\" name=\"session_title\" id=\"session_title\" size=\"60\" value=\"" . $sess['title'] . "\"/></td></tr>\n";

echo '<tr><td class="field">' . $string['session'] . '</td><td>';
$validfrom = '<select name="session" disablied="disabled">'."\n";
$validfrom .= "<option value=\"" . $_GET['calendar_year'] . "\" selected=\"selected\">" . $_GET['calendar_year'] . "</option>";
$validfrom .= "</select></td></tr>\n";
echo $validfrom;

list($date,$time) = explode(' ', $sess['occurrence']);
list($y,$m,$d) = explode('-', $date);

echo '<tr><td class="field">' . $string['date'] . '</td><td>';

// Day
if (isset($d)) {
  $currentday = $d;
} else {
  $currentday   = date('j');
}
$validfrom = '<select name="day">'."\n";
foreach (range(1,31) as $day) {
  $selected = ($day == $currentday ) ? ' selected="selected"' : '';
  $day_value = $day;
  if ($day_value < 10) $day_value = '0' . $day_value;
  $validfrom .= "<option value=\"$day_value\" $selected>$day_value</option>\n";
}
$validfrom .= '</select>&nbsp;';
echo $validfrom;

// Month
if (isset($m)) {
  $currentmonth = $m;
} else {
  $currentmonth   = date('m');
}
$validfrom = '<select name="month">'."\n";
$month_names = array(1=>'january', 2=>'february', 3=>'march', 4=>'april', 5=>'may', 6=>'june', 7=>'july', 8=>'august', 9=>'september', 10=>'october', 11=>'november', 12=>'december');
for ($month = 1; $month <= 12; $month++) {
  $selected = ($month == $currentmonth ) ? ' selected="selected"' : '';
  $month_value = $month;
  if ($month_value < 10) $month_value = '0' . $month_value;
  $validfrom .= "<option value=\"$month_value\" $selected>" . mb_substr($string[$month_names[$month]],0,3,'UTF-8') . "</option>\n";
}
$validfrom .= '</select>&nbsp;';
echo $validfrom;

// Year
$startyear = ( date('Y') - 1 );
if (isset($y)) {
  $currentyear = $y;
} else {
  $currentyear = date('Y');
}
$maxyear  = ( date('Y') + 1 );
$validfrom = '<select name="year">'."\n";
foreach ( range($startyear,$maxyear) as $years ){
  $selected = ($years == $currentyear ) ? ' selected="selected"' : '';
  $validfrom .= "<option value=\"$years\" $selected>$years</option>\n";
}
$validfrom .= '</select>';
echo $validfrom;

echo "</select>\n<select name=\"time\">\n";
// Available from Hour
if (isset($time)) {
  $now = str_replace(':','',$time);
} else {
  $now = date('H') . '0000';
}
$times = array('000000'=>'00:00','003000'=>'00:30','010000'=>'01:00','013000'=>'01:30','020000'=>'02:00','023000'=>'02:30','030000'=>'03:00','033000'=>'03:30','040000'=>'04:00','043000'=>'04:30','050000'=>'05:00','053000'=>'05:30','060000'=>'06:00','063000'=>'06:30','070000'=>'07:00','073000'=>'07:30','080000'=>'08:00','083000'=>'08:30','090000'=>'09:00','093000'=>'09:30','100000'=>'10:00','103000'=>'10:30','110000'=>'11:00','113000'=>'11:30','120000'=>'12:00','123000'=>'12:30','130000'=>'13:00','133000'=>'13:30','140000'=>'14:00','143000'=>'14:30','150000'=>'15:00','153000'=>'15:30','160000'=>'16:00','163000'=>'16:30','170000'=>'17:00','173000'=>'17:30','180000'=>'18:00','183000'=>'18:30','190000'=>'19:00','193000'=>'19:30','200000'=>'20:00','203000'=>'20:30','210000'=>'21:00','213000'=>'21:30','220000'=>'22:00','223000'=>'22:30','230000'=>'23:00','233000'=>'23:30');
foreach ($times as $key => $value) {
  if ($key == $now) {
    echo "<option value=\"" . $key . "\" selected>" . $value . "</option>\n";
  } else {
    echo "<option value=\"" . $key . "\">" . $value . "</option>\n";
  }
}
echo "</select>\n</td></tr>\n";

echo '<tr><td class="field">' . $string['url'] . '</td><td><input name="url" class="editBox" type="text" value="' . $sess['source_url'] . '" /></td></tr>';

echo "\n<tr><td colspan=\"2\"><ul id=\"objList\" style=\"margin-left:0px; list-style-type:none; width:100%\">\t<li>\n\t<table callpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"width:93%; font-size:100%\">\n<tr>\n\t<td class=\"subheading\"></td>\n";
echo "\t<td valign=\"center\" style=\"color:gray; padding-left:1em; font-size:75%; width:100%;\"></td>\t";
echo "\t<td></td></tr></table></li>\n";
if (isset($sess['objectives'])) {
  foreach ($sess['objectives'] as $id => $obj) {
    echo "\t<li id=\"li_$id\" style=\"margin:0.5em; margin-left:3.5em\">";
    echo '<img src="./up_on.png" onclick="promote( \'li_' . $id . '\' )" />&nbsp<img src="./down_on.png" onclick="demote( \'li_' . $id . '\' )" />&nbsp';
    echo "<input class='editBox' onfocus=\"clearTextbox('obj_" . $id . "');\" id=\"obj_" . $id . "\" name=\"obj_" . $id . "\" type=\"text\" value=\"$obj\" />";
    echo "</li>\n";
  }
}
echo '<li style="margin: 0.5em; margin-left:6em"><input style="width:80px" type="button" value="' . $string['new'] . '"  onclick="addNew(\'objList\')"></li>';
echo '</ul>';

//add the save buttens
echo '<ul style="margin-left:0px; list-style-type:none; width:100%">';
echo '<li style="margin: 0.5em; margin-left: 0.5em; text-align: center">';
echo '<input name="Edit"  style="height=90%; width: 120px;" type="submit" value="' . $string['save'] . '" >&nbsp;&nbsp;';
echo '<input name="cancel" style="width: 120px;" type="submit" value="' . $string['cancel'] . '">';
echo '</li>';
echo "</ul>\n";

echo "<input type=\"hidden\" name=\"identifier\" value=\"$identifier\" />";
echo "</td></tr>\n</table>\n</div>\n";
echo "</form>\n";
echo '<script language="Javascript">updateButtons();</script>';
?>
</div>
</body>
</html>
<?php
}
?>