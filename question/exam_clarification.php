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

$clarif_types = $configObject->get('midexam_clarification');
if ($properties->get_paper_type() == '2' and $userObject->has_role(array('SysAdmin', 'Admin')) and $properties->is_live() and $properties->get_bidirectional() == '1' and count($clarif_types) > 0) {
  $exam_clarifications = true;  
} else {
  $exam_clarifications = false;  
}

// Check the paper is not set to be linear.
// Check if paper is Summative Exam.
// Check if paper is not live.
if (!$exam_clarifications) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

// Check that the questionID exists
$q_id = check_var('q_id', 'REQUEST', true, false, true);
if (!QuestionUtils::question_exists($q_id, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$exam_announcementObj = new ExamAnnouncements($paperID, $mysqli, $string);
  
if (isset($_POST['submit'])) {
  $screenNo = check_var('screenNo', 'POST', true, false, true);
  $questionNo = check_var('questionNo', 'POST', true, false, true);
  $msg = check_var('msg', 'POST', true, false, true);

  $exam_announcementObj->replace_announcement($q_id, $questionNo, $screenNo, $msg);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['midexamclarification'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      opener.location.reload();
      window.close();
    });
  </script>
</head>
<body>
</body>
</html>
exit();
<?php
} else {
  $screenNo = check_var('screenNo', 'GET', true, false, true);
  $questionNo = check_var('questionNo', 'GET', true, false, true);

  $exam_announcements = $exam_announcementObj->get_announcements();
  
  if (isset($exam_announcements[$q_id]['msg'])) {
    $msg = $exam_announcements[$q_id]['msg'];
  } else {
    $msg = '';
  }
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['midexamclarification'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:80%; text-align:center; margin:2px}
    h1 {text-align:left; font-size:150%; margin-left:4px; font-weight:normal}
  </style>
  
  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="../tools/mee/mee/js/mee_src.js"></script>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_config_announcements.js"></script>
  <script>
    $(function () {
      var new_height = $(window).height() - 105;
      $('#msg').height(new_height);
      
      $('form').submit(function() {
        tinyMCE.triggerSave();
        if ($('#msg').val() == '') {
          $('.defaultSkin table.mceLayout').css('border-color', '#C00000');
          $('.defaultSkin table.mceLayout').css('box-shadow', '0 0 6px rgba(200, 0, 0, 0.85)');
          $('.defaultSkin table.mceLayout tr.mceFirst td').css('border-top-color', '#C00000');
          $('.defaultSkin table.mceLayout tr.mceLast td').css('border-bottom-color', '#C00000');
          return false;
        }
      });
    });
  </script>
</head>

<body>
<form name="myform" id="myform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">
<h1 class="dkblue_header"><?php echo sprintf($string['questionscreen'], $questionNo, $screenNo); ?></h1>
<textarea class="mceEditor" id="msg" name="msg" cols="80" rows="4" style="width:100%; height:340px"><?php echo htmlspecialchars($msg, ENT_NOQUOTES); ?></textarea><br />
<div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['save']; ?>" class="ok" /><input type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="window.close()" class="cancel" /></div>
<input type="hidden" name="paperID" value="<?php echo $paperID ?>" />
<input type="hidden" name="q_id" value="<?php echo $q_id ?>" />
<input type="hidden" name="screenNo" value="<?php echo $screenNo ?>" />
<input type="hidden" name="questionNo" value="<?php echo $questionNo ?>" />
</form>

</body>
</html>
<?php
}
?>
