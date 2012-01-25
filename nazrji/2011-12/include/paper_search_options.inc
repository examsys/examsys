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

require_once '../classes/schoolutils.class.php';
require_once '../classes/searchutils.class.php';
?>
<script language="JavaScript">
  function updateCookies(ID) {
    if (document.getElementById('checkbox' + ID).checked == 1) {
      setting = " checked";
    } else {
      setting = "";
    }

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    if (ID == 6) {
      NameOfCookie = "myquestions";
    } else {
      NameOfCookie = "papersearch[" + ID + "]";
    }
    document.cookie = NameOfCookie + "=" + setting +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
  }

  function updateMenu(ID) {
    current = (document.getElementById('menu' + ID).style.display == 'block') ? 'none' : 'block';
    document.getElementById('menu' + ID).style.display = current;

    <?php
      echo "icon = (document.getElementById('icon' + ID).getAttribute('src') == '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/down_arrow_icon.gif') ? '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/up_arrow_icon.gif' : '$protocol" . $_SERVER['HTTP_HOST'] . "{$cfg_root_path}/artwork/down_arrow_icon.gif';\n";
    ?>
    alttag = (document.getElementById('icon' + ID).getAttribute('alt') == 'Hide') ? 'Show' : 'Hide';
    document.getElementById('icon' + ID).setAttribute('src',icon);
    document.getElementById('icon' + ID).setAttribute('alt',alttag);

    var ExpireDate = new Date ();
    expiredays = 100;
    ExpireDate.setTime(ExpireDate.getTime() + (expiredays * 24 * 3600 * 1000));
    NameOfCookie = "papersearch[" + ID + "]";
    document.cookie = NameOfCookie + "=" + current +  ((expiredays == null) ? "" : "; expires=" + ExpireDate.toGMTString());
  }
</script>

<div id="left-sidebar" class="sidebar">
<form name="PapersMenu" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<br />

<table cellpadding="4" cellspacing="0" border="0" width="185">
<tr><td>
<div><strong><?php echo $string['wordorphrase']; ?></strong><br /><input type="text" name="searchterm" id="searchterm" size="25" style="width:98%" value="<?php if (isset($_POST['searchterm'])) echo $_POST['searchterm']; ?>" /></div>
<table cellpadding="0" cellspacing="0" border="0">
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
  } else {
    $radio[0] = ' checked';
    $radio[1] = ' checked';
    $radio[2] = ' checked';
    $radio[3] = ' checked';
    $radio[4] = ' checked';
    $radio[5] = ' checked';
  }
?>

<tr><td><input type="checkbox" name="formative" value="1"<?php echo $radio[0]; ?> /></td><td><?php echo $string['formative']; ?></td></tr>
<tr><td><input type="checkbox" name="progress" value="1"<?php echo $radio[1]; ?> /></td><td><?php echo $string['progresstest']; ?></td></tr>
<tr><td><input type="checkbox" name="summative" value="1"<?php echo $radio[2]; ?> /></td><td><?php echo $string['summative']; ?></td></tr>
<tr><td><input type="checkbox" name="survey" value="1"<?php echo $radio[3]; ?> /></td><td><?php echo $string['survey']; ?></td></tr>
<tr><td><input type="checkbox" name="osce" value="1"<?php echo $radio[4]; ?> /></td><td><?php echo $string['oscestation']; ?></td></tr>
<tr><td><input type="checkbox" name="offline" value="1"<?php echo $radio[5]; ?> /></td><td><?php echo $string['offlinepaper']; ?></td></tr>
</table>

<br />

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-size:110%; font-weight:bold; color:black" onclick="updateMenu(6);"><?php echo $string['accessibility']; ?></a></td>
  <td align="right"><a href="#" onclick="updateMenu(6);"><?php
    if (isset($_COOKIE['papersearch'][6]) and $_COOKIE['papersearch'][6] == 'block') {
      echo '<img id="icon6" src="../artwork/up_arrow_icon.gif" width="9" height="9" alt="Hide" border="0" />';
    } else {
      echo '<img id="icon6" src="../artwork/down_arrow_icon.gif" width="9" height="9" alt="Show" border="0" />';
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($_COOKIE['papersearch'][6])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu6\" style=\"display:" . $_COOKIE['papersearch'][6] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu6\" style=\"display:none\">\n";
  }
?>
<tr><td><div style="margin-left:4px"><?php echo $string['date']; ?>&nbsp;</div></td><td>
<?php
  echo "<select name=\"day\">\n";
  echo "<option value=\"\"></option>\n";
  for ($i=1; $i<=31; $i++) {
    $display_data = $i;
    if ($i < 10) $display_data = '0' . $i;
    if (isset($_POST['day']) and $display_data == $_POST['day']) {
      echo "<option value=\"$display_data\" selected>$display_data</option>\n";
    } else {
      echo "<option value=\"$display_data\">$display_data</option>\n";
    }
  }
  echo "</select><select name=\"month\">\n";
  echo "<option value=\"\"></option>\n";
  for ($i=1; $i<=12; $i++) {
    $display_data = $i;
    if ($i < 10) $display_data = '0' . $i;
    if (isset($_POST['month']) and $display_data == $_POST['month']) {
      echo "<option value=\"$display_data\" selected>$display_data</option>\n";
    } else {
      echo "<option value=\"$display_data\">$display_data</option>\n";
    }
  }
  echo "</select><select name=\"year\">\n";
  echo "<option value=\"\"></option>\n";
  for ($i=2000; $i<=date("Y")+10; $i++) {
    if (isset($_POST['year']) and $i == $_POST['year']) {
      echo "<option value=\"$i\" selected>$i</option>\n";
    } else {
      echo "<option value=\"$i\">$i</option>\n";
    }
  }
?>
</select>
</td></tr>
<tr><td><div style="margin-left:4px"><?php echo $string['lab']; ?>&nbsp;</div></td><td><select style="width:150px" name="lab"><option value=""><?php echo $string['anylab']; ?></option>
<?php
  $old_campus = '';
  $search_results = $mysqli->query("SELECT DISTINCT id, name, campus FROM labs ORDER BY campus, name");
  while ($row = $search_results->fetch_assoc()) {
    if ($old_campus != $row['campus']) {
      echo "<optgroup label=\"" . $row['campus'] . "\">\n";
    }
    if (isset($_POST['lab']) and $row['id'] == $_POST['lab']) {
      echo "<option value=\"" . $row['id'] . "\" selected>" . $row['name'] . "</option>\n";
    } else {
      echo "<option value=\"" . $row['id'] . "\">" . $row['name'] . "</option>\n";
    }
    $old_campus = $row['campus'];
  }
  $search_results->close();
  echo "</optgroup>\n";  
?>
</select></td></tr>
<tr><td>&nbsp;</td></tr>
</table>

  <table cellpadding="4" cellspacing="0" border="0" width="100%">
  <tr><td><a href="#" style="font-weight:bold; color:black" onclick="updateMenu(9);"><?php echo $string['ownership']; ?></a></td>
  <td align="right"><a href="#" onclick="updateMenu(9);"><?php
    if (isset($_COOKIE['papersearch'][9]) and $_COOKIE['papersearch'][9] == 'block') {
      echo "<img id=\"icon9\" src=\"../artwork/up_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Hide\" border=\"0\" />";
    } else {
      echo "<img id=\"icon9\" src=\"../artwork/down_arrow_icon.gif\" width=\"9\" height=\"9\" alt=\"Show\" border=\"0\" />";
    }
  ?></a></td></tr>
  </table>

<?php
  if (isset($_COOKIE['papersearch'][9])) {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu9\" style=\"display:" . $_COOKIE['papersearch'][9] . "\">\n";
  } else {
    echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" id=\"menu9\" style=\"display:none\">\n";
  }
?>
  <tr><td>
  <div style="margin-left:4px">Module: <?php SearchUtils::displayTeamDropdown($teams, $userroles, $userID, $mysqli); ?></div>
  <div style="margin-left:4px">Owner: <?php SearchUtils::displayOwnersDropdown($teams, $userroles, $userID, $mysqli, 'papers'); ?></div>
</td></tr>
</table>

<br />
<div style="text-align:center">
<?php
  if (isset($_GET['paperID'])) {
    echo '<input style="width:80px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'/paper/details.php?paperID=' . $_GET['paperID'] . '&module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '\'"/>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '<input style="width:80px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'/folder/details.php?module=' . $_GET['module'] . '&folder=' . $_GET['folder'] . '\'"/>';
  } else {
    echo '<input style="width:80px" type="button" name="back" value="' . $string['back'] . '" onclick="window.location=\'/staff/index.php\'"/>';
  }
?>
&nbsp;<input style="width:80px" type="submit" name="submit" value="<?php echo $string['search']; ?>" /></div>
</td></tr>
</table>

</form>
</div>
