<?php
/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
?>
<html>
<head>
<title>Update Query Details</title>
</head>
<body>
<?php
  $log = $_GET['type'];
  echo "SELECT paper_title, user_answer, title, first_names, surname FROM ($log, properties, users) WHERE $log.q_paper=properties.property_id AND $log.userID=users.id AND $log.id=" . $_GET['id'] . "<br />\n";
  
  $result = $mysqli->prepare("SELECT paper_title, user_answer, title, first_names, surname FROM ($log, properties, users) WHERE $log.q_paper=properties.property_id AND $log.userID=users.id AND $log.id=?");
  $result->bind_param('i',$_GET['id']);
  $result->execute();  
  $result->bind_result($paper_title, $user_answer, $tmp_title, $tmp_first_names, $tmp_surname);
  $result->fetch();
  $result->close();
  
  echo "$paper_title<br />$user_anser<br />$tmp_title $tmp_surname, $tmp_first_names\n";
  $mysqli->close();
?>

</body>
</html>