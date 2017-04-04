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
* Admin screen to edit an oauth client
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2015 onwards The University of Nottingham
*/

require '../../include/sysadmin_auth.inc';
require_once '../../include/errors.php';
require '../../include/toprightmenu.inc';

if (isset($_POST['submit'])) {
    $client = check_var('client', 'POST', true, false, true);
    $secret = check_var('secret', 'POST', true, false, true);
    $uri = check_var('uri', 'POST', true, false, true);
    $userid = check_var('userid', 'POST', true, false, true);
    $oauth = new oauth($configObject);
    $storage = $oauth->get_storage();
    $storage->setClientDetails($client, $secret, $uri, null, null, $userid);
    
    $manage = array('modulemanagement', 'usermanagement', 'assessmentmanagement',
            'coursemanagement', 'schoolmanagement', 'facultymanagement');
    foreach ($manage as $management) {
        if (isset($_POST[$management . '/create'])) {
            $oauth->set_permission($management . '/create', $client, true);
        } else {
            $oauth->set_permission($management . '/create', $client, false);
        }
        if (isset($_POST[$management . '/delete'])) {
            $oauth->set_permission($management . '/delete', $client, true);
        } else {
            $oauth->set_permission($management . '/delete', $client, false);
        }
        if (isset($_POST[$management . '/update'])) {
            $oauth->set_permission($management . '/update', $client, true);
        } else {
            $oauth->set_permission($management . '/update', $client, false);
        }
    }
    if (isset($_POST['modulemanagement/enrol'])) {
        $oauth->set_permission('modulemanagement/enrol', $client, true);
    } else {
        $oauth->set_permission('modulemanagement/enrol', $client, false);
    }
    
    if (isset($_POST['modulemanagement/unenrol'])) {
        $oauth->set_permission('modulemanagement/unenrol', $client, true);
    } else {
        $oauth->set_permission('modulemanagement/unenrol', $client, false);
    }
    if (isset($_POST['assessmentmanagement/schedule'])) {
        $oauth->set_permission('assessmentmanagement/schedule', $client, true);
    } else {
        $oauth->set_permission('assessmentmanagement/schedule', $client, false);
    }  
    if (isset($_POST['gradebook'])) {
        $oauth->set_permission('gradebook', $client, true);
    } else {
        $oauth->set_permission('gradebook', $client, false);
    }
    
    header("location: list_oauthclient.php", true, 303);
    exit();
} else {
    $client = check_var('client', 'GET', true, false, true);
    $clients = array();
    $result = $mysqli->prepare("SELECT client_id, client_secret, redirect_uri, user_id FROM oauth_clients WHERE client_id = ?");
    $result->bind_param('s', $client);
    $result->execute();
    $result->bind_result($client_id, $client_secret, $redriect_uri, $user_id);
    while ($result->fetch()) {
        $clients[$client_id] = array($client_secret, $redriect_uri, $user_id);
    }
    $result->close();
    
    $clientperms = array();
    $result = $mysqli->prepare("SELECT p.action, w.access FROM permissions p
        LEFT JOIN webservice_permissions w ON p.action = w.action and w.client_id = ?");
    $result->bind_param('s', $client);
    $result->execute();
    $result->bind_result($action, $access);
    while ($result->fetch()) {
        $clientperms[$action] = $access;
    }
    $result->close();

}

$render = new render($configObject);
$toprightmenu = draw_toprightmenu(741);
$lang['title'] = $string['editoauthclient'];
$lang['create'] = $string['addoauthclient'];
$lang['view'] = $string['editoauthclient'];
$lang['delete'] = $string['deleteoauthclient'];
$additionaljs = "<script type=\"text/javascript\" src=\"../../js/jquery.validate.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/jquery-ui-1.10.4.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/system_tooltips.js\"></script>
    <script type=\"text/javascript\" src=\"js/oauthclients.min.js\"></script>
    <script type=\"text/javascript\" src=\"js/oauthclients_validate.min.js\"></script>";
$addtionalcss = "<style type=\"text/css\">
          td {text-align:left}
          .field {text-align:right; padding-right:10px}
          .form-error {
            width: 468px;
            margin: 18px auto;
            padding: 16px;
            background-color: #FFD9D9;
            color: #800000;
            border: 2px solid #800000;
          }
        </style>";
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php", $string['oauthkeys'] => "list_oauth.php", $string['listoauthclient'] => "list_oauthclient.php" );
$action = $_SERVER['PHP_SELF'];
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('add_oauthclient.php', 'lti_key_16.png', $lang, $toprightmenu, 'admin/options.html');
$render->render_admin_content($breadcrumb, $lang);
?>

<br />
<div align="center">
    <form id="theform" name="add_session" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
        <table cellpadding="0" cellspacing="2" border="0">
        <?php 
            foreach ($clients as $id => $client) {
                echo "<tr><td class=\"field\">" . $string['secret'] . "</td><td><input type=\"text\" size=\"80\" maxlength=\"80\" id=\"secret\" name=\"secret\" value=\"" . $client[0] . "\" required /></td></tr>";
                echo "<tr><td class=\"field\">" . $string['uri'] . "</td><td><input type=\"text\" size=\"80\" maxlength=\"80\" id=\"uri\" name=\"uri\" value=\"" . $client[1] . "\" required /></td></tr>";
                echo "<input type=\"hidden\" name=\"client\" id=\"client\" value=\"" . $id . "\"/>";
                echo "<input type=\"hidden\" name=\"userid\" id=\"userid\" value=\"" . $client[2] . "\"/>";
            }
       
            foreach ($clientperms as $action => $access) {
                if ($access) {
                    echo "<tr><td class=\"field\">" . $string[$action] . "</td><td><input type=\"checkbox\" name=\"" . $action . "\" checked /></td></tr>";
                } else {
                    echo "<tr><td class=\"field\">" . $string[$action] . "</td><td><input type=\"checkbox\" name=\"" . $action . "\"/></td></tr>";
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
