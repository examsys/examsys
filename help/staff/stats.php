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
* Display usage information for the staff help pages.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../../include/sysadmin_auth.inc';

$id = null;
$help_system = new OnlineHelp($userObject, $configObject, $string, $notice, 'staff', $language, $mysqli);

if (isset($_POST['startday'])) {
$start_date = $_POST['startyear'] . $_POST['startmonth'] . $_POST['startday'] .  '000000';
$end_date = $_POST['endyear'] . $_POST['endmonth'] . $_POST['endday'] . '000000';
} else {
  $start_date = date('Ymd',time() - 31536000) . '000000';
  $end_date = date('Ymd') . '000000';
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=utf-8" />
  <title><?php echo page::title('Rog&#333;: ' . $string['help']); ?></title>

  <link rel="stylesheet" type="text/css" href="../../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../../css/help.css" />
  <style type="text/css">
    ul {list-style-type:square; color:#FF9900}
    a:link.title {color:#0560A6; font-weight:bold}
    a:visited.title {color:#0560A6; font-weight:bold}
    a:link.page {color:white}
    a:visited.page {color:white}
    .path {color:#808080}
    .num {text-align:right; border-bottom: 1px solid #6B82B2; border-right: 1px solid #6B82B2}
    .txt {border-bottom: 1px solid #6B82B2; border-right: 1px solid #6B82B2}
    .stats {border-collapse:collapse}
    .stats td {vertical-align:top; border-bottom: 1px solid #295AAD; border-right: 1px solid #295AAD}
		th {background-color:#295AAD; color:white; border:#295AAD 1px solid}
  </style>
  <script type="text/javascript" src="../../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../../js/help.js"></script>
</head>
<body>
<div id="wrapper">
  <div id="toolbar">
    <?php $help_system->display_toolbar($id); ?>
  </div>

  <div id="toc">
    <?php $help_system->display_toc($id); ?>
  </div>
  <div id="contents">
<?php
  echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";

  echo "<tr><td colspan=\"3\" style=\"margin-bottom:5px\">\n<form action=\"\" method=\"post\" autocomplete=\"off\">" . $string['dates'] . " ";
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
    echo "</select>";
    // start Month
    $months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');
    echo "<select name=\"startmonth\">\n";
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
    echo "</select>";
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
    echo ' - ';
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
    echo "</select>";
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
    echo "</select>";
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

  echo "<tr style=\"width:49%; font-size:130%; font-weight:bold; color:#295AAD\"><td>" . $string['pagehits'] . "</td><td style=\"width:2%\"></td><td width=\"49%\">" . $string['searches'] . "</td></tr>\n";
  echo "<tr ><td>&nbsp;</td><td></td><td>&nbsp;</td></tr>\n";

  echo "<tr><td style=\"vertical-align:top\">";
  $search_results = $mysqli->prepare("SELECT count(pageID) AS hits, title
    FROM help_log, staff_help
    WHERE help_log.pageID = staff_help.id AND help_log.type = 'staff' AND accessed > ? AND accessed < ?
    GROUP BY title
    ORDER BY hits DESC, title");
  $search_results->bind_param('ss', $start_date, $end_date);
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($hits, $title);
  $total_hits = $search_results->num_rows;
  if ($search_results->num_rows == 0) {
    echo "<p>" . $string['nohits'] . "</p>\n";
  } else {
    echo "<table class=\"stats\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; border-left: #295AAD 1px solid\">\n";
    echo "<tr><th>" . $string['page'] . "</th><th>" . $string['hits'] . "</th></tr>\n";
    while ($search_results->fetch()) {
      echo "<tr><td class=\"txt\">$title</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
    }
    echo "</table>\n";
  }
  $search_results->free_result();
  $search_results->close();

  echo "\n</td><td style=\"width:20px\">&nbsp;</td><td style=\"vertical-align:top\">\n";

  $search_results = $mysqli->prepare("SELECT COUNT(id) AS search_no, searchstring, hits
    FROM help_searches
    WHERE type='staff' AND searched > ? AND searched < ?
    GROUP BY searchstring, hits
    ORDER BY search_no DESC");
  $search_results->bind_param('ss', $start_date, $end_date);
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($no_searches, $searchstring, $hits);
  $total_hits = $search_results->num_rows;
  if ($search_results->num_rows == 0) {
    echo "<p>" . $string['nosearches'] . "</p>\n";
  } else {
    echo "<table class=\"stats\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\" style=\"width:100%; border-left: #295AAD 1px solid\">\n";
    echo "<tr><th>" . $string['searches'] . "</th><th>" . $string['term'] . "</th><th>" . $string['results'] . "</th></tr>\n";
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

  echo "</td></tr></table>\n";

  $mysqli->close();
?>
  </div>
</div>
</body>
</html>