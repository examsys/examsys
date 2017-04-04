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

require '../include/invigilator_auth.inc';
require_once '../include/errors.php';

$userID  = check_var('userID', 'POST', true, false, true);
$paperID = check_var('paperID', 'POST', true, false, true);

$sql = 'UPDATE log_metadata SET completed = null WHERE userID = ? AND paperID = ?';

$stmt = $mysqli->prepare($sql);
$stmt->bind_param('ii', $userID, $paperID);
$stmt->execute();
$stmt->close();
    
$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Exam Unfinished</title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.opener.location.reload();
      self.close();
    });
  </script>
</head>

<body>

<p>Exam successfully unfinished.<p>

<div class="button_bar">
<form action="" method="get" autocomplete="off">
<input type="button" name="cancel" value="OK" class="ok" onclick="javascript:window.close();" />
</form>
</div>

</body>
</html>
