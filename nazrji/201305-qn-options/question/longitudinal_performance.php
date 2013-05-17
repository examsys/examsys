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
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.inc';
require '../include/sort.inc';
require '../classes/questioninfo.class.php';
  
check_var('q_id', 'GET', true, false, false);
  
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['longitudinalperformance'] .  ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/add_edit.css" />
  <style type="text/css">
    body {background-color:#F1F5FB; font-size:80%}
    th {background-color:#CFDBEB; text-align:left; font-weight:normal}
    td {vertical-align:top; padding-top:3px; padding-bottom:3px}
    .num {text-align:right}
  </style>
  
  <script type="text/javascript">
    function loadPaper(paperID) {
      window.opener.close();
      window.opener.opener.location = "../paper/details.php?paperID=" + paperID;
      window.close();
    }
    
    function loadModule(moduleID) {
      window.opener.location = "../folder/details.php?module=" + moduleID;
      window.close();
    }
  </script>
</head>

<body>
<table cellpadding="2" cellspacing="0" border="0" style="width:100%">
<tr>
<th></th>
<th><?php echo $string['papername']; ?></th>
<th><?php echo $string['calendaryear']; ?></th>
<th><?php echo $string['questionno']; ?></th>
<th><?php echo $string['datetaken']; ?></th>
<th><?php echo $string['cohort']; ?></th>
<th></th>
<th><?php echo $string['p']; ?></th>
<th><?php echo $string['d']; ?></th>
<th style="width:40%"></th>
</tr>
<?php
  $q_id = (int)$_GET['q_id'];

  $question_data = $mysqli->prepare("SELECT email, title, surname, initials, DATE_FORMAT(creation_date,\"%d/%m/%Y %H:%i\") AS creation_date, DATE_FORMAT(last_edited,\"%d/%m/%Y %H:%i\") AS last_edited, DATE_FORMAT(locked,\"$configObject->get('cfg_long_date_time')\") AS locked, q_type, std, status FROM (users, questions) WHERE users.id=questions.ownerID AND q_id=? LIMIT 1");
  $question_data->bind_param('i', $q_id);
  $question_data->execute();
  $question_data->bind_result($email, $title, $surname, $initials, $creation_date, $last_edited, $locked, $q_type, $std, $status);
  $question_data->store_result();
  $question_data->fetch();
  $question_data->close();
  
  $q_id_list = array();
  
  $found = false;
  $target_id = $q_id;
  do {
    $data = question_info::check_copies($target_id, $mysqli);
    if (count($data) > 0 and isset($data[0]['paperID'])) {
      $q_id_list[] = $data[0]['question_id'];
      $target_id = $data[0]['question_id'];
      $found = true;
    } else {
      $found = false;
    }
  } while ($found);
  
  $q_id_list[] = $q_id;
  
  unset($data);
  
  $data = question_info::check_copied($q_id, $mysqli);
  $rows = count($data);
  for ($i=0; $i<$rows; $i++) {
    if (isset($data[$i]['paperID'])) {
      $q_id_list[] = $data[$i]['question_id'];
    }
  }
  
  $display_data = array();
  
  $row = -1;
  foreach ($q_id_list as $lookup_q_id) {  
    $performance_array = question_info::question_performance($lookup_q_id, $mysqli);
    if (count($performance_array) > 0) {
      $row++;
      foreach ($performance_array as $paper => $performance) {
        $display_data[$row]['q_id'] = $lookup_q_id;
        $display_data[$row]['icon'] = $performance['icon'];
        $display_data[$row]['paperID'] = $paper;
        $display_data[$row]['title'] = $performance['title'];
        $display_data[$row]['screen'] = $performance['screen'];
        $display_data[$row]['calendar_year'] = $performance['calendar_year'];
        
        if (isset($performance['performance'][1]['taken'])) {
          $display_data[$row]['taken'] = $performance['performance'][1]['taken'];
          $display_data[$row]['cohort'] = $performance['performance'][1]['cohort'];
          $display_data[$row]['parts'] = question_info::display_parts($performance['performance'], $q_type);
          $display_data[$row]['p'] = question_info::display_p($performance['performance'], $q_type);
          $display_data[$row]['d'] = question_info::display_d($performance['performance'], $q_type);
        }
      }
    }
  }
  
  $sortby = 'calendar_year';
  $ordering = 'asc';
  $display_data = array_csort($display_data, $sortby, $ordering); 
 
  $row = 0;
  foreach ($display_data as $display_line) {
    $alt = ($row++ % 2 == 1) ? ' class="alt"' : '';
    if ($display_line['q_id'] == $q_id) {
      echo "<tr style=\"background-color:#FFC0C0\">";
    } else {
      echo "<tr{$alt}>";
    }
    echo "<td><img src=\"../artwork/" . $display_line['icon'] . "\" width=\"16\" height=\"16\" border=\"0\" alt=\"0\" /></td>";
    echo "<td><a href=\"\" onclick=\"loadPaper(" . $display_line['paperID'] . ")\">" . $display_line['title'] . "</a></td><td>" . $display_line['calendar_year'] . "</td>";
    echo "<td class=\"num\">" . $display_line['screen'] . "</td>";
    if (isset($display_line['taken'])) {
      echo "<td>" . $display_line['taken'] . "</td>";
      echo "<td class=\"num\">" . $display_line['cohort'] . "</td>";
      echo "<td class=\"num\">" . $display_line['parts'] . "</td>";
      echo "<td class=\"num\">" . $display_line['p'] . "</td>";
      echo "<td class=\"num\">" . $display_line['d'] . "</td>";
    } else {
      echo "<td></td>";
      echo "<td></td>";
      echo "<td></td>";
      echo "<td></td>";
      echo "<td></td>";
    }
    echo "<td></td></tr>\n";
  }

?>
</table>
</body>
</html>
