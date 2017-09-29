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
* Admin screen to add external systems.
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

require '../../include/sysadmin_auth.inc';
require_once '../../include/errors.php';
require '../../include/toprightmenu.inc';

$external = new \external_systems();

if (isset($_POST['submit'])) {
    $name = param::required('name', param::ALPHANUM, param::FETCH_POST);   
    $external->insert_external_system($name, \external_systems::API);
    header("location: list_extsys.php", true, 303);
    exit();
}

$render = new render($configObject);
$toprightmenu = draw_toprightmenu(741);
$lang['title'] = $string['addextsys'];
$lang['view'] = $string['addextsys'];
$lang['delete'] = $string['deleteextsys'];
$additionaljs = "
    <script type=\"text/javascript\" src=\"../../js/jquery.validate.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/jquery-ui-1.10.4.min.js\"></script>
    <script type=\"text/javascript\" src=\"../../js/system_tooltips.js\"></script>
    <script type=\"text/javascript\" src=\"js/extsys.min.js\"></script>
    <script type=\"text/javascript\" src=\"js/extsys_validate.min.js\"></script>";
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
$breadcrumb = array($string['home'] => "../../index.php", $string['administrativetools'] => "../index.php", $string['listextsys'] => "list_extsys.php");
$action = $_SERVER['PHP_SELF'];
$render->render_admin_header($lang, $additionaljs, $addtionalcss);
$render->render_admin_options('add_extsys.php', 'sync_16.png', $lang, $toprightmenu, 'admin/options_list.html');
$render->render_admin_content($breadcrumb, $lang);
?>

<br />
<div align="center">
    <form id="theform" name="add_session" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
        <table cellpadding="0" cellspacing="2" border="0">
        <tr><td class="field"><?php echo $string['name'] ?></td><td><input type="text" size="80" maxlength="80" id="name" name="name" value="" required /></td></tr>
        </table>
      <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
    </form>
</div>

<?php
    $render->render_admin_footer();
?>
