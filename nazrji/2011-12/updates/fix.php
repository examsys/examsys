<?php
  require '../include/staff_auth.inc';

  $old_userID = -1;
  
  $query = $mysqli->prepare("SELECT id, userID, ipaddress FROM log_metadata WHERE paperID=4118 ORDER BY userID, id");
  $query->execute();
  $query->bind_result($id, $userID, $ipaddress);
  $query->store_result();
  while ($query->fetch()) {
    if ($userID == $old_userID) {
      echo "<span style=\"color:red\">$id - $userID</span><br />";
      
      $del_query = $mysqli->prepare("DELETE FROM log_metadata WHERE id=$id");
      $del_query->execute();
      $del_query->close();
    } else {
      echo "$id - $userID<br />";
    }
    $old_userID = $userID;
  }
  $query->close();

?>