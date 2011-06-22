<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../../include/staff_student_auth.inc';
  
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
body {margin:6px; background-color:white; color:black; font-family:Arial,sans-serif; font-size:80%}
table {font-size:100%}
ul {list-style-type:square; color:#FF9900}
a:link.title {color:#0560A6; font-weight:bold}
a:visited.title {color:#0560A6; font-weight:bold}
a:link.page {color:white}
a:visited.page {color:white}
.path {color:#808080}
.num {text-align:right}
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
  
  echo "<tr><td colspan=\"3\" style=\"font-size:130%; font-weight:bold; margin-bottom:5px; color:#7598C4; font-family:Verdana,sans-serif\">\n<form action=\"\" method=\"post\">\nFrom:\n"; 
    // Split the end date
    $split_year = substr($start_date,0,4);
    $split_month = substr($start_date,4,2);
    $split_day = substr($start_date,6,2);
    echo "\n<select name=\"startmonth\">\n";
    // start Month
    $months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    for ($i=0; $i<12; $i++) {
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>" . $months[$i] . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>" . $months[$i] . "</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">" . $months[$i] . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">" . $months[$i] . "</option>\n";
        }
      }
    }
    echo "</select>\n";
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
      if ($i == 1 or $i == 21 or $i == 31) {
        echo $i . "st</option>\n";
      } elseif ($i == 2 or $i == 22) {
        echo $i . "nd</option>\n";
      } elseif ($i == 3 or $i == 23) {
        echo $i . "rd</option>\n";
      } else {
        echo $i . "th</option>\n";
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
  echo "To:";
    // Split the end date
    $split_year = substr($end_date,0,4);
    $split_month = substr($end_date,4,2);
    $split_day = substr($end_date,6,2);
    echo "<select name=\"endmonth\">\n";
    // end Month
    $months = array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
    for ($i=0; $i<12; $i++) {
      if (($split_month-1) == $i) {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\" selected>" . $months[$i] . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\" selected>" . $months[$i] . "</option>\n";
        }
      } else {
        if ($i < 9) {
          echo "<option value=\"0" . ($i+1) . "\">" . $months[$i] . "</option>\n";
        } else {
          echo "<option value=\"" . ($i+1) . "\">" . $months[$i] . "</option>\n";
        }
      }
    }
    echo "</select>\n";
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
       if ($i == 1 or $i == 21 or $i == 31) {
         echo $i . "st</option>\n";
       } elseif ($i == 2 or $i == 22) {
         echo $i . "nd</option>\n";
       } elseif ($i == 3 or $i == 23) {
         echo $i . "rd</option>\n";
       } else {
         echo $i . "th</option>\n";
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
  echo " <input type=\"submit\" value=\"Filter\" name=\"Filter\" /></form></td></tr>\n";
  
  echo "<tr style=\"font-size:130%; font-weight:bold; margin-bottom:5px; color:#7598C4; font-family:Verdana,sans-serif\"><td>Page Statistics</td><td></td><td>Search Statistics</td></tr>\n";
  echo "<tr ><td>&nbsp;</td><td></td><td>&nbsp;</td></tr>\n";
  
  echo "<tr><td style=\"vertical-align:top\">";
  $search_results = $mysqli->prepare("SELECT count(pageID) AS hits, title FROM help_log, student_help WHERE help_log.pageID=student_help.id AND help_log.type='student' AND accessed > '$start_date' AND accessed < '$end_date' GROUP BY pageID ORDER BY hits DESC, title");
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($hits, $title);
  $total_hits = $search_results->num_rows;
  if ($search_results->num_rows == 0) {
    echo "<p>There were no hits</p>\n";
  } else {
    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"1\" style=\"border:1px solid #E0E0E0; border-collapse:collapse\">\n";
    echo "<tr style=\"font-weight:bold; background:#808080; color:white\"><td>Page</td><td>Hits</td></tr>\n";
    while ($row = $search_results->fetch()) {
      echo "<tr><td>$title</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
    }
    echo "</table>\n";
  }
  $search_results->free_result();
  $search_results->close();
  
  echo "\n</td><td style=\"width:20px\">&nbsp;</td><td style=\"vertical-align:top\">\n";
  
  $search_results = $mysqli->prepare("SELECT COUNT(id) AS search_no, searchstring, hits FROM help_searches WHERE type='student' AND searched > '$start_date' AND searched < '$end_date' GROUP BY searchstring ORDER BY search_no DESC");
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($no_searches, $searchstring, $hits);
  $total_hits = $search_results->num_rows;
  if ($search_results->num_rows == 0) {
    echo "<p>There were no searches</p>\n";
  } else {
    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"1\" style=\"font-size:100%; border:1px solid #E0E0E0; border-collapse:collapse\">\n";
    echo "<tr style=\"font-weight:bold; background:#808080; color:white\"><td>Searches</td><td>Term</td><td>Results</td></tr>\n";
    while ($row = $search_results->fetch()) {
      if ($hits == 0) {
        echo "<tr style=\"color:#C00000\"><td class=\"num\">" . number_format($no_searches) . "</td><td>$searchstring</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
      } else {
        echo "<tr><td class=\"num\">" . number_format($no_searches) . "</td><td>$searchstring</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
      }
    }
    echo "</table>\n";
  }
  $search_results->free_result();
  $search_results->close();
  echo "<br/><div style=\"font-size:130%; font-weight:bold; margin-bottom:5px; color:#7598C4; font-family:Verdana,sans-serif\">Tutotial Stats</div>";
  $tutorial_results = $mysqli->prepare("SELECT COUNT(id) AS num, tutorial FROM help_tutorial_log WHERE type='student' AND accessed > '$start_date' AND accessed < '$end_date' GROUP BY tutorial ORDER BY id DESC");
  $tutorial_results->execute();
  $tutorial_results->store_result();
  $tutorial_results->bind_result($hits, $tutorial);
  $total_hits = $tutorial_results->num_rows;
  if ($tutorial_results->num_rows == 0) {
    echo "<p>There were no tutorials viewed</p>\n";
  } else {
    echo "<table cellpadding=\"2\" cellspacing=\"0\" border=\"1\" style=\"font-size:100%; border:1px solid #E0E0E0; border-collapse:collapse\">\n";
    echo "<tr style=\"font-weight:bold; background:#808080; color:white\"><td>Tutorial</td><td>Hits</td></tr>\n";
    while ($row = $tutorial_results->fetch()) {
        echo "<tr><td>" . $tutorial . "</td><td class=\"num\">" . number_format($hits) . "</td></tr>\n";
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