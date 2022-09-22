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
 * Initial screen of the create new paper dialog box.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/staff_auth.inc';
$paper_name = param::optional('paper_name', '', param::TEXT, param::FETCH_GET);
$paper_type = param::optional('paper_type', '', param::TEXT, param::FETCH_GET);
$paper_types = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('ExamSys: ' . $string['createnewpaper']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/new_paper.css" />
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/newpaperinit.min.js"></script>

</head>

<body>
<form id="theform" name="theform" action="new_paper2.php" method="post" autocomplete="off">
<div style="text-align:center; border:solid 1px #295AAD; background-color:white">
<table cellpadding="0" cellspacing="0" border="0" style="background-color:white; width:100%">
<tr>
<td colspan="8" class="titlebar" style="text-align:left">&nbsp;<?php echo $string['papertype']; ?></td>
</tr>
<tr>
<td class="icon" id="formative"><img src="../artwork/formative.png" width="48" height="48" alt="<?php echo $string['formative self-assessment']; ?>" /><br /><?php echo $string['formative self-assessment']; ?></td>
<td class="icon" id="progress"><img src="../artwork/progress.png" width="48" height="48" alt="<?php echo $string['progress test']; ?>" /><br /><?php echo $string['progress test']; ?></td>
<?php
if (!$configObject->get_setting('core', 'summative_hide_external')) {
    ?>
<td class="icon" id="summative"><img src="../artwork/summative.png" width="48" height="48" alt="<?php echo $string['summative exam']; ?>" /><br /><?php echo $string['summative exam']; ?></td>
    <?php
}
?>
<td class="icon" id="survey"><img src="../artwork/survey.png" width="48" height="48" alt="<?php echo $string['survey']; ?>" /><br /><?php echo $string['survey']; ?></td>
<td class="icon" id="osce"><img src="../artwork/osce.png" width="48" height="48" alt="<?php echo $string['osce station']; ?>" /><br /><?php echo $string['osce station']; ?></td>
<td class="icon" id="offline"><img src="../artwork/offline.png" width="48" height="48" alt="<?php echo $string['offline paper']; ?>" /><br /><?php echo $string['offline paper']; ?></td>
<td class="icon" id="peer_review"><img src="../artwork/peer_review.png" width="48" height="48" alt="<?php echo $string['peer review']; ?>" /><br /><?php echo $string['peer review']; ?></td>
<td>&nbsp;</td>
</tr>
<tr>
<td colspan="8" style="text-align:left; padding-top:10px; padding-left:4px; padding-right:4px; padding-bottom:6px; font-size:90%; color:black" id="description">&nbsp;</td>
</tr>
</table>
</div>
<br />
<div id="warning" data-name="<?php echo $paper_name; ?>">
<?php
if ($paper_name != '') {
    echo sprintf($string['msg5'], $paper_name);
}
?>
</div>
<?php echo $string['name']; ?> <input type="text" id="paper_name" name="paper_name" value="<?php echo $paper_name; ?>" maxlength="200" style="width:650px" required />
<input type="hidden" name="module" value="<?php if (isset($_GET['module'])) {
    echo $_GET['module'];
                                          } ?>" />
<?php
if (isset($_GET['module'])) {
    $module_details = module_utils::get_full_details_by_ID($_GET['module'], $mysqli);
    $yearutils = new yearutils($mysqli);
    $default_academic_year = $yearutils->get_current_session($module_details['academic_year_start']);
} else {
    $default_academic_year = $configObject->get_setting('core', 'system_academic_year_start');
}
?>
<input type="hidden" name="default_academic_year" value="<?php echo $default_academic_year ?>" />
<input type="hidden" name="folder" value="<?php if (isset($_GET['folder'])) {
    echo $_GET['folder'];
                                          } ?>" />
<input type="hidden" id="paper_type" name="paper_type" value="<?php echo $paper_type; ?>" />
<br />
<br />
<div style="text-align:right"><input type="button" id="cancel" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" style="margin-right:8px" /><input type="submit" name="submit" value="<?php echo $string['next']; ?>" class="ok"/></div>
</form>
</body>
<?php
// JS utils dataset.
$render = new render($configObject);
$jsdataset['name'] = 'jsutils';
$jsdataset['attributes']['xls'] = json_encode($string);
$render->render($jsdataset, array(), 'dataset.html');
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['warn'] = $configObject->get_setting('core', 'summative_warn_external');
if (isset($_GET['type'])) {
    $miscdataset['attributes']['type'] = $paper_types[$_GET['type']];
}
$render->render($miscdataset, array(), 'dataset.html');
?>
</html>
