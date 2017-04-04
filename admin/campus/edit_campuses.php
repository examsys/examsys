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
* Admin screen to edit a campus
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2016 onwards The University of Nottingham
*/

require '../../include/sysadmin_auth.inc';
require_once '../../include/errors.php';

$campus = check_var('campus', 'REQUEST', true, false, true);
$campusobj = new campus($mysqli);
$details = $campusobj->get_campus_details($campus);

if ($details === false) {
    $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
    $title = $string['pagenotfound'];
    $notice->display_notice_and_exit($mysqli, $title, $msg, $title, '../artwork/page_not_found.png', '#C00000', true, true);
}

require '../../include/toprightmenu.inc';

if (isset($_POST['submit'])) {
    $name = check_var('name', 'POST', true, false, true);
    $duplicate = $campusobj->check_campus_name_inuse($name);
    if (!$duplicate or $name == $details['campusname']) {
        if ($details['isdefault'] or $_POST['defaultchk']) {

            $params['name'] = array('s', $name);
            $params['isdefault'] = array('i', 1);
            DBUtils::exec_db_update('campus', 'id', $params, $campus, $mysqli);
            
            $update = $mysqli->prepare("UPDATE campus SET isdefault = 0 WHERE id != ?");
            $update->bind_param("i", $campus);
            $update->execute();
            $update->close();

        } else {
            $params['name'] = array('s', $name);
            $params['isdefault'] = array('i', 0);
            DBUtils::exec_db_update('campus', 'id', $params, $campus, $mysqli);
        }
        header("location: list_campuses.php", true, 303);
        exit();
    }
}

$render = new render($configObject);
$toprightmenu = draw_toprightmenu(744);
$lang['title'] = $string['editcampus'];
$lang['create'] = $string['createnewcampus'];
$lang['view'] = $string['viewcampus'];
$lang['delete'] = $string['deletecampus'];
$additionaljs = "<script type=\"text/javascript\" src=\"../../js/jquery.validate.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/jquery-ui-1.10.4.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/system_tooltips.js\"></script>
    <script type=\"text/javascript\" src=\"js/campuses.min.js\"></script>
    <script type=\"text/javascript\" src=\"js/campuses_validate.min.js\"></script>";
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
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php", $string['computerlabs'] => "../list_labs.php", $string['campuses'] => "list_campuses.php" );
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('add_campuses.php', 'new_campus_16.png', $lang, $toprightmenu);
$render->render_admin_content($breadcrumb, $lang);

?>

<br />
<div align="center">
<?php
    if (isset($_POST['submit']) and $duplicate) {
        echo $notice->info_strip($string['duplicate'], 100);
    }
?>
    <form id="theform" name="add_session" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
        <table cellpadding="0" cellspacing="2" border="0">
        <?php 
            echo "<tr><td class=\"field\">" . $string['name'] . "</td><td><input type=\"text\" size=\"80\" maxlength=\"80\" id=\"name\" name=\"name\" value=\"" . $details['campusname'] . "\" required /></td></tr>";
            if ($details['isdefault']) {
                echo "<tr><td class=\"field\">" . $string['default'] . "</td><td><input type=\"checkbox\" name=\"defaultchk\" checked disabled/></td></tr>";
            } else {
                echo "<tr><td class=\"field\">" . $string['default'] . "</td><td><input type=\"checkbox\" name=\"defaultchk\"/></td></tr>";
            }
            echo "<input type=\"hidden\" name=\"campus\" id=\"campus\" value=\"" . $details['campusid']. "\"/>";
        ?>
        </table>
      <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
    </form>
</div>

<?php
    $render->render_admin_footer();
?>
