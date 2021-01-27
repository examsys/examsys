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

require '../../include/staff_auth.inc';
$questionstoadd = param::optional('questions_to_add', '', param::TEXT, param::FETCH_POST);
if ($questionstoadd != '') {
    $logger = new Logger($mysqli);
    $questions = explode(',', ltrim($questionstoadd, ','));
    $display_pos = $_GET['display_pos'];
    foreach ($questions as $item) {
        Paper_utils::add_question($_GET['paperID'], $item, $_POST['screen'], $display_pos, $mysqli);
        $display_pos++;

        // Create a track changes record to say new question added.
        $tmp_paperID = intval($_GET['paperID']);
        $success = $logger->track_change('Paper', $tmp_paperID, $userObject->get_user_ID(), '', $item, 'Add Question');
    }
}
$mysqli->close();
$paperID = '';
$type = '';
$scrOfY = '';
$module = '';
$folder = '';
if (isset($_GET['paperID'])) {
    $paperID = $_GET['paperID'];
}
if (isset($_GET['type'])) {
    $type = $_GET['type'];
}
if (isset($_GET['scrOfY'])) {
    $scrOfY = $_GET['scrOfY'];
}
if (isset($_GET['module'])) {
    $module = $_GET['module'];
}
if (isset($_GET['folder'])) {
    $folder = $_GET['folder'];
}
?>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Add new Question</title>
</head>
<body>
</body>
</html>
