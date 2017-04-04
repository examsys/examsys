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
* Set a paper as retired.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once  '../include/errors.php';

$paperID = check_var('paperID', 'POST', true, false, true);

if (!Paper_utils::paper_exists($paperID, $mysqli)) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$logger = new Logger($mysqli);

if (isset($_POST['questions'])) {
  $status_array = QuestionStatus::get_all_statuses($mysqli, $string);
  $retired_status_id = -1;
  // TODO: ask which retired status to use if there is more than one?
  foreach ($status_array as $status) {
    if ($status->get_retired()) {
      $retired_status_id = $status->id;
      break;
    }
  }

  if ($retired_status_id != -1) {
    $mysqli->autocommit(false);

    // Look up and retire the questions
    $result = $mysqli->prepare("SELECT question FROM papers WHERE paper = ?");
    $result->bind_param('i', $paperID);
    $result->execute();
    $result->store_result();
    $result->bind_result($question_id);
    while ($result->fetch()) {
      $stmt = $mysqli->prepare("UPDATE questions SET status=? WHERE q_id = ?");
      $stmt->bind_param('ii', $retired_status_id, $question_id);
      $stmt->execute();
      $stmt->close();

      $logger->track_change('Retire question', $question_id, $userObject->get_user_ID(), '', '', 'retired');
    }
    $result->close();

    $mysqli->commit();
    $mysqli->autocommit(true);
  }
}

// Retire the paper itself
$assessment = new assessment($mysqli, $configObject);
$now = date("Y-m-d H:i:s");
$update_params = array(
    'retired' => array('s', $now)
);
$assessment->db_update_assessment($paperID, $update_params);

$logger->track_change('paper', $paperID, $userObject->get_user_ID(), '', '', 'retired');


$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['paperretired'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
	<style type="text/css">
	  body {background-color:#F1F5FB; font-size:90%}
	</style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.opener.location.reload(true);
      window.close();
    });
  </script>
</head>

<body>

<table cellpadding="8" cellspacing="0" border="0" width="100%">
<tr>
<td valign="top"><img src="../artwork/formative_retired.png" width="48" height="48" alt="<?php echo $string['paperretired']; ?>" /></td>

<td><p><?php echo $string['msg']; ?><p>

<div style="text-align:center">
<form action="" method="get" autocomplete="off">
<input type="button" name="ok" value="  <?php echo $string['ok']; ?>  " onclick="window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>