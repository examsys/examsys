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

require '../../include/staff_auth.inc';

if(isset($_POST['startday'])) {
$start_date = $_POST['startyear'] . $_POST['startmonth'] . $_POST['startday'] .  '000000';
$end_date = $_POST['endyear'] . $_POST['endmonth'] . $_POST['endday'] . '000000';
} else {
  $start_date = date('Ymd',time() - 31536000) . '000000';
  $end_date = date('Ymd') . '000000';
}
?>
<html>
<head>
<title>Help and Support Center<?php echo " $cfg_install_type"; ?></title>
<style>
body {background-color:white; color:black; font-family:Arial,sans-serif; font-size:80%}
table {font-size:100%}
ul {list-style-type:square; color:#FF9900}
a:link.title {color:#0560A6; font-weight:bold}
a:visited.title {color:#0560A6; font-weight:bold}
a:link.page {color:white}
a:visited.page {color:white}
.path {color:#808080}
.num {text-align:right; border-bottom: 1px solid #6B82B2; border-right: 1px solid #6B82B2}
.txt {border-bottom: 1px solid #6B82B2; border-right: 1px solid #6B82B2}
</style>
<script language="JavaScript">
  function displayPage(targetID, page_no) {
    for (page=1; page<=page_no; page++) {
      document.getElementById('page' + page).style.display='none';
    }
    document.getElementById('page' + targetID).style.display='block';
    window.scrollTo(0,0)
  }
</script>
</head>
<body>

<?php
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n"; 
  
  echo "<tr><td colspan=\"3\" style=\"font-size:130%; font-weight:bold; margin-bottom:5px; color:#7598C4\">\n<form action=\"\" method=\"post\">\n" . $string['from'] . "\n"; 
    // Split the end date
    $split_year = substr($start_date,0,4);
    $split_month = substr($start_date,4,2);
    $split_day = substr($start_date,6,2);
    // start Day
    echo "<select name=\"startday\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
          echo "<option value=\"0$i\">";
        }
      } else {
        if ($i == $split_day) {
          echo "<option value=\"$i\" selected>";
        } else {
          echo "<option value=\"$i\">";
        }
      }
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";
    // start Month
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    echo "\n<select name=\"startmonth\">\n";
    for ($i=0; $i<12; $i++) {
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>" . mb_substr($string[$months[$i]],0,3,'UTF-8') . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>" . mb_substr($string[$months[$i]],0,3,'UTF-8') . "</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">" . mb_substr($string[$months[$i]],0,3,'UTF-8') . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">" . mb_substr($string[$months[$i]],0,3,'UTF-8') . "</option>\n";
        }
      }
    }
    echo "</select>\n";
    // start Year
    echo "<select name=\"startyear\">\n";
    for ($i = 2005; $i < (date('Y')+2); $i++) {
      if ($i == $split_year) {
        echo "<option value=\"$i\" selected>$i</option>\n";
      } else {
        echo "<option value=\"$i\">$i</option>\n";
      }
    }
    echo "</select>\n";
    echo $string['to'];
    // Split the end date
    $split_year = substr($end_date,0,4);
    $split_month = substr($end_date,4,2);
    $split_day = substr($end_date,6,2);
    // end Day
    echo "<select name=\"endday\">\n";
    for ($i = 1; $i < 32; $i++) {
      if ($i < 10) {
        if ($i == $split_day) {
          echo "<option value=\"0$i\" selected>";
        } else {
          echo "<option value=\"0$i\">";
        }
      } else {
        if ($i == $split_day) {
          echo "<option value=\"$i\" selected>";
        } else {
          echo "<option value=\"$i\">";
        }
      }
      if ($i < 10) echo '0';
      echo "$i</option>\n";
    }
    echo "</select>\n";
    // end Month
    echo "<select name=\"endmonth\">\n";
    for ($i=0; $i<12; $i++) {
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>" . mb_substr($string[$months[$i]],0,3,'UTF-8') . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>" . mb_substr($string[$months[$i]],0,3,'UTF-8') . "</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">" . mb_substr($string[$months[$i]],0,3,'UTF-8') . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">" . mb_substr($string[$months[$i]],0,3,'UTF-8') . "</option>\n";
        }
      }
    }
    echo "</select>\n";
     // end Year
     echo "<select name=\"endyear\">\n";
     for ($i = 2005; $i < (date('Y')+2); $i++) {
       if ($i == $split_year) {
         echo "<option value=\"$i\" selected>$i</option>\n";
       } else {
         echo "<option value=\"$i\">$i</option>\n";
       }
     }
	 echo "</select>\n";
  echo " <input type=\"submit\" value=\" " . $string['filter'] . " \" name=\"Filter\" /></form></td></tr>\n";
  
  echo "<tr style=\"width:49%; font-size:130%; font-weight:bold; margin-bottom:5px; color:#7598C4\"><td>" . $string['pagehits'] . "</td><td style=\"width:2%\"></td><td width=\"49%\">" . $string['searches'] . "</td></tr>\n";
  echo "<tr ><td>&nbsp;</td><td></td><td>&nbsp;</td></tr>\n";
  
  echo "<tr><td style=\"vertical-align:top\">";
  $search_results = $mysqli->prepare("SELECT count(pageID) AS hits, title FROM help_log, staff_help WHERE help_log.pageID=staff_help.id AND help_log.type='staff' AND accessed > '$start_date' AND accessed < '$end_date' GROUP BY pageID ORDER BY hits DESC, title");
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($hits, $title);
  $total_hits = $search_results->num_rows;
  if ($search_results->num_rows == 0) {
    echo "<p>" . $string['nohits'] . "</p>\n";
  } else {
    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; border:1px solid #6B82B2; border-collapse:collapse\">\n";
    echo "<tr style=\"text-align:center; border-top: 1px solid #6B82B2; border-bottom:1px solid #6B82B2; border-left:1px solid #6B82B2; background-image:url(../search_bar_background.png); background-repeat:repeat-x; height:23px; color:white; font-weight:bold\"><td style=\"border-right: 1px solid #6B82B2\">" . $string['page'] . "</td><td>" . $string['hits'] . "</td></tr>\n";
    while ($search_results->fetch()) {
      echo "<tr><td class=\"txt\">$title</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
    }
    echo "</table>\n";
  }
  $search_results->free_result();
  $search_results->close();
  
  echo "\n</td><td style=\"width:20px\">&nbsp;</td><td style=\"vertical-align:top\">\n";
  
  $search_results = $mysqli->prepare("SELECT COUNT(id) AS search_no, searchstring, hits FROM help_searches WHERE type='staff' AND searched > '$start_date' AND searched < '$end_date' GROUP BY searchstring ORDER BY search_no DESC");
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($no_searches, $searchstring, $hits);
  $total_hits = $search_results->num_rows;
  if ($search_results->num_rows == 0) {
    echo "<p>" . $string['nosearches'] . "</p>\n";
  } else {
    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:100%; border:1px solid #6B82B2; border-collapse:collapse\">\n";
    echo "<tr style=\"text-align:center; border-top: 1px solid #6B82B2; border-bottom:1px solid #6B82B2; border-left:1px solid #6B82B2; background-image:url(../search_bar_background.png); background-repeat:repeat-x; height:23px; color:white; font-weight:bold\"><td style=\"border-right: 1px solid #6B82B2\">" . $string['searches'] . "</td><td style=\"border-right: 1px solid #6B82B2\">" . $string['term'] . "</td><td>" . $string['results'] . "</td></tr>\n";
    while ($search_results->fetch()) {
      if ($hits == 0) {
        echo "<tr style=\"color:#C00000\"><td class=\"num\">" . number_format($no_searches) . "</td><td class=\"txt\">$searchstring</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
      } else {
        echo "<tr><td class=\"num\">" . number_format($no_searches) . "</td><td class=\"txt\">$searchstring</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
      }
    }
    echo "</table>\n";
  }
  $search_results->free_result();
  $search_results->close();
  echo "<br /><div style=\"font-size:130%; font-weight:bold; margin-bottom:5px; color:#7598C4\">" . $string['tutorialstats'] . "</div>";
  $tutorial_results = $mysqli->prepare("SELECT COUNT(id) AS num, tutorial FROM help_tutorial_log WHERE type='staff' AND accessed > '$start_date' AND accessed < '$end_date' GROUP BY tutorial ORDER BY id DESC");
  $tutorial_results->execute();
  $tutorial_results->store_result();
  $tutorial_results->bind_result($hits, $tutorial);
  $total_hits = $tutorial_results->num_rows;
  if ($tutorial_results->num_rows == 0) {
    echo "<p>" . $string['notutorials'] . "</p>\n";
  } else {
    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; font-size:100%; border:1px solid #6B82B2; border-collapse:collapse\">\n";
    echo "<tr style=\"text-align:center; border-top: 1px solid #6B82B2; border-bottom:1px solid #6B82B2; border-left:1px solid #6B82B2; background-image:url(../search_bar_background.png); background-repeat:repeat-x; height:23px; color:white; font-weight:bold\"><td style=\"border-right: 1px solid #6B82B2\">" . $string['tutorial'] . "</td><td>" . $string['hits'] . "</td></tr>\n";
    while ($row = $tutorial_results->fetch()) {
        echo "<tr><td class=\"txt\">" . $tutorial . "</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
    }
    echo "</table>\n";
  }
  $tutorial_results->free_result();
  $tutorial_results->close();
  
  echo "</td></tr></table>\n";

  $mysqli->close();
?>
</body>
</html>