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
* Admin screen to edit a config settings
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';
require '../include/timezones.php';
require '../include/toprightmenu.inc';

if (isset($_POST['submit'])) {
    foreach ($configObject->get_setting('core') as $setting => $value) {
        $new_value = param::optional($setting, '', param::RAW, param::FETCH_POST);
        // Timezones are display in a multi selectbox so the post will be an array.
        if ($setting == 'paper_timezones') {
            $arrayvalue = array();
            foreach ($new_value as $v) {
                $parts = explode("|", $v);
                $arrayvalue[$parts[0]] = $parts[1];
            }
            $new_value = $arrayvalue;
        }
        $type = $configObject->get_setting_type('core', $setting);
        if ($type == Config::CSV) {
            $new_value = explode(',', $new_value);
        }
        // Check value is of expected type. No change if not expected type.
        if (!Config::check_type($new_value, $type)) {
            $new_value = $value;
        }
        if ($value != $new_value) {
            $configObject->set_setting($setting, $new_value, $type);
        }
    }
    header("location: config.php", true, 303);
    exit();
}

$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$lang['title'] = $string['config'];
$additionaljs = "<script type=\"text/javascript\" src=\"../js/jquery-ui-1.10.4.min.js\"></script><script type=\"text/javascript\" src=\"../js/system_tooltips.js\"></script>
    <script type=\"text/javascript\" src=\"../js/config.js\"></script>";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/config.css\"/>";
$breadcrumb = array($string['home'] => "../index.php", $string['administrativetools'] => "index.php");
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('', '', $lang, $toprightmenu, 'admin/options_empty.html');
$render->render_admin_content($breadcrumb, $lang);

?>

<br />
<div align="center">
    <p><?php echo $string['configblurb']; ?></p>
    <form id="theform" name="add_config" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" autocomplete="off">
        <table cellpadding="0" cellspacing="2" border="0">
        <?php
            $displayconfigs = array();
            $configs = $configObject->get_setting('core');
            foreach (Config::$config_area as $area) {
                foreach ($configs as $setting => $value) {
                    if (strpos($setting, $area) !== false) {
                        $displayconfigs[$area][$setting] = $value;
                    }
                }
            }
            foreach ($displayconfigs as $area => $conf) {
                echo '<tr><td class="fieldheader">' . $string[$area] . '</td></tr>';
                foreach ($conf as $setting => $value) {
                    $type = $configObject->get_setting_type('core', $setting);
                    if (!is_null($configObject->get('file_config_override'))) {
                        $override = $configObject->get('file_config_override');
                    } else {
                        $override = false;
                    }
                    if (!is_null($configObject->get($setting)) and $override) {
                        $disabled = " disabled";
                    } else {
                        $disabled = "";
                    }
                    if ($type == Config::BOOLEAN) {
                        if ($value == true) {
                            $checked = "checked";
                        } else {
                            $checked = "";
                        }
                        echo "<tr><td class=\"field\"><label for=\"" . $setting . "\">" . $setting . "</label>&nbsp;<img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string[$setting] . "\" /></td><td><input type=\"checkbox\" name=\"" . $setting . "\" id=\"" . $setting . "\"" . $checked . $disabled . "/></td>";
                    } elseif ($type == Config::PASSWORD) {
                        echo "<tr><td class=\"field\"><label for=\"" . $setting . "\">" . $setting. "</label>&nbsp;<img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string[$setting] . "\" /></td><td><input type=\"password\" size=\"20\" id=\"" . $setting . "\" name=\"" . $setting . "\" value=\"" . htmlspecialchars($value) . "\""  . $disabled . "/></td>";
                    } elseif ($type == Config::TIMEZONES) {
                        echo "<tr><td class=\"field\"><label for=\"" . $setting . "\">" . $setting. "</label>&nbsp;<img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string[$setting] . "\" /></td><td><select id=\"" . $setting . "\" name=\"" . $setting . "[]\" multiple>";
                        // Compare config setting against list of possible timezones.
                        foreach ($timezone_array as $individual_zone => $display_zone) {
                            foreach ($value as $i => $v) {
                                if ($individual_zone == $i) {
                                    $selected = "selected";
                                    break;
                                }
                                $selected = "";
                            }
                            echo "<option value=\"" . htmlspecialchars($individual_zone) . "|" . htmlspecialchars($display_zone) . "\" $selected>" . htmlspecialchars($display_zone) . "</option>";
                        }
                        echo "</select></td>";
                    } else {
                        if ($type == Config::CSV) {
                            $value = implode(',', $value);
                        }
                        if ($type == Config::STRING or $type == Config::INTEGER) {
                            $size = 20;
                        } else {
                            $size = 100;
                        }
                        echo "<tr><td class=\"field\"><label for=\"" . $setting . "\">" . $setting. "</label>&nbsp;<img src=\"../artwork/tooltip_icon.gif\" class=\"help_tip\" title=\"" . $string[$setting] . "\" /></td><td><input type=\"text\" size=\"" . $size . "\" id=\"" . $setting . "\" name=\"" . $setting . "\" value=\"" . htmlspecialchars($value) . "\""  . $disabled . "/></td>";
                    }
                    if ($disabled != "") {
                       echo "<td>" . $string['fileoverride'] . "</td>";
                    }
                    echo "</tr>";
                }
            }
        ?>
        </table>
      <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
    </form>
</div>

<?php
    $render->render_admin_footer();
?>
