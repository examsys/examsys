<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
  
  if ($_GET['type'] == 'paper') {
    // Get the paper title of the restored paper.
    $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE property_id=?");
    $result->bind_param('i', $_GET['item_id']);
    $result->execute();  
    $result->bind_result($deleted_paper_title);
    $result->fetch();
    $result->close();
    
    // Check to see if the original paper name has been reused by any active papers.
    $split_title = explode('[deleted',$deleted_paper_title);
    $tmp_title = trim($split_title[0]);
    $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE paper_title=? and property_id!=?");
    $result->bind_param('si', $tmp_title, $_GET['item_id']);
    $result->execute();  
    $result->store_result();
    $result->bind_result($paper_title);
    $result->fetch();
    
    if ($result->num_rows == 0) {
      $new_title = trim($split_title[0]);
    } else {
      $new_title = $deleted_paper_title;
    }
    $result->close();
    
    $restore = $mysqli->prepare("UPDATE properties SET deleted=NULL, paper_title=? WHERE property_id=?");
    $restore->bind_param('si', $new_title, $_GET['item_id']);
    $restore->execute();  
    $restore->close();
    
    $result = $mysqli->prepare("SELECT question, deleted FROM (papers, questions) WHERE paper=? AND papers.question=questions.q_id");
    $result->bind_param('i', $_GET['item_id']);
    $result->execute();  
    $result->store_result();
    $result->bind_result($question, $deleted);
    while ($row = $result->fetch()) {
      if ($deleted != '') {
        // If the question has been deleted in the question bank then remove from the paper.
        $deleteQuery = $mysqli->prepare("DELETE FROM papers WHERE paper=? AND question=?");
        $deleteQuery->bind_param('ii', $_GET['item_id'], $question);
        $deleteQuery->execute();  
        $deleteQuery->close();
      }
    }
  } elseif ($_GET['type'] == 'folder') {
    $restore = $mysqli->prepare("UPDATE folders SET deleted=NULL WHERE id=?");
    $restore->bind_param('i', $_GET['item_id']);
    $restore->execute();  
    $restore->close();
  } elseif ($_GET['type'] == 'question') {
    $restore = $mysqli->prepare("UPDATE questions SET deleted=NULL WHERE q_id=?");
    $restore->bind_param('i', $_GET['item_id']);
    $restore->execute();  
    $restore->close();
  }
  $mysqli->close();
  
  header("location: " . $protocol . $_SERVER['HTTP_HOST'] . "/touchstone/delete/recycle_list.php");
?>