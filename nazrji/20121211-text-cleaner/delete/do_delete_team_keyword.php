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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
  
check_var('keywordID', 'POST', true, false);

$keyword_list = explode(',', substr($_POST['keywordID'], 1));
foreach ($keyword_list as $individualID) {
  // Delete the keyword
  $result = $mysqli->prepare("DELETE FROM keywords_user WHERE id=?");
  $result->bind_param('i', $individualID);
  $result->execute();  
  $result->close();

  // Remove the deleted keyword from questions
  $result = $mysqli->prepare("DELETE FROM keywords_question WHERE keywordID=?");
  $result->bind_param('i', $individualID);
  $result->execute();  
  $result->close();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Keyword Deleted</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript">
    $(function () {
      window.opener.location.href = '<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php?module=<?php echo $_POST['module']; ?>';
      self.close();
    });
  </script>
</head>

<body style="background-color:#EEEEEE; font-size:80%; text-align:justifed">

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/delete_warning.png" width="48" height="48" alt="<?php echo $string['recyclebin']; ?>" /></td>

<td><p><?php echo $string['msg']; ?><p>

<div style="text-align:center">
<form action="" method="get">
<input type="button" name="ok" value="  <?php echo $string['ok']; ?>  " onclick="javascript:self.opener.location.href='<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php?moduleid=<?php echo $_POST['moduleID']; ?>';window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>