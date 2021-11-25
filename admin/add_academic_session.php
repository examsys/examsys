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

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
        <title><?php echo page::title('ExamSys: ' . $string['addacademicsession']); ?></title>
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
          <div class="page_title"><?php echo $string['addacademicsession'] ?></div>
        </div>

        <br />
            <div align="center">
                <form id="theform" name="add_session" method="post" action="" autocomplete="off">
                    <div class="form-error"><?php echo $string['duplicateerror'] ?></div>
                    <table cellpadding="0" cellspacing="2" border="0">
                        <tr><td class="field"><?php echo $string['calendaryear'] ?> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['calendaryear_tt'] ?>" /></td><td><input type="text" size="4" maxlength="4" id="calendar_year" name="calendar_year" value="" required /></td></tr>
                        <tr><td class="field"><?php echo $string['academicyear'] ?> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['academicyear_tt'] ?>" /></td><td><input type="text" size="30" maxlength="30" id="academic_year" name="academic_year" value="" required /></td></tr>
                        <tr><td class="field"><?php echo $string['calstatus'] ?> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['calendarenabled_tt'] ?>" /></td><td><input type="checkbox" id="cal_status" name="cal_status" /></td></tr>
                        <tr><td class="field"><?php echo $string['statstatus'] ?> <img src="../artwork/tooltip_icon.gif" class="help_tip" title="<?php echo $string['statenabled_tt'] ?>" /></td><td><input type="checkbox" id="stat_status" name="stat_status" /></td></tr>
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
$miscdataset['attributes']['posturl'] = 'do_add_academic_session.php';
$render->render($miscdataset, array(), 'dataset.html');
?>
    </body>
</html>
