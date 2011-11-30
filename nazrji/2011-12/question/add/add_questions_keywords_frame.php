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
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../../include/staff_auth.inc';
  $mysqli->close();
?>
<html>
<head>
<title>Add Questions</title>
</head>
  <frameset cols="200,*" frameborder="0" framespacing="0" border="0">
    <frame scrolling="auto" src="add_questions_keyword_list.php" name="keywords">
    <frame scrolling="auto" resizable="no" src="add_questions_by_keyword.php" name="keywordlist">
  </frameset>
  <noframes>
    <?php echo $string['frameserr'];?>
  </noframes>
</html>
