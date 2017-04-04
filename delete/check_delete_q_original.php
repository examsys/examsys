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
* Confirm that it is OK to proceed deleting a question in the bank.
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

check_var('q_id', 'GET', true, false, false);

$icons = array('formative', 'progress', 'summative', 'survey', 'osce', 'offline', 'peer_review');

?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['confirmdelete']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>
<body>

<?php
  $qIDs = substr($_GET['q_id'], 1);

  // We need to check if the question is in a random block or a keyword block.
  // If so we should add those ids to qIDs to be search un the query below.
  $questions = explode(',', $qIDs);
  $questionutils = new QuestionUtils();
  foreach ($questions as $qid) {
    $random = $questionutils->is_in_random_block($qid, $mysqli);
    if (sizeof($random) > 0) {
        $qIDs = $qIDs . ", " . implode(',', $random);
    }
  }

  foreach ($questions as $qid) {
    $keyword = $questionutils->is_in_keyword_block($qid, $mysqli);
    if (sizeof($keyword) > 0) {
        $qIDs = $qIDs . ", " . implode(',', $keyword);
    }
  }

  // Search for question usage.
  $result = $mysqli->prepare("SELECT DISTINCT paper_title, paper, paper_type FROM (papers, properties) WHERE papers.paper = properties.property_id AND properties.deleted IS NULL AND question IN ($qIDs)");
  $result->execute();  
  $result->store_result();
  $result->bind_result($paper_title, $paper, $paper_type);

  if ($result->num_rows == 0) {
  ?>
<p><?php echo $string['msg']; ?></p>

<div class="button_bar">
<form action="do_delete_q_original.php" method="post" autocomplete="off">
<input type="hidden" name="q_id" value="<?php echo $_GET['q_id']; ?>" />
<input class="delete" type="submit" name="submit" value="<?php echo $string['delete']; ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="window.close();" />
</form>
</div>
    <?php
  } else {
    echo "<p>" . $string['warning1'] . "</p>\n<blockquote>\n";
    while ($result->fetch()) {
      echo "<img src=\"../artwork/" . $icons[$paper_type] . "_16.gif\" width=\"16\" height=\"16\" alt=\"\" />&nbsp;" . $paper_title . "<br />\n";
    }
    echo "</blockquote>\n";
  ?>
<p><?php echo $string['warning2']; ?></p>
<div style="text-align:right">
<form action="do_delete_q_original.php" method="post" autocomplete="off">
<input type="hidden" name="q_id" value="<?php echo $_GET['q_id']; ?>" />
<input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="window.close();" />
</form>
</div>
    <?php
  }
  $result->free_result();
  $result->close();
  $mysqli->close();
?>
</body>
</html>