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
 * List External systems.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2017 onwards The University of Nottingham
 * @package oauth
 */

require '../../include/sysadmin_auth.inc';
require '../../include/toprightmenu.inc';

$external = new \external_systems();
$extsys = $external->get_all_externalsystems_details();
foreach ($extsys as $idx => $details) {
    $ext[$idx] = array($details['name'], $details['type']);
}
$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$lang['title'] = $string['extsys'];
$lang['view'] = $string['addextsys'];
$lang['delete'] = $string['deleteextsys'];
$header = array(array('class' => 'col10', 'style' => 'width:50%', 'value' => $string['name']),
    array('class' => 'col10', 'style' => 'width:50%', 'value' => $string['type']));
$additionaljs ="<script type=\"text/javascript\" src=\"../../js/jquery_tablesorter/jquery.tablesorter.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/list.js\"></script>
    <script type=\"text/javascript\" src=\"js/extsys.min.js\"></script>";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/list.css\"/>";
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php",
    $string['extsys'] => "list_extsys.php",);
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('add_extsys.php', 'sync_16.png', $lang, $toprightmenu, 'admin/options_list.html');
$render->render_admin_content($breadcrumb, $lang);
$render->render_admin_list($ext, $header);
$render->render_admin_footer();