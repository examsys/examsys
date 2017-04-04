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
* Delete a team or personal keyword.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';
  
$keywordIDs = check_var('keywordID', 'POST', true, false, true);

$keyword_names = array();
$result = $mysqli->prepare("SELECT keyword FROM keywords_user WHERE id IN (" . substr($keywordIDs, 1) . ")");
$result->execute();
$result->bind_result($keyword);
while ($result->fetch()) {
  $keyword_names[] = $keyword;
}
$result->close();

if (count($keyword_names) < substr_count($keywordIDs, ',')) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}


$keyword_list = explode(',', substr($keywordIDs, 1));
foreach ($keyword_list as $individualID) {
  // Delete the keyword
  $result = $mysqli->prepare("DELETE FROM keywords_user WHERE id = ?");
  $result->bind_param('i', $individualID);
  $result->execute();  
  $result->close();

  // Remove the deleted keyword from questions
  $result = $mysqli->prepare("DELETE FROM keywords_question WHERE keywordID = ?");
  $result->bind_param('i', $individualID);
  $result->execute();  
  $result->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Keyword Deleted</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.opener.location.href = '<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php?module=<?php echo $_POST['module']; ?>';
      self.close();
    });
  </script>
</head>

<body>

<p><?php echo $string['msg']; ?><p>

<div style="text-align:center">
<form action="" method="get" autocomplete="off">
<input type="button" name="ok" value="  <?php echo $string['ok']; ?>  " onclick="javascript:self.opener.location.href='<?php echo $configObject->get('cfg_root_path') ?>/folder/list_keywords.php?moduleid=<?php echo $_POST['moduleID']; ?>';window.close();" />
</form>
</div>

</body>
</html>