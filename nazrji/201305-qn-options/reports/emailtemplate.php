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

  require '../include/staff_auth.inc';
  require '../include/add_edit.inc';  // needed for the WYSIWYG editor

  $message = '';
  if (file_exists("../email_templates/" . $userObject->get_user_ID() . ".txt")) {
    $file = fopen("../email_templates/" . $userObject->get_user_ID() . ".txt",'r');
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title>Email Template<?php echo " " . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    body {font-size:90%; background-color:#F0F0F0; margin-top:2px}
    .heading {background-color:#EBEADB; border-left:solid white 1px; border-right:solid #D8D2BD 1px; border-top:solid white 1px; border-bottom: solid #D8D2BD 1px}
  </style>

  <?php echo $configObject->get('cfg_js_root') ?>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
  <script type="text/javascript" src="../tools/tinymce/jscripts/tiny_mce/tiny_config_email.js"></script>
  <script type="text/javascript">
    function submitValues() {
      opener.document.theform.from.value = document.templateform.from.value;
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
<form name="templateform" onsubmit="return submitValues()" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<table cellpadding="2" cellspacing="0" border="0" width="100%" style="text-align:left">
<tr>
<td>&nbsp;&nbsp;<?php echo $string['cc'];?></td><td><input type="text" size="70" name="ccaddress" value="<?php echo $ccaddress; ?>" /></td>
<td style="text-align:right" rowspan="3" valign="top"><img src="../artwork/stamp.png" width="63" height="67" alt="stamp" /></td>
</tr>
<tr>
<td>&nbsp;&nbsp;<?php echo $string['bcc'];?></td><td><input type="text" size="70" name="bccaddress" value="<?php echo $bccaddress; ?>" /></td>
</tr>
<tr>
<td>&nbsp;&nbsp;<?php echo $string['subject'];?></td><td><input type="text" size="70" name="subject" value="<?php echo $subject; ?>" /></td>
</tr>
<tr>
<td colspan="3"><p><?php echo wysiwyg_editor('oEdit1', 'template', $message, 782, 350); ?></p></td>
</tr>
<tr><td colspan="3">&nbsp;</td></tr>
<tr>
<td colspan="3" style="text-align: center">
<input type="submit" style="width:120px" name="submit" value="<?php echo $string['email_class'];?>" />&nbsp;<input type="button" name="cancel" style="width: 120px" value="<?php echo $string['cancel'];?>" onclick="window.close();" />
<?php
  $result = $mysqli->prepare("SELECT email FROM users WHERE id=?");
  $result->bind_param('i',$userObject->get_user_ID());
  $result->execute();
  $result->bind_result($from);
  $result->fetch();
  $result->close();

  $mysqli->close();
?>
<input type="hidden" name="from" value="<?php echo $from; ?>" /></td>
</tr>
</table>
</form>

</body>
</html>
