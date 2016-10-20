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
* Initial screen of the create new paper dialog box.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

$paper_types = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['createnewpaper'] . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/new_paper.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  <script>
    $(function () {
      $('#theform').validate({
        errorClass: 'errfield',
        errorPlacement: function(error,element) {
          return true;
        }
      });
      $('form').removeAttr('novalidate');
      
      <?php
      if (isset($_GET['type'])) {
        echo "  activate('" . $paper_types[$_GET['type']] . "')\n";
      }
      ?>
    });

    function over(id) {
      if (id != $('#paper_type').val()) {
				$('#' + id).css('background-color', '#FFE7A2');
      }
      switch (id) {
        case 'formative':
          $('#description').html("<?php echo $string['description0']; ?>");
          break;
        case 'progress':
          $('#description').html("<?php echo $string['description1']; ?>");
          break;
        case 'summative':
          $('#description').html("<?php echo $string['description2']; ?>");
          break;
        case 'survey':
          $('#description').html("<?php echo $string['description3']; ?>");
          break;
        case 'osce':
          $('#description').html("<?php echo $string['description4']; ?>");
          break;
        case 'offline':
          $('#description').html("<?php echo $string['description5']; ?>");
          break;
        case 'peer_review':
          $('#description').html("<?php echo $string['description6']; ?>");
          break;
      }
    }

    function out(id) {
      if (id != $('#paper_type').val()) {
				$('#' + id).css('background-color', 'white');
      }
    }

    function activate(id) {
      $('#formative').css('background-color', 'white');
      $('#progress').css('background-color', 'white');
      $('#summative').css('background-color', 'white');
      $('#survey').css('background-color', 'white');
      $('#osce').css('background-color', 'white');
      $('#offline').css('background-color', 'white');

			$('#' + id).css('background-color', '#FFBD69');
      $('#paper_type').val(id);
    }

    function checkForm() {
      if ($('#paper_type').val() == '') {
        alert("<?php echo $string['msg1']; ?>");
        return false;
      }
      <?php
        if ($configObject->get_setting('core', 'summative_warn_external')) {
        ?>
        if ($('#paper_type').val() == 'summative') {
          return confirm("<?php echo $string['msg3']; ?>");
        }
        <?php
        }
        ?>
    }
  </script>
</head>

<body>
<form id="theform" name="theform" action="new_paper2.php" method="post" onsubmit="return checkForm();" autocomplete="off">
<div style="text-align:center; border:solid 1px #295AAD; background-color:white">
<table cellpadding="0" cellspacing="0" border="0" style="background-color:white; width:100%">
<tr>
<td colspan="8" class="titlebar" style="text-align:left">&nbsp;<?php echo $string['papertype']; ?></td>
</tr>
<tr>
<td class="icon" onclick="activate('formative')" onmouseover="over('formative')" onmouseout="out('formative')" id="formative"><img src="../artwork/formative.png" width="48" height="48" alt="Formative Self-Assessment" /><br /><?php echo $string['formative self-assessment']; ?></td>
<td class="icon" onclick="activate('progress')" onmouseover="over('progress')" onmouseout="out('progress')" id="progress"><img src="../artwork/progress.png" width="48" height="48" alt="Progress Test" /><br /><?php echo $string['progress test']; ?></td>
<?php
if (!$configObject->get_setting('core', 'summative_hide_external')) {
    ?>
<td class="icon" onclick="activate('summative')" onmouseover="over('summative')" onmouseout="out('summative')" id="summative"><img src="../artwork/summative.png" width="48" height="48" alt="Summative Exam" /><br /><?php echo $string['summative exam']; ?></td>
<?php
}
?>
<td class="icon" onclick="activate('survey')" onmouseover="over('survey')" onmouseout="out('survey')" id="survey"><img src="../artwork/survey.png" width="48" height="48" alt="Survey" /><br /><?php echo $string['survey']; ?></td>
<td class="icon" onclick="activate('osce')" onmouseover="over('osce')" onmouseout="out('osce')" id="osce"><img src="../artwork/osce.png" width="48" height="48" alt="OSCE" /><br /><?php echo $string['osce station']; ?></td>
<td class="icon" onclick="activate('offline')" onmouseover="over('offline')" onmouseout="out('offline')" id="offline"><img src="../artwork/offline.png" width="48" height="48" alt="Offline" /><br /><?php echo $string['offline paper']; ?></td>
<td class="icon" onclick="activate('peer_review')" onmouseover="over('peer_review')" onmouseout="out('peer_review')" id="peer_review"><img src="../artwork/peer_review.png" width="48" height="48" alt="Peer Review" /><br /><?php echo $string['peer review']; ?></td>
<td>&nbsp;</td>
</tr>
<tr>
<td colspan="8" style="text-align:left; padding-top:10px; padding-left:4px; padding-right:4px; padding-bottom:6px; font-size:90%; color:black" id="description">&nbsp;</td>
</tr>
</table>
</div>
<br />
<?php echo $string['name']; ?> <input type="text" id="paper_name" name="paper_name" value="" maxlength="200" style="width:650px" required />
<input type="hidden" name="module" value="<?php if (isset($_GET['module'])) echo $_GET['module']; ?>" />
<?php
if (isset($_GET['module'])) {
  $module_details = module_utils::get_full_details_by_ID($_GET['module'], $mysqli);
  $yearutils = new yearutils($mysqli);
  $default_academic_year = $yearutils->get_current_session($module_details['academic_year_start']);
} else {
  $default_academic_year = $configObject->get('cfg_academic_year_start');
}
?>
<input type="hidden" name="default_academic_year" value="<?php echo $default_academic_year ?>" />
<input type="hidden" name="folder" value="<?php if (isset($_GET['folder'])) echo $_GET['folder']; ?>" />
<input type="hidden" id="paper_type" name="paper_type" value="" />
<br />
<br />
<div style="text-align:right"><input onclick="window.close();" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" class="cancel" style="margin-right:8px" /><input type="submit" name="submit" value="<?php echo $string['next']; ?>" class="ok" /></div>
</form>
</body>
</html>
