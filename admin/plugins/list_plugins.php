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
 * Listing of available plugins.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package oauth
 */

require '../../include/sysadmin_auth.inc';
require '../../include/toprightmenu.inc';

// Get all enabled plugins.
$enabledplugins = plugin_manager::get_all_enabled_plugins();
// Get all plugins.
$pluginslist = plugin_manager::listplugins();
$pluginstatus = array();
foreach ($pluginslist as $plugin => $pluginns) {
    if (plugin_manager::plugin_installed($plugin)) {
        // Set plugin status array.
        if (in_array($plugin, $enabledplugins)) {
            $pluginstatus[$plugin] = array($plugin, "true");
        } else {
            $pluginstatus[$plugin] = array($plugin, "false");
        }
    }
}

$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$lang['title'] = $string['rogoplugins'];
$lang['view'] = $string['editplugins'];
$lang['link'] = $string['addpluginlink'];
$header = array(array('class' => 'col10', 'style' => 'width:80%', 'value' => $string['plugins']),
array('class' => 'col', 'style' => 'width:20%', 'value' => $string['enabled']));
$additionaljs = "<script type=\"text/javascript\" src=\"../../js/jquery_tablesorter/jquery.tablesorter.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/list.js\"></script>
    <script type=\"text/javascript\" src=\"js/plugins.min.js\"></script>";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/list.css\"/>";
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php");
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('../../plugins/index.php', 'plugins_16.png', $lang, $toprightmenu, 'admin/options_link.html');
$render->render_admin_content($breadcrumb, $lang);
$render->render_admin_list($pluginstatus, $header);
$render->render_admin_footer();
                     