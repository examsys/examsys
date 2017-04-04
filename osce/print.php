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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require '../include/errors.php';

$paperID = check_var('paperID', 'GET', true, false, true);

//get the paper properties
$propertyObj = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
?>
  <html>
  <head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>OSCE: Marking Form</title>
  <style type="text/css">
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
  <h1><?php echo $propertyObj->get_paper_title(); ?></h1>
  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
  <tr>
  <td style="text-align:left"><strong><?php echo $string['student']; ?></strong></td>
  <td style="text-align:left"><strong><?php echo $string['examiner']; ?></strong></td>
  </tr>
  </table>
  
  <br />
  
  <table cellpadding="2" cellspacing="0" border="0">
<?php

  // Get the questions.
  $question_no = 1;
  $cell_colors = array('#FF8080','#FFC169','#50E850');
  $result = $mysqli->prepare("SELECT q_id, q_type, theme, notes, scenario, leadin, display_method FROM papers, questions WHERE papers.paper = ? AND papers.question = questions.q_id ORDER BY display_pos");
  $result->bind_param('i', $paperID);
  $result->execute();
  $result->bind_result($q_id, $q_type, $theme, $notes, $scenario, $leadin, $display_method);
  while ($result->fetch()) {
    if ($question_no == 1) {
      // Header row
      $cols = substr_count($display_method, '|');
      $headings = explode('|', $display_method);
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
      echo "<span style=\"color:$labelcolor\"><img src=\"../artwork/notes_icon.gif\" width=\"16\" height=\"16\" alt=\"note\" />&nbsp;$notes</span><br />\n";
    }
    echo "$leadin</td>";
    for ($i=0; $i<$cols; $i++) {
      echo "<td>[&nbsp;&nbsp;&nbsp;]</td>";
    }
    echo "</tr>\n";
    $question_no++;
  }
  $result->close();
?>  
  </table>
  
  <br /><div><strong><?php echo $string['overallclassification']; ?></strong></div>
  <br />
  <div><?php echo $string['msg']; ?></div>
  <br />

  <table cellpadding="2" cellspacing="0" border="0" style="width:100%">
  <tr>
  <?php
    $marking = $propertyObj->get_marking();

    if ($marking == '3') {
      echo '<td>[' . $string['clear fail'] . ']</td><td class="overall">[' . $string['borderline'] . ']</td><td class="overall">[' . $string['clear pass'] . ']</td>';
    } elseif ($marking == '4') {
      echo '<td>[' . $string['fail'] . ']</td><td class="overall">[' . $string['borderline fail'] . ']</td><td class="overall">[' . $string['borderline pass'] . ']</td><td class="overall">[' . $string['pass'] . ']</td><td class="overall">[' . $string['good pass'] . ']</td>';
    } else {
      echo '<td>[' . $string['clear fail'] . ']</td><td class="overall">[' . $string['borderline'] . ']</td><td class="overall">[' . $string['clear pass'] . ']</td><td class="overall">[' . $string['honours pass'] . ']</td>';
    }
  ?>
  </tr>
  </table>
  <br />
  <div><strong><?php echo $string['feedback']; ?></strong></div>

<?php
  $mysqli->close();
?>
</body>
</html>
