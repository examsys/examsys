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
* Displays tasks for the papers frame (papers_menu.php).
*
* @author Rob Ingram
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

ob_start('ob_gzhandler');
require '../include/staff_auth.inc';
//require '../include/question_types.inc';
require '../include/errors.inc';
//require '../include/calculate_marks.inc';
require '../include/paper_functions.inc.php';

check_var('paperID', 'GET', true, false);
$paperID = $_GET['paperID'];

$result = $mysqli->prepare("SELECT paper_title, moduleID, pass_mark, users.title, users.initials, users.surname, moduleID, folder, random_mark, total_mark, marking, paper_ownerID, DATE_FORMAT(start_date,'%H') as start_hour, DATE_FORMAT(start_date,'%Y%m%d%H%i') AS start_date, DATE_FORMAT(start_date,'$cfg_long_date_time') AS display_start_date, DATE_FORMAT(end_date,'%Y%m%d%H%i') AS end_date, paper_type, deleted, latex_needed FROM (properties, users) WHERE property_id=? AND paper_ownerID=users.id LIMIT 1");
$result->bind_param('i', $paperID);
$result->execute();
$result->bind_result($paper_title, $moduleID, $pass_mark, $title, $initials, $surname, $tmp_module, $tmp_folder, $random_mark, $total_mark, $marking, $paper_ownerID, $tmp_start_hour, $start_date, $display_start_date, $end_date, $paper_type, $deleted, $latex_needed);
$result->fetch();
$result->close();

$module = (isset($_GET['module'])) ? $_GET['module'] : '';
$folder = (isset($_GET['folder'])) ? $_GET['folder'] : '';
$folder_name = '';
get_module_folder_details($module, $folder, $folder_name, $userroles, $teams, $tmp_module, $mysqli);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html onscroll="scrollXY();">
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $cfg_page_charset ?>" />
  <title>Rogō<?php echo ' ' . $ts_version . ' ' . $cfg_install_type; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/screen.css" />
  <link rel="stylesheet" type="text/css" href="../css/metrics.css" />

  <script type="text/javascript" src="../js/staff_help.js"></script>
</head>

<body>
<?php
if (!isset($paper_title)) {
  echo render_missing($string, $support_email);
} elseif ($deleted != '') {
  echo render_deleted($string, $paper_title, $paper_ownerID, $userID);
} else {
  // Main content
?>
  <div id="content">
    <div id="header">
      <a href="#" onclick="launchHelp(1); return false;" id="help_link"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help'] ?>" border="0" /></a>
      <div class="breadcrumb clearfix">
        <ol>
          <li class="alpha"><a href="../staff/index.php"><?php echo $string['home'] ?></a></li>

  <?php
          if ($module != '') {
            echo '<li class="omega"><a href="../folder/details.php?module=' . $module . '">' . $module . '</a></li>';
          }
          if ($folder != '') {
            echo '<li class="omega"><a href="../folder/details.php?folder=' . $folder . '">' . $folder_name . '</a></li>';
          }
  ?>
        </ol>
      </div>
      <h1><?php echo $moduleID . '  &mdash; ' . $string['metrics'] ?></h1>
</div>

<?php
}
?>

</body>
</html>
<?php
function render_missing($string, $support_email) {
  $assistance = sprintf($string['furtherassistance'], $support_email, $support_email);

  $html = <<< HTML
    <div class="warning">
      <h1>{$string['papernotfound']}</h1>
      <hr size="1" align="left" width="500" />
      <p>{$assistance}</p>
    </div>

HTML;

  return $html;
}

function render_deleted($string, $paper_title, $paper_ownerID, $userID) {
  $deleted_parts = explode('[deleted', $paper_title);
  $message1 = sprintf($string['deleted_msg1'], $deleted_parts[0]);

  if ($paper_ownerID == $userID) {
    $message2 = "<li>" . $string['deleted_msg2'] . "</li>\n";
  } else {
    $result = $mysqli->prepare("SELECT title, surname, email FROM users WHERE id=?");
    $result->bind_param('i', $paper_ownerID);
    $result->execute();
    $result->bind_result($tmp_title, $tmp_surname, $tmp_email);
    $result->fetch();
    $result->close();
    $message2 = "<li>" . sprintf($string['deleted_msg3'], $tmp_email, $tmp_title, $tmp_surname). "</li>\n";
  }


  $html = <<< HTML
?>
    <div class="warning">
<?php
      <h1>{$string['paperdeleted']}</h1>
      <hr size="1" align="left" width="500" />
      <p>{$message1}</p>
      <ul>
        {$message2}
      </ul>
    </div>

HTML;

  return $html;
}
?>