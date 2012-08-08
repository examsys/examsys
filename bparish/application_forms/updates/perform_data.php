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
set_time_limit(0);
ob_start();

$end_dateSQL = 'NOW()';
if (isset($_GET['period'])) {
  if ($_GET['period'] == 'week') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 WEEK)';
  } elseif ($_GET['period'] == 'month') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 MONTH)';
  } elseif ($_GET['period'] == 'year') {
    $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 1 YEAR)';
  }
} else {
  $start_dateSQL = 'SUBDATE(NOW(), INTERVAL 7 YEAR)';
}

if (isset($_GET['server'])) {
  $server = $_GET['server'];
} else {
  $server = $protocol . $_SERVER['HTTP_HOST'];
}

  
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

$papers = array();
$result = $mysqli->prepare("SELECT crypt_name, property_id, paper_title, DATE_FORMAT(start_date,'%d/%m/%Y'), DATE_FORMAT(start_date,'%Y%m%d%H%i%s'), DATE_FORMAT(end_date,'%Y%m%d%H%i%s') FROM properties WHERE paper_type = '2' AND start_date > $start_dateSQL AND end_date < $end_dateSQL AND deleted IS NULL ORDER BY start_date");
$result->execute();
$result->bind_result($crypt_name, $paperID, $title, $display_start_date, $start_date, $end_date);
while ($result->fetch()) {
  $papers[] = array('crypt_name'=>$crypt_name, 'paperID'=>$paperID, 'title'=>$title, 'display_start_date'=>$display_start_date, 'start_date'=>$start_date, 'end_date'=>$end_date);
}
$result->close();
?>
<html>
<head>
<title>Upgrade: Populating Performance Data</title>
<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%}
table {font-size:100%}
.n {text-align:right}
</style>
</head>
<body>
<?php
$paper_no = count($papers);
$current_no = 0;

foreach ($papers as $paper) {
  $url = $server . "/reports/frequency_discrimination_analysis.php?paperID=" . $paper['paperID'] . "&startdate=" . $paper['start_date'] . "&enddate=" . $paper['end_date'] . "&repmodule=&repcourse=%&sortby=name&module=A14ACE&folder=&percent=100&absent=0&studentsonly=1&direction=asc";
  
  $output = getData($url);
    
  $current_no++;
  echo "<br /><strong>$current_no/$paper_no Reading paperID " . $paper['paperID'] . "...</strong><br />\n";
  ob_flush();
  flush();
}

ob_end_flush();
?>
Finished<br />
</body>
</html>
