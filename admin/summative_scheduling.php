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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/sysadmin_auth.inc';

function getLabs($labs, $mysqlidb)
{

    global $string;
    $html = '';
    if ($labs != '') {
        $results = $mysqlidb->prepare("SELECT room_no FROM labs WHERE id IN ($labs)");
        $results->execute();
        $results->bind_result($room_no);
        while ($results->fetch()) {
            if ($html == '') {
                $html = $room_no;
            } else {
                $html .= ', ' . $room_no;
            }
        }
        $results->close();
        $html = '<span style="color:#FF6300">' . $html . '</span>';
    } else {
        $html = '<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" /> <span style="color:#C00000">' . $string['nolabsset'] . '</span>';
    }

    return $html;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['summativescheduling']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/summativeschedulinginit.min.js"></script>
</head>

<body>
<?php
  require '../include/scheduling_options.inc';
require '../include/toprightmenu.inc';

    echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools']; ?></a></div>
  <div class="page_title"><?php echo $string['summativescheduling'] ?></div>
</div>

<table class="header">
<tr>
<th><div class="col10 s"><?php echo $string['title'] ?></div></th>
<th class="col"><?php echo $string['month'] ?></th>
<th class="col"><?php echo $string['campus'] ?></th>
<th class="col"><?php echo $string['modules'] ?></th>
<th class="col"><?php echo $string['cohortsize'] ?></th>

</tr>
  <tr><td colspan="5"><table border="0" class="subsect" style="width:98%"><tr><td><nobr><?php echo $string['unscheduled']; ?></nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>
<?php
  $rowID = 0;
$months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
$papers = array();
$results = $mysqli->prepare('SELECT properties.property_id, paper_title, moduleid, period, barriers_needed, cohort_size, campus FROM (properties, properties_modules, modules, scheduling) WHERE start_date IS NULL AND properties.property_id=scheduling.paperID AND properties.property_id=properties_modules.property_id AND properties_modules.idMod=modules.id AND deleted IS NULL ORDER BY period');
$results->execute();
$results->store_result();
$results->bind_result($property_id, $paper_title, $moduleID, $period, $barriers_needed, $cohort_size, $campus);
while ($results->fetch()) {
    if (!isset($papers[$property_id])) {
        $papers[$property_id] = array('paper_title' => $paper_title, 'period' => $period, 'barriers_needed' => $barriers_needed, 'cohort_size' => $cohort_size, 'campus' => $campus);
    }
    $papers[$property_id]['modules'][] = $moduleID;
}
  $results->close();
foreach ($papers as $property_id => $paper_details) {
    $cohort_size = str_replace('<', '&lt;', $paper_details['cohort_size']);
    $cohort_size = str_replace('>', '&gt;', $cohort_size);
    if ($paper_details['period'] != '') {
        $display_month = $string[$months[$paper_details['period']]];
    } else {
        $display_month = '&lt;unknown&gt;';
    }

    echo "<tr class=\"l\" id=\"$property_id\">";
    echo '<td style="padding-left:24px">' . $paper_details['paper_title'] . "</td><td>$display_month</td><td>" . $paper_details['campus'] . '</td><td>';
    $html = '';
    foreach ($paper_details['modules'] as $individual_module) {
        if ($html == '') {
            $html = $individual_module;
        } else {
            $html .= ', ' . $individual_module;
        }
    }
    echo "$html</td><td>$cohort_size</td></tr>\n";
}
?>
  <tr><td colspan="5">&nbsp;</td></tr>
  <tr><td colspan="5"><table border="0" class="subsect" style="width:98%"><tr><td><nobr><?php echo $string['scheduled']; ?></nobr></td><td style="width:98%"><hr noshade="noshade" style="border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%" /></td></tr></table></td></tr>
<?php
  $papers = array();
$results = $mysqli->prepare("SELECT properties.property_id, paper_title, moduleid, period, barriers_needed, cohort_size, campus, DATE_FORMAT(start_date,'{$configObject->get('cfg_long_date_time')}'), end_date, labs FROM (properties, properties_modules, modules, scheduling) WHERE start_date > NOW() AND properties.property_id=scheduling.paperID AND properties.property_id=properties_modules.property_id AND properties_modules.idMod=modules.id AND deleted IS NULL ORDER BY period");
$results->execute();
$results->store_result();
$results->bind_result($property_id, $paper_title, $moduleID, $period, $barriers_needed, $cohort_size, $campus, $start_date, $end_date, $labs);
while ($results->fetch()) {
    if (!isset($papers[$property_id])) {
        $papers[$property_id] = array('paper_title' => $paper_title, 'period' => $period, 'barriers_needed' => $barriers_needed, 'cohort_size' => $cohort_size, 'campus' => $campus, 'start_date' => $start_date, 'end_date' => $end_date, 'labs' => $labs);
    }
    $papers[$property_id]['modules'][] = $moduleID;
}
  $results->close();
foreach ($papers as $property_id => $paper_details) {
    $cohort_size = str_replace('<', '&lt;', $paper_details['cohort_size']);
    $cohort_size = str_replace('>', '&gt;', $cohort_size);
    echo "<tr class=\"l\" id=\"$property_id\">";
    echo '<td><img src="../artwork/shortcut_calendar_icon.png" width="16" height="16" />&nbsp;' . $paper_details['paper_title'] . '</td><td>' . $paper_details['start_date'] . "</td><td>$campus " . getLabs($paper_details['labs'], $mysqli) . '</td><td>';
    $html = '';
    foreach ($paper_details['modules'] as $individual_module) {
        if ($html == '') {
            $html = $individual_module;
        } else {
            $html .= ', ' . $individual_module;
        }
    }
    echo "$html</td><td>$cohort_size</td></tr>\n";
}
?>
</table>
</div>

</body>
</html>
