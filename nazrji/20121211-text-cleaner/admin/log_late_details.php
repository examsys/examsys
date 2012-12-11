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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/sysadmin_auth.inc';
  require '../include/sidebar_menu.inc';
  require_once '../classes/networkutils.class.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title><?php echo $string['loglatedetails']; ?></title>
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <style type="text/css">
    .icon {padding-left:10px}
  </style>
  
  <script type="text/javascript" src="../js/staff_help.js"></script>
</head>

<body>
<?php
  include '../include/admin_options.inc';
?>

<div id="content" class="content" style="font-size:80%">

<table class="header">
<tr>
<th colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:200%; margin-left:10px; font-weight:bold"><?php echo $string['loglatedetails']; ?></div></th>
<th style="text-align:right; vertical-align:top; padding-top:2px; padding-right:6px"><a href="#" onclick="launchHelp(221); return false;"><img src="../artwork/small_help_icon.gif" width="16" height="16" alt="<?php echo $string['help']; ?>" border="0" /></a></th>
</tr>
<tr>
<th></th>
<th><?php echo $string['papertitle']; ?></th>
<th style="width:50%"><?php echo $string['studentslate']; ?></th>
</tr>
<tr><th colspan="3" class="bevel"></th></tr>

<?php
  $icons = array('formative_16.gif', 'progress_16.gif', 'summative_16.gif');
  $data = array();

  $result = $mysqli->prepare("SELECT DISTINCT paper_type, paper_title, q_paper, userID FROM log_late, properties WHERE log_late.q_paper=properties.property_id GROUP BY userID ORDER BY paper_title");
  $result->execute();
  $result->bind_result($paper_type, $paper_title, $paperID, $uID);
  while ($result->fetch()) {
    $data[$paperID]['paper_title'] = $paper_title;
    $data[$paperID]['paper_type'] = $paper_type;
    $data[$paperID]['students'][] = $uID;
  }
  $result->close();
  
  
  foreach ($data as $paperID=>$row) {
    echo "<tr><td class=\"icon\"><a href=\"../paper/details.php?paperID=$paperID\"><img src=\"../artwork/" . $icons[$row['paper_type']] . "\" width=\"16\" height=\"16\" border=\"0\" alt=\"\" /></a></td><td><a href=\"../paper/details.php?paperID=$paperID\">" . $row['paper_title'] . "</a></td><td>" . count($row['students']) . "</td></tr>";
  }
?>
</table>

</div>
</body>
</html>