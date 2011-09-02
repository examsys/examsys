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

  require '../../include/staff_auth.inc';
  $mysqli->close();
  $questionlist = '';
  $question_no = '';
  if( isset($_GET['questionlist']) ) $questionlist = $_GET['questionlist'];
  if( isset($_GET['question_no']) ) $question_no = $_GET['question_no'];
 
?>
<html>
<head>
<title>Questions Bank</title>
</head>

  <frameset rows="*,32" frameborder="0" framespacing="0" border="0">
    <frameset cols="134,*" frameborder="0" framespacing="0" border="0">
      <frame scrolling="no" src="add_questions_buttons.php" name="qbuttons">
      <frame scrolling="no" src="add_questions_iframe.php" name="qlist">
    </frameset>
    <frame scrolling="no" resizable="no" src="add_random_question_controls.php?q_no=<?php echo $_GET['q_no']; ?>&questionlist=<?php echo $questionlist; ?>&question_no=<?php echo $question_no; ?>" name="controls">
  </frameset>
  <noframes>
    Sorry, you need frames to use the TouchStone.
  </noframes>
</html>
