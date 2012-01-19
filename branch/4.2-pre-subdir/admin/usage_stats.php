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
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';

  $years = array('2007','2008','2009');

  echo "<html><body><table>";


  foreach($years as $y) {

    for($m = 1; $m <= 12; $m++) {

      echo "<tr><td>$y</td><td>$m</td>";

      for($log = 0; $log <= 3; $log++) {
        if($m < 12) {
          $sql = "select distinct q_paper, username, started from log$log where started > '$y-$m-01 00:00:00' AND started < '$y-" . ($m + 1) . "-01 00:00:00'";
        } else {
          $sql = "select distinct q_paper, username, started from log$log where started > '$y-$m-01 00:00:00' AND started < '" . ($y + 1) . "-01-01 00:00:00'";
        }

        $results = $mysqli->query($sql);

        echo "<td>"  . $results->num_rows .  "</td>";

      }

      echo "</tr>";

    }

  }

  echo "</table></body></html>";

?>