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
 * Edit a students modules
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/admin_auth.inc';
require_once '../include/errors.php';

$userID = check_var('userID', 'REQUEST', true, false, true);
$student_id = check_var('student_id', 'REQUEST', false, false, true);
$search_surname = check_var('search_surname', 'REQUEST', false, false, true);
$search_username = check_var('search_username', 'REQUEST', false, false, true);

if (!UserUtils::userid_exists($userID, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

function drawTabs($current_tab, $string, $session)
{
    $html = '<table cellpadding="0" cellspacing="0" border="0" style="width:100%; font-size:100%; background-color:#F1F5FB"><tr><td style="width:264px"><strong>' . $string['modulesfor'] . ' ' . $session . ':</strong></td>';
    for ($i = 1; $i <= 3; $i++) {
        if ($i == $current_tab) {
            $html .= "<td class=\"tabon\" data-tabid=\"list$i\">" . $string[$i] . '</td>';
        } else {
            $html .= "<td class=\"taboff\" data-tabid=\"list$i\">" . $string[$i] . '</td>';
        }
    }
    $html .= "</tr></table>\n";
    return $html;
}

function list_modules($mod, $id, $student_mod, $string, $session)
{
    $old_letter = '';

    if ($id == '1') {
        echo "<div class=\"content\" style=\"display:block\" id=\"list$id\">";
    } else {
        echo "<div class=\"content\" style=\"display:none\" id=\"list$id\">";
    }

    echo drawTabs($id, $string, $session);

    if ($id == '1') {
        echo "<div style=\"width:100%; height:100%; overflow-y:scroll; border:1px solid #95AEC8; font-size:90%\" id=\"list$id\">";
    } else {
        echo "<div style=\"width:100%; height:100%; overflow-y:scroll; border:1px solid #95AEC8; font-size:90%\" id=\"list$id\">";
    }

    $loop = 0;
    foreach ($mod as $idMod => $mod_info) {
        $moduleid = $mod_info['moduleid'];
        $fullname = $mod_info['fullname'];

        if ($old_letter != mb_strtoupper(mb_substr($moduleid, 0, 1))) {
            echo '<table border="0" style="padding-bottom:5px; width:100%; color:#1E3287"><tr><td><nobr>&nbsp;' . mb_strtoupper(mb_substr($moduleid, 0, 1)) . "</nobr></td><td style=\"width:98%\"><hr noshade=\"noshade\" style=\"border:0px; height:1px; color:#E5E5E5; background-color:#E5E5E5; width:100%\" /></td></tr></table>\n";
        }

        if (isset($student_mod[$idMod]) and $student_mod[$idMod]['attempt'] == $id) {
            echo '<div class="r2" id="divmod' . $id . '_' . $loop . '"><input type="checkbox" name="mod' . $id . '_' . $loop . '" id="mod' . $id . '_' . $loop . '" value="' . $idMod . '" checked />&nbsp;<label for="mod' . $id . '_' . $loop . "\">$moduleid:&nbsp;$fullname</label></div>\n";
        } else {
            echo '<div class="r1" id="divmod' . $id . '_' . $loop . '"><input type="checkbox" name="mod' . $id . '_' . $loop . '" id="mod' . $id . '_' . $loop . '" value="' . $idMod . '" />&nbsp;<label for="mod' . $id . '_' . $loop . "\">$moduleid:&nbsp;$fullname</label></div>\n";
        }
        $loop++;
        $old_letter = mb_strtoupper(mb_substr($moduleid, 0, 1));
    }
    echo "</div>\n</div>\n";
}

$yearutils = new yearutils($mysqli);
$session = check_var('session', 'GET', false, false, true);
if (empty($session)) {
    $session = $yearutils->get_current_session();
}
$display_year = $yearutils->get_academic_session($session);
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>ExamSys: <?php echo $display_year; ?> Modules</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/tabs.css" />
  <link rel="stylesheet" type="text/css" href="../css/usermodules.css" />

  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/usermodulesinit.min.js"></script>
</head>
<body>
<form id="teamform" name="teamform" action="" method="post" autocomplete="off">

<?php
  // Get existing modules for the user in passed calendar year.
  $student_modules = array();
  $result = $mysqli->prepare('SELECT idMod, moduleid, attempt FROM modules_student, modules WHERE modules_student.idMod = modules.id AND userID = ? AND calendar_year = ?');
  $result->bind_param('is', $userID, $session);
  $result->execute();
  $result->bind_result($idMod, $moduleid, $attempt);
while ($result->fetch()) {
    $student_modules[$idMod]['moduleid'] = $moduleid;
    $student_modules[$idMod]['attempt'] = $attempt;
}
  $result->close();

  $module_no = 0;
  $old_year = '';
  $modules = array();
  $mod_count = 0;

  // Get a list of all modules for display.
  $result = $mysqli->prepare('SELECT modules.id, moduleid, fullname FROM modules, schools WHERE modules.schoolid = schools.id AND active = 1 AND deleted IS NULL AND mod_deleted IS NULL ORDER BY moduleid');
  $result->execute();
  $result->store_result();
  $result->bind_result($idMod, $moduleid, $fullname);
while ($result->fetch()) {
    $modules[$idMod]['moduleid'] = $moduleid;
    $modules[$idMod]['fullname'] = $fullname;
    $mod_count++;
}
  $result->close();

if ($mod_count == 0) {
    echo '<div style="color:#C00000">&nbsp;<img src="../artwork/small_yellow_warning_icon.gif" width="12" height="11" alt="Warning" />&nbsp;' . $string['nomodules'] . ' <strong>' . $display_year . '</strong>.</div>';
} else {
    list_modules($modules, 1, $student_modules, $string, $display_year);
    list_modules($modules, 2, $student_modules, $string, $display_year);
    list_modules($modules, 3, $student_modules, $string, $display_year);
}

  echo "<input type=\"hidden\" name=\"mod_count\" value=\"$mod_count\" /></div></td>\n</tr>\n";
  echo "<input type=\"hidden\" id=\"userID\"  name=\"userID\" value=\"$userID\" /></div></td>\n</tr>\n";
  echo "<input type=\"hidden\" name=\"session\" value=\"$session\" /></div></td>\n</tr>\n";
  echo "<input type=\"hidden\" id=\"student_id\" name=\"student_id\" value=\"$student_id\" /></div></td>\n</tr>\n";
  echo "<input type=\"hidden\" id=\"search_surname\" name=\"search_surname\" value=\"$search_surname\" /></div></td>\n</tr>\n";
  echo "<input type=\"hidden\" id=\"search_username\" name=\"search_username\" value=\"$search_username\" /></div></td>\n</tr>\n";
?>

  <div class="footer" align="center"><input class="ok" type="submit" name="submit" value="<?php echo $string['ok'] ?>" /><input class="cancel" type="submit" name="cancel" value="<?php echo $string['cancel'] ?>" onclick="window.close()" /></div>

</form>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
?>
</body>
</html>
<?php
  $mysqli->close();
?>
