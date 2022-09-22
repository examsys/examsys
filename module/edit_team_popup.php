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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

$moduleID = check_var('module', 'GET', true, false, true);

$module_details = module_utils::get_full_details_by_ID($moduleID, $mysqli);

if (!$module_details) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

if (!$userObject->has_role(array('SysAdmin', 'Admin'))) {
    if ($module_details['add_team_members'] == 0) {
        $contactemail = support::get_email();
        $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
        $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
    }
}

?>

<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset'); ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['teammembers'] . ' ' . $module_details['moduleid']); ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/teams.css" />
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/teamsinit.min.js"></script>
</head>
<body>
<form id="teamform" name="teamform" action="" method="post" autocomplete="off">

  <table cellpadding="0" cellspacing="0" border="0" width="100%" class="header">
  <tr><td style="width:66px; height:55px; background-color:white; border-bottom:1px solid #CCD9EA; text-align:center"><img src="../artwork/user_accounts_icon.png" width="48" height="48 alt="Members" /></td><td class="dkblue_header" style="background-color:white; font-size:150%; border-bottom:1px solid #CCD9EA"><strong><?php echo $string['teammembers']; ?> </strong><?php echo $module_details['moduleid']; ?></td></tr>
  </table>

<?php
  $team_members = UserUtils::get_staff_modules_list_by_modID($_GET['module'], $mysqli);

  echo '<div class="content" id="list">';
  $staff_no = 0;
  $old_letter = '';

  $tmp_role = 'Staff';

  $sql = "
    SELECT DISTINCT 
        u.id, u.surname, u.initials, u.first_names, u.title 
    FROM 
        users u
        JOIN user_roles ur ON u.id = ur.userid
        JOIN roles r ON r.id = ur.roleid
    WHERE 
        u.surname != '' AND grade != 'left' AND u.user_deleted IS NULL 
        AND r.name = ?
    ORDER BY
        u.surname, u.initials
  ";
  $result = $mysqli->prepare($sql);
  $result->bind_param('s', $tmp_role);
  $result->execute();
  $result->store_result();
  $result->bind_result($tmp_id, $tmp_surname, $tmp_initials, $tmp_first_names, $tmp_title);
while ($result->fetch()) {
    if ($old_letter != mb_strtoupper(mb_substr($tmp_surname, 0, 1))) {
        echo '<div class="subsect_table"><div class="subsect_title"><nobr>' . mb_strtoupper(mb_substr($tmp_surname, 0, 1)) . '</nobr></div><div class="subsect_hr"><hr noshade="noshade" /></div></div>';
    }

    $match = false;
    foreach ($team_members as $member) {
        if ($member == $tmp_id) {
            $match = true;
        }
    }

    if ($match == true) {
        echo "<div class=\"r2\" id=\"divstaff$staff_no\"><input type=\"checkbox\" name=\"staff$staff_no\" id=\"staff$staff_no\" value=\"" . $tmp_id . '" checked="checked" />';
    } else {
        echo "<div class=\"r1\" id=\"divstaff$staff_no\"><input type=\"checkbox\" name=\"staff$staff_no\" id=\"staff$staff_no\" value=\"" . $tmp_id . '" />';
    }
    echo "<label for=\"staff$staff_no\">";
    if ($tmp_first_names != '') {
        $display_text = $tmp_first_names;
    } else {
        $display_text = $tmp_initials;
    }
    echo $tmp_surname . '<span class="g">, ' . $display_text . '. ' . $tmp_title . "</span></label></div>\n";
    $old_letter = mb_strtoupper(mb_substr($tmp_surname, 0, 1));
    $staff_no++;
}
  $result->close();
  echo "<input type=\"hidden\" name=\"staff_no\" value=\"$staff_no\" /></div></td>\n</tr>\n";
  echo "<input type=\"hidden\" id=\"moduleID\" name=\"moduleID\" value=\"$moduleID\" /></div></td>\n</tr>\n";
?>

<div class="footer" style="text-align:center"><input class="ok" type="submit" name="submit" value="<?php echo $string['ok']; ?>" /><input class="cancel" type="submit" name="cancel" value="<?php echo $string['cancel']; ?>" /></div>

</form>
</body>
</html>
<?php
  // JS utils dataset.
  $render = new render($configObject);
  $jsdataset['name'] = 'jsutils';
  $jsdataset['attributes']['xls'] = json_encode($string);
  $render->render($jsdataset, array(), 'dataset.html');
  $dataset['name'] = 'dataset';
  $dataset['attributes']['posturl'] = 'do_edit_team.php';
  $render->render($dataset, array(), 'dataset.html');
  $mysqli->close();

