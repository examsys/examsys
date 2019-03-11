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
        $type = $configObject->get_setting_type('core', $setting);
        if ($type === Config::ASSOC) {
          $new_value = array();
          // Media types have a boolean value.
          if ($setting == 'system_mediatypes') {
            foreach ($value as $name => $oldval) {
              $enabled = param::optional($setting . '_' . str_replace('.', '_', $name), false, param::BOOLEAN, param::FETCH_POST);
              if ($enabled) {
                $new_value[$name] = 1;
              } else {
                $new_value[$name] = 0;
              }
            }
          } else {
            foreach ($value as $name => $oldval) {
              $newval = param::optional($setting . '_' . $name, '', param::TEXT, param::FETCH_POST);
              $new_value[$name] = $newval;
            }
          }
        } else {
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
            if ($type === Config::CSV or $type === Config::EMAIL) {
                $new_value = explode(',', $new_value);
            }
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
$additionaljs = "<script type=\"text/javascript\" src=\"../js/jquery-ui-1.10.4.min.js\"></script><script type=\"text/javascript\" src=\"../js/system_tooltips.js\"></script>
    <script type=\"text/javascript\" src=\"../js/config.min.js\"></script>";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../css/config.css\"/>";
$breadcrumb = array($string['home'] => "../index.php", $string['administrativetools'] => "index.php");
$render->render_admin_header($string, $additionaljs, $addtionalcss);
$render->render_admin_options('', '', $string, $toprightmenu, 'admin/options_empty.html');
$render->render_admin_content($breadcrumb, $string);
$data['action'] = Url::fromGlobals();
$render->render($data, $string, 'admin/config/config_header.html');
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
    $data['area'] = $area;
    $render->render($data, $string, 'admin/config/config_area.html');
    foreach ($conf as $setting => $value) {
        $data = array();
        $data['setting'] = $setting;
        $data['value'] = $value;
        $type = $configObject->get_setting_type('core', $setting);
        $data['type'] = $type;
        if (!is_null($configObject->get('file_config_override'))) {
            $override = $configObject->get('file_config_override');
        } else {
            $override = false;
        }
        if (!is_null($configObject->get($setting)) and $override) {
            $data['disabled'] = " disabled";
        } else {
            $data['disabled'] = "";
        }
        if ($type === Config::BOOLEAN) {
            if ($value == true) {
                $data['checked'] = "checked";
            } else {
                $data['checked'] = "";
            }
            
            $render->render($data, $string, 'admin/config/config_chk.html');
        } elseif ($type === Config::PASSWORD) {
          $data['value'] = htmlspecialchars($value);
          $render->render($data, $string, 'admin/config/config_pass.html');
        } elseif ($type === Config::TIMEZONES) {
            // Compare config setting against list of possible timezones.
            $i = 0;
            
            foreach ($timezone_array as $individual_zone => $display_zone) {
                $selected = "";
                if (isset($value[$individual_zone])) {
                    $selected = "selected";
                }
                $data['zone'][$i]['iz'] =  htmlspecialchars($individual_zone);
                $data['zone'][$i]['dz'] =  htmlspecialchars($display_zone);
                $data['zone'][$i]['selected'] = $selected;
                $i++;
            }
            $render->render($data, $string, 'admin/config/config_tz.html');
        } elseif ($type === Config::ASSOC) {
            $idx = 0;
            foreach ($value as $i => $v) {
              $data['item'][$idx]['i'] = htmlspecialchars($i);
              $data['item'][$idx]['v'] = htmlspecialchars($v);

              $idx++;
            }
            $render->render($data, $string, 'admin/config/config_assoc.html');
        } else {
            if ($type === Config::CSV or $type === Config::EMAIL) {
                $value = implode(',', $value);
            }
            if ($type == Config::STRING or $type === Config::INTEGER or $type === Config::DOUBLE) {
                $data['size'] = 20;
            } else {
                $data['size'] = 100;
            }
            $data['value'] = htmlspecialchars($value);
            $render->render($data, $string, 'admin/config/config.html');
        }
    }
}

$render->render(array(), $string, 'admin/config/config_footer.html');
$render->render_admin_footer();