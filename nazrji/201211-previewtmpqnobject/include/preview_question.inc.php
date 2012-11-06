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
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2012 The University of Nottingham
 * @package
 */

?>
<div id="maincontent">
  <table cellpadding="4" cellspacing="0" border="0" width="100%" style="table-layout:fixed">
    <col width="40"><col>
<?php
$question_no = 0;
$paper_type = 0;
$unanswered = false;
$user_answers[1] = array();
display_question($question, $paper_type, 1, '', $question_no, $question_offset, $user_answers, $unanswered);
?>
<table>
</div>
