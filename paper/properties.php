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
 * Allows the properties of a paper to be edited.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';
require_once '../include/add_edit.inc';  // to clear MS Office tags
require_once '../include/load_config.php';
require_once '../include/timezones.php';

// Marking options
define('MARK_NO_ADJUSTMENT', '0');
define('MARK_RANDOM', '1');
define('MARK_STD_SET', '2');

$paperID = check_var('paperID', 'REQUEST', true, false, true);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);
$render = new render($configObject);
$texteditorplugin = \plugins\plugins_texteditor::get_editor();
/**
 * Define callbacks to be used when retrieving tracked changes
 * @param  array  $changed_reviewers    Array of reviewers referenced in changes
 * @param  array  $changed_labs         Array of labs referenced in changes
 * @return array                        Array of callbacks to be registered with the logger
 */
function setup_change_callbacks(&$changed_reviewers, &$changed_labs)
{
    // Define a closure to populate past reviewer IDs
    $reviewers_cb = function ($old, $new) use (&$changed_reviewers) {
        $old_reviewers = explode(',', $old);
        $new_reviewers = explode(',', $new);

        // Add any reviewers in the current change to the $changed_reviewers array
        foreach ($old_reviewers as $reviewer) {
            if ($reviewer != '') {
                $changed_reviewers[$reviewer] = false;
            }
        }
        foreach ($new_reviewers as $reviewer) {
            if ($reviewer != '') {
                $changed_reviewers[$reviewer] = false;
            }
        }
    };

    // Define a closure to populate past labs
    $labs_cb = function ($old, $new) use (&$changed_labs) {
        $old_labs = explode(',', $old);
        $new_labs = explode(',', $new);

        // Add any labs in the current change to the $changed_labs array
        foreach ($old_labs as $lab) {
            if ($lab != '') {
                $changed_labs[$lab] = false;
            }
        }
        foreach ($new_labs as $lab) {
            if ($lab != '') {
                $changed_labs[$lab] = false;
            }
        }
    };

    // Use the closures for changes
    $callbacks = array('externals' => $reviewers_cb, 'internals' => $reviewers_cb, 'labs' => $labs_cb);

    return $callbacks;
}

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
if ($properties->get_paper_type() == '2') {
    $minavailability = $properties->getMinAvailability();
} else {
    $minavailability = 0;
}
$modules_array = $properties->get_modules();

$q_feedback_enabled = Paper_utils::q_feedback_enabled(array_keys($modules_array), $mysqli);  // See if question-based feedback is enabled on all modules.

// Build up a list of all past reviewers and labs for the 'changes' tab
$changed_reviewers = array();
$changed_labs = array();

$change_callbacks = setup_change_callbacks($changed_reviewers, $changed_labs);

$logger = new Logger($mysqli);

// Get the changes to be used later
$changes = $logger->get_changes('Paper', $paperID, $change_callbacks);

if ($properties->get_summative_lock() and !$userObject->has_role('SysAdmin')) {
    $locked = true;
    $disabled = ' disabled';
} else {
    $locked = false;
    $disabled = '';
}

if (!isset($staff_modules)) {
    $staff_modules = get_staff_modules($userObject->get_user_ID(), $mysqli, $userObject);
}

function format_color($color)
{
    return '<div style="background-color:' . $color . '; border:1px solid #C0C0C0; width:50px; height:15px"></div>';
}

function format_referencematerial($ID, $refID)
{
    if ($ID == '') {
        return '';
    }

    return $refID[$ID];
}

function format_folders($id, $folders)
{
    if ($id == '') {
        return '';
    }

    if (isset($folders[$id])) {
        $formatted_string = str_replace(';', '/', $folders[$id]);
    } else {
        $formatted_string = $id;
    }

    return $formatted_string;
}

function format_user($text, $user_list)
{
    if ($text == '') {
        return '';
    }

    $formatted_string = '';
    $parts = explode(',', $text);
    foreach ($parts as $part) {
        if ($formatted_string == '') {
            $formatted_string = $user_list[$part];
        } else {
            $formatted_string .= ', ' . $user_list[$part];
        }
    }

    return $formatted_string;
}

function format_lab($lab_id, $lab_list)
{
    $formatted_string = '';

    $parts = explode(',', $lab_id);
    foreach ($parts as $part) {
        if (isset($lab_list[$part])) {
            $lab_name = $lab_list[$part];
        } else {
            $lab_name = 'unknown';
        }
        if ($formatted_string == '') {
            $formatted_string = $lab_name;
        } else {
            $formatted_string .= ', ' . $lab_name;
        }
    }

    return $formatted_string;
}

function format_marking($marking, $string)
{
    $marking_string = $marking;

    $marking_type = $marking[0];

    switch ($marking_type) {
        case MARK_NO_ADJUSTMENT:
            $marking_string = $string['noadjustment'];
            break;
        case MARK_RANDOM:
            $marking_string = $string['calculatrrandommark'];
            break;
        case MARK_STD_SET:
            $marking_string = $string['stdset'];
            break;
        case '3':
            $marking_string = $string['overallclass2'];
            break;
        case '4':
            $marking_string = $string['overallclass3'];
            break;
        case '6':
            $marking_string = $string['overallclass4'];
            break;
        case '7':
            $marking_string = $string['overallclass5'];
            break;
    }

    return $marking_string;
}

function format_method($method, $string)
{
    if ($method == '0') {
        return $string['noadjustment'];
    } elseif ($method == '1') {
        return $string['calculatrrandommark'];
    } elseif ($method[0] == '2') {
        return $string['stdset'];
    } elseif ($method == '3') {
        return $string['overallclass2'];
    } elseif ($method == '4') {
        return $string['overallclass3'];
    } elseif ($method == '5') {
        return $string['overallclass1'];
    } elseif ($method == '6') {
        return $string['overallclass4'];
    }
}

function format_review($method, $string)
{
    if ($method == '0') {
        return $string['singlereview'];
    } else {
        return $string['allpeerspergroup'];
    }
}

function format_passmark($method, $string)
{
    if ($method == 101) {
        return 'Borderline Method';
    } elseif ($method == 102 or $method == 127) {
        return 'N/A';
    } else {
        return $method . '%';
    }
}

function format_on_off($data, $string)
{
    if ($data == 0) {
        return $string['off'];
    } else {
        return $string['on'];
    }
}

function format_display($data, $string)
{
    if ($data == 0) {
        return $string['windowed'];
    } else {
        return $string['fullscreen'];
    }
}

function format_navigation($data, $string)
{
    if ($data == 0) {
        return $string['unidirectional'];
    } else {
        return $string['bidirectional'];
    }
}

function output_labs($labs, $cfg_summative_mgmt, $paper_type, $userObject, &$changed_labs, $db)
{
    if ($cfg_summative_mgmt and $paper_type == '2' and !$userObject->has_role(array('Admin', 'SysAdmin'))) {
        $r1class = 'r1disabled';
        $r2class = 'r2disabled';
        $disabled = ' disabled';
        $html = '<div id="labs_list" style="height:278px; overflow-y:scroll;border:1px solid #808080; color:#808080; font-size:90%">';
    } elseif ($paper_type == '4') {
        $r1class = 'r1disabled';
        $r2class = 'r2disabled';
        $disabled = ' disabled';
        $html = '<div id="labs_list" style="height:278px; overflow-y:scroll;border:1px solid #808080; color:#808080; font-size:90%">';
    } else {
        $r1class = 'r1';
        $r2class = 'r2';
        $disabled = '';
        $html = '<div id="labs_list" style="height:278px; overflow-y:scroll;border:1px solid #828790; font-size:90%">';
    }

    $current_labs = explode(',', $labs);

    $result = $db->prepare('SELECT labs.id, labs.name, campus.name, COUNT(client_identifiers.id) FROM labs, client_identifiers, campus
    WHERE labs.id = client_identifiers.lab AND labs.campus = campus.id GROUP BY client_identifiers.lab ORDER BY campus.name, labs.name');
    $result->execute();
    $result->bind_result($lab_id, $lab_name, $lab_campus, $computer_no);
    $lab_no = 0;
    $old_campus = '';
    while ($result->fetch()) {
        if ($old_campus != $lab_campus) {
            //$html .= "<div><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"lab\" />&nbsp;<strong>$lab_campus</strong></div>\n";
            $html .= "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr><img src=\"../artwork/new_lab_16.png\" width=\"16\" height=\"16\" alt=\"lab\" /> $lab_campus</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
        }
        $match = false;
        foreach ($current_labs as $individual_lab) {
            if ($lab_id == $individual_lab) {
                $match = true;
            }
        }
        if ($match) {
            $html .= "<div class=\"$r2class\" id=\"divlab$lab_no\"><input type=\"checkbox\"$disabled class=\"toggle\" data-toggleid=\"lab" . $lab_no . "\" name=\"lab$lab_no\" id=\"lab$lab_no\" value=\"$lab_id\" checked><label for=\"lab$lab_no\">$lab_name</label> <span style=\"color:#808080\">($computer_no)</span></div>\n";
        } else {
            $html .= "<div class=\"$r1class\" id=\"divlab$lab_no\"><input type=\"checkbox\"$disabled class=\"toggle\" data-toggleid=\"lab" . $lab_no . "\" name=\"lab$lab_no\" id=\"lab$lab_no\" value=\"$lab_id\"><label for=\"lab$lab_no\">$lab_name</label> <span style=\"color:#808080\">($computer_no)</span></div>\n";
        }
        $lab_no++;
        $old_campus = $lab_campus;

        if (isset($changed_labs[$lab_id])) {
            $changed_labs[$lab_id] = $lab_name;
        }
    }
    $result->close();
    $html .= "<input type=\"hidden\" name=\"lab_no\" value=\"$lab_no\" /></div>";

    return $html;
}

function getSchools($staff_modules, $db)
{
    $schools = array();

    $staff_modules_list = implode("','", $staff_modules);

    $result = $db->prepare("SELECT DISTINCT schools.id FROM schools, modules WHERE modules.schoolid = schools.id AND modules.moduleid IN ('$staff_modules_list')");
    $result->execute();
    $result->bind_result($schoolID);
    while ($result->fetch()) {
        $schools[] = $schoolID;
    }
    $result->close();

    return $schools;
}

$option_no = 1;

// Work out if any negative marking is used
$neg_marking = false;
$result = $mysqli->prepare('SELECT marks_incorrect FROM papers, questions, options WHERE papers.question = questions.q_id AND questions.q_id = options.o_id AND paper = ?');
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($marks_incorrect);
while ($result->fetch()) {
    if ($marks_incorrect < 0) {
        $neg_marking = true;
    }
}
$result->close();

// Load textual feedback
$textual_feedback = Paper_utils::get_textual_feedback($paperID, $mysqli);

$local_time = new DateTimeZone($configObject->get('cfg_timezone'));
$target_timezone = new DateTimeZone($properties->get_timezone());

if ($properties->get_start_date() != '') {
    $start_date = DateTime::createFromFormat('U', $properties->get_start_date(), $local_time);
    $start_date->setTimezone($target_timezone);
} else {
    $start_date = '';
}

if ($properties->get_end_date() != '') {
    $end_date = DateTime::createFromFormat('U', $properties->get_end_date(), $local_time);
    $end_date->setTimezone($target_timezone);
} else {
    $end_date = '';
}

if ($configObject->get_setting('core', 'cfg_summative_mgmt') and $properties->get_paper_type() == '2' and !$userObject->has_role(array('SysAdmin', 'Admin'))) {
    $sum_disabled = ' disabled';
} elseif ($userObject->has_role('Admin') and $locked) {
    $sum_disabled = ' disabled';
} else {
    $sum_disabled = '';
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('Rog&#333;: ' . $string['propertiestitle']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css"/>
  <link rel="stylesheet" type="text/css" href="../css/header.css"/>
  <link rel="stylesheet" type="text/css" href="../css/properties.css"/>
  <link rel="stylesheet" type="text/css" href="../css/warnings.css"/>
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
<?php
  $texteditorplugin->display_header();
  $texteditorplugin->get_javascript_config(\plugins\plugins_texteditor::PROPERTIES);
?>
</head>
<body>
<div id="content">
<?php
require '../include/toprightmenu.inc';

echo draw_toprightmenu();
// initial link of breadcrumb
$links = array('/' => $string['home']);

if ($folder) {
    // links of parent folders
    $folderName = folder_utils::get_folder_name($folder, $mysqli);
    foreach (folder_utils::get_parent_list($folderName, $userObject, $mysqli) as $parentId => $parentName) {
        $href = '/folder/index.php?folder=' . $parentId;
        $links[$href] = $parentName;
    }

    // link of current folder
    $href = '/folder/index.php?folder=' . $folder;
    $links[$href] = false === strpos($folderName, ';') ? $folderName : substr($folderName, strrpos($folderName, ';') + 1);
} else {
    if (is_null($module)) {
        // Get the modules from paper properties
        $modules = Paper_utils::get_modules($paperID, $mysqli);
        $module = key($modules);
    }
    // link to module
    $href = '/module/index.php?module=' . $module ;
    $links[$href] = module_utils::get_moduleid_from_id($module, $mysqli);

    // link to module
    $href = '/paper/type.php?module=' . $module . '&type=' . $properties->get_paper_type();
    $links[$href] = Paper_utils::type_to_name($properties->get_paper_type(), $string);
}

// link of current paper
$href = '/paper/details.php?paperID=' . $paperID;
$links[$href] = $properties->get_paper_title();
$href = '/paper/properties.php?paperID=' . $paperID . '&caller=details&module=' . $module . '&folder=' . $folder;
$links[$href] = $string['propertiestitle'];

// breadcrumb
echo $render->render_admin_navigation($links);
?>
</div>
<form id="theform" name="edit_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
<?php
  require '../tools/colour_picker/colour_picker.inc';
?>
<table border="0" cellpadding="0" cellspacing="5" style="width:100%; font-size:90%">
<tr><td valign="top" style="background-color:white; border:1px solid #828790; width:120px">

<table cellspacing="0" cellpadding="0" border="0" style="font-size:90%; width:140px">
<?php
if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') {
    echo '<tr><td id="tab1" class="tab" data-name="general">' . $string['generaltab'] . "</td></tr>\n";
    echo '<tr><td id="tab2" class="tabon" data-name="security">' . $string['securitytab'] . "</td></tr>\n";
} else {
    echo '<tr><td id="tab1" class="tabon" data-name="general">' . $string['generaltab'] . "</td></tr>\n";
    echo '<tr><td id="tab2" class="tab" data-name="security">' . $string['securitytab'] . "</td></tr>\n";
}
$papersettings = new PaperSettings($paperID, $properties->get_paper_type());
if ($configObject->get_setting('core', 'paper_seb_enabled') and $papersettings->settingsCategoryEnabled('seb')) {
    echo '<tr><td id="tab3" class="tab" data-name="seb">' . $string['sebtab'] . '</td></tr>';
} else {
    echo '<tr><td id="tab3" style="display:none">' . $string['sebtab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '3' and $properties->get_paper_type() != '6') {
    echo '<tr><td id="tab4" class="tab" data-name="feedback">' . $string['feedback'] . '</td></tr>';
    echo '<tr><td id="tab5" class="tab" data-name="reviewers">' . $string['reviewerstab'] . '</td></tr>';
} else {
    echo '<tr><td id="tab4" style="display:none">' . $string['feedback'] . '</td></tr>';
    echo '<tr><td id="tab5" style="display:none">' . $string['reviewerstab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '3' and $properties->get_paper_type() != '4' and $properties->get_paper_type() != '5' and $properties->get_paper_type() != '6') {
    echo '<tr><td id="tab6" class="tab" data-name="rubric">' . $string['rubrictab'] . '</td></tr>';
} else {
    echo '<tr><td id="tab6" style="display:none">' . $string['rubrictab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '4' and $properties->get_paper_type() != '5') {
    echo '<tr><td id="tab7" class="tab" data-name="prologue">' . $string['prologuetab'] . '</td></tr>';
    echo '<tr><td id="tab8" class="tab" data-name="postscript">' . $string['postscripttab'] . '</td></tr>';
} else {
    echo '<tr><td id="tab7" style="display:none">' . $string['prologuetab'] . '</td></tr>';
    echo '<tr><td id="tab8" style="display:none">' . $string['postscripttab'] . '</td></tr>';
}
if ($properties->get_paper_type() != '4' and $properties->get_paper_type() != '5' and $properties->get_paper_type() != '6') {
    echo '<tr><td id="tab9" class="tab" data-name="reference">' . $string['referencematerial'] . '</td></tr>';
} else {
    echo '<tr><td id="tab9" style="display:none">' . $string['referencematerial'] . '</td></tr>';
}
?>
<tr><td id="tab10" class="tab" data-name="changes"><?php echo $string['changes']; ?></td></tr>
</table>

</td>

<td style="background-color:white; border:1px solid #828790; vertical-align:top">

<table id="general" class="tabsection" style="<?php if (isset($_GET['noadd']) and $_GET['noadd'] == 'y') {
    echo 'display:none';
                                              } ?>">
<tr><td class="tabtitle" colspan="2"><img src="../artwork/general_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['generalheading']; ?></td></tr>
<td style="text-align:left; vertical-align:top" colspan="2">
   <?php
     echo "<table class=\"cellpad2\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\">\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
     echo '<tr><td colspan="4" class="headbar">&nbsp;' . $string['paperdetails'] . "</td></tr>\n";
     echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";

     echo '<tr><td align="right" valign="top">' . $string['url'] . '&nbsp;</td><td colspan="3">';
    switch ($properties->get_paper_type()) {
        case '2':
            echo '<a href="' . $configObject->get('cfg_root_path') . '" target="_blank">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '</a> ' . $string['onlyonexamday'];
            break;
        case '4':
            echo '<a href="' . $configObject->get('cfg_root_path') . '/osce/" target="_blank">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/osce/</a> ' . $string['onlyonexamday'];
            break;
        case '5':
            echo $string['na'];
            break;
        case '6':
            echo '<a href="' . $configObject->get('cfg_root_path') . '/peer_review/form.php?id=' . urlencode($properties->get_crypt_name()) . '" target="_blank">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/peer_review/form.php?id=' . urlencode($properties->get_crypt_name()) . '</a>';
            break;
        default:
            echo '<a href="' . $configObject->get('cfg_root_path') . '/paper/user_index.php?id=' . urlencode($properties->get_crypt_name()) . '" target="_blank">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/paper/user_index.php?id=' . urlencode($properties->get_crypt_name()) . '</a>';
    }
     echo "</td></tr>\n";
     echo '<tr><td align="right" valign="top">' . $string['name'] . '&nbsp;</td><td colspan="3">';

     echo '<input id="papertitle" type="text" size="75" maxlength="200" value="' . $properties->get_paper_title() . "\" name=\"paper_title\"$disabled required />";

     echo "<input type=\"hidden\" name=\"paperID\" value=\"$paperID\"></td></tr>\n";
    ?>
    <tr><td align="right" valign="top"><?php echo $string['type']; ?>&nbsp;</td><td>
   <?php
    if ($properties->get_paper_type() == '0') {
        echo '<select id="paper_type" name="paper_type">';
        echo '<option value="0" selected="selected" />' . $string['formative self-assessment'] . "</option>\n";
        echo '<option value="1" />' . $string['progress test'] . "</option>\n";
    } elseif ($properties->get_paper_type() == '1') {
        echo '<select id="paper_type" name="paper_type">';
        echo '<option value="0" />' . $string['formative self-assessment'] . "</option>\n";
        echo '<option value="1" selected="selected" />' . $string['progress test'] . "</option>\n";
    } else {
        echo '<select id="paper_type" name="paper_type">';
        $tmp_types = array('formative self-assessment', 'progress test', 'summative exam', 'survey', 'osce station', 'offline paper', 'peer review');
        echo '<option value="' . $properties->get_paper_type() . '" selected="selected" />' . $string[$tmp_types[$properties->get_paper_type()]] . "</option>\n";
    }

    echo '<td align="right" valign="top">' . $string['folder'] . "&nbsp;</td><td valign=\"top\">\n<select style=\"width:210px\" name=\"folderID\">\n";
    echo '<option value=""></option>';
    $additional = '';

    if (is_array($staff_modules) and count($staff_modules) > 0) {
        $additional = ' OR idMod IN (' . implode(',', array_keys($staff_modules)) . ')';
    }

    if ($properties->get_folder() != '') {
        $additional .= ' OR id=' . $properties->get_folder();
    }

    $folder_details = $mysqli->prepare("SELECT DISTINCT id, name FROM folders LEFT JOIN folders_modules_staff ON folders.id = folders_modules_staff.folders_id WHERE (ownerID = ? $additional) AND deleted IS NULL ORDER BY name");
    $folder_details->bind_param('i', $userObject->get_user_ID());
    $folder_details->execute();
    $folder_details->bind_result($folder_id, $folder_name);
    while ($folder_details->fetch()) {
        $path_parts = mb_substr_count($folder_name, ';');
        $folder_array = explode(';', $folder_name);
        $display_name = str_repeat('&nbsp;', $path_parts * 4) . $folder_array[$path_parts];
        if ($properties->get_folder() == $folder_id) {
            echo '<option value="' . $folder_id . '" selected>' . $display_name . '</option>';
        } else {
            echo '<option value="' . $folder_id . '">' . $display_name . '</option>';
        }
    }
    $folder_details->close();

    // External system details.
    $external = new \external_systems();
    $extsys = $external->get_all_externalsystems();
    echo "</select>\n</td></tr>\n";
    if ($userObject->has_role('SysAdmin')) {
        // Sys admins can edit.
        echo '<tr><td align="right" valign="top">' . $string['externalsys'] . '</td><td><select name="externalsys">';
        echo "<option value=\"\"></option>\n";
        foreach ($extsys as $i => $s) {
            if ($s == $properties->get_externalsys()) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            echo "<option value=\"$s\" $selected>$s</option>\n";
        }
        echo '</select></td></tr>';
        echo '<tr><td align="right" valign="top">' . $string['externalid'] . '</td><td><input type="text" size="30" maxlength="255" name="externalid" value="' . $properties->get_externalid() . '"></td></tr>';
    } else {
        // Non sys admins can only view.
        echo '<tr><td>' . $string['externalid'] . '</td><td>' . $properties->get_externalid() . '</td></tr>';
        echo '<tr><td>' . $string['externalsys'] . '</td><td>' . $properties->get_externalsys() . '</td></tr>';
    }
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    if ($properties->get_paper_type() == '4') {
        echo '<input type="hidden" name="bgcolor" value="' . $properties->get_bgcolor() . '" />';
        echo '<input type="hidden" name="fgcolor" value="' . $properties->get_fgcolor() . '" />';
        echo '<input type="hidden" name="themecolor" value="' . $properties->get_themecolor() . '" />';
        echo '<input type="hidden" name="labelcolor" value="' . $properties->get_labelcolor() . '" />';
        echo '<input type="hidden" name="fullscreen" value="' . $properties->get_fullscreen() . '" />';
    } else {
        echo '<tr><td colspan="4" class="headbar">&nbsp;' . $string['displayoptions'] . "</td></tr>\n";
        echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
        if ($properties->get_fullscreen() == 0) {
            echo '<tr><td align="right">' . $string['display'] . "&nbsp;</td><td><select name=\"fullscreen\"$disabled>\n<option value=\"0\" selected>" . $string['windowed'] . '</option><option value="1">' . $string['fullscreen'] . "</option>\n</select></td>";
        } else {
            echo '<tr><td align="right">' . $string['display'] . "&nbsp;</td><td><select name=\"fullscreen\"$disabled>\n<option value=\"0\">" . $string['windowed'] . '</option><option value="1" selected>' . $string['fullscreen'] . "</option>\n</select></td>";
        }
        if ($properties->get_bidirectional() == 1) {
            echo '<td align="right">' . $string['navigation'] . "&nbsp;</td><td><select name=\"bidirectional\"$disabled><option value=\"0\">" . $string['unidirectional'] . '</option><option value="1"selected>' . $string['bidirectional'] . "</option></select></td></tr>\n";
        } else {
            echo '<td align="right">' . $string['navigation'] . "&nbsp;</td><td><select name=\"bidirectional\"$disabled><option value=\"0\" selected>" . $string['unidirectional'] . '</option><option value="1">' . $string['bidirectional'] . "</option></select></td></tr>\n";
        }

        echo "<tr>\n";
        echo '<td align="right">' . $string['background'] . '&nbsp;</td><td><div class="showpicker" data-pickertype="background" id="span_background" style="border:1px solid #C5C5C5; width:20px; background-color:' . $properties->get_bgcolor() . '">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type="hidden" id="background" name="background" value="' . $properties->get_bgcolor() . '" /></td>';
        echo '<td align="right">' . $string['foreground'] . '&nbsp;</td><td><div class="showpicker" data-pickertype="foreground" id="span_foreground" style="border:1px solid #C5C5C5; width:20px; background-color:' . $properties->get_fgcolor() . '">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type="hidden" id="foreground" name="foreground" value="' . $properties->get_fgcolor() . '" /></td>';
        echo "</tr>\n";

        echo "<tr>\n";
        echo '<td align="right">' . $string['theme'] . '&nbsp;</td><td><div class="showpicker" data-pickertype="themecolor" id="span_themecolor" style="border:1px solid #C5C5C5; width:20px; background-color:' . $properties->get_themecolor() . '">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type="hidden" id="themecolor" name="themecolor" value="' . $properties->get_themecolor() . '" /></td>';
        echo '<td align="right">' . $string['labelsnotes'] . '&nbsp;</td><td><div class="showpicker" data-pickertype="labelcolor" id="span_labelcolor" style="border:1px solid #C5C5C5; width:20px; background-color:' . $properties->get_labelcolor() . '">&nbsp;&nbsp;&nbsp;&nbsp;</div><input type="hidden" id="labelcolor" name="labelcolor" value="' . $properties->get_labelcolor() . '" /></td>';
        echo "</tr>\n";

        if ($properties->get_paper_type() == '6') {
            echo '<tr><td align="right">' . $string['photos'] . '&nbsp;</td><td colspan="3">';
            if ($properties->get_display_correct_answer() == '1') {
                echo '<input type="checkbox" name="display_photos" value="1" checked />';
            } else {
                echo '<input type="checkbox" name="display_photos" value="1" />';
            }
            echo $string['ifavailable'] . "</td></tr>\n";
        } else {
            if ($properties->get_calculator() == 1) {
                $checked = ' checked="checked"';
            } else {
                $checked = '';
            }
            echo '<tr><td align="right">' . $string['calculator'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" id=\"calculator\" name=\"calculator\"$checked$disabled /><label for=\"calculator\">" . $string['displaycalculator'] . '</label> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_calculator'] . '" /></td>';

            if ($properties->get_sound_demo() == 1) {
                $checked = ' checked="checked"';
            } else {
                $checked = '';
            }
            echo '<td align="right">' . $string['audio'] . "&nbsp;</td><td><input type=\"checkbox\" value=\"1\" id=\"sound_demo\" name=\"sound_demo\"$checked$disabled /><label for=\"sound_demo\">" . $string['demosoundclip'] . '</label> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_audio'] . "\" /></td></tr>\n";
        }

        echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    }
    if ($properties->get_paper_type() != '3') {
        echo '<tr><td colspan="4" class="headbar">&nbsp;' . $string['marking'] . "</td></tr>\n";
    }

    $gradebook = new gradebook($mysqli);
    $graded = $gradebook->paper_graded($paperID);
    $published = '';
    if ($graded) {
        $sum_disabled = 'disabled="disabled"';
        $published = 'disabled="disabled"';
        echo '<tr><td style="padding-right:0"><div class="yellowwarn"><img src="../artwork/paper_locked_padlock.png" width="32" height="32" alt="Published" /></div></td><td colspan="3" style="vertical-align:middle; padding-left:0"><div class="yellowwarn">' . $string['paperpublishedwarning'] . "</div></td></tr>\n";
    }
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    if ($properties->get_paper_type() == '4') {    // OSCE Stations
        echo '<tr><td align="right" valign="top">' . $string['passmark'] . "&nbsp;</td><td valign=\"top\">\n<select name=\"pass_mark\" id=\"pass_mark\" $published>\n";
        if ($properties->get_pass_mark() == 102) {
            echo '<option value="102">N/A</option>';
        } else {
            echo '<option value="102" selected>N/A</option>';
        }
        if ($properties->get_pass_mark() == 101) {
            echo '<option value="101" selected>' . $string['borderlinemethod'] . '</option>';
        } else {
            echo '<option value="101">' . $string['borderlinemethod'] . '</option>';
        }
        for ($i = 0; $i <= 100; $i++) {
            if ($i == $properties->get_pass_mark()) {
                echo "<option value=\"$i\" selected>$i%</option>\n";
            } else {
                echo "<option value=\"$i\">$i%</option>\n";
            }
        }
        echo '</select></td></tr>';

        $oscestarted = $properties->get_osce_started_status($paperID, $mysqli);
        if ($oscestarted) {
            echo '<tr><td align="right" valign="top"><nobr>' . $string['overallclassification'] . ':</nobr>&nbsp;</td><td valign="top" colspan="3">';
            ?>
            <?php if ($properties->get_marking() == '5') {
                echo 'N/A';
            } ?>
            <?php if ($properties->get_marking() == '7') {
                echo $string['overallclass5'];
            } ?>
            <?php if ($properties->get_marking() == '3') {
                echo $string['overallclass2'];
            } ?>
            <?php if ($properties->get_marking() == '4') {
                echo $string['overallclass3'];
            } ?>
            <?php if ($properties->get_marking() == '6') {
                echo $string['overallclass4'];
            } ?>

            <?php
        } else {
            echo '<tr><td align="right" valign="top"><nobr>' . $string['overallclassification'] . "</nobr>&nbsp;</td><td valign=\"top\" colspan=\"3\"><select name=\"marking\" $published>";
            ?>
          <option value="5"<?php if ($properties->get_marking() == '5') {
                echo ' selected';
                           } ?> />N/A</option>
          <option value="7"<?php if ($properties->get_marking() == '7') {
                echo ' selected';
                           } ?> /><?php echo $string['overallclass5']; ?></option>
          <option value="3"<?php if ($properties->get_marking() == '3') {
                echo ' selected';
                           } ?> /><?php echo $string['overallclass2']; ?></option>
          <option value="4"<?php if ($properties->get_marking() == '4') {
                echo ' selected';
                           } ?> /><?php echo $string['overallclass3']; ?></option>
          <option value="6"<?php if ($properties->get_marking() == '6') {
                echo ' selected';
                           } ?> /><?php echo $string['overallclass4']; ?></option>
          </select>
            <?php
        }
        echo '<img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_osceclassification'] . '" />';
        echo '</td></tr>';
        echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
        echo '<tr><td colspan="4">' . $string['markingguidance'] . "</td></tr>\n";
        echo '<tr><td colspan="4" style="padding: 0">';
        $texteditorplugin->get_textarea('osce_marking_guidance', 'osce_marking_guidance', $texteditorplugin->get_text_for_display(htmlspecialchars($properties->get_paper_postscript()), ENT_NOQUOTES), plugins\plugins_texteditor::TYPE_STANDARD, 'width:100%; height:230px;');
        echo '</td></tr>';
    } elseif ($properties->get_paper_type() == '6') {  // Peer Review
        $review = $properties->get_display_question_mark();

        echo '<tr><td align="right">' . $string['groupdetails'] . "&nbsp;</td><td><select name=\"type\">\n";
        echo "<option value=\"\" selected>&nbsp;</option>\n";

            $modules_array = $properties->get_modules();

        $field_details = $mysqli->prepare('SELECT DISTINCT type FROM users_metadata, modules WHERE users_metadata.idMod = modules.id AND modules.id IN (' . implode(',', array_keys($modules_array)) . ') ORDER BY type');
        $field_details->execute();
        $field_details->bind_result($type);
        while ($field_details->fetch()) {
            if ($properties->get_rubric() == $type) {
                echo "<option value=\"$type\" selected>$type</option>\n";
            } else {
                echo "<option value=\"$type\">$type</option>\n";
            }
        }
        $field_details->close();
        echo "</select>\n</td>\n";
        echo '<td align="right">' . $string['numberfrom'] . "</td><td><select name=\"marking\">\n";
        if ($properties->get_marking() == '1') {
            echo "<option value=\"0\">0</option>\n<option value=\"1\" selected>1</option>\n";
        } else {
            echo "<option value=\"0\" selected>0</option>\n<option value=\"1\">1</option>\n";
        }
        echo "</select>\n</td></tr>\n";
        echo '<tr><td align="right">' . $string['review']  . '</td><td>';
        if ($review == '1') {
            echo '<input type="radio" name="review" value="1" checked="checked" />';
        } else {
            echo '<input type="radio" name="review" value="1" />';
        }
        echo $string['allpeerspergroup'] . '<br />';
        if ($review == '0') {
            echo '<input type="radio" name="review" value="0" checked="checked" />';
        } else {
            echo '<input type="radio" name="review" value="0" />';
        }
        echo $string['singlereview'] . '</td></tr>';
    } elseif ($properties->get_paper_type() != '3') {
        echo '<tr><td align="right" valign="top">' . $string['passmark'] . "&nbsp;</td><td valign=\"top\"><select name=\"pass_mark\" id=\"pass_mark\" $published>";
        for ($i = 0; $i <= 100; $i++) {
            if ($i == $properties->get_pass_mark()) {
                echo "<option value=\"$i\" selected>$i%</option>\n";
            } else {
                echo "<option value=\"$i\">$i%</option>\n";
            }
        }
        echo '</select></td><td rowspan="2" style="text-align:right" valign="top">' . $string['method'] . '&nbsp;</td><td rowspan="2">';
        ?>
       <input type="radio" id="marking1" name="marking" value="<?php echo MARK_NO_ADJUSTMENT ?>"<?php if ($properties->get_marking() == MARK_NO_ADJUSTMENT) {
            echo ' checked';
                                                               } ?> <?php echo $published ?>/><?php echo $string['noadjustment'] ?><br />
       <input type="radio" id="marking2" name="marking" value="<?php echo MARK_RANDOM ?>"<?php
        if ($properties->get_marking() == MARK_RANDOM) {
            echo ' checked ';
        }
        if ($neg_marking) {
            echo ' disabled';
        } else {
            echo ' ' . $published;
        }
        if ($neg_marking) {
            echo '><span style="color:#808080">' . $string['calculatrrandommark'] . '</span>&nbsp;<img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_random'] . '" /><br />';
        } else {
            echo '>' . $string['calculatrrandommark'] . '&nbsp;<img src="../artwork/tooltip_icon.gif" class="help_tip" title="' . $string['tooltip_random'] . '" /><br />';
        }

        // Look for any Standard Setting reviews for the paper.
        $std_set_array = array();
        $i = 0;

        $std_set_details = $mysqli->prepare("SELECT std_set.id, title, surname, initials, setterID, DATE_FORMAT(std_set,'%d/%m/%y %H:%i') AS display_date, group_review FROM std_set, users WHERE std_set.setterID = users.id AND paperID = ? ORDER BY std_set DESC");
        $std_set_details->bind_param('i', $paperID);
        $std_set_details->execute();
        $std_set_details->bind_result($std_setID, $std_set_title, $std_set_surname, $std_set_initials, $std_set_reviewer, $std_set_display_date, $group_review);
        while ($std_set_details->fetch()) {
            $std_set_array[$i] = array('std_setID' => $std_setID, 'title' => $std_set_title, 'surname' => $std_set_surname, 'initials' => $std_set_initials, 'reviewer' => $std_set_reviewer, 'display_date' => $std_set_display_date, 'group_review' => $group_review);
            $i++;
        }
        $std_set_details->close();

        if (count($std_set_array) > 0) {
            echo '<input type="radio" id="marking3" name="marking" value="' . MARK_STD_SET . '"';
            if (mb_substr($properties->get_marking(), 0, 1) == MARK_STD_SET) {
                echo ' checked';
            }
            echo " $published/>";
            echo $string['stdset'] . ' <select name="std_set" ' . $published . '>';
            foreach ($std_set_array as $std_set_line) {
                $std_set_title = $std_set_line['title'];
                $std_set_surname = $std_set_line['surname'];
                $std_set_initials = $std_set_line['initials'];
                $std_set_reviewer = $std_set_line['reviewer'];
                $std_setID = $std_set_line['std_setID'];
                $std_set_display_date = $std_set_line['display_date'];

                if ($properties->get_marking() == MARK_STD_SET . ",$std_setID") {
                    echo '<option value="' . MARK_STD_SET . ",$std_setID\" selected>$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
                } else {
                    echo '<option value="' . MARK_STD_SET . ",$std_setID\">$std_set_title $std_set_surname, $std_set_initials - $std_set_display_date</option>";
                }
            }
            echo "</select>\n";
        } else {
            echo '<input type="radio" id="marking3" name="marking" value="' . MARK_STD_SET . '" disabled />';
            echo '<span style="color:#808080">' . $string['stdset'] . '</span>';
        }
    }
    if ($properties->get_paper_type() == '0' or $properties->get_paper_type() == '1' or $properties->get_paper_type() == '2') {
        echo '<tr><td align="right" valign="top">' . $string['distinction'] . "</td><td><select name=\"distinction_mark\" $published>";
        echo "<option value=\"127\" selected>N/A</option>\n";    // N/A = 127 which should be impossible to ever get.
        for ($i = 0; $i <= 100; $i++) {
            if ($i == $properties->get_distinction_mark()) {
                echo "<option value=\"$i\" selected>$i%</option>\n";
            } else {
                echo "<option value=\"$i\">$i%</option>\n";
            }
        }
        echo "</select></td></tr>\n";
    } else {
        echo "<tr><td></td><td></td></tr>\n";
    }
    ?>
   </table>
</td>
</tr>
<?php
$properties->renderSettings('general');
?>
</table>

<table id="prologue" class="tabsection" style="display: none">
<tr><td class="tabtitle"><img src="../artwork/prologue_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['prologueheading']; ?></td></tr>
<tr><td><?php $texteditorplugin->get_textarea('paper_prologue', 'paper_prologue', $texteditorplugin->get_text_for_display(htmlspecialchars($properties->get_paper_prologue()), ENT_NOQUOTES), plugins\plugins_texteditor::TYPE_STANDARD, 'width:100%; height:537px'); ?></td></tr>
<?php
$properties->renderSettings('prologue');
?>
</table>

<table id="postscript" class="tabsection" style="display: none">
<tr><td class="tabtitle"><img src="../artwork/postscript_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['postscriptheading']; ?></td></tr>
<tr><td><?php $texteditorplugin->get_textarea('paper_postscript', 'paper_postscript', $texteditorplugin->get_text_for_display(htmlspecialchars($properties->get_paper_postscript()), ENT_NOQUOTES), plugins\plugins_texteditor::TYPE_STANDARD, 'width:100%; height:537px'); ?></td></tr>
<?php
$properties->renderSettings('postscript');
?>
</table>

<table id="security" class="tabsection" style="display: none">
<tr><td class="tabtitle"><img src="../artwork/security_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['securityheading']; ?></td></tr>
<?php
if ($properties->get_summative_lock() and $userObject->has_role(array('SysAdmin'))) {
    ?>
<tr>
  <td>
    <div class="yellowwarn">
      <img src="../artwork/paper_locked_padlock.png" width="32" height="32" alt="Locked" /><span style="vertical-align:top; padding-left:10px"><?php echo $string['donotchangewarning']; ?></span>
    </div>
  </td>
</tr>
    <?php
}
?>
<tr>
<td style="text-align:center; vertical-align:top">
<?php
    echo "<table cellpadding=\"0\" cellspacing=\"3\" border=\"0\" style=\"width:100%; padding-bottom:10px\">\n";
    echo '<tr><td align="right">' . $string['session'] . "</td><td><select name=\"calendar_year\" id=\"session\" class='meta' \"$sum_disabled>\n";
    $yearutils = new yearutils($mysqli);
    echo $yearutils->get_calendar_year_dropdown_options($properties->get_paper_type(), $properties->get_calendar_year(), $string);
    echo '</select></td>';

if ($properties->get_paper_type() == '4') {
    echo '<td></td><td><input type="hidden" size="20" name="password" value="' . $properties->get_decrypted_password() . "\" /></td></tr>\n";
} else {
    echo '<td align="right">' . $string['password'] . '</td><td><input type="text" size="20" name="password" value="' . $properties->get_decrypted_password() . "\"$disabled /> <img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string['tooltip_password'] . "\" /></td></tr>\n";
}

    echo '<tr><td align="right">' . $string['timezone'] .  "</td><td><select name=\"timezone\"$sum_disabled style=\"width:270px\">";
foreach ($timezone_array as $individual_zone => $display_zone) {
    if ($properties->get_timezone() == $individual_zone) {
        echo "<option value=\"$individual_zone\" selected>$display_zone</option>";
    } else {
        echo "<option value=\"$individual_zone\">$display_zone</option>";
    }
}
    echo '</select></td>';

        $exam_duration = $properties->get_exam_duration();
if ($exam_duration == null) {
    $duration_hours = 'NULL';
    $duration_mins = 'NULL';
} else {
    $duration_hours = (int)floor($exam_duration / 60);
    $duration_mins = (int)$exam_duration - ($duration_hours * 60);
}
        echo '<td align="right">' . $string['duration'] . "</td><td><select id=\"exam_duration_hours\" name=\"exam_duration_hours\"$sum_disabled>";
if ($duration_hours == 'NULL') {
    echo '<option value="NULL" selected>N/A</option>';
} else {
    echo '<option value="NULL">N/A</option>';
}
for ($i = 0; $i <= 12; $i++) {
    if ($i === $duration_hours) {
                echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
                echo "<option value=\"$i\">$i</option>\n";
    }
}
    echo '</select> ' . $string['hrs'] . " <select id=\"exam_duration_mins\" name=\"exam_duration_mins\"$sum_disabled>";
if ($duration_mins == 'NULL') {
    echo '<option value="NULL" selected>N/A</option>';
} else {
    echo '<option value="NULL">N/A</option>';
}
for ($i = 0; $i < 60; $i++) {
    if ($i === $duration_mins) {
                echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
                echo "<option value=\"$i\">$i</option>\n";
    }
}
        echo '</select> ' . $string['mins'] . "</td></tr>\n";
    echo '<tr><td align="right" valign="top">' . $string['availablefrom'] . '</td><td>';

    // Split the start date if available
if (isset($start_date) and $start_date != '') {
    $split_year = $start_date->format('Y');
    $split_month = $start_date->format('m');
    $split_day = $start_date->format('d');
    $split_hour = $start_date->format('H');
    $split_minute = $start_date->format('i');
} else {
    $split_year = $split_month = $split_day = $split_hour = $split_minute = 0;
}

    // Available from Day
    echo "<select name=\"fday\" id=\"fday\" class=\"datecopy\"$sum_disabled>\n";
if ($start_date == '') {
    echo '<option value=""></option>';
}
for ($i = 1; $i < 32; $i++) {
    if ($i < 10) {
        if ($i == $split_day) {
            echo "<option value=\"0$i\" selected>";
        } else {
            echo "<option value=\"0$i\">";
        }
    } else {
        if ($i == $split_day) {
            echo "<option value=\"$i\" selected>";
        } else {
            echo "<option value=\"$i\">";
        }
    }
    if ($i < 10) {
        echo '0';
    }
        echo "$i</option>\n";
}
    echo '</select>';
   // Available from Month
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    echo "<select name=\"fmonth\" id=\"fmonth\" class=\"datecopy\"$sum_disabled>\n";
if ($start_date == '') {
    echo '<option value=""></option>';
}
for ($i = 0; $i < 12; $i++) {
    $trans_month = mb_substr($string[$months[$i]], 0, 3, 'UTF-8');
    if (($split_month - 1) == $i) {
        if ($i < 9) {
            echo '<option value="0' . ($i + 1) . "\" selected>$trans_month</option>\n";
        } else {
            echo '<option value="' . ($i + 1) . "\" selected>$trans_month</option>\n";
        }
    } else {
        if ($i < 9) {
            echo '<option value="0' . ($i + 1) . "\">$trans_month</option>\n";
        } else {
            echo '<option value="' . ($i + 1) . "\">$trans_month</option>\n";
        }
    }
}
    echo '</select>';
    // Available from Year
    echo "<select name=\"fyear\" id=\"fyear\" class=\"datecopy\"$sum_disabled>\n";
if ($start_date == '') {
    echo '<option value=""></option>';
}
$startfyear = date('Y');
if ($split_year !== 0 and $split_year < $startfyear) {
    $startfyear = $split_year;
}
echo '<optgroup>';
for ($i = $startfyear; $i < ($startfyear + 21); $i++) {
    // Temp bailout to handle 2038 mysql unixtimestamp issue.
    if ($i > 2037) {
        break;
    }
    if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
        echo "<option value=\"$i\">$i</option>\n";
    }
}
echo '</optgroup><optgroup>';
$diffyears = $startfyear - 2002;
if ($diffyears > 0) {
    for ($i = 2002; $i < (2002 + $diffyears); $i++) {
        echo "<option value=\"$i\">$i</option>\n";
    }
}
echo '</optgroup>';
echo "</select><select id=\"fhour\" name=\"fhour\" $sum_disabled>\n";
// Available from Hour
if ($start_date == '') {
    echo '<option value=""></option>';
}
for ($tmp_hour = 0; $tmp_hour <= 23; $tmp_hour++) {
    if ($tmp_hour < 10) {
              $display_hour = '0' . $tmp_hour;
    } else {
                      $display_hour = $tmp_hour;
    }
    if ($display_hour == $split_hour and $start_date != '') {
        echo '<option value="' . $display_hour . '" selected>' . $display_hour . "</option>\n";
    } else {
        echo '<option value="' . $display_hour . '">' . $display_hour . "</option>\n";
    }
}
echo '</select>';

echo "</select><select id=\"fminute\" name=\"fminute\" $sum_disabled>\n";
// Available from Minute
if ($start_date == '') {
    echo '<option value=""></option>';
}
for ($tmp_minute = 0; $tmp_minute <= 59; $tmp_minute++) {
    if ($tmp_minute < 10) {
        $display_minute = '0' . $tmp_minute;
    } else {
        $display_minute = $tmp_minute;
    }
    if ($display_minute == $split_minute and $start_date != '') {
        echo '<option value="' . $display_minute . '" selected>' . $display_minute . "</option>\n";
    } else {
        echo '<option value="' . $display_minute . '">' . $display_minute . "</option>\n";
    }
}
echo "</select>\n</td>\n";

    // Split the end date if available
if (isset($end_date) and $end_date != '') {
    $split_year = $end_date->format('Y');
    $split_month = $end_date->format('m');
    $split_day = $end_date->format('d');
    $split_hour = $end_date->format('H');
    $split_minute = $end_date->format('i');
} else {
    $split_year = $split_month = $split_day = $split_hour = $split_minute = 0;
}

echo '<td align="right">' . $string['to'] . '&nbsp;</td><td>';

// Available from Day
echo "<select name=\"tday\" id=\"tday\" class=\"datecopy\"$sum_disabled>\n";
if ($end_date == '') {
    echo '<option value=""></option>';
}
for ($i = 1; $i < 32; $i++) {
    if ($i < 10) {
        if ($i == $split_day) {
            echo "<option value=\"0$i\" selected>";
        } else {
            echo "<option value=\"0$i\">";
        }
    } else {
        if ($i == $split_day) {
            echo "<option value=\"$i\" selected>";
        } else {
            echo "<option value=\"$i\">";
        }
    }
    if ($i < 10) {
        echo '0';
    }
        echo "$i</option>\n";
}
echo '</select>';

// Available to Month
echo "<select name=\"tmonth\" id=\"tmonth\" class=\"datecopy\"$sum_disabled>\n";
if ($end_date == '') {
    echo '<option value=""></option>';
}
for ($i = 0; $i < 12; $i++) {
    $trans_month = mb_substr($string[$months[$i]], 0, 3, 'UTF-8');
    if (($split_month - 1) == $i) {
        if ($i < 9) {
            echo '<option value="0' . ($i + 1) . "\" selected>$trans_month</option>\n";
        } else {
            echo '<option value="' . ($i + 1) . "\" selected>$trans_month</option>\n";
        }
    } else {
        if ($i < 9) {
            echo '<option value="0' . ($i + 1) . "\">$trans_month</option>\n";
        } else {
            echo '<option value="' . ($i + 1) . "\">$trans_month</option>\n";
        }
    }
}
echo '</select>';
// Available to Year
echo "<select name=\"tyear\" id=\"tyear\" class=\"datecopy\"$sum_disabled>\n";
if ($end_date == '') {
    echo '<option value=""></option>';
}
$starttyear = date('Y');
if ($split_year !== 0 and $split_year < $starttyear) {
    $starttyear = $split_year;
}
echo '<optgroup>';
for ($i = $starttyear; $i < ($starttyear + 21); $i++) {
    // Temp bailout to handle 2038 mysql unixtimestamp issue.
    if ($i > 2037) {
        break;
    }
    if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
        echo "<option value=\"$i\">$i</option>\n";
    }
}
echo '</optgroup><optgroup>';
$diffyears = $starttyear - 2002;
if ($diffyears > 0) {
    for ($i = 2002; $i < (2002 + $diffyears); $i++) {
        echo "<option value=\"$i\">$i</option>\n";
    }
}
echo '</optgroup>';
echo "</select><select id=\"thour\" name=\"thour\" $sum_disabled>\n";
// Available from Hour
if ($start_date == '') {
    echo '<option value=""></option>';
}
for ($tmp_hour = 0; $tmp_hour <= 23; $tmp_hour++) {
    if ($tmp_hour < 10) {
        $display_hour = '0' . $tmp_hour;
    } else {
        $display_hour = $tmp_hour;
    }
    if ($display_hour == $split_hour and $start_date != '') {
        echo '<option value="' . $display_hour . '" selected>' . $display_hour . "</option>\n";
    } else {
        echo '<option value="' . $display_hour . '">' . $display_hour . "</option>\n";
    }
}
echo '</select>';

echo "</select><select id=\"tminute\" name=\"tminute\" $sum_disabled>\n";
// Available from Minute
if ($start_date == '') {
    echo '<option value=""></option>';
}
for ($tmp_minute = 0; $tmp_minute <= 59; $tmp_minute++) {
    if ($tmp_minute < 10) {
        $display_minute = '0' . $tmp_minute;
    } else {
        $display_minute = $tmp_minute;
    }
    if ($display_minute == $split_minute and $start_date != '') {
        echo '<option value="' . $display_minute . '" selected>' . $display_minute . "</option>\n";
    } else {
        echo '<option value="' . $display_minute . '">' . $display_minute . "</option>\n";
    }
}
echo '</select>';
if ($properties->get_paper_type() == '2' and !is_null($exam_duration)) {
    echo '<span id="minavailflag">*</span>';
}
echo "\n</td></tr>\n</table>\n";
if ($properties->get_paper_type() == '2' and !is_null($exam_duration)) {
    echo '<div id="minavail">';
    echo sprintf($string['minavailability'], $minavailability);
    echo '</div>';
}
$properties->renderSettings('security');
echo "<table cellpadding=\"0\" cellspacing=\"4\" border=\"0\" width=\"100%\">\n";
echo '<tr><td class="headbar" style="padding:2px; width:400px">&nbsp;' . $string['modules'] . '</td><td class="headbar" style="padding:2px">&nbsp;' . $string['restricttolabs'] . '</td></tr>';
echo '<tr><td rowspan="3" style="vertical-align:top">';

echo '<div id="modules_list" style="display:block; width:400px; height:420px; overflow-y:scroll; border:1px solid #828790; font-size:90%">';

$modules_array = $properties->get_modules();

$total_modules = array_merge($staff_modules, $modules_array);

$module_sql = implode("','", $total_modules);
if ($module_sql != '') {
    $module_sql = "'$module_sql'";
}

$module_no = 0;
if ($module_sql != '') {
    $module_array = $userObject->get_staff_accessable_modules();
    $old_school = '';
    $old_schoolcode = '';
    foreach ($module_array as $module) {
        if (is_null($module['schoolcode'])) {
            if ($module['school'] != $old_school or !is_null($old_schoolcode)) {
                echo '<div class="subsect_table"><div class="subsect_title"><nobr>' . $module['school'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
            }
        } else {
            if ($module['schoolcode'] != $old_schoolcode) {
                echo '<div class="subsect_table"><div class="subsect_title"><nobr>' . $module['schoolcode'] . ' ' . $module['school'] . "</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\" /></div></div>\n";
            }
        }
        $match = false;
        foreach ($modules_array as $separate_module) {
            if ($separate_module == $module['id']) {
                $match = true;
            }
        }
        if ($match == true) {
            if (in_array($module['id'], $staff_modules) or $userObject->has_role('SysAdmin')) {
                echo "<div class=\"r2 mod\" id=\"divmod$module_no\"><input type=\"checkbox\" class=\"toggle meta\" data-toggleid=\"mod" . $module_no . "\" name=\"mod$module_no\" id=\"mod$module_no\" value=\"" . $module['idMod'] . "\" checked $disabled><label for=\"mod$module_no\">" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</label></div>\n";
            } else {
                echo "<div class=\"r2 mod\" id=\"divmod$module_no\"><input type=\"checkbox\" name=\"dummymod$module_no\" value=\"" . $module['idMod'] . "\" checked disabled><input type=\"checkbox\" name=\"mod$module_no\" id=\"mod$module_no\" style=\"display:none\" value=\"" . $module['idMod'] . "\" checked><label for=\"mod$module_no\">" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</label></div>\n";
            }
        } else {
            echo "<div class=\"r1 mod\" id=\"divmod$module_no\"><input type=\"checkbox\"  class=\"toggle meta\" data-toggleid=\"mod" . $module_no . "\" name=\"mod$module_no\" id=\"mod$module_no\" value=\"" . $module['idMod'] . "\"$disabled><label for=\"mod$module_no\">" . $module['id'] . ': ' . mb_substr($module['fullname'], 0, 60) . "</label></div>\n";
        }
        $module_no++;
        $old_school = $module['school'];
        $old_schoolcode = $module['schoolcode'];
    }
}
    echo "<input type=\"hidden\" name=\"module_no\" id=\"module_no\" value=\"$module_no\" /></div>\n";
    echo "</td>\n";

    echo '<td>' . output_labs($properties->get_labs(), $configObject->get_setting('core', 'cfg_summative_mgmt'), $properties->get_paper_type(), $userObject, $changed_labs, $mysqli) . "</td></tr>\n";

?>
  <tr><td class="headbar" style="padding:2px" colspan="2">&nbsp;<?php echo $string['restricttometadata']; ?></td></tr>
  <tr><td style="vertical-align:top; height:110px" colspan="2"><div style="height:111px; overflow-y:scroll;border:1px solid #828790; font-size:90%" id="metadata_security"></div></td></tr>
  </table>

  </td></tr>
</table>

<table id="seb" class="tabsection" style="display: none">
<?php
  $seb_metadata = Paper_utils::get_metadata($mysqli, $paperID, 'seb_hash');
  $seb_keys = $seb_metadata['seb_hash'] ?? [];
?>
  <tr><td class="tabtitle"><img src="../artwork/safe_exam_browser.png" alt="Icon" align="middle" /><?php echo $string['seb_keys_heading']; ?></td></tr>
  <tr><td>
    <?php $properties->renderSettings('seb'); ?>
  </td></tr>
  <tr><td style="padding: 5px 0; text-align: center;"><?php echo $string['seb_keys_title'] ?></td></tr>
  <tr><td>
    <textarea id="seb_keys_text" name="seb_keys_text" class="sebkeys"><?php echo htmlspecialchars(implode("\n", $seb_keys)); ?></textarea>
  </td></tr>
</table>

<table id="rubric" class="tabsection" style="display: none">
  <tr><td class="tabtitle"><img src="../artwork/rubric_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['rubricheading']; ?></td></tr>
  <tr><td><?php $texteditorplugin->get_textarea('rubric_text', 'rubric_text', $texteditorplugin->get_text_for_display(htmlspecialchars($properties->get_rubric()), ENT_NOQUOTES), plugins\plugins_texteditor::TYPE_STANDARD, 'width:100%; height:537px'); ?></td></tr>
<?php
$properties->renderSettings('rubric');
?>
</table>

<table id="feedback" class="tabsection" style="display: none">
  <tr><td class="tabtitle" colspan="2"><img src="../artwork/feedback_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['feedbackheading']; ?></td></tr>

<?php
echo '<tr><td colspan="2" valign="top">';

echo "<table class=\"cellpad6\" style=\"margin:15px\">\n";

$feedback_reports = array('objectives' => '', 'questions' => '', 'cohort_performance' => '', 'external_examiner' => '');

$feedback_details = $mysqli->prepare('SELECT idfeedback_release, type FROM feedback_release WHERE paper_id = ?');
$feedback_details->bind_param('i', $paperID);
$feedback_details->execute();
$feedback_details->bind_result($idfeedback_release, $type);
$feedback_details->store_result();
while ($feedback_details->fetch()) {
    $feedback_reports[$type] = 1;
}
$feedback_details->close();

if (in_array($properties->get_paper_type(), array('0', '1', '2', '4', '5'))) {
    echo '<tr><td><img src="../artwork/feedback_release_icon.png" width="48" height="48" />';
    echo '<td><input type="hidden" name="old_objectives_report" value="' . $feedback_reports['objectives'] . '" />';
    if ($feedback_reports['objectives'] === '') {
        echo '<input type="radio" name="objectives_report" value="1" />' . $string['on'] . '</td><td><input type="radio" name="objectives_report" value="0" checked="checked" />' . $string['off'] . '</td>';
    } else {
        echo '<input type="radio" name="objectives_report" value="1" checked="checked" />' . $string['on'] . '</td><td><input type="radio" name="objectives_report" value="0" />' . $string['off'] . '</td>';
    }
    echo '<td>' . $string['objectivesreport'] . '<br /><a href="https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/students/objectives_feedback.php?id=' . $properties->get_crypt_name() . '" target="_blank">https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/students/objectives_feedback.php?id=' . $properties->get_crypt_name() . "</a></td></tr>\n";
}
if ($q_feedback_enabled and in_array($properties->get_paper_type(), array('1', '2', '4', '5'))) {
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    echo '<tr><td><img src="../artwork/question_release_icon.png" width="48" height="48" />';
    // Question-based Feedback
    echo '<td><input type="hidden" name="old_questions_report" value="' . $feedback_reports['questions'] . '" />';
    if ($feedback_reports['questions'] === '') {
        echo '<input type="radio" name="questions_report" value="1" />' . $string['on'] . '</td><td><input type="radio" name="questions_report" value="0" checked="checked" />' . $string['off'] . '</td>';
    } else {
        echo '<input type="radio" name="questions_report" value="1" checked="checked" />' . $string['on'] . '</td><td><input type="radio" name="questions_report" value="0" />' . $string['off'] . '</td>';
    }
    echo '<td>' . $string['questionfeedback'] . '<br />';
    if ($properties->get_paper_type() == '2') {
        echo '<span style="color:#C00000">' . $string['feedbackwarning'] . '</span></br />';
    }
    echo '<a href="https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/students/question_feedback.php?id=' . $properties->get_crypt_name() . '" target="_blank">https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/students/question_feedback.php?id=' . $properties->get_crypt_name() . "</a></td></tr>\n";
}

if (in_array($properties->get_paper_type(), array('2', '4', '5'))) {
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    echo '<tr><td><img src="../artwork/cohort_performance_icon.png" width="48" height="48" />';
    // Cohort performance-based Feedback
    echo '<td><input type="hidden" name="old_cohort_performance" value="' . $feedback_reports['cohort_performance'] . '" />';
    if ($feedback_reports['cohort_performance'] === '') {
        echo '<input type="radio" name="cohort_performance" value="1" />' . $string['on'] . '</td><td><input type="radio" name="cohort_performance" value="0" checked="checked" />' . $string['off'] . '</td>';
    } else {
        echo '<input type="radio" name="cohort_performance" value="1" checked="checked" />' . $string['on'] . '</td><td><input type="radio" name="cohort_performance" value="0" />' . $string['off'] . '</td>';
    }
    echo '<td>' . $string['cohortperformancefeedback'] . '<br /><a href="https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/students/performance_summary.php" target="_blank">https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/students/performance_summary.php</a></td></tr>\n";
}

if (in_array($properties->get_paper_type(), array('1', '2'))) {
    echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";
    echo '<tr><td><img src="../artwork/external_examiner_icon.png" width="48" height="48" />';
    // External Examiner Feedback
    echo '<td><input type="hidden" name="old_external_examiner" value="' . $feedback_reports['external_examiner'] . '" />';
    if ($feedback_reports['external_examiner'] === '') {
        echo '<input type="radio" name="external_examiner" value="1" />' . $string['on'] . '</td><td><input type="radio" name="external_examiner" value="0" checked="checked" />' . $string['off'] . '</td>';
    } else {
        echo '<input type="radio" name="external_examiner" value="1" checked="checked" />' . $string['on'] . '</td><td><input type="radio" name="external_examiner" value="0" />' . $string['off'] . '</td>';
    }
    echo '<td>' . $string['externalexaminerfeedback'] . '<br /><span style="color:#808080">' . $string['externalwarning'] . '</span><br /><a href="https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/reviews/" target="_blank">https://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . "/reviews/</a></td></tr>\n";
}

echo "</table>\n";

if ($properties->get_paper_type() == '0') {
    echo '<table cellpadding="3" cellspacing="0" border="0" id="feedback_on" style="width:100%">';
} else {
    echo '<table cellpadding="0" cellspacing="0" border="0" id="feedback_on" style="width:100%; display:none">';
}
if ($properties->get_paper_type() != '4') {
    echo '<tr><td colspan="4" class="headbar">&nbsp;';
    echo $string['answerscreensettings'];
    echo '</td></tr>
          <tr><td colspan="4">&nbsp;</td></tr>
          <tr><td style="width:33%"><input type="checkbox" name="display_students_response" value="1"';
    if ($properties->get_display_students_response() == '1') {
        echo ' checked';
    }
    echo ' />';
    echo $string['ticks_crosses'];
    echo '</td><td style="width:33%"><input type="checkbox" name="display_question_mark" value="1"';
    if ($properties->get_display_question_mark() == '1') {
            echo ' checked';
    }
    echo ' />';
    echo $string['question_marks'];
    echo '</td><td rowspan="2" style="width:33%; text-indent:-24px; padding-left:24px"><input type="checkbox" name="hide_if_unanswered" value="1"';
    if ($properties->get_hide_if_unanswered() == '1') {
        echo ' checked';
    }
    echo ' />';
    echo $string['hideallfeedback'];
    echo '</td></tr>
           <tr><td><input type="checkbox" name="display_correct_answer" value="1"';
    if ($properties->get_display_correct_answer() == '1') {
        echo ' checked';
    }
    echo ' />';
    echo $string['correctanswerhighlight'] ;
    echo '</td><td><input type="checkbox" name="display_feedback" value="1"';
    if ($properties->get_display_feedback() == '1') {
        echo ' checked';
    }
    echo ' />';
    echo $string['textfeedback'] ;
    echo '</td></tr>';
}
echo "</table>\n";

echo "</td></tr>\n";
echo "<tr><td colspan=\"4\">&nbsp;</td></tr>\n";

if (!in_array($properties->get_paper_type(), array('2', '4'))) {
    echo '<tr><td colspan="2" class="headbar">&nbsp;' . $string['textualfeedback'] . "</td></tr>\n";
    echo '<tr><td style="text-align:center">' . $string['above'] . '</td><td style="text-align:center">' . $string['message'] . "</td></tr>\n";
    for ($i = 1; $i <= 10; $i++) {
        echo "<tr><td><select name=\"feedback_value$i\"><option value=\"\"></option>";
        for ($percent = 0; $percent <= 100; $percent++) {
            if (isset($textual_feedback[$i]['boundary']) and  $textual_feedback[$i]['boundary'] == $percent) {
                echo "<option value=\"$percent\" selected>$percent%</option>";
            } else {
                echo "<option value=\"$percent\">$percent%</option>";
            }
        }
        $msg = '';
        if (isset($textual_feedback[$i]['msg'])) {
            $msg = $textual_feedback[$i]['msg'];
        }
        echo "</select></td><td><textarea name=\"feedback_msg$i\" cols=\"60\" rows=\"1\" style=\"width:620px; height:18px;\">$msg</textarea></td></tr>\n";
    }
}
$properties->renderSettings('feedback');
?>
</table>

<table id="reviewers" class="tabsection" style="display: none">
<tr><td class="tabtitle" colspan="2"><img src="../artwork/reviewers_heading_icon.png" alt="Icon" align="middle" /><?php echo $string['reviewersheading']; ?></td></tr>
<tr>
<td align="center" colspan="2">
<table cellpadding="1" cellspacing="2" border="0">
<tr><td colspan="3">&nbsp;<?php
  $result = $mysqli->prepare("SELECT COUNT(q_id) AS sct_no FROM (papers, questions) WHERE papers.paper = ? AND papers.question = questions.q_id AND q_type = 'sct'");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($sct_no);
  $result->fetch();
  $result->close();
if ($sct_no > 0) {
    echo '<a href="' . $configObject->get('cfg_root_path') . '/reviews/sct_review.php?id=' . urlencode($properties->get_crypt_name()) . '" target="_blank">' . NetworkUtils::get_protocol() . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path') . '/reviews/sct_review.php?id=' . urlencode($properties->get_crypt_name()) . '</a>';
}

?></td></tr>
<tr><td class="headbar">&nbsp;<?php echo $string['internalreviewers']; ?></td><td>&nbsp;&nbsp;</td><td class="headbar">&nbsp;<?php echo $string['externalexaminers']; ?></td></tr>
<tr><td><?php echo $string['deadline']; ?>&nbsp;
<?php
    // Split the end date
if ($properties->get_internal_review_deadline() == '') {
    $split_year = 0;
    $split_month = 0;
    $split_day = 0;
} else {
    $internal_review_deadline = DateTime::createFromFormat('Y-m-d', $properties->get_internal_review_deadline(), $local_time);
    $internal_review_deadline->setTimezone($local_time);

    $split_year = $internal_review_deadline->format('Y');
    $split_month = $internal_review_deadline->format('m');
    $split_day = $internal_review_deadline->format('d');
}

// Available to Day
echo "<select id=\"int_tday\" name=\"int_tday\">\n<option value=\"\">" . $string['na'] . "</option>\n";
for ($i = 1; $i < 32; $i++) {
    if ($i < 10) {
        if ($i == $split_day) {
            echo "<option value=\"0$i\" selected>";
        } else {
            echo "<option value=\"0$i\">";
        }
    } else {
        if ($i == $split_day) {
            echo "<option value=\"$i\" selected>";
        } else {
            echo "<option value=\"$i\">";
        }
    }
    if ($i < 10) {
        echo '0';
    }
        echo "$i</option>\n";
}
echo "</select>\n";
// Available to Month
echo "<select id=\"int_tmonth\" name=\"int_tmonth\">\n<option value=\"\">" . $string['na'] . "</option>\n";
for ($i = 0; $i < 12; $i++) {
    $trans_month = mb_substr($string[$months[$i]], 0, 3, 'UTF-8');
    if (($split_month - 1) == $i) {
        if ($i < 9) {
            echo '<option value="0' . ($i + 1) . "\" selected>$trans_month</option>\n";
        } else {
            echo '<option value="' . ($i + 1) . "\" selected>$trans_month</option>\n";
        }
    } else {
        if ($i < 9) {
            echo '<option value="0' . ($i + 1) . "\">$trans_month</option>\n";
        } else {
            echo '<option value="' . ($i + 1) . "\">$trans_month</option>\n";
        }
    }
}
echo "</select>\n";
// Available to Year
echo "<select id=\"int_tyear\" name=\"int_tyear\">\n<option value=\"\">" . $string['na'] . "</option>\n";
if ($split_year < date('Y') and $split_year > 1999) {
    $start_year = $split_year;
} else {
    $start_year = date('Y');
}
for ($i = $start_year; $i < (date('Y') + 2); $i++) {
    if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
        echo "<option value=\"$i\">$i</option>\n";
    }
}
?>
</td><td></td>
<td><?php echo $string['deadline']; ?>&nbsp;
<?php
    // Split the end date
if ($properties->get_external_review_deadline() == '') {
    $split_year = 0;
    $split_month = 0;
    $split_day = 0;
} else {
    $external_review_deadline = DateTime::createFromFormat('Y-m-d', $properties->get_external_review_deadline(), $local_time);
    $external_review_deadline->setTimezone($local_time);

    $split_year = $external_review_deadline->format('Y');
    $split_month = $external_review_deadline->format('m');
    $split_day = $external_review_deadline->format('d');
}

// Available to Day
echo "<select id=\"ext_tday\" name=\"ext_tday\">\n<option value=\"\">" . $string['na'] . "</option>\n";
for ($i = 1; $i < 32; $i++) {
    if ($i < 10) {
        if ($i == $split_day) {
            echo "<option value=\"0$i\" selected>";
        } else {
            echo "<option value=\"0$i\">";
        }
    } else {
        if ($i == $split_day) {
            echo "<option value=\"$i\" selected>";
        } else {
            echo "<option value=\"$i\">";
        }
    }
    if ($i < 10) {
        echo '0';
    }
    echo "$i</option>\n";
}
echo "</select>\n";
// Available to Month
echo "<select id=\"ext_tmonth\" name=\"ext_tmonth\">\n<option value=\"\">" . $string['na'] . "</option>\n";
for ($i = 0; $i < 12; $i++) {
    $trans_month = mb_substr($string[$months[$i]], 0, 3, 'UTF-8');
    if (($split_month - 1) == $i) {
        if ($i < 9) {
            echo '<option value="0' . ($i + 1) . "\" selected>$trans_month</option>\n";
        } else {
            echo '<option value="' . ($i + 1) . "\" selected>$trans_month</option>\n";
        }
    } else {
        if ($i < 9) {
            echo '<option value="0' . ($i + 1) . "\">$trans_month</option>\n";
        } else {
            echo '<option value="' . ($i + 1) . "\">$trans_month</option>\n";
        }
    }
}
echo "</select>\n";
// Available to Year
echo "<select id=\"ext_tyear\" name=\"ext_tyear\">\n<option value=\"\">" . $string['na'] . "</option>\n";
if ($split_year < date('Y') and $split_year > 1999) {
    $start_year = $split_year;
} else {
    $start_year = date('Y');
}
for ($i = $start_year; $i < (date('Y') + 2); $i++) {
    if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
        echo "<option value=\"$i\">$i</option>\n";
    }
}
?>
</td></tr>
<?php
echo '<tr><td><div style="width:350px; height:468px; overflow-y:scroll; border:1px solid #828790; font-size:90%">';

// Get all users for teams within the schools of the current user
// Also get all admin users for those schools
$school_sql = '';
$admin_school_sql = '';
$schools = getSchools($staff_modules, $mysqli);

if (count($schools) > 0) {
    $schools_list = implode(',', $schools);
    if ($userObject->has_role('SysAdmin')) {
        $school_sql = 'AND user_deleted IS NULL';
    } else {
        $school_sql = "AND schoolid IN ($schools_list) AND user_deleted IS NULL";
    }
    $admin_school_sql = <<< SQL
UNION SELECT DISTINCT users.id, title, initials, surname, first_names
FROM users, admin_access
WHERE users.id = admin_access.userID AND admin_access.schools_id IN ($schools_list)
AND user_deleted IS NULL
SQL;
}

// Make sure that current reviewers always appear on the list
$current_internals = $properties->get_internal_reviewers();
$current_internals_sql = '';
if (count($properties->get_internal_reviewers()) > 0) {
    $current_internals_sql = 'UNION SELECT DISTINCT id, title, initials, surname, first_names FROM users WHERE id IN (' . implode(',', array_keys($current_internals)) . ') AND user_deleted IS NULL';
}
// Add internal reviewers to list.
$internal_reviwers = "
UNION SELECT DISTINCT 
    users.id, title, initials, surname, first_names 
FROM 
    users, user_roles ur, roles r  
WHERE
    ur.roleid = r.id
    AND r.name = 'Internal Reviewer'
    AND users.id = ur.userid
    AND user_deleted IS NULL
";

// Dynamically choose tables and join based on role.
if ($userObject->has_role('SysAdmin')) {
    $tables = 'users, modules_staff, user_roles ur, roles r';
    $join = 'users.id = modules_staff.memberID AND ur.roleid = r.id AND users.id = ur.userid';
} else {
    $tables = 'users, modules_staff, modules, user_roles ur, roles r';
    $join = 'users.id = modules_staff.memberID AND modules.id = modules_staff.idMod AND ur.roleid = r.id AND users.id = ur.userid';
}

$query = "
SELECT DISTINCT 
    users.id, title, initials, surname, first_names 
FROM 
    $tables 
WHERE 
    r.name != 'left'
    AND $join $school_sql $admin_school_sql $current_internals_sql $internal_reviwers 
ORDER BY 
    surname, initials
";
$internal_details = $mysqli->prepare($query);
$internal_details->execute();
$internal_details->bind_result($internal_id, $internal_title, $internal_initials, $internal_surname, $internal_first_names);
$internal_no = 0;
while ($internal_details->fetch()) {
    $match = false;
    foreach ($current_internals as $reviewerID => $reviewer_name) {
        if ($internal_id == $reviewerID) {
            $match = true;
        }
    }
    if ($match) {
        echo "<div class=\"r2\" id=\"divinternal$internal_no\"><input type=\"checkbox\" class=\"toggle\" data-toggleid=\"internal" . $internal_no . "\" name=\"internal$internal_no\" id=\"internal$internal_no\" value=\"$internal_id\" checked><label for=\"internal$internal_no\">" . ucwords(mb_strtolower($internal_surname)) . "<span style=\"color:#808080\">, $internal_first_names. $internal_title</span></label></div>\n";
    } else {
        echo "<div class=\"r1\" id=\"divinternal$internal_no\"><input type=\"checkbox\" class=\"toggle\" data-toggleid=\"internal" . $internal_no . "\" name=\"internal$internal_no\" id=\"internal$internal_no\" value=\"$internal_id\"><label for=\"internal$internal_no\">" . ucwords(mb_strtolower($internal_surname)) . "<span style=\"color:#808080\">, $internal_first_names. $internal_title</span></label></div>\n";
    }
    $internal_no++;
}
$internal_details->close();
echo "<input type=\"hidden\" id=\"internal_no\" name=\"internal_no\" value=\"$internal_no\" /></div></td><td></td>";

echo '<td><div style="width:350px; height:468px; overflow-y:scroll; border:1px solid #828790; font-size:90%">';
$current_externals = $properties->get_externals();
$sql = "
SELECT DISTINCT 
    users.id, title, initials, surname, first_names 
FROM 
    users, user_roles ur, roles r  
WHERE
    ur.roleid = r.id
    AND r.name = 'External Examiner'
    AND users.id = ur.userid
    AND grade != 'left' AND user_deleted IS NULL 
ORDER BY 
    surname, initials
";
$external_details = $mysqli->prepare($sql);
$external_details->execute();
$external_details->bind_result($external_id, $external_title, $external_initials, $external_surname, $external_first_names);
$examiner_no = 0;
while ($external_details->fetch()) {
    $match = false;
    foreach ($current_externals as $reviewerID => $reviewer_name) {
        if ($external_id == $reviewerID) {
            $match = true;
        }
    }
    if ($match) {
        echo "<div class=\"r2\" id=\"divexaminer$examiner_no\"><input type=\"checkbox\" class=\"toggle\" data-toggleid=\"examiner" . $examiner_no . "\" name=\"examiner$examiner_no\" id=\"examiner$examiner_no\" value=\"$external_id\" checked><label for=\"examiner$examiner_no\">" . ucwords(mb_strtolower($external_surname)) . "<span style=\"color:#808080\">, $external_first_names. $external_title</span></label></div>\n";
    } else {
        echo "<div class=\"r1\" id=\"divexaminer$examiner_no\"><input type=\"checkbox\" class=\"toggle\" data-toggleid=\"examiner" . $examiner_no . "\" name=\"examiner$examiner_no\" id=\"examiner$examiner_no\" value=\"$external_id\"><label for=\"examiner$examiner_no\">" . ucwords(mb_strtolower($external_surname)) . "<span style=\"color:#808080\">, $external_first_names. $external_title</span></label></div>\n";
    }
    $examiner_no++;
}
$external_details->close();
echo "<input type=\"hidden\" name=\"examiner_no\" id=\"examiner_no\" value=\"$examiner_no\" /></div></td>\n</tr>\n";
?>
</table>
</td>
</tr>
<?php
$properties->renderSettings('reviwers');
?>
</table>

<table id="reference" class="tabsection" style="display: none">
<tr><td class="tabtitle" colspan="2"><img src="../artwork/toggle_log.png" alt="Icon" align="middle" /><?php echo $string['referenceheading'] ?></td></tr>
<tr><td style="vertical-align:top"><div id="reference_list" style="padding: 5px"></div></td></tr>
<?php
$properties->renderSettings('reference');
?>
</table>

<table id="changes" class="tabsection" style="display: none">
<tr><td class="tabtitle" colspan="2"><img src="../artwork/version_icon.png" alt="Icon" align="middle" /><?php echo $string['changesheading'] ?></td></tr>
<tr><td style="vertical-align:bottom"><div id="change_list" style="height:543px; overflow-y:scroll">
<table cellspacing="0" cellpadding="2" border="0" style="width:100%">
<?php
$modules = module_utils::get_module_list_by_id($mysqli);

$user_list = array();
if (count($changed_reviewers) > 0) {
    $reviewer_in = implode(',', array_keys($changed_reviewers));
    $results = $mysqli->prepare("SELECT id, title, surname FROM users WHERE id IN ($reviewer_in)");
    $results->execute();
    $results->bind_result($id, $title, $surname);
    while ($results->fetch()) {
        $user_list[$id] = $title . ' ' . $surname;
    }
    $results->close();
}

$reference_material = array();
$results = $mysqli->prepare('SELECT id, title FROM reference_material');
$results->execute();
$results->bind_result($id, $title);
while ($results->fetch()) {
    $reference_material[$id] = $title;
}
$results->close();

$folders = folder_utils::get_all_folders($mysqli);

echo '<tr><th>' . $string['part'] . '</th><th>' . $string['old'] . '</th><th>' . $string['new'] . '</th><th>' . $string['date'] . '</th><th>' . $string['author'] . '</th></tr>';
// Changes retrieved at beginning of file
$rows = count($changes);
for ($i = 0; $i < $rows; $i++) {
    $part = $changes[$i]['part'];

    $old = $changes[$i]['old'];
    $new = $changes[$i]['new'];

    switch ($part) {
        case 'startdate':
        case 'enddate':
            $old = date($configObject->get('cfg_short_datetime_php'), $old);
            $new = date($configObject->get('cfg_short_datetime_php'), $new);
            break;
        case 'folder':
            $old = format_folders($old, $folders);
            $new = format_folders($new, $folders);
            break;
        case 'method':
            $old = format_method($old, $string);
            $new = format_method($new, $string);
            break;
        case 'displaycalculator':
        case 'demosoundclip':
        case 'photos':
        case 'ticks_crosses':
        case 'hideallfeedback':
        case 'textfeedback':
        case 'correctanswerhighlight':
        case 'question_marks':
            $old = format_on_off($old, $string);
            $new = format_on_off($new, $string);
            break;
        case 'externals':
        case 'internals':
            $old = format_user($old, $user_list);
            $new = format_user($new, $user_list);
            break;
        case 'background':
        case 'foreground':
        case 'theme':
        case 'labelsnotes':
            $old = format_color($old);
            $new = format_color($new);
            break;
        case 'referencematerial':
            $old = format_referencematerial($old, $reference_material);
            $new = format_referencematerial($new, $reference_material);
            break;
        case 'display':
            $old = format_display($old, $string);
            $new = format_display($new, $string);
            break;
        case 'navigation':
            $old = format_navigation($old, $string);
            $new = format_navigation($new, $string);
            break;
        case 'review':
            $old = format_review($old, $string);
            $new = format_review($new, $string);
            break;
        case 'passmark':
        case 'distinction':
            $old = format_passmark($old, $string);
            $new = format_passmark($new, $string);
            break;
        case 'labs':
            $old = format_lab($old, $changed_labs);
            $new = format_lab($new, $changed_labs);
            break;
        case 'marking':
            $old = format_marking($old, $string);
            $new = format_marking($new, $string);
            break;
    }

    if (isset($string[$part])) {
        $part = $string[$part];
    }
    echo '<tr><td>' . ucfirst($part) . "</td><td>$old</td><td>$new</td><td>" . date($configObject->get('cfg_very_short_datetime_php'), $changes[$i]['date']) . '</td><td>' . $changes[$i]['title'] . ' ' . $changes[$i]['surname'] . "</td><tr>\n";
}
?>
</table>
</div></td></tr>
</table>

</td>
</tr>
<tr><td colspan="2" align="right"><input type="submit" class="ok" id="submitpropeties" name="Submit" value="<?php echo $string['ok']; ?>" disabled="disabled"/><input type="button" name="home" class="cancel" value="<?php echo $string['cancel']; ?>" /></td></tr>
</table>

<input type="hidden" id="noadd" name="noadd" value="<?php
if (isset($_GET['noadd'])) {
    echo $_GET['noadd'];
}
?>" />
<input type="hidden" name="caller" value="<?php
if (isset($_GET['caller'])) {
    echo $_GET['caller'];
}
?>" />
</form>
<?php
$dataset['name'] = 'dataset';
$dataset['attributes']['rootpath'] = $cfg_root_path;
$dataset['attributes']['type'] = $properties->get_paper_type();
$dataset['attributes']['id'] = $paperID;
$dataset['attributes']['minavail'] = $minavailability;
$dataset['attributes']['summativemanagment'] = ($sum_disabled === '');
$render->render($dataset, array(), 'dataset.html');
// JS utils dataset.
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
$mysqli->close();
?>
<script src='../js/paperpropertiesinit.min.js'></script>
</body>
</html>
