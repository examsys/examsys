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

  require '../touchstone/include/staff_auth.inc';

  // Get properties of the paper.
  $result = $mysqli->prepare("SELECT paper_title FROM properties WHERE property_id=?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($paper_title);
  $result->fetch();
  $result->close();
?>
  <html>
  <head>
  <title>OSCE: Marking Form</title>
  <style>
    body {font-family:Arial,sans-serif; font-size:90%; color:black}
    h1 {font-weight:bold; font-size:150%}
    table {font-size:100%; border-collapse:collapse}
    td {text-align:center}
    .question {text-align:left}
    .theme {text-align:left; font-size:125%; font-weight:bold; padding-top:10px}
    .overall {width:20%; text-align:center}
    ul {margin-top:0px; margin-bottom:0px}
  </style>
  </head>
  
  <body>
  <h1><?php echo $paper_title; ?></h1>
  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
  <tr>
  <td style="text-align:left"><strong>Student:</strong></td>
  <td style="text-align:left"><strong>Examiner:</strong></td>
  </tr>
  </table>
  
  <br />
  
  <table cellpadding="2" cellspacing="0" border="0">
<?php

  // Get the questions.
  $question_no = 1;
  $cell_colors = array('#FF8080','#FFC169','#50E850');
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, notes, scenario, leadin, score_method FROM papers, questions WHERE paper=? AND papers.question=questions.q_id ORDER BY display_pos");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($q_id, $q_type, $theme, $notes, $scenario, $leadin, $score_method);
  while ($row = $result->fetch()) {
    if ($question_no == 1) {
      // Header row
      $cols = substr_count($score_method,"|");
      $headings = explode("|",$score_method);
      echo '<tr><td></td>';
      for ($i=0; $i<$cols; $i++) {
        echo "<td style=\"width:80px; font-weight:bold\">" . $headings[$i] . "</td>";
      }
      echo "</tr>\n";
    }
    if (trim($theme) != '') {
      echo "<tr><td colspan=\"4\" class=\"theme\">$theme</td></tr>\n";
    }
    echo "<tr><td class=\"question\">";
    if (trim($notes) != '') {
      echo "<span style=\"color:$labelcolor\"><img src=\"../touchstone/artwork/notes_icon.gif\" width=\"14\" height=\"14\" border=\"0\" alt=\"note\" />&nbsp;$notes</span><br />\n";
    }
    echo "$leadin</td>";
    for ($i=0; $i<$cols; $i++) {
      echo "<td>[&nbsp;&nbsp;&nbsp;]</td>";
    }
    echo "</tr>\n";
    $question_no++;
  }
?>  
  </table>
  
  <br /><div><strong>Overall Classification:</strong></div>
  <br />
  <div>Please circle the most appropriate grading</div>
  <br />

  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
  <tr><td>[Fail]</td><td class="overall">[Borderline Fail]</td><td class="overall">[Borderline pass]</td><td class="overall">[Pass]</td><td class="overall">[Good Pass]</td>
  </tr>
  </table>
  <br />
  <div><strong>Feedback:</strong></div>

<?php
  $result->close();
  $mysqli->close();
?>
</body>
</html>
