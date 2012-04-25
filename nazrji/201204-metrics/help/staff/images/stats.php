<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2010 The University of Nottingham
* @package
*/

  if (strpos($_SERVER['PHP_SELF'],'student_help') !== false) {
    $help_type = 'student';
    $require_file = '../include/staff_student_auth.inc';
  } else {
    $help_type = 'staff';
    $require_file = '../include/staff_auth.inc';
  }

  require $require_file;
?>
<html>
<head>
<title>Help and Support Center</title>
<style>
body {margin:6px; background-color:white; color:black; font-family:Arial,sans-serif; font-size:80%}
table {font-size:100%}
ul {list-style-type:square; color:#FF9900}
a:link.title {color:#0560A6; font-weight:bold}
a:visited.title {color:#0560A6; font-weight:bold}
a:link.page {color:white}
a:visited.page {color:white}
.path {color:#808080}
.num1 {text-align:right; border-bottom:1px solid #E0E0E0; border-left:1px solid #E0E0E0}
.norm1 {border-bottom:1px solid #E0E0E0; border-left:1px solid #E0E0E0}
.num2 {text-align:right; border-bottom:1px solid #E0E0E0; border-left:1px solid #E0E0E0; border-right:1px solid #E0E0E0}
.norm2 {border-bottom:1px solid #E0E0E0; border-left:1px solid #E0E0E0; border-right:1px solid #E0E0E0}
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
  echo "<table cellpadding=\"6\" cellspacing=\"0\" border=\"0\">\n";
  echo "<tr style=\"font-size:130%; font-weight:bold; margin-bottom:5px; color:#7598C4; font-family:Verdana,sans-serif\"><td>Page Statistics</td><td></td><td>Search Statistics</td></tr>\n";
  echo "<tr><td style=\"vertical-align:top\">";
  
  $search_results = $mysqli->prepare("SELECT count(pageID) AS hits, title FROM help_log, " . $help_type . "_help WHERE help_log.pageID=" . $help_type . "_help.id AND help_log.type='$help_type' GROUP BY pageID ORDER BY hits DESC, title");
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($hits, $title);
  $total_hits = $search_results->num_rows;
  if ($search_results->num_rows == 0) {
    echo "<p>There were no hits in the last 12 months.</p>\n";
  } else {
    echo "<table cellpadding=\"2\" cellspacing=\"0\">\n";
    echo "<tr style=\"font-weight:bold; background:#808080; color:white\"><td>Hits</td><td>Page</td></tr>\n";
    while ($row = $search_results->fetch()) {
      echo "<tr><td class=\"num1\">" . number_format($hits) . "</td><td class=\"norm2\">$title</td></tr>\n";
    }
    echo "</table>\n";
  }
  $search_results->free_result();
  $search_results->close();
  
  echo "\n</td><td style=\"width:20px\">&nbsp;</td><td style=\"vertical-align:top\">\n";
  
  $search_results = $mysqli->prepare("SELECT COUNT(id) AS search_no, searchstring, hits FROM help_searches WHERE type='$help_type' AND searched > now() - INTERVAL 1 YEAR GROUP BY searchstring ORDER BY search_no DESC");
  $search_results->execute();
  $search_results->store_result();
  $search_results->bind_result($no_searches, $searchstring, $hits);
  $total_hits = $search_results->num_rows;
  if ($search_results->num_rows == 0) {
    echo "<p>There were no searches in the last 12 months.</p>\n";
  } else {
    echo "<table cellpadding=\"2\" cellspacing=\"0\" style=\"font-size:100%\">\n";
    echo "<tr style=\"font-weight:bold; background:#808080; color:white\"><td>Searches</td><td>Term</td><td>Results</td></tr>\n";
    while ($row = $search_results->fetch()) {
      if ($searchstring == '') $searchstring = '&lt;no term&gt;';
      if ($hits == 0) {
        echo "<tr style=\"color:#C00000\"><td class=\"num1\">" . number_format($no_searches) . "</td><td class=\"norm1\">$searchstring</td><td class=\"num2\">" . number_format($hits) . "</td></tr>\n";
      } else {
        echo "<tr><td class=\"num1\">" . number_format($no_searches) . "</td><td class=\"norm1\">$searchstring</td><td class=\"num2\">" . number_format($hits) . "</td></tr>\n";
      }
    }
    echo "</table>\n";
  }
  $search_results->free_result();
  $search_results->close();
  
  echo "</td></tr></table>\n";

  $mysqli->close();
?>
</body>
</html>