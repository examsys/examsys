<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

$stateutil = new StateUtils($userObject->get_user_ID(), $mysqli);
$state = $stateutil->getState($configObject->get('cfg_root_path') . '/paper/search.php');
?>

<div id="left-sidebar" class="sidebar">
<div style="padding-right:16px">
<form name="PapersMenu" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">

<div><strong><?php echo $string['wordorphrase']; ?></strong><br /><input type="text" name="searchterm" id="searchterm" size="25" style="width:98%" value="<?php if (isset($_POST['searchterm'])) {
    echo $_POST['searchterm'];
             } ?>" /></div>

<?php
if (isset($_POST['submit'])) {
    if (isset($_POST['formative']) and $_POST['formative'] == 1) {
        $radio[0] = ' checked';
    } else {
        $radio[0] = '';
    }
    if (isset($_POST['progress']) and $_POST['progress'] == 1) {
        $radio[1] = ' checked';
    } else {
        $radio[1] = '';
    }
    if (isset($_POST['summative']) and $_POST['summative'] == 1) {
        $radio[2] = ' checked';
    } else {
        $radio[2] = '';
    }
    if (isset($_POST['survey']) and $_POST['survey'] == 1) {
        $radio[3] = ' checked';
    } else {
        $radio[3] = '';
    }
    if (isset($_POST['osce']) and $_POST['osce'] == 1) {
        $radio[4] = ' checked';
    } else {
        $radio[4] = '';
    }
    if (isset($_POST['offline']) and $_POST['offline'] == 1) {
        $radio[5] = ' checked';
    } else {
        $radio[5] = '';
    }
    if (isset($_POST['peerreview']) and $_POST['peerreview'] == 1) {
        $radio[6] = ' checked';
    } else {
        $radio[6] = '';
    }
} else {
    $radio[0] = ' checked';
    $radio[1] = ' checked';
    $radio[2] = ' checked';
    $radio[3] = ' checked';
    $radio[4] = ' checked';
    $radio[5] = ' checked';
    $radio[6] = ' checked';
}
?>

<div><label><input type="checkbox" name="formative" id="formative" value="1"<?php echo $radio[0]; ?> /><?php echo $string['formative'] ?></label></div>
<div><label><input type="checkbox" name="progress" id="progress" value="1"<?php echo $radio[1]; ?> /><?php echo $string['progresstest'] ?></label></div>
<div><label><input type="checkbox" name="summative" id="summative" value="1"<?php echo $radio[2]; ?> /><?php echo $string['summative'] ?></label></div>
<div><label><input type="checkbox" name="survey" id="survey" value="1"<?php echo $radio[3]; ?> /><?php echo $string['survey'] ?></label></div>
<div><label><input type="checkbox" name="osce" id="osce" value="1"<?php echo $radio[4]; ?> /><?php echo $string['oscestation'] ?></label></div>
<div><label><input type="checkbox" name="offline" id="offline" value="1"<?php echo $radio[5]; ?> /><?php echo $string['offlinepaper'] ?></label></div>
<div><label><input type="checkbox" name="peerreview" id="peerreview" value="1"<?php echo $radio[6]; ?> /><?php echo $string['peerreview'] ?></label></div>

<br />

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr class="accessibility"><td><a href="#" style="font-weight:bold; color:black"><?php echo $string['accessibility'] ?></a></td>
  <td align="right"><a href="#"><?php
    if (isset($state['menu6']) and $state['menu6'] == 'table') {
        echo '<img id="icon6" src="../artwork/up_arrow_icon.gif" width="10" height="9" alt="Hide" />';
    } else {
        echo '<img id="icon6" src="../artwork/down_arrow_icon.gif" width="10" height="9" alt="Show" />';
    }
    ?></a></td></tr>
  </table>

<?php
if (isset($state['menu6'])) {
    echo '<table id="menu6" style="margin-left:12px; display:' . $state['menu6'] . "\">\n";
} else {
    echo "<table id=\"menu6\" style=\"margin-left:12px; display:none\">\n";
}
?>
<tr><td><div style="margin-left:4px"><?php echo $string['date']; ?>&nbsp;</div></td><td>
<?php
  echo "<select name=\"day\">\n";
  echo "<option value=\"\"></option>\n";
for ($i = 1; $i <= 31; $i++) {
    $display_data = $i;
    if ($i < 10) {
        $display_data = '0' . $i;
    }
    if (isset($_POST['day']) and $display_data == $_POST['day']) {
        echo "<option value=\"$display_data\" selected>$display_data</option>\n";
    } else {
        echo "<option value=\"$display_data\">$display_data</option>\n";
    }
}
  echo "</select><select name=\"month\">\n";
  echo "<option value=\"\"></option>\n";
for ($i = 1; $i <= 12; $i++) {
    $display_data = $i;
    if ($i < 10) {
        $display_data = '0' . $i;
    }
    if (isset($_POST['month']) and $display_data == $_POST['month']) {
        echo "<option value=\"$display_data\" selected>$display_data</option>\n";
    } else {
        echo "<option value=\"$display_data\">$display_data</option>\n";
    }
}
  echo "</select><select name=\"year\">\n";
  echo "<option value=\"\"></option>\n";
for ($i = 2000; $i <= date('Y') + 10; $i++) {
    if (isset($_POST['year']) and $i == $_POST['year']) {
        echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
        echo "<option value=\"$i\">$i</option>\n";
    }
}
?>
</select>
</td></tr>
<tr><td><div style="margin-left:4px"><?php echo $string['lab']; ?>&nbsp;</div></td><td><select style="width:150px" name="lab"><option value=""><?php echo $string['anylab'] ?></option>
<?php
  $old_campus = '';

  $result = $mysqli->prepare('SELECT DISTINCT labs.id, labs.name, campus.name FROM labs, campus
    WHERE labs.campus = campus.id ORDER BY campus.name, labs.name');
  $result->execute();
  $result->bind_result($lab_id, $lab_name, $campus);
  while ($result->fetch()) {
      if ($old_campus != $campus) {
          echo "<optgroup label=\"$campus\">\n";
      }
      if (isset($_POST['lab']) and $lab_id == $_POST['lab']) {
          echo "<option value=\"$lab_id\" selected>$lab_name</option>\n";
      } else {
          echo "<option value=\"$lab_id\">$lab_name</option>\n";
      }
      $old_campus = $campus;
  }
  $result->close();
  echo "</optgroup>\n";
    ?>
</select></td></tr>
<tr><td>&nbsp;</td></tr>
</table>

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr class="ownership"><td><a href="#" style="font-weight:bold; color:black"><?php echo $string['ownership'] ?></a></td>
  <td align="right"><a href="#"><?php
    if (isset($state['menu9']) and $state['menu9'] == 'block') {
        echo '<img id="icon9" src="../artwork/up_arrow_icon.gif" width="10" height="9" alt="Hide" />';
    } else {
        echo '<img id="icon9" src="../artwork/down_arrow_icon.gif" width="10" height="9" alt="Show" />';
    }
    ?></a></td></tr>
  </table>

<?php
if (isset($_GET['module'])) {
    $state['menu9'] = 'block';
}
if (isset($state['menu9'])) {
    echo '<div id="menu9" style="margin-left:15px; display:' . $state['menu9'] . "\">\n";
} else {
    echo "<div id=\"menu9\" style=\"margin-left:15px; display:none\">\n";
}
?>
  <div style="margin-left:4px"><?php echo $string['module'] ?> <?php search_utils::display_staff_modules_dropdown($userObject, $string, $mysqli); ?></div>
  <div style="margin-left:4px"><?php echo $string['owner'] ?> <?php search_utils::display_owners_dropdown($userObject, $mysqli, 'papers', $string, $state, 100); ?></div>
</div>

<br />
<div style="text-align:center"><input class="ok" type="submit" name="submit" value="<?php echo $string['search'] ?>" /></div>
</div>

</form>
</div>
