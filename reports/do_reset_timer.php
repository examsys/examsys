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

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$metadataID = check_var('metadataID', 'POST', true, false, true);
$userID     = check_var('userID', 'POST', true, false, true);
$paperID    = check_var('paperID', 'POST', true, false, true);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
if ($properties->get_paper_type() != '1') {   // Only allow timer reset of Progress Test papers.
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$log_metadata = new LogMetadata($userID, $paperID, $mysqli);

if ($log_metadata->get_record($metadataID) === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$user_details = UserUtils::get_user_details($userID, $mysqli);
if ($user_details === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$log_metadata->set_started_to_null();

$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Reset Timer</title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    $(function () {
      window.close();
    });
  </script>
</head>

<body>
</body>
</html>