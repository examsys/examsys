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
 * Confirm that it is OK to set a student's exam to 'unfinished'.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../include/invigilator_auth.inc';
require '../include/errors.php';
$userID = check_var('userID', 'GET', true, false, true);
$paperID = check_var('paperID', 'GET', true, false, true);
$remote = param::optional('remote', false, param::BOOLEAN, param::FETCH_REQUEST);

$user_details = UserUtils::get_user_details($userID, $mysqli);
$name = $user_details['title'] . ' ' . $user_details['first_name'] . ' ' . $user_details['surname'];
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['confirmunfinishexam'] ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/confirm.css" />
  <script id="rogoconfig" data-root="<?php echo $configObject->get('cfg_root_path'); ?>"></script>
  <script src="../js/require.js"></script>
  <script src="../js/main.min.js"></script>
  <script src="../js/checkunfinishexaminit.min.js"></script>
</head>

<body>

  <p><?php echo sprintf($string['user'], $name) ?></p>
  <p><?php echo $string['msg']; ?></p>

  <div class="button_bar">
  <input id="userID"  type="hidden" name="userID" value="<?php echo $userID ?>" />
  <input id="paperID" type="hidden" name="paperID" value="<?php echo $paperID ?>" />
  <input id="remote" type="hidden" name="remote" value="<?php echo $remote ?>" />
  <input id="unfinishexam" class="ok" type="submit" name="submit" value=" <?php echo $string['ok'] ?> " /><input class="cancel" type="button" name="cancel" value=" <?php echo $string['cancel'] ?> " onclick="javascript:window.close();" />
  </div>

</body>
</html>
