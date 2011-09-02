<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../../include/staff_auth.inc';

if ($_POST['questions_to_add'] != '') {
  $questions = explode(',',$_POST['questions_to_add']);
  $display_pos = $_GET['display_pos'];
  foreach ($questions as $item) {
    $result = $mysqli->prepare("INSERT INTO papers VALUES (NULL,?,?,?,?)");
    $result->bind_param('iiii', $_GET['paperID'], $item, $_POST['screen'], $display_pos);
    $result->execute();
    $result->close();
    $display_pos++;

    // Create a track changes record to say new question added.
    $tmp_paperID = intval($_GET['paperID']);
    $trackChange = $mysqli->prepare("INSERT INTO track_changes VALUES (NULL,'Alter Paper',?,$userID,'',?,NOW(),'Add Question')");
    $trackChange->bind_param('is', $tmp_paperID, $item);
    $trackChange->execute();
    $trackChange->close();
  }
}
$mysqli->close();
$paperID = '';
$type = '';
$scrOfY = '';
$module = '';
$folder = '';
if (isset($_GET['paperID'])) $paperID = $_GET['paperID'];
if (isset($_GET['type'])) $type = $_GET['type'];
if (isset($_GET['scrOfY'])) $scrOfY = $_GET['scrOfY'];
if (isset($_GET['module'])) $module = $_GET['module'];
if (isset($_GET['folder'])) $folder = $_GET['folder'];
?>
<html>
<head>
<title>Add new Question</title>
  <script language="javascript">
    function closeWindow() {
      top.window.opener.location.href='../../paper/details.php?paperID=<?php echo $paperID; ?>&type=<?php echo $type; ?>&module=<?php echo $module; ?>&folder=<?php echo $folder; ?>&scrOfY=<?php echo $scrOfY; ?>';
      top.window.close();
    }
  </script>
</head>
<body onload="closeWindow();">
</body>
</html>