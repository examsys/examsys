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

require '../include/sysadmin_auth.inc';
require '../include/errors.php';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html; charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>ExamSys: <?php echo $string['addannouncement']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/announcement.css" />
    <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
    <script src='../js/require.js'></script>
    <script src='../js/main.min.js'></script>
    <script src="../js/announcementinit.min.js"></script>
<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
$texteditorplugin->display_header();
$texteditorplugin->get_javascript_config(\plugins\plugins_texteditor::ANNOUNCEMENTS);
?>
</head>

<body>
<?php
  require '../include/announcement_options.inc';
require '../include/toprightmenu.inc';
echo draw_toprightmenu();
?>
<div id="content">
<div class="head_title">
  <div><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" /></div>
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home'] ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['addannouncement'] ?></div>
</div>


<form id="theform" name="myform" action="" method="post" autocomplete="off">

<table class="tblform">
  <tr>
    <td></td>
    <td>
      <img class="icon" id="icon1" src="../artwork/news_64.png" style="border-color:#FFBD69" />
      <img class="icon" id="icon2" src="../artwork/new_64.png" />
      <img class="icon" id="icon3" src="../artwork/tip_64.png" />
      <img class="icon" id="icon4" src="../artwork/software_64.png" />
      <img class="icon" id="icon5" src="../artwork/exclamation_64.png" />
      <img class="icon" id="icon6" src="../artwork/sync_64.png" />
      <img class="icon" id="icon7" src="../artwork/megaphone_64.png" />
    </td>
  </tr>
  <tr>
    <td class="field"><?php echo $string['Title']; ?></td>
    <td><input type="text" name="title" size="60" maxlength="255" required /></td>
  </tr>
  <tr>
    <td class="field"><?php echo $string['Available from']; ?></td>
    <td><?php echo date_utils::timedate_select('f', date('YmdH00'), false, date('Y'), date('Y') + 2, $string); ?></td>
  </tr>
  <tr>
    <td class="field"><?php echo $string['Available to']; ?></td>
    <td><?php echo date_utils::timedate_select('t', date('YmdH00'), false, date('Y'), date('Y') + 2, $string); ?></td>
  </tr>
  <tr>
    <td class="field"><?php echo $string['Staff Message']; ?></td>
    <td><?php $texteditorplugin->get_textarea('staff_msg', 'staff_msg', '', plugins\plugins_texteditor::TYPE_STANDARD); ?></td>
  </tr>
  <tr>
    <td class="field"><?php echo $string['Student Message']; ?></td>
    <td><?php $texteditorplugin->get_textarea('student_msg', 'student_msg', '', plugins\plugins_texteditor::TYPE_STANDARD); ?></td>
  </tr>
  <tr>
    <td colspan="2" style="text-align:center; padding-top:10px"><input type="submit" name="ok" value="<?php echo $string['ok'] ?>" class="ok" /><input type="button" name="cancel" id="cancel" value="<?php echo $string['cancel'] ?>" class="cancel" /></td>
  </tr>
</table>

<input type="hidden" id="icon_type" name="icon_type" value="icon1" />

</form>
</div>
<?php
// JS utils dataset.
$render = new render($configObject);
$miscdataset['name'] = 'dataset';
$miscdataset['attributes']['posturl'] = 'do_add_announcement.php';
$render->render($miscdataset, array(), 'dataset.html');
?>
</body>
</html>
