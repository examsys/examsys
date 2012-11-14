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
* Check for duplicate answers for individual questions in log files where should be unique
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';

function render_error($error) {
  echo "<li>$error</li>";
}

// Default logs to check
$logs = array(2);
if (!empty($_GET['logs'])) {
  $logs = explode(',', $_GET['logs']);
}

$errors = array();
foreach ($logs as $log) {
  $query = <<< QUERY
SELECT q_id, userID, q_paper, count(q_id) 
FROM log{$log} INNER JOIN users ON log{$log}.userID = users.id 
WHERE users.roles='Student' 
GROUP BY userID, q_paper, q_id HAVING count(q_id) > 1
ORDER BY count(q_id) DESC, q_paper,userID ASC
QUERY;
  $stmt = $mysqli->prepare($query);
  $stmt->execute();
  $stmt->bind_result($q_id, $tmp_userid, $paper_id, $count);
  while ($stmt->fetch()) {
    $errors[$log][] =  "$count records found for user $tmp_userid on paper $paper_id, question $q_id";
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Log duplicate check</title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style type="text/css">
    #content {
      padding: 24px;
    }
  </style>
</head>
<body>
  <div id="content">
<?php
foreach ($logs as $log) {
?>
    <h1>Checking log<?php echo $log ?></h1>
<?php
  if (isset($errors[$log]) and count($errors[$log]) > 0) {
?>
    <ul>
      <?php array_map('render_error', $errors[$log]); ?>
    </ul>
<?php
  } else {
?>
    <p>No duplicate records found.</p>
<?php
  }
}
?>
  </div>
</body>
</html>
