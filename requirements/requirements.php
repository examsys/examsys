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
 * Check that system requirements are met before updating.
 * As we may not have twig we cannot use templates in this file.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 The University of Nottingham
 */

require_once '../include/load_config.php';

if (is_null($configObject->get('cfg_web_root'))) {
    require_once '../include/path_functions.inc.php';
    $cfg_web_root = get_root_path() . '/';
    $configObject->set('cfg_web_root', $cfg_web_root);
} elseif (!file_exists($configObject->get('cfg_web_root'))) {
    // A configuration file exists, but the configured web root does not exist.
    require_once '../include/path_functions.inc.php';
    $cfg_web_root = get_root_path() . '/';
    $configObject->set('cfg_web_root', $cfg_web_root);
    $badconfigfile = true;
}
$language = LangUtils::getLang($cfg_web_root);

// Install lang packs if not installed.
$langpackfound = 0;
if (!LangUtils::langPackInstalled($language)) {
    try {
        InstallUtils::download_langpacks();
        $langpackfound = 1;
    } catch (Exception $e) {
        $langpackfound = 2;
    }
}

LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));

if (!isset($string)) {
    // The language files were not loaded. This means that either it is not configured for the ExamSys root directory,
    // or there is a differnece in case on a system that is not case sensitive.
    InstallUtils::displayError(['91' => 'Could not load language files. Please check the case of the cfg_web_root setting.']);
    die();
}

if (isset($badconfigfile)) {
    // The cfg_web_root setting directory does not exist.
    InstallUtils::displayError(['92' => $string['invalidconfig']]);
    die();
}

$php_min_ver = $configObject->getxml('php', 'min_version');
$phpversion = requirements::check_php_version();
$phpext = requirements::check_php_extensions();
$phpallext = true;
foreach ($phpext as $idx => $val) {
    if (!$val) {
        $phpallext = false;
    }
}

// Lang packs.
if ($langpackfound === 1) {
    $info['langpacks'] = array($string['langpacksfound'], true);
} elseif ($langpackfound === 2) {
    $info['langpacks'] = array(sprintf($string['langpacksmissing'], $language), 'warn');
}
// php version.
if (!$phpversion) {
    $info['phpversion'] = array(sprintf($string['phpversion'], $php_min_ver), false);
} else {
    $info['phpversion'] = array($string['phpsuccess'],true);
}
// db version.
if (InstallUtils::config_exists()) {
    $mysql_min_ver = $configObject->getxml('database', 'mysql', 'min_version');
    try {
        $dbversion = requirements::check_db($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'), $configObject->get('cfg_db_port'));
        if (!$dbversion) {
            $info['dbversion'] = array(sprintf($string['dbversion'], $mysql_min_ver), false);
        } else {
            $info['dbversion'] = array($string['dbsuccess'], true);
        }
    } catch (Exception $e) {
        $dbversion = false;
        $info['dbversion'] = array(sprintf($string['dbconnection'], $e->getMessage()), false);
    }
} else {
    // On install skip check here as done in insall process.
    $dbversion = true;
}
// php extensions.
foreach ($phpext as $idx => $val) {
    if ($val === false) {
        $blurb = sprintf($string['phpextension'], $idx);
        $info[$idx] = array($blurb, false);
    } elseif ($val === true) {
        $blurb = sprintf($string['phpextensionsuccess'], $idx);
        $info[$idx] = array($blurb, true);
    } else {
        $blurb = sprintf($string['phpextensionwarn'], $idx);
        $info[$idx] = array($blurb, 'warn');
    }
}

$html = <<<HTML
  <div class="requirements-header">
    <div class="requirements-body-item">Requirement</div>
    <div class="requirements-body-item">Passed?</div>
  </div>
HTML;
echo $html;
foreach ($info as $idx => $val) {
    echo "<div class=\"requirements-body\"><div class=\"requirements-body-item\">$val[0]</div><div class=\"requirements-body-item\">";
    if ($val[1] === true) {
        echo '<img src="../artwork/tick.png" id="yes" /></div>';
    } elseif ($val[1] === false) {
        echo '<img src="../artwork/cross.png" id="no" /></div>';
    } else {
        echo '<img src="../artwork/exclamation.png" id="warn" /></div>';
    }
    echo '</div>';
}
echo '<div id="action" class="requirements-body">';
if ($phpversion and $phpallext and $dbversion) {
    if (InstallUtils::config_exists()) {
        echo '<button id="update" class="updatebutton">Update</button>';
    } else {
        echo '<button id="install" class="updatebutton">Install</button>';
    }
} else {
    echo '<p>' . $string['help'] . '</p>';
}
echo '</div>';
