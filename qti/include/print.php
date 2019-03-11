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
 * @author Adam Clarke
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$nice_id = 1;
$pr_p_head = 0;

function OutputHeader_pp() {
  global $pr_p_head;
  if ($pr_p_head) return;
  $pr_p_head = 1;
?>

<style type="text/css">
body {font-family:Arial,sans-serif; font-size:90%}
.print_cont {
	/*border: 1px solid #CCCCCC;*/
	margin:6px;
	background-color:#EEFFEE;
}

.print_head {
	/*font-size:100%;*/
	padding: 4px;
}

.print_body {
	display: none;
	/*border-top: 1px solid #CCCCCC;*/
}

.print_label {
	font-weight: bold;
	/*font-size:80%;*/
}

.print_variable {
	/*font-size:80%;*/
}

.print_raw {
	font-size:85%;
}

.print_raw pre {
	background-color:#FFEEEE;
	padding:6px;
}
</style>
<script>
  function print_nice_expand(id) {
    $('print_nice_' + id).style.display = 'inline';
  }

  function print_nice_contract(id) {
    $('print_nice_' + id).style.display = 'none';
  }

  function print_nice_expand_all(id) {
    print_nice_expand(id)
  }

  function print_nice_toggle_raw(id) {
    if ($('print_nice_raw_' + id).style.display == 'none') {
      $('print_nice_raw_' + id).style.display = 'inline';
    } else {
      $('print_nice_raw_' + id).style.display = 'none';
    }
  }
</script>

<?php
}

function print_p($elem, $expandfirst = true, $trim = 100, $max_level = 10, $print_nice_stack = array()) {
	$configObject = Config::get_instance();
  $base_dir = $configObject->get('cfg_root_path');

  OutputHeader_pp();
  global $nice_id;
  if (is_array($elem) || is_object($elem)) {
    if (in_array($elem, $print_nice_stack, true)) {
      echo "<font color=red>RECURSION</font>";
      return;
    }
    $print_nice_stack[] =& $elem;
    if ($max_level < 1) {
      echo "<font color=red>nivel maximo alcanzado</font>";
      return;
    }
    $max_level--;
    //echo "<table border=0 cellspacing=0 cellpadding=3 width=100%>";

    $title = '';
    $isarray = false;
    if (is_array($elem)) {
      $title = "<img src='$base_dir/qti/artwork/array.png' width='16' height='16'> Array (".count($elem).")";
      $isarray = true;
      //echo '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>ARRAY</font></strong></td></tr>';
      } else {
      //echo '<tr><td colspan=2 style="background-color:#333333;"><strong>';
      //echo '<font color=white>+ * - OBJECT Type: '.get_class($elem).'</font></strong></td></tr>';
      $title = "<img src='$base_dir/qti/artwork/class.png' width='16' height='16'> Object Type: ".get_class($elem);

      //			if (is_callable(array(get_class($elem),"__toString")))
      if (is_callable(array($elem, "__toString"))) {
        //if (get_class($elem) == "ST_QTI12_Material")
        //{
        $title .= " (".trim(substr($elem->__toString(), 0, 125)).")";
        //}		
        }
    }
    echo "<div class='print_cont'>";
    echo "<div class='print_head'>";
    echo "<img src='$base_dir/qti/artwork/plus.png' width='16' height='16' onclick='print_nice_expand(".$nice_id.")'>";
    echo "<img src='$base_dir/qti/artwork/star.png' width='16' height='16' onclick='print_nice_expand_all(".$nice_id.")'>";
    echo "<img src='$base_dir/qti/artwork/minus.png' width='16' height='16' onclick='print_nice_contract(".$nice_id.")'>";
    echo "<img src='$base_dir/qti/artwork/raw.png' width='16' height='16' onclick='print_nice_toggle_raw(".$nice_id.")'>";
    echo "&nbsp;&nbsp;";
    echo $title."</div>";
    echo "<div class='print_raw' id='print_nice_raw_".$nice_id."' style=\"display:none\"><pre>";
    print_r($elem);
    echo "</pre></div>";
    $style = '';
    if ($expandfirst && ($max_level == 9 || $isarray)) $style = "display:inline;";

    echo "<div class='print_body' id='print_nice_".$nice_id."' style='".$style."'>";
    $nice_id++;
    if (is_array($elem) and count($elem) > 0) {
      echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" style=\"width:100%\">";
      $color = 0;
      foreach ($elem as $k => $v) {
        if ($max_level % 2) {
          $rgb = ($color++ % 2) ? "#DDDDDD" : "#EEEEEE";
        } else {
          $rgb = ($color++ % 2) ? "#DDDDFF" : "#EEEEFF";
        }
        //$rgb = '#FFFFFF';
        echo '<tr bgcolor="'.$rgb.'"><td valign="top" class="print_label" nowrap width="0%">';
        echo $k;
        echo '</td><td class="print_variable" width="100%">';
        print_p($v, $expandfirst, $trim, $max_level, $print_nice_stack);
        echo "</td></tr>";
      }
      echo "</table>";
    } else {
      echo "<font color=green>Empty Array</font>";
    }
    echo "</div>";
    echo "</div>";
    return;
  }
  if ($elem === null) {
    echo "<font color=green>NULL</font>";
  } elseif ($elem === 0) {
    echo "0";
  } elseif ($elem === true) {
    echo "<font color=green>TRUE</font>";
  } elseif ($elem === false) {
    echo "<font color=green>FALSE</font>";
  } elseif ($elem === "") {
    echo "<font color=green>EMPTY STRING</font>";
  } else {
    $out = "";
    $elem = htmlentities($elem);
    if (strlen($elem) > $trim) {
      while ($elem != "") {
        $out .= substr($elem, 0, $trim)."<strong><font color=blue>*</font></strong><br />";
        $elem = substr($elem, $trim);
      }
      $elem = $out;
    }
    echo str_replace("\n", "<strong><font color=red>*</font></strong><br>\n", $elem);
  }
}

?>