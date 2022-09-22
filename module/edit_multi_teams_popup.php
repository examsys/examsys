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

require_once '../include/admin_auth.inc';
require_once '../include/errors.php';

$userID = check_var('userID', 'REQUEST', true, false, true);
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
<title><?php echo page::title('ExamSys: ' . $string['manageteams']); ?></title>
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

  <table cellpadding="6" cellspacing="0" border="0" width="100%" class="header">
  <tr><td style="width:48px"><img src="../artwork/user_accounts_icon.png" width="48" height="48" alt="Members" /></td><td class="dkblue_header" style="font-size:150%"><strong><?php echo $string['teams']; ?></strong></td></tr>
  </table>

<?php
  $user_teams = array();
  $user_modules = UserUtils::getStaffModules($userID);

  $old_school = '';
  $mod_no = 0;
  echo '<div class="content" id="list">';
  
  $result = $mysqli->prepare('SELECT school, moduleid, fullname, modules.id FROM modules, schools WHERE modules.schoolid = schools.id AND active = 1 AND mod_deleted IS NULL ORDER BY school, moduleid');
  $result->execute();
  $result->bind_result($school, $moduleid, $fullname, $idMod);
while ($result->fetch()) {
    if ($old_school != $school) {
        echo "<div class=\"subsect_table\"><div class=\"subsect_title\"><nobr>$school</nobr></div><div class=\"subsect_hr\"><hr noshade=\"noshade\"/></div></div>\n";
    }
   
    if (isset($user_modules[$idMod])) {
        echo "<div class=\"r2\" id=\"divmod$mod_no\"><input type=\"checkbox\" name=\"mod$mod_no\" id=\"mod$mod_no\" value=\"$idMod\" checked />";
    } else {
        echo "<div class=\"r1\" id=\"divmod$mod_no\"><input type=\"checkbox\" name=\"mod$mod_no\" id=\"mod$mod_no\" value=\"$idMod\" />";
    }
    echo "<label for=\"mod$mod_no\">$moduleid: $fullname</label></div>\n";
    $old_school = $school;
    $mod_no++;
}
  $result->close();
  echo "<input type=\"hidden\" name=\"module_no\" value=\"$mod_no\" /><input type=\"hidden\" id=\"userID\" name=\"userID\" value=\"" . $userID . "\" /></div></td>\n</tr>\n</table>\n";
?>

<div class="footer" align="center"><input class="ok" type="submit" name="submit" value="<?php echo $string['ok'] ?>" /><input class="cancel" type="submit" name="cancel" value="<?php echo $string['cancel'] ?>" /></div>

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
  $dataset['attributes']['posturl'] = 'do_edit_multi_team.php';
  $render->render($dataset, array(), 'dataset.html');
  $mysqli->close();
