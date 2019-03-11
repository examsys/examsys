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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$emailtemplatedir = rogo_directory::get_directory('email_templates');
if (!$emailtemplatedir->check_permissions()) {
    $msg = "File:emailtemplete.php Line :" . (__LINE__ -1) . "  Error:" . $string['filepermission'];
    $notice->display_notice_and_exit($mysqli, $string['filepermission'], $string['filepermission'], $msg, '../artwork/exclamation_red_bg.png', '#C00000', true, true);
}

 $templatefile = $emailtemplatedir->fullpath($userObject->get_user_ID() . ".txt");
$message = '';
if (file_exists($templatefile)) {
	$file = fopen($templatefile,'r');
	$from = fgets($file, 64000);
	$ccaddress = fgets($file, 64000);
	$bccaddress = fgets($file, 64000);
	$subject = fgets($file, 64000);
	while (!feof($file)) {
		$message .= fgets($file, 64000);
	}
} else {
	$from = '';
	$ccaddress = '';
	$bccaddress = '';
	$subject = '';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('Rog&#333;: ' . $string['emailtemplate']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/emailtemplate.css" />
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>

<?php
  $texteditorplugin = \plugins\plugins_texteditor::get_editor();
  $texteditorplugin->display_header();
  $texteditorplugin->get_javascript_config(\plugins\plugins_texteditor::EMAIL);
?>
  <script>
    function submitValues() {
      opener.document.theform.emailtemplate.value = tinyMCE.get('template').getContent();
      opener.document.theform.ccaddress.value = document.templateform.ccaddress.value;
      opener.document.theform.bccaddress.value = document.templateform.bccaddress.value;
      opener.document.theform.subject.value = document.templateform.subject.value;
      opener.document.theform.emailclass.value = "yes";
      window.opener.document.theform.submit();
      window.close();
      return false;
    }
  </script>
</head>

<body>
<form name="templateform" onsubmit="return submitValues()" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" autocomplete="off">

<table cellpadding="2" cellspacing="0" border="0" width="100%" style="text-align:left">
<tr>
<td><?php echo $string['cc'] ?></td><td><input type="text" size="70" name="ccaddress" value="<?php echo $ccaddress ?>" /></td>
<td style="text-align:right" rowspan="3" valign="top"><img src="../artwork/stamp.png" width="89" height="93" alt="stamp" /></td>
</tr>
<tr>
<td><?php echo $string['bcc'] ?></td><td><input type="text" size="70" name="bccaddress" value="<?php echo $bccaddress ?>" /></td>
</tr>
<tr>
<td><?php echo $string['subject'] ?></td><td><input type="text" size="70" name="subject" value="<?php echo $subject ?>" /></td>
</tr>
<tr>
<td colspan="3"><?php $texteditorplugin->get_textarea('template', 'template', htmlspecialchars($message, ENT_NOQUOTES), plugins\plugins_texteditor::TYPE_STANDARD); ?></td>
</tr>
<tr>
<td colspan="3" style="text-align: center">
<input type="submit" class="ok" name="submit" value="<?php echo $string['email_class'] ?>" /><input type="button" name="cancel" class="cancel" value="<?php echo $string['cancel'] ?>" onclick="window.close();" />
</td>
</tr>
</table>
</form>

</body>
</html>
