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
* Class total report
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
set_time_limit(0);
ob_start();
  
function getData($url) {
  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_USERPWD, $_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSLVERSION, 3);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: en-us,en;q=0.5'));

  $output = curl_exec($ch);
  curl_close($ch);
  
  return $output;
}

function tidyLine($line) {
  $parts = explode('>', $line);
  $parts2 = explode('<', $parts[1]);
  
  return str_replace('&nbsp;', '', $parts2[0]);
}

function parseRawMarks($data) {
  $marks = array();
  $line = 0;
  $data_line = explode('<tr', $data);
  
  foreach ($data_line as $row) {
    if (strpos($row,'Display exam script') !== false) {
      $cols = explode('<td', $row);
      
      $marks[$line]['mark'] = tidyLine($cols[5]);
      $marks[$line]['percent'] = tidyLine($cols[6]);
      $marks[$line]['classification'] = tidyLine($cols[7]);

      $line++;
    }
  }
  return $marks;
}

function compareMarks($set1, $set2, &$classifications) {
  $classifications = array();
  
  $classifications[1]['Pass'] = 0;
  $classifications[2]['Pass'] = 0;
  $classifications[1]['Fail'] = 0;
  $classifications[2]['Fail'] = 0;
  $classifications[1]['Distinction'] = 0;
  $classifications[2]['Distinction'] = 0;

  $outcome = true;
  if (count($set1) != count($set2)) {
    $outcome = false;
  }

  $row_count = count($set1);

  if ($outcome) {
    for ($i=0; $i<$row_count; $i++) {
      if ($set1[$i]['mark'] != $set2[$i]['mark'] or $set1[$i]['percent'] != $set2[$i]['percent']) {
        $outcome = false;
      }
      
      if ($set1[$i]['classification'] != $set2[$i]['classification']) {
        $outcome = false;
      }
            
      $classifications[1][$set1[$i]['classification']]++;
      $classifications[2][$set2[$i]['classification']]++;
    }
  }
 
  return $outcome;
}

$papers = array();

$result = $mysqli->prepare("SELECT property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y'), DATE_FORMAT(start_date,'%Y%m%d%H%i%s'), DATE_FORMAT(end_date,'%Y%m%d%H%i%s') FROM properties WHERE paper_type = '2' AND start_date > 20110326080000 AND end_date < 20120229070000 AND deleted IS NULL ORDER BY start_date");
$result->execute();
$result->bind_result($paperID, $title, $display_start_date, $start_date, $end_date);
while ($result->fetch()) {
  $papers[] = array('paperID'=>$paperID, 'title'=>$title, 'display_start_date'=>$display_start_date, 'start_date'=>$start_date, 'end_date'=>$end_date);
}
$result->close();
?>
<html>
<head>
<title>Testing: Class Totals</title>
<style>
body {font-family:Arial,sans-serif; font-size:90%}
table {font-size:100%}
.n {text-align:right}
</style>
</head>
<body>
<?php
echo "<table border=\"1\" cellspacing=\"0\" cellpadding=\"2\" widht=\"100%\">\n";
echo "<tr><td>Start Date</td><td>Paper ID</td><td>Title</td><td>Status</td><td>Old Fails</td><td>New Fails</td><td>Old Passes</td><td>New Passes</td><td>Old Distinctions</td><td>New Distinctions</td></tr>";
foreach ($papers as $paper) {

  $url = "https://suivarro.nottingham.ac.uk/reports/class_totals.php?paperID=" . $paper['paperID'] . "&startdate=" . $paper['start_date'] . "&enddate=" . $paper['end_date'] . "&repmodule=&repcourse=%&sortby=student_id&module=A14CHH&folder=&percent=100&absent=0&direction=asc";
  $output = getData($url);
  $marks_set1 = parseRawMarks($output);
  
  $url = "https://rogo.local/reports/class_totals.php?paperID=" . $paper['paperID'] . "&startdate=" . $paper['start_date'] . "&enddate=" . $paper['end_date'] . "&repmodule=&repcourse=%&sortby=student_id&module=A14CHH&folder=&percent=100&absent=0&direction=asc";
  $output = getData($url);
  $marks_set2 = parseRawMarks($output);
  
  $same = compareMarks($marks_set1, $marks_set2, $classifications);
  
  if ($same) {
    echo '<tr>'; 
    $status = 'OK';
  } else {
    echo '<tr style="background-color:#FFC0C0">'; 
    $status = 'Problem';
  }
  echo "<td>" . $paper['display_start_date'] . "</td><td>" . $paper['paperID'] . "</td><td>" . $paper['title'] . "</td><td>$status</td><td class=\"n\">" . $classifications[1]['Fail'] . "</td><td class=\"n\">" . $classifications[2]['Fail'] . "</td><td class=\"n\">" . $classifications[1]['Pass'] . "</td><td class=\"n\">" . $classifications[2]['Pass'] . "</td><td class=\"n\">" . $classifications[1]['Distinction'] . "</td><td class=\"n\">" . $classifications[2]['Distinction'] . "</td></tr>\n"; 
  ob_flush();
  flush();  
}
echo "</table>\n";
ob_end_flush();
?>
</body>
</html>
