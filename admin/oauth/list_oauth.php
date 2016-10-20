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
$oauth = array();
$result = $mysqli->prepare("SELECT client_id, access_token, username, expires, 'access token' as type FROM
    oauth_access_tokens o, users u WHERE u.id = o.user_id
    UNION
    SELECT client_id, refresh_token, username, expires, 'refresh token' as type FROM
    oauth_refresh_tokens o, users u WHERE u.id = o.user_id");
$result->execute();
$result->bind_result($client_id, $access_token, $username, $expires, $type);
while ($result->fetch()) {
    $oauth[$access_token] = array($username, $client_id, $access_token, $type, $expires);
}
$result->close();
$render = new render($configObject);
$toprightmenu = draw_toprightmenu();
$lang['title'] = $string['oauthkeys'];
$lang['view'] = $string['listoauthclient'];
$lang['delete'] = $string['deleteoauthkeys'];
$header = array(array('class' => 'col10', 'style' => 'width:15%', 'value' => $string['username']),
array('class' => 'col', 'style' => 'width:15%', 'value' => $string['client']),
array('class' => 'col', 'style' => 'width:40%', 'value' => $string['token']),
array('class' => 'col', 'style' => 'width:10%', 'value' => $string['type']),
array('class' => 'col', 'style' => 'width:10%', 'value' => $string['expires']));
$additionaljs = "<script type=\"text/javascript\" src=\"../../js/jquery_tablesorter/jquery.tablesorter.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/list.js\"></script>
    <script type=\"text/javascript\" src=\"js/oauth.min.js\"></script>";
$addtionalcss = "<link rel=\"stylesheet\" type=\"text/css\" href=\"../../css/list.css\"/>";
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php");
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('list_oauthclient.php', 'lti_key_16.png', $lang, $toprightmenu, 'admin/options_list.html');
$render->render_admin_content($breadcrumb, $lang);
$render->render_admin_list($oauth, $header);
$render->render_admin_footer();