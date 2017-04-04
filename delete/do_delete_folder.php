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
* Delete a personal folder.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

$folderID = check_var('folderID', 'POST', true, false, true);

if ($userObject->get_user_ID() != folder_utils::get_ownerID($folderID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$result = $mysqli->prepare("SELECT name FROM folders WHERE id = ?");
$result->bind_param('i', $folderID);
$result->execute();
$result->bind_result($name);
$result->fetch();
$result->close();

$directories = explode(';', $name);
$parent = '';
if (count($directories) > 1) {
  for ($i=1; $i<count($directories); $i++) {
    if ($parent == '') {
      $parent = $directories[$i-1];
    } else {
      $parent .= ';' . $directories[$i-1];
    }
  }
}

if ($parent != '') {
  $result = $mysqli->prepare("SELECT id FROM folders WHERE name = ? AND ownerID = ?");
  $result->bind_param('si', $parent, $userObject->get_user_ID());
  $result->execute();
  $result->bind_result($parentID);
  $result->fetch();
  $result->close();
}

// Delete sub dirs.
$sub_folder_name = $name . ';%';
$result = $mysqli->prepare("SELECT id FROM folders WHERE name LIKE ? AND ownerID = ? AND deleted IS NULL");
$result->bind_param('si', $sub_folder_name, $userObject->get_user_ID());
$result->execute();
$result->store_result();
$result->bind_result($subID);
while ($result->fetch()) {
  $delete = $mysqli->prepare("UPDATE folders SET deleted = NOW(), name=CONCAT(name,' [deleted ',DATE_FORMAT(NOW(),'%d/%m/%Y'),']') WHERE id = ? AND ownerID = ?");
  $delete->bind_param('ii', $subID, $userObject->get_user_ID());
  $delete->execute();
  $delete->close();
}
$result->close();

$result = $mysqli->prepare("UPDATE folders SET deleted = NOW(), name=CONCAT(name,' [deleted ',DATE_FORMAT(NOW(),'%d/%m/%Y'),']') WHERE id = ? AND ownerID = ?");
$result->bind_param('ii', $folderID, $userObject->get_user_ID());
$result->execute();
$result->close();

// Remove papers from the deleted folder
$result = $mysqli->prepare("UPDATE properties SET folder = '' WHERE folder = ?");
$result->bind_param('i', $_POST['folderID']);
$result->execute();
$result->close();

$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['folderdeleted']; ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      <?php
      if ($parent == '') {
        echo "window.opener.location.href = '../index.php'\n";
      } else {
        echo "window.opener.location.href = '../folder/index.php?folder=$parentID'\n";
      }
      ?>
      self.close();
    });
  </script>
</head>

<body>

<p><?php echo $string['msg']; ?></p>

<div class="button_bar">
<form action="" method="get" autocomplete="off">
<?php
if ($parent == '') {
  echo "<input type=\"button\" name=\"cancel\" value=\"OK\" class=\"ok\" onclick=\"javascript:self.opener.location.href='../index.php';window.close();\" />\n";
} else {
  echo "<input type=\"button\" name=\"cancel\" value=\"OK\" class=\"ok\" onclick=\"javascript:self.opener.location.href='../folder.php?folder=$parentID';window.close();\" />\n";
}
?>
</form>
</div>

</body>
</html>