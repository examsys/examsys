<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Anthony Brown, Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$modID = (int)check_var('module', 'GET', true, false, true);

if (!module_utils::get_moduleid_from_id($modID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (isset($_POST['Save'])) {
    //save session
    $occurrence = $_POST['session_year'] . '-' . $_POST['session_month'] . '-' . $_POST['session_day'] . ' ' . $_POST['session_time'];

    $stmt = $mysqli->prepare('INSERT INTO sessions VALUES (NULL, ?, ?, ?, ?, ?, ?)');
    $identifier = mappingutils::generate_session_identifier();
    $stmt->bind_param('ssssss', $identifier, $modID, $_POST['session_title'], $_POST['url'], $_POST['session'], $occurrence);
    $stmt->execute();
    $stmt->close();

    $obj_id = mappingutils::get_objectives_start();
    if (isset($_POST['objectives']) and $_POST['objectives'] != '') {
        parse_str($_POST['objectives'], $sortarray);
        $list = $sortarray['li'];
        $j = 0;
        foreach ($list as $i) {
            if ($_POST["objnew_$i"] != $string['msg1']) {
                $stmt = $mysqli->prepare('INSERT INTO objectives VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('issssi', $obj_id, $_POST["objnew_$i"], $modID, $identifier, $_POST['session'], $j);
                $stmt->execute();
                $stmt->close();
            }
            $obj_id++;
            $j++;
        }
    }

    //redirect to list sessions
    header('Location: ./sessions_list.php?module=' . $_GET['module'] . '&folder=' . $_GET['folder']);
    exit();
} elseif (isset($_POST['cancel']) and $_POST['cancel'] == 'Cancel') {
    header('Location: ./sessions_list.php?module=' . $_GET['module'] . '&folder=' . $_GET['folder']);
    exit();
} else {
    $stmt = $mysqli->prepare('SELECT calendar_year FROM modules_student, modules WHERE modules_student.idMod = modules.id AND modules_student.idMod = ? ORDER BY calendar_year DESC LIMIT 1');
    $stmt->bind_param('i', $_GET['module']);
    $stmt->execute();
    $stmt->bind_result($session);
    $stmt->fetch();
    $stmt->close();
    ?>
<!DOCTYPE html>
  <html>
  <head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['manageobjectives']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/jquery-ui.css" />
  <link rel="stylesheet" type="text/css" href="../css/jquery-theme.css" />
  <style type="text/css">
    .editBox {width:90%}
    .field {text-align:right}
    .note {width:90%}
  </style>

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/mappingsessioninit.min.js"></script>

  </head>
  <body>
    <?php
    require '../include/sessions_options.inc';
    require '../include/toprightmenu.inc';

    echo draw_toprightmenu();

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
    ?>
<div id="content">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="../module/index.php?module=<?php echo $modID ?>"><?php echo module_utils::get_moduleid_from_id($modID, $mysqli) ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="sessions_list.php?module=<?php echo $modID . '&folder=' . $folder ?>"><?php echo $string['manageobjectives'] ?></a></div>
  <div class="page_title"><?php echo $string['newsession'] ?></div>
</div>
<br />
    <?php
    echo '<form id="theform" name="editObj" action="' . $_SERVER['PHP_SELF'] . '?module=' . $_GET['module'] . "&folder=\" method=\"post\" autocomplete=\"off\">\n<div align=\"center\"><table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:85%; text-align:left\">\n";
    echo '<tr><td style="width:92px" class="field">' . $string['title'] . "</td><td><input type=\"text\" name=\"session_title\" id=\"session_title\" size=\"60\" value=\"\" required autofocus /></td></tr>\n";

    echo '<tr><td class="field">' . $string['session'] . '</td><td>';
    $validfrom = '<select name="session">' . "\n";
    $startyear = ( date('Y') - 1 );
    for ($i = 0; $i < 2; $i++) {
        $tmp_session = ($startyear + $i) . '/' . mb_substr(($startyear + $i + 1), 2);
        $tmp_calyear = $startyear + $i;
        if ($tmp_session == $session) {
            $validfrom .= '<option value="' . $tmp_calyear . '" selected>' . $tmp_session . '</option>';
        } else {
            $validfrom .= '<option value="' . $tmp_calyear . '">' . $tmp_session . '</option>';
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
    $validfrom = '<select name="session_day">' . "\n";
    foreach (range(1, 31) as $day) {
        $selected = ($day == $currentday ) ? ' selected="selected"' : '';
        if ($day < 10) {
            $day = '0' . $day;
        }
        $validfrom .= '<option value="' . $day . '"' . $selected . '>' . $day . '</option>' . "\n";
    }
    $validfrom .= '</select>&nbsp;';
    echo $validfrom;

    // Month
    $validfrom = '<select name="session_month">' . "\n";
    $month_names = array(1 => 'january', 2 => 'february', 3 => 'march', 4 => 'april', 5 => 'may', 6 => 'june', 7 => 'july', 8 => 'august', 9 => 'september', 10 => 'october', 11 => 'november', 12 => 'december');
    for ($month = 1; $month <= 12; $month++) {
        $selected = ($month == $currentmonth ) ? ' selected="selected"' : '';
        $validfrom .= '<option value="' . $month . '"' . $selected . '>' . mb_substr($string[$month_names[$month]], 0, 3, 'UTF-8') . '</option>' . "\n";
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
    $validfrom = '<select name="session_year">' . "\n";
    foreach (range($startyear, $maxyear) as $years) {
        $selected = ($years == $currentyear ) ? ' selected="selected"' : '';
        $validfrom .= '<option value="' . $years . '"' . $selected . '>' . $years . '</option>' . "\n";
    }
    $validfrom .= '</select>';
    echo $validfrom;

    echo "</select>\n";

    echo "<select name=\"session_time\">\n";

    // Available from Hour
    $now = date('H') . ':00' . ':00';
    $times = array('00:00:00' => '00:00','00:30:00' => '00:30','01:00:00' => '01:00','01:30:00' => '01:30','02:00:00' => '02:00','02:30:00' => '02:30','03:00:00' => '03:00','03:30:00' => '03:30','04:00:00' => '04:00','04:30:00' => '04:30','05:00:00' => '05:00','05:30:00' => '05:30','06:00:00' => '06:00','06:30:00' => '06:30','07:00:00' => '07:00','07:30:00' => '07:30','08:00:00' => '08:00','08:30:00' => '08:30','09:00:00' => '09:00','09:30:00' => '09:30','10:00:00' => '10:00','10:30:00' => '10:30','11:00:00' => '11:00','11:30:00' => '11:30','12:00:00' => '12:00','12:30:00' => '12:30','13:00:00' => '13:00','13:30:00' => '13:30','140000' => '14:00','14:30:00' => '14:30','15:00:00' => '15:00','15:30:00' => '15:30','16:00:00' => '16:00','16:30:00' => '16:30','17:00:00' => '17:00','17:30:00' => '17:30','18:00:00' => '18:00','18:30:00' => '18:30','19:00:00' => '19:00','19:30:00' => '19:30','20:00:00' => '20:00','20:30:00' => '20:30','21:00:00' => '21:00','21:30:00' => '21:30','22:00:00' => '22:00','22:30:00' => '22:30','23:00:00' => '23:00','23:30:00' => '23:30');
    foreach ($times as $key => $value) {
        if ($key == $now) {
            echo '<option value="' . $key . '" selected>' . $value . "</option>\n";
        } else {
            echo '<option value="' . $key . '">' . $value . "</option>\n";
        }
    }
    echo "</select></td></tr>\n";
    echo '<tr><td class="field">' . $string['url'] . '</td><td><input name="url" class="editBox" type="text" value="" /></td></tr>';
    echo "\n<tr><td colspan=\"2\"><ul id=\"objList\" style=\"margin-left:0px; list-style-type: none; width: 100%\">\n";
    for ($i = 0; $i < 3; $i++) {
        $id = $i;
        echo "\t<li class=\"ui-state-default\" id=\"li_$id\" style=\"margin:0.5em; margin-left:3.5em\">";
        echo '<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>';
        echo "<input class='editBox' id=\"objnew_" . $id . '" name="objnew_' . $id . '" type="text" value="" placeholder="' . $string['msg1'] . '" />';
        echo "</li>\n";
    }
    echo '</ul>';
    echo '<input id="new" style="margin:0.5em; margin-left:6em; width: 80px" type="button" value="' . $string['new'] . '" />';
    echo '<input id="objectives" name="objectives" type="hidden" value="" />';


    //add the save buttens
    echo '<ul style="margin-left:0px; list-style-type:none; width:100%">';
    echo '<li style="margin:0.5em; margin-left:0.5em; text-align:center">';
    echo '<input name="Save" class="ok" type="submit" value="' . $string['save'] . '" /><input name="cancel" class="cancel" type="button" value="' . $string['cancel'] . '" />';
    echo '</li>';
    echo "</ul>\n";

    echo "</td></tr>\n</table>\n</div>\n</form>\n";
    ?>
    </div>
    <?php
    // Dataset.
    $render = new render($configObject);
    $miscdataset['name'] = 'dataset';
    $miscdataset['attributes']['folder'] = $folder;
    $miscdataset['attributes']['module'] = $module;
    $render->render($miscdataset, array(), 'dataset.html');
    // JS utils dataset.
    $jsdataset['name'] = 'jsutils';
    $jsdataset['attributes']['xls'] = json_encode($string);
    $render->render($jsdataset, array(), 'dataset.html');
    ?>
  </body>
  </html>
    <?php
}
?>
