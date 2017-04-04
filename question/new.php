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
require '../include/errors.php';

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['newquestion'] . ' ' . $configObject->get('cfg_install_type') ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <style>
    body {font-size:75%}
    tr {cursor:pointer; height:48px}
    .icon {width:48px; height:48px; line-height:0}
    .desc {background-color:white; padding:3px}
    .desc:hover {background-color:#FFE7A2}
  </style>
  
  <script>
    function createQ(type) {
      window.close();
      window.opener.location.href='./edit/index.php?type=' + type + '&module=<?php echo $_GET['module'] ?>';
    }
  </script>
</head>
<body>
<?php
  $types = array('area', 'enhancedcalc', 'dichotomous', 'extmatch', 'blank', 'info', 'matrix', 'hotspot', 'labelling', 'likert', 'mcq', 'mrq', 'keyword_based', 'random', 'rank', 'sct', 'textbox', 'true_false');

  $question_types = array();
  foreach ($types as $type) {
    $question_types[$type]['desc'] = $string[$type . '_desc'];
    $question_types[$type]['title'] = $string[$type];
  }
  
  $break_no = round(count($question_types) / 2);
?>
  <table cellspacing="1" cellpadding="0" border="0">
<?php
foreach ($question_types as $type=>$details) {
  echo "<tr onclick=\"createQ('$type')\"><td class=\"icon\"><img src=\"../artwork/new_$type.png\" width=\"48\" height=\"48\" /></td><td class=\"desc\"><strong>" . $details['title'] . "</strong><br />" . $details['desc'] . "</td></tr>\n";
}
?>
  </table>  
</body>
</html>