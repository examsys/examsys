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
* Rogō plugin hompage.
*
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

require_once '../include/sysadmin_auth.inc';
require_once '../include/toprightmenu.inc';
require_once '../include/errors.php';

$pluginslist = plugin_manager::listplugins();

if (isset($_POST['Uninstall']) or isset($_POST['Update'])) {
    $pluginarray = array();
    foreach ($pluginslist as $plugin => $pluginns) {
        $change = check_var('chk_' . $plugin, 'POST', false, false, true);
        if (!is_null($change)) {
            $pluginarray[] = $plugin;
        }
    }
    $dbuser = check_var('dbuser', 'POST', true, false, true);
    $dbpasswd = check_var('dbpasswd', 'POST', true, false, true);
    $error = array();
    // Uninstall.
    if (isset($_POST['Uninstall'])) {
        foreach ($pluginarray as $plugin) {
            $p = new $pluginslist[$plugin]($mysqli);
            $uninstalled = $p->uninstall($dbuser, $dbpasswd);
            switch ($uninstalled) {
                case 'OK':
                    // All is well.
                    break;
                case 'DROP_SCHEMA_FAIL':
                    $error[$plugin] = $string['uninstallschemafail'];
                    break;
                default:
                    $error[$plugin] = $string['unsintallversionincorrect'];
            }
        }
    } else {
        // Install / Update.
        foreach ($pluginarray as $plugin) {
            $p = new $pluginslist[$plugin]($mysqli);
            $installed = $p->install($dbuser, $dbpasswd);
            if ($installed != 'OK') {
                switch ($installed) {
                    case 'INCORRECT_VERSION':
                        $error[$plugin] = sprintf($string['versionincorrect'], $p->get_file_version());
                        break;
                    case 'CURRENT_VERSION_HIGHER':
                        $error[$plugin] = sprintf($string['versionhigher'], $p->get_plugin_version());
                        break;
                    case 'ALREADY_INSTALLED':
                        $error[$plugin] = sprintf($string['alreadyinstalled'], $p->get_plugin_version());
                        break;
                    case 'UPDATE_FAIL':
                        $error[$plugin] = sprintf($string['updatefail'], $plugin);
                        break;
                    case 'SCHEMA_FAIL':
                        $error[$plugin] = sprintf($string['schemafail'], $plugin);
                        break;
                    default:
                        $error[$plugin] = sprintf($string['rogorequired'], $p->get_file_requires());
                }
            }
        }
    }
    if (count($error) == 0) {
        header("location: index.php", true, 303);
        exit();
    }
}
$render = new render($configObject);
$config['cfg_root_path'] = $configObject->get('cfg_root_path');
$toprightmenu = draw_toprightmenu();
$lang['title'] = $string['plugins'];
$lang['dbuser'] = $string['dbuser'];
$lang['dbpasswd'] = $string['dbpasswd'];
$lang['dbsettings'] = $string['dbsettings'];
$lang['update'] = $string['update'];
$lang['uninstall'] = $string['uninstall'];
$additionaljs = "";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $config['cfg_root_path'] . "/css/list.css\"/>";
$breadcrumb = array($string['home'] => "../index.php", $string['administrativetools'] => "../admin/index.php");
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('', '', $lang, $toprightmenu, 'admin/options_empty.html');
$render->render_admin_content($breadcrumb, $lang);
$plugins = array();
foreach ($pluginslist as $plugin => $pluginns) {
    $p = new $pluginns($mysqli);
    $newversion = $p->get_file_version();
    $oldversion = $p->get_plugin_version();
    $update = "";
    if (version::is_version_higher($newversion, $oldversion) or $newversion === $oldversion or $oldversion === false) {
        $install = true;
        if (!empty($error[$plugin])) {
            $update = "<div class=\"error\">" . $error[$plugin] . "</div>";
            $install = false;
         } else {
            $update = "<input type=\"checkbox\" class=\"ok\" name=\"chk_" . $plugin . "\">";
        }
    } else {
        $install = false;
    }
    $plugins[$plugin] = array($newversion, $oldversion, $install, $update);
}
$header = array(array('class' => 'col10', 'style' => 'width:20%', 'value' => $string['plugins']),
array('class' => 'col', 'style' => 'width:20%', 'value' => $string['newversion']),
array('class' => 'col', 'style' => 'width:20%', 'value' => $string['oldversion']),
array('class' => 'col', 'style' => 'width:20%', 'value' => $string['status']),
array('class' => 'col', 'style' => 'width:20%', 'value' => ''));
$render->render_admin_update($plugins, $header, $_SERVER['PHP_SELF'], $lang);
$render->render_admin_footer();