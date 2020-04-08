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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
require '../include/timezones.php';

$paper_name = param::required('paper_name', param::TEXT, param::FETCH_POST);
$paper_type = param::required('paper_type', param::TEXT, param::FETCH_POST);

// Check that the new paper name is not already used by any other paper (i.e. unique).
if (!Paper_utils::is_paper_title_unique($paper_name, $mysqli)) {
    header('location: new_paper1.php?paper_name=' . $paper_name . '&paper_type=' . $paper_type);
    exit();
}

$assessment = new assessment($mysqli, $configObject);
$papertype = $assessment->get_type_value($paper_type);
$central_mgmt = $configObject->get_setting('core', 'cfg_summative_mgmt');
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('Rog&#333;: ' . $string['createnewpaper']); ?></title>
<?php

  // Setup the new paper.
if (isset($_POST['folder'])) {
    $folder = $_POST['folder'];
} else {
    $folder = '';
}

?>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/new_paper.css" />

  <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/newpaper2init.min.js"></script>

<body>
<form id="myform" name="myform" action="" method="post" autocomplete="off">
<table border="0" cellpadding="0" cellspacing="4" style="width:100%">
<tr>
<td>
<?php
  echo "<table width=\"100%\" border=\"0\">\n";
if (!$central_mgmt or $papertype != $assessment::TYPE_SUMMATIVE) {
    echo '<tr><td colspan="6" class="titlebar">' . $string['availability'] . "</td></tr>\n";
} else {
    echo '<tr><td colspan="6" class="titlebar">' . $string['summativeexamdetails'] . "</td></tr>\n";
}
if ($papertype == $assessment::TYPE_SUMMATIVE or $papertype == $assessment::TYPE_OSCE or $papertype == $assessment::TYPE_OFFLINE) {
    echo '<tr><td style="width:140px; text-align:right; vertical-align:top">' . $string['academicsession'] . '</td><td>';
    echo "<select name=\"session\">\n";
    $module_details = module_utils::get_full_details_by_ID($_POST['module'], $mysqli);
    $yearutils = new yearutils($mysqli);
    echo $yearutils->get_calendar_year_dropdown_options($papertype, $yearutils->get_current_session($module_details['academic_year_start']), $string);
    echo "</select></td>\n";
} else {
    echo "<input type=\"hidden\" name=\"session\" value=\"\" />\n";
}

if (!$central_mgmt or $papertype != $assessment::TYPE_SUMMATIVE) {
    echo '</tr><tr><td align="right" valign="top">' . $string['from'] . '&nbsp;</td><td>';
    $date_array = getdate();

    // Available from Day
    $current_day = date('j');
    echo "<select id=\"fday\" name=\"fday\" class=\"datecopy\">\n";
    for ($i = 1; $i <= 31; $i++) {
        echo '<option value="';
        if ($i < 10) {
            echo '0';
        }
        echo "$i\"";
        if ($i == $current_day) {
            echo ' selected';
        }
        echo '>';
        if ($i < 10) {
            echo '0';
        }
        echo "$i</option>\n";
    }
    echo "</select><select id=\"fmonth\" name=\"fmonth\" class=\"datecopy\">\n";
    $current_month = (date('n') + 1);
    if ($current_month > 12) {
        $current_month = 1;
    }
    $months = array('', 'january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    for ($i = 1; $i <= 12; $i++) {
        $trans_month = mb_substr($string[$months[$i]], 0, 3, 'UTF-8');
        if ($i < 10) {
            if ($i == $current_month) {
                echo "<option value=\"0$i\" selected>$trans_month</option>\n";
            } else {
                echo "<option value=\"0$i\">$trans_month</option>\n";
            }
        } else {
            if ($i == $current_month) {
                echo "<option value=\"$i\" selected>$trans_month</option>\n";
            } else {
                echo "<option value=\"$i\">$trans_month</option>\n";
            }
        }
    }
    echo "</select><select id=\"fyear\" name=\"fyear\" class=\"datecopy\">\n";
    for ($i = $date_array['year']; $i < ($date_array['year'] + 21); $i++) {
        if ($current_month == 1 and $i == ($date_array['year'] + 1)) {
            echo "<option value=\"$i\" selected>$i</option>\n";
        } else {
            echo "<option value=\"$i\">$i</option>\n";
        }
    }
    echo "</select><select id=\"ftime\" name=\"ftime\" class=\"datecopy\">\n";
    // Available from Hour
    $times = array('000000' => '00:00','003000' => '00:30','010000' => '01:00','013000' => '01:30','020000' => '02:00','023000' => '02:30','030000' => '03:00','033000' => '03:30','040000' => '04:00','043000' => '04:30','050000' => '05:00','053000' => '05:30','060000' => '06:00','063000' => '06:30','070000' => '07:00','073000' => '07:30','080000' => '08:00','083000' => '08:30','090000' => '09:00','093000' => '09:30','100000' => '10:00','103000' => '10:30','110000' => '11:00','113000' => '11:30','120000' => '12:00','123000' => '12:30','130000' => '13:00','133000' => '13:30','140000' => '14:00','143000' => '14:30','150000' => '15:00','153000' => '15:30','160000' => '16:00','163000' => '16:30','170000' => '17:00','173000' => '17:30','180000' => '18:00','183000' => '18:30','190000' => '19:00','193000' => '19:30','200000' => '20:00','203000' => '20:30','210000' => '21:00','213000' => '21:30','220000' => '22:00','223000' => '22:30','230000' => '23:00','233000' => '23:30');
    foreach ($times as $key => $value) {
        echo '<option value="' . $key . '">' . $value . "</option>\n";
    }
    echo "</select>\n</td>";
    echo '<td align="right">' . $string['to'] . '&nbsp;</td><td>';

    // Available to Day
    $current_day = date('j');
    echo "<select id=\"tday\" name=\"tday\" class=\"datecopy\">\n";
    for ($i = 1; $i <= 31; $i++) {
        echo '<option value="';
        if ($i < 10) {
            echo '0';
        }
        echo "$i\"";
        if ($i == $current_day) {
            echo ' selected';
        }
        echo '>';
        if ($i < 10) {
            echo '0';
        }
        echo "$i</option>\n";
    }
    echo "</select><select id=\"tmonth\" name=\"tmonth\" class=\"datecopy\">\n";
    for ($i = 1; $i <= 12; $i++) {
        $trans_month = mb_substr($string[$months[$i]], 0, 3, 'UTF-8');
        if ($i < 10) {
            if ($i == $current_month) {
                echo "<option value=\"0$i\" selected>$trans_month</option>\n";
            } else {
                echo "<option value=\"0$i\">$trans_month</option>\n";
            }
        } else {
            if ($i == $current_month) {
                echo "<option value=\"$i\" selected>$trans_month</option>\n";
            } else {
                echo "<option value=\"$i\">$trans_month</option>\n";
            }
        }
    }
    echo '</select>';

    // Available to Year
    if ($paper_type == 'summative' or $paper_type == 'osce' or $paper_type == 'offline') {
        $target_year = $date_array['year'];
        if ($current_month == 1) {
            $target_year += 1;
        }
    } else {
        $target_year = $date_array['year'] + 20;
    }
    echo "<select id=\"tyear\" name=\"tyear\" class=\"datecopy\">\n";
    for ($i = $date_array['year']; $i < $date_array['year'] + 21; $i++) {
        if ($i == $target_year) {
            echo "<option value=\"$i\" selected>$i</option>\n";
        } else {
            echo "<option value=\"$i\">$i</option>\n";
        }
    }
    echo "</select><select id=\"ttime\" name=\"ttime\" class=\"datecopy\">\n";
    // Available to Hour
    foreach ($times as $key => $value) {
        echo '<option value="' . $key . '">' . $value . "</option>\n";
    }
    echo "</select>\n</td></tr>\n";

    echo '<tr><td align="right">' . $string['timezone'] . '</td><td colspan="3"><select name="timezone">';
    foreach ($timezone_array as $individual_zone => $display_zone) {
        if ($individual_zone == $configObject->get('cfg_timezone')) {
            echo "<option value=\"$individual_zone\" selected>$display_zone</option>";
        } else {
            echo "<option value=\"$individual_zone\">$display_zone</option>";
        }
    }
    echo '</optgroup></select></td></tr>';
} else {
    echo '<td style="text-align:right">' . $string['barriersneeded'] . '</td><td><input type="checkbox" name="barriers_needed" value="1" checked="checked" /><td style="text-align:right">' . $string['duration'] . '</td><td>';
      echo '<select name="duration_hours" id="duration_hours">';
    echo "<option value=\"\"></option>\n";
    for ($i = 0; $i <= 12; $i++) {
        echo "<option value=\"$i\">$i</option>\n";
    }
    echo '</select> ' . $string['hrs'] . ' <select name="duration_mins" id="duration_mins">';
    echo "<option value=\"\"></option>\n";
    for ($i = 0; $i < 60; $i++) {
        echo "<option value=\"$i\">$i</option>\n";
    }
    echo '</select> ' . $string['mins'] . '</td></tr>';
    echo '<tr><td style="text-align:right">' . $string['daterequired'] . '</td><td><select name="period" id="period">';
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    echo "<option value=\"\"></option>\n";
    for ($i = 0; $i < 12; $i++) {
        echo "<option value=\"$i\">" . $string[$months[$i]] . "</option>\n";
    }
    echo '</select></td><td style="text-align:right">' . $string['cohortsize'] . '</td><td><select name="cohort_size" id="cohort_size">';
    echo "<option value=\"\"></option>\n";
    $sizes = array('&lt;' . $string['wholecohort'] . '&gt', '0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300', '301-400', '401-500');
    foreach ($sizes as $size) {
        echo "<option value=\"$size\">$size</option>\n";
    }
    echo '</select></td><td style="text-align:right">' . $string['sittings'] . '</td><td><select name="sittings">';
    for ($i = 1; $i <= 6; $i++) {
        echo "<option value=\"$i\">$i</option>";
    }
    echo '</select></td></tr>';

    echo '<tr><td style="text-align:right">' . $string['campus'] . '</td><td colspan="5"><select name="campus">';
    $campusobj = new campus($mysqli);
    $campuses = $campusobj->get_all_campus_details();
    foreach ($campuses as $key => $campusarray) {
        if ($campusarray['isdefault']) {
            echo '<option value="' . $campusarray['campusname'] . '" selected>' . $campusarray['campusname'] . '</option>';
        } else {
            echo '<option value="' . $campusarray['campusname'] . '">' . $campusarray['campusname'] . '</option>';
        }
    }
    echo '</select></td></tr>';
    echo '<tr><td style="text-align:right">' . $string['notes'] . '</td><td colspan="5"><textarea style="width:100%; height:75px" cols="40" rows="3" name="notes"></textarea></td></tr>';
}

echo "</table>\n";

if (!$central_mgmt) {
    PaperSettings::renderNewSettings($string, $mysqli, $paper_type, 'security');
}

echo '<div class="titlebar" style="margin-top:5px; border-top:1px solid #295AAD; border-left:1px solid #295AAD; border-right:1px solid #295AAD">' . $string['modules'] . '</div>';
if ($central_mgmt and $papertype == $assessment::TYPE_SUMMATIVE) {
    echo '<div style="display:block; background-color:white; height:230px; overflow-y:scroll; border:1px solid #295AAD; font-size:90%">';
} elseif ($papertype == $assessment::TYPE_OSCE or (!$central_mgmt and $papertype == $assessment::TYPE_SUMMATIVE)) {
    echo '<div style="display:block; background-color:white; height:310px; overflow-y:scroll; border:1px solid #295AAD; font-size:90%">';
} else {
    echo '<div style="display:block; background-color:white; height:340px; overflow-y:scroll; border:1px solid #295AAD; font-size:90%">';
}
  $staff_modules_sql = "'" . implode("','", array_keys($staff_modules)) . "'";

  $module_no = 0;
  $module_array = $userObject->get_staff_accessable_modules();
  $current_school = '---';
  $old_schoolcode = '';
foreach ($module_array as $module) {
    if (is_null($module['schoolcode'])) {
        if ($module['school'] != $current_school or !is_null($old_schoolcode)) {
            $current_school = $module['school'];
            echo '<div class="subsect_table"><div class="subsect_title"><nobr>' . $module['school'] . "&nbsp;</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
        }
    } else {
        if ($module['schoolcode'] != $old_schoolcode) {
            $old_schoolcode = $module['schoolcode'];
            echo '<div class="subsect_table"><div class="subsect_title"><nobr>' . $module['schoolcode'] . ' ' . $module['school'] . "&nbsp;</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
        }
    }
    if (isset($_POST['module']) and $_POST['module'] == $module['idMod']) {
        echo "<div class=\"r2\" id=\"div$module_no\"><input data-mod=\"$module_no\" type=\"checkbox\" name=\"mod$module_no\" id=\"mod$module_no\" value=\"" . $module['idMod'] . "\" checked /><label for=\"mod$module_no\">" . $module['id'] . ' - ' . substr($module['fullname'], 0, 60) . "</label></div>\n";
    } else {
        echo "<div class=\"r1\" id=\"div$module_no\"><input data-mod=\"$module_no\" type=\"checkbox\"  name=\"mod$module_no\" id=\"mod$module_no\" value=\"" . $module['idMod'] . "\" /><label for=\"mod$module_no\">" . $module['id'] . ' - ' . substr($module['fullname'], 0, 60) . "</label></div>\n";
    }
    $module_no++;
    $old_schoolcode = $module['schoolcode'];
}

  echo "</div>\n";

  echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" />\n";
  echo '<input type="hidden" name="paper_type" id="paper_type" value="' . $paper_type . "\" />\n";
  echo '<input type="hidden" name="paper_name" id="paper_name" value="' . $paper_name . "\" />\n";
  echo "<input type=\"hidden\" name=\"current_year\" id=\"current_year\" value=\"year1\" />\n";
  echo '<input type="hidden" name="folder" value="' . $_POST['folder'] . "\" />\n";
  echo '<input type="hidden" name="paper_owner" value="' . $userObject->get_user_ID() . "\" />\n";
?>
<div style="text-align:right"><input type="submit" name="submit2" value="<?php echo $string['finish']; ?>" class="ok" /></div>

</td>
</tr>
</table>
<?php
    // JS utils dataset.
    $render = new render($configObject);
    $jsdataset['name'] = 'jsutils';
    $jsdataset['attributes']['xls'] = json_encode($string);
    $render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
