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
* This screen presents a list of students assigned to a particular cohort.
* You click on the student name of interest and the OSCE station marking
* form comes up.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$id = check_var('id', 'GET', true, false, true);

$properties = PaperProperties::get_paper_properties_by_crypt_name($id, $mysqli, $string, true);

$paperID 				= $properties->get_property_id();
$paper_title 		= $properties->get_paper_title();
$calendar_year 	= $properties->get_calendar_year();
$modules				= $properties->get_modules();

function quick_links() {
	$html = '';
	
	$html .= "<table style=\"width:100%; text-align:center\">\n<tr>\n";
	for ($i=1; $i<=26; $i++) {
		$html .= "<td class=\"qlink\"><a href=\"#" . chr($i+64) . "\" class=\"qlink\">" . chr($i+64) . "</a></td>";
	}
	$html .= "</tr>\n</table>\n";
	
	return $html;
}
?>
<!DOCTYPE html>
<html>
<head>
  <?php
  if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') or strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
    echo "<meta name=\"viewport\" content=\"user-scalable=no\">\n";
  } else {
    echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n";
  }
  ?>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

  <title><?php echo $string['classlist'] ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/osce_list.css" />
  <style type="text/css">
  <?php
    if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') or strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
      echo "body {font-size:110%}\n";
    } else {
      echo "body {font-size:90%}\n";
    }
  ?>
  </style>
  
  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script>
    function load(userID) {
      window.location.href = "form.php?id=<?php echo $_GET['id']; ?>&userID=" + userID.substr(4);
    }
    
    $(function() {
      $('.bl').click(function() {
        load($(this).attr('id'));
      });
      
      $('.l').click(function() {
        load($(this).attr('id'));
      });
      
      
    });
  </script>
  </head>

  <body>
  <div class="title"><?php echo $paper_title; ?></div>
  <form autocomplete="off">
  
  <?php
  
  if (count($modules) == 0) {
		echo $notice->info_strip($string['error1'], 100);
  } elseif (trim($calendar_year) == '') {
		echo $notice->info_strip($string['error2'], 100);
  } else {
    // Get the students who are enrolled on the module/session.
    $student_no = 0;
    $old_letter = '';
    
    $result = $mysqli->prepare("SELECT users.id, surname, first_names, title, student_id, started FROM (modules_student, users, sid) LEFT JOIN log4_overall ON users.id = log4_overall.userID AND q_paper = ? WHERE modules_student.userID = users.id AND users.id = sid.userID AND modules_student.idMod IN (" . implode(',', array_keys($modules)) . ") AND calendar_year = ? ORDER BY surname, initials");
    $result->bind_param('is', $paperID, $calendar_year);
    $result->execute();
		$result->store_result();
    $result->bind_result($tmp_userID, $surname, $first_names, $title, $student_id, $started);
		if ($result->num_rows == 0) {
			echo $notice->info_strip($string['error3'], 100);
		} else {
		  echo quick_links();
			
			echo "<table cellpadding=\"6\" cellspacing=\"0\" border=\"0\" style=\"width:100%\">\n";
				
			while ($result->fetch()) {
				$current_letter = strtoupper($surname{0});
				if ($old_letter != $current_letter) {
					echo "<tr><td colspan=\"3\" class=\"letter\"><a name=\"$current_letter\"></a>$current_letter</td></tr>";
				}
				if ($started == '') {
					echo "<tr class=\"bl\" id=\"user$tmp_userID\"><td class=\"indent\">$title</td><td>$surname, <span class=\"n\">$first_names</span</td><td>$student_id</td></tr>\n";
				} else {
					echo "<tr class=\"l\" id=\"user$tmp_userID\"><td class=\"indent\">$title</td><td>$surname, $first_names</td><td>$student_id</td></tr>\n";
				}
				$student_no++;
				$old_letter = $current_letter;
			}
		}
    $result->close();
  }
  echo "</table>\n</form>\n";

  $mysqli->close();
?>
</body>
</html>
