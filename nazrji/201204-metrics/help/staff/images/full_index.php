<?php
  include('../staff_student_auth.inc');
?>
<html>
<head>
<title>Help and Support Center</title>
<style>
body {margin:6px; background-color:#DDECFE; color:black; font-family:Tahoma,Arial,sans-serif}
div {line-height:180%; font-size:80%}
a:link {color:blue}
a:visited {color:blue}
</style>
</head>
<body oncontextmenu="return false;">
<div style="font-size:80%; text-align: center"><strong>Index</strong> | <a href="search.php">Search</a></div>
<hr noshade size="1" style="color: black" width="100%" />

<?php
  if ($_SERVER['SERVER_PORT'] == 443) {
    $protocol = 'https://';
  } else {
    $protocol = 'http://';
  }
  
  $query_string = "SELECT id, title FROM student_help ORDER BY title";
  $search_results = mysql_query($query_string,$link_id);
  while ($row = mysql_fetch_array($search_results)) {
    echo "<div><img src=\"" . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/staff_help/single_page.png\" id=\"button$i\" width=\"16\" height=\"16\" alt=\"\" border=\"0\" />&nbsp;<a href=\"display_page.php?id=" . $row['id'] . "\" target=\"content\"><nobr>" . $row['title'] . "</nobr></a></div>\n";
  }
?>

</body>
</html>