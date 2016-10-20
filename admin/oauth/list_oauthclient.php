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
 * Listing of OAuth Keys.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2015 onwards The University of Nottingham
 * @package oauth
 */

require '../../include/sysadmin_auth.inc';
require '../../include/toprightmenu.inc';

$clients = array();
$result = $mysqli->prepare("SELECT client_id, client_secret, redirect_uri, username FROM oauth_clients o LEFT OUTER JOIN users u ON u.id = o.user_id");
$result->execute();
$result->bind_result($client_id, $client_secret, $redriect_uri, $username);
while ($result->fetch()) {
    $clients[$client_id] = array($username, $client_id, $client_secret, $redriect_uri);
}
$result->close();
$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$lang['title'] = $string['oauthclients'];
$lang['create'] = $string['addoauthclient'];
$lang['view'] = $string['editoauthclient'];
$lang['delete'] = $string['deleteoauthclient'];
$header = array(array('class' => 'col10', 'style' => 'width:20%', 'value' => $string['username']),
array('class' => 'col', 'style' => 'width:20%', 'value' => $string['client']),
array('class' => 'col', 'style' => 'width:20%', 'value' => $string['secret']),
array('class' => 'col', 'style' => 'width:20%', 'value' => $string['uri']));
$additionaljs ="<script type=\"text/javascript\" src=\"../../js/jquery_tablesorter/jquery.tablesorter.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/list.js\"></script>
    <script type=\"text/javascript\" src=\"js/oauthclients.min.js\"></script>";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/list.css\"/>";
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php",
 $string['oauthkeys'] => "list_oauth.php");
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('add_oauthclient.php', 'lti_key_16.png', $lang, $toprightmenu, 'admin/options.html');
$render->render_admin_content($breadcrumb, $lang);
$render->render_admin_list($clients, $header);
$render->render_admin_footer();
                     