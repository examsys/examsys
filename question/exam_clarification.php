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
 * @copyright Copyright (c) 2013 The University of Nottingham
 * @package
 */

require '../include/admin_auth.inc';
require_once '../include/errors.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);

// Check the paperID exists
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

$clarif_types = $configObject->get_setting('core', 'summative_midexam_clarification');
if ($properties->get_paper_type() == '2' and $userObject->has_role(array('SysAdmin', 'Admin')) and $properties->is_live() and $properties->get_bidirectional() == '1' and count($clarif_types) > 0) {
    $exam_clarifications = true;
} else {
    $exam_clarifications = false;
}

// Check the paper is not set to be linear.
// Check if paper is Summative Exam.
// Check if paper is not live.
if (!$exam_clarifications) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Check that the questionID exists
$q_id = check_var('q_id', 'REQUEST', true, false, true);
if (!QuestionUtils::question_exists($q_id, $mysqli)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
  
$screenNo = check_var('screenNo', 'GET', true, false, true);
$questionNo = check_var('questionNo', 'GET', true, false, true);

$exam_announcements = $exam_announcementObj->get_announcements();

if (isset($exam_announcements[$q_id]['msg'])) {
    $msg = $exam_announcements[$q_id]['msg'];
} else {
    $msg = '';
}
$editor = \plugin_manager::get_plugin_type_enabled('plugin_texteditor');
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title($string['midexamclarification']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/examclarification.css" />
  <script id="rogoconfig" data-lang="<?php echo \LangUtils::getLang($cfg_web_root); ?>" data-root="<?php echo $configObject->get('cfg_root_path'); ?>" data-editor="<?php echo $editor[0]; ?>"></script>
  <script src='../js/require.js'></script>
  <script src='../js/main.min.js'></script>
  <script src="../js/examclarificationinit.min.js"></script>

<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
  $texteditorplugin->display_header();
  $texteditorplugin->get_javascript_config(\plugins\plugins_texteditor::ANNOUNCEMENTS);
?>
</head>

<body>
<form name="myform" id="myform" method="post" action="" autocomplete="off">
<h1 class="dkblue_header"><?php echo sprintf($string['questionscreen'], $questionNo, $screenNo); ?></h1>
<?php $texteditorplugin->get_textarea('msg', 'msg', htmlspecialchars($msg, ENT_NOQUOTES), plugins\plugins_texteditor::TYPE_STANDARD); ?><br />
<div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>"  class="cancel" /></div>
<input type="hidden" name="paperID" value="<?php echo $paperID ?>" />
<input type="hidden" name="q_id" value="<?php echo $q_id ?>" />
<input type="hidden" name="screenNo" value="<?php echo $screenNo ?>" />
<input type="hidden" name="questionNo" value="<?php echo $questionNo ?>" />
</form>

</body>
</html>
