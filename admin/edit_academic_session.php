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
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2015 The University of Nottingham
 */

require '../include/sysadmin_auth.inc';
require_once '../include/errors.php';

$year = check_var('year', 'GET', true, false, true);

$result = $mysqli->prepare('SELECT academic_year, cal_status, stat_status FROM academic_year WHERE calendar_year = ?');
$result->bind_param('i', $year);
$result->execute();
$result->store_result();
$result->bind_result($curr_academic_year, $curr_cal_status, $curr_stat_status);
$result->fetch();
if ($result->num_rows == 0) {
    $result->close();
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$result->close();

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
        <title><?php echo page::title('ExamSys: ' . $string['editacademicsession']); ?></title>
        <link rel="stylesheet" type="text/css" href="../css/body.css" />
        <link rel="stylesheet" type="text/css" href="../css/header.css" />
        <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
        <link rel="stylesheet" type="text/css" href="../css/session.css" />

        <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
        <script src='../js/require.js'></script>
        <script src='../js/main.min.js'></script>
        <script src="../js/sessioninit.min.js"></script>

    </head>
    <body>
<?php
    require '../include/academic_session_options.inc';
    require '../include/toprightmenu.inc';

    echo draw_toprightmenu(740);
?>
        <div id="content">

        <div class="head_title">
          <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
          <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="academic_sessions.php"><?php echo $string['academicsessions'] ?></a></div>
          <div class="page_title"><?php echo $string['editacademicsession'] ?></div>
        </div>

        <br />
            <div align="center">
                <form id="theform" name="edit_session" method="post" action="" autocomplete="off">
                    <table cellpadding="0" cellspacing="2" border="0">
                        <tr><td class="field"><?php echo $string['academicyear'] ?></td><td><input type="text" size="30" maxlength="30" id="academic_year" name="academic_year" value="<?php echo $curr_academic_year ?>" required /></td></tr>
                        <tr><td class="field"><?php echo $string['calstatus'] ?></td><td><input type="checkbox" id="cal_status" name="cal_status" <?php if ($curr_cal_status) {
                            echo ' checked';
                                              } ?> /></td></tr>
                        <tr><td class="field"><?php echo $string['statstatus'] ?></td><td><input type="checkbox" id="stat_status" name="stat_status" <?php if ($curr_stat_status) {
                            echo ' checked';
                                              } ?>  /></td></tr>
                    </table>
                  <p><input type="submit" class="ok" name="submit" value="<?php echo $string['save'] ?>"><input class="cancel" id="cancel" type="button" name="home" value="<?php echo $string['cancel'] ?>" /></p>
                </form>
            </div>
        </div>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['posturl'] = 'do_edit_academic_session.php?year=' . $year;
$render->render($miscdataset, array(), 'dataset.html');
?>
    </body>
</html>
