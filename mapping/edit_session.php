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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

$identifier     = check_var('identifier', 'REQUEST', true, false, true);
$calendar_year  = check_var('calendar_year', 'GET', true, false, true);
$modID          = check_var('module', 'GET', true, false, true);

if (isset($_GET['folder'])) {
  $folder = $_GET['folder'];
} else {
  $folder = '';
}

// Get session information
$result = $mysqli->prepare("SELECT sessions.title, source_url, sessions.calendar_year, sessions.occurrence, obj_id, objective FROM sessions LEFT JOIN objectives ON sessions.identifier=objectives.identifier AND sessions.calendar_year = objectives.calendar_year AND sessions.idMod = objectives.idMod WHERE sessions.idMod = ? and sessions.identifier = ? AND sessions.calendar_year = ? ORDER BY sequence");
$result->bind_param('iss', $modID, $identifier, $calendar_year);
$result->execute();
$result->bind_result($title, $source_url, $calendar_year, $occurrence, $obj_id, $objective);
$sess = array();
while ($result->fetch()) {
  if ( !isset($sess['identifier']) ) {
    $sess['identifier'] = $identifier;
    $sess['moduleID'] = $modID;
    $sess['title'] = $title;
    $sess['source_url'] = $source_url;
    $sess['calendar_year'] = $calendar_year;
    $sess['occurrence'] = $occurrence;
  }
  if ($obj_id != '') {
   $sess['objectives'][$obj_id] = $objective;
  }
}
$result->close();

if (count($sess) == 0) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}


if (isset($_POST['Edit'])) {
  //save session
  $occurrence = $_POST['year'] . $_POST['month'] . $_POST['day'] . $_POST['time'];

  //update session
  $stmt = $mysqli->prepare("UPDATE sessions SET title = ?,source_url = ?, occurrence = ? WHERE identifier = ? AND idMod = ? AND identifier = ? AND calendar_year = ?");
  $stmt->bind_param('ssssiss', $_POST['session_title'], $_POST['url'], $occurrence, $identifier, $modID, $identifier, $calendar_year);
  $stmt->execute();
  $stmt->close();

  $maxID = 0;
  $sequence = 0;
  foreach ($_POST as $key => $value) {
    $tmp = explode('_', $key);
    if (count($tmp) > 1) {
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
          $stmt = $mysqli->prepare("DELETE FROM objectives WHERE obj_id = ? AND idMod = ? AND identifier = ? AND calendar_year = ?");
          $stmt->bind_param('iiss', $objId, $modID, $identifier, $calendar_year);
          $stmt->execute();
          $stmt->close();

          $stmt = $mysqli->prepare("DELETE FROM relationships WHERE obj_id = ? AND idMod = ? AND calendar_year = ? AND vle_api = ''");
          $stmt->bind_param('iis', $objId, $modID, $calendar_year);
          $stmt->execute();
          $stmt->close();
        } else {
          $sequence++;
          //update obj
          $stmt = $mysqli->prepare("UPDATE objectives SET objective = ?, sequence = ? WHERE obj_id = ? AND idMod = ? AND identifier = ? AND calendar_year = ?");
          $stmt->bind_param('sisiss', $value, $sequence, $objId, $modID, $identifier, $_POST['session']);
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
          while ($result->fetch()) {
            $maxID = $largest + 1;
          }
        }
        if ($value != '' and $value != 'Type New Objective here...') {
          $sequence++;
          //insert new obj
          $stmt = $mysqli->prepare("INSERT INTO objectives VALUES (?, ?, ?, ?, ?, ?)");
          $stmt->bind_param('issssi', $maxID, $value, $modID, $identifier, $calendar_year, $sequence);
          $stmt->execute();
          $stmt->close();
          $maxID++;
        }
        break;
    }
  }

  //redirect to list sessions
  header("Location: ./sessions_list.php?module=" . $modID . "&folder=" . $folder);
	exit();
} else if(isset($_POST['cancel'])) {
  header("Location: ./sessions_list.php?module=" . $modID . "&folder=" . $folder);
	exit();
} else {
  //display form
?>
<!DOCTYPE html>
  <html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

    <title>Rog&#333;: <?php echo $string['manageobjectives'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

    <link rel="stylesheet" type="text/css" href="../css/body.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css" />
    <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
    <link rel="stylesheet" type="text/css" href="../css/warnings.css" />
    <style type="text/css">
      .editBox {width:90%}
      .field {text-align:right}
      .note {width:90%}
    </style>
		
    <script src="../js/staff_help.js" type="text/javascript"></script>
    <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
		<script type="text/javascript" src="../js/toprightmenu.js"></script>
    <script>
      $(function () {
        $('#theform').validate({
          errorClass: 'errfield',
          errorPlacement: function(error,element) {
            return true;
          }
        });
        $('form').removeAttr('novalidate');
      });

      var ObjNewCount = 0;
      var ObjCount = 0;
      function addNew(ulId) {
        ul = document.getElementById( ulId );
        li = document.createElement("li");
        li.id = 'li_' + ulId + ObjNewCount;
        li.style.margin = '0.5em';
        li.style.marginLeft = '3.5em';
        li.innerHTML = '<img src="./up_on.png" onclick="promote( \'' + li.id + '\' )" />&nbsp<img src="./down_on.png" onclick="demote( \'' + li.id + '\' )" />&nbsp<input class="editBox" name="objnew_' + ObjNewCount + '" id="objnew_' + ObjNewCount + '" type="text" value="" placeholder="<?php echo $string['msg1']; ?>" /></li>';
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
    </script>
  </head>
  <body onclick="hideSessCopyMenu(event);">
<?php
require '../include/sessions_options.inc';
require '../include/toprightmenu.inc';

echo draw_toprightmenu();

?>
<div id="content">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=<?php echo $modID ?>"><?php echo module_utils::get_moduleid_from_id($modID, $mysqli) ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="sessions_list.php?module=<?php echo $modID . '&folder=' . $folder ?>"><?php echo $string['manageobjectives'] ?></a></div>
  <div class="page_title"><?php echo $string['editsession'] ?></div>
</div>
<br />
<?php

echo "<form id=\"theform\" name=\"editObj\" action=\"" . $_SERVER['PHP_SELF'] . "?module=$modID&calendar_year=$calendar_year\" method=\"post\" autocomplete=\"off\">\n<div align=\"center\"><table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:85%; text-align:left\">\n";

echo "<tr><td style=\"width:92px\" class=\"field\">" . $string['title'] . "</td><td><input type=\"text\" name=\"session_title\" id=\"session_title\" size=\"60\" value=\"" . $sess['title'] . "\" required autofocus /></td></tr>\n";

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
    echo "<input class='editBox' onfocus=\"clearTextbox('obj_" . $id . "');\" id=\"obj_" . $id . "\" name=\"obj_" . $id . "\" type=\"text\" value=\"" . htmlentities($obj, ENT_QUOTES, 'UTF-8') . "\" />";
    echo "</li>\n";
  }
}
echo '<li style="margin: 0.5em; margin-left:6em"><input style="width:80px" type="button" value="' . $string['new'] . '"  onclick="addNew(\'objList\')"></li>';
echo '</ul>';

//add the save buttens
echo '<ul style="margin-left:0px; list-style-type:none; width:100%">';
echo '<li style="margin: 0.5em; margin-left: 0.5em; text-align: center">';
echo '<input name="Edit" class="ok" type="submit" value="' . $string['save'] . '" ><input name="cancel" class="cancel" type="submit" value="' . $string['cancel'] . '">';
echo '</li>';
echo "</ul>\n";

echo "<input type=\"hidden\" name=\"identifier\" value=\"$identifier\" />";
echo "</td></tr>\n</table>\n</div>\n";
echo "</form>\n";
echo '<script>updateButtons();</script>';
?>
</div>
</body>
</html>
<?php
}
?>