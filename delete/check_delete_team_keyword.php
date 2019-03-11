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
* Confirm that it is OK to proceed deleting a keyword.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$keywordIDs = check_var('keywordID', 'GET', true, false, true);

$keyword_names = array();
$result = $mysqli->prepare("SELECT keyword FROM keywords_user WHERE id IN (" . substr($keywordIDs, 1) . ")");
$result->execute();
$result->bind_result($keyword);
while ($result->fetch()) {
  $keyword_names[] = $keyword;
}
$result->close();

if (count($keyword_names) < substr_count($keywordIDs, ',')) {
  $contactemail = support::get_email();
  $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo page::title('Rog&#333;: ' . $string['confirmdelete']); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>

<body>

<?php
$i = 0;
$keywordss = '';
foreach ($keyword_names as $keyword_name) {
  if ($i == 0) {
    $keywordss = $keyword_name;
  } else {
    $keywordss .= ', ' . $keyword_name;
  }
  $i++;
}
?>
<p><?php printf($string['msg'], $keywordss); ?></p>

<div class="button_bar">
<form action="do_delete_team_keyword.php" method="post" autocomplete="off">
<input type="hidden" name="keywordID" value="<?php echo $_GET['keywordID']; ?>" />
<input type="hidden" name="module" value="<?php echo $_GET['module']; ?>" />
<input class="delete" type="submit" name="submit" value="<?php echo $string['delete']; ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" />
</form>
</div>

</body>
</html>