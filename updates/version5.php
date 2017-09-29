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
 * Update script updates any V5 Rogō to latest V5 Rogō.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/load_config.php';

$language = LangUtils::getLang($cfg_web_root);
// If supported lang pack not installed install them.
if(!LangUtils::langPackInstalled($language)) {
    InstallUtils::download_langpacks();
}

require_once '../include/auth.inc';
require_once '../include/errors.php';
require_once '../include/std_set_shared_functions.inc';
require_once '../include/timezones.php';
require_once dirname(__DIR__) . '/lang/' . $language . '/install/index.php';
require_once dirname(__DIR__) . '/lang/' . $language . '/updates/version5.php';

// Get the code version.
$version = $configObject->getxml('version');
$migration_path = 'version5';

set_time_limit(0);

// Get the installed version.
$old_version = $configObject->get('rogo_version');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>Rog&#333; <?php echo $configObject->get('rogo_version') . ' to ' . $version; ?> update Script</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
		<link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <link rel="stylesheet" type="text/css" href="../css/updater.css"/>

    <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  </head>
  <body>
  <table class="header">
    <tr>
      <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
          <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />

          <div class="logo_lrg_txt">Rog&#333;</div>
          <div class="logo_small_txt">Update Utility (<?php echo $old_version . ' to ' . $version; ?>)</div>
      </th>
      <th style="text-align:right; padding-right:10px"><img src="../artwork/software_64.png" width="64" height="64" alt="Upgrade Icon" /></th>
    </tr>
  </table>
<?php
if (round($old_version,0) < 5) {
  echo "<p style=\"margin-left:10px\">Rog&#333; $old_version is installed.<br /><br />Please updgrade to version 6.3.0 before proceeding with this upgrade.</p>";
  exit;
}
if (!isset($_POST['update'])) {
  InstallUtils::checkSoftware();
  ?>
<script>
  $(document).ready(function () {
    $("#installForm").validate();
  });

  $(document).ready(function () {
    $('#useLdap').change(function () {
      $('#ldapOptions').toggle();
    });
  });
</script>
  <?php
  if (!InstallUtils::configFileIsWriteable()) {
    ?>
    <h2><?php echo $string['updatefromversion'] . ' ' . $configObject->get('rogo_version') . ' to ' . $version; ?></h2>
    <div><?php echo $string['warning1']; ?></div>
    <div><?php echo $string['warning2']; ?></div>
    <?php
  } elseif (!InstallUtils::configPathIsWriteable()) {
    ?>
    <h2><?php echo $string['updatefromversion'] . ' ' . $configObject->get('rogo_version') . ' to ' . $version; ?></h2>
    <div><?php echo $string['warning3']; ?></div>
    <div><?php echo $string['warning4']; ?></div>
    <?php
  } else {
    ?>
  <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" autocomplete="off">
      <div><?php printf($string['msg1'], $version); ?></div>
      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['databaseadminuser']; ?></nobr>
              </td>
              <td class="line">
                  <hr/>
              </td>
          </tr>
      </table>
      <div><?php echo $string['msg2']; ?></div>
      <br/>

      <div><label for="mysql_admin_user"><?php echo $string['dbusername']; ?></label> <input type="text" value="" name="mysql_admin_user" class="required" minlength="2"  autocomplete="off"/></div>
      <div><label for="mysql_admin_pass"><?php echo $string['dbpassword']; ?></label> <input type="password" value="" name="mysql_admin_pass" autocomplete="off"/>
      </div>

      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['onlinehelpsystems']; ?></nobr>
              </td>
              <td class="line">
                  <hr />
              </td>
          </tr>
      </table>
      <div><label for="update_staff_help"><?php echo $string['updatestaffhelp']; ?></label> <input type="checkbox" name="update_staff_help" checked="checked" /></div>
      <div><label for="update_student_help"><?php echo $string['updatestudenthelp']; ?></label> <input type="checkbox" name="update_student_help" checked="checked" /></div>
      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['translationpacks']; ?></nobr>
              </td>
              <td class="line">
                  <hr />
              </td>
          </tr>
      </table>
      <div><label for="update_translationpack"><?php echo $string['updatetranslationpack']; ?></label> <input type="checkbox" name="update_translationpack" /></div>
      <div class="submit"><input type="submit" name="update" value="<?php echo $string['startupdate']; ?>" class="ok" /></div>
  </form>
    <?php
  }
  ?>
   </body>
   </html>
  <?php

} else {
  if ($configObject->get('cfg_db_charset') == null) {
    $cfg_db_charset = 'latin1';
  } else {
    $cfg_db_charset = $configObject->get('cfg_db_charset');
  }

  $mysql_admin_user = param::required('mysql_admin_user', param::TEXT, param::FETCH_POST);
  $mysql_admin_pass = param::required('mysql_admin_pass', param::TEXT, param::FETCH_POST);
  $mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $mysql_admin_user, $mysql_admin_pass, $configObject->get('cfg_db_database'), $cfg_db_charset, $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

  if ($mysqli->connect_error) {
    echo "<div>Failed to contect to MySQL using " . $mysql_admin_user . '</div>';
    echo "</body>";
    echo "</html>";
    exit;
  }

  // Set db object in config.
  $configObject->set_db_object($mysqli);

  $updater_utils = new UpdaterUtils($mysqli, $configObject->get('cfg_db_database'));

  // Backup the config file before proceeding.
  $updater_utils->backup_file($cfg_web_root, $old_version);

  // Avoid repeated method calls
  $cfg_db_database      = $configObject->get('cfg_db_database');
  $cfg_db_student_user  = $configObject->get('cfg_db_student_user');
  $cfg_db_staff_user    = $configObject->get('cfg_db_staff_user');
  $cfg_db_host          = $configObject->get('cfg_db_host');
  $cfg_db_username      = $configObject->get('cfg_db_username');
  $cfg_db_external_user = $configObject->get('cfg_db_external_user');
  $cfg_db_inv_username  = $configObject->get('cfg_db_inv_user');
  $cfg_use_ldap         = $configObject->get('cfg_use_ldap');

  $cfg_web_host         = $configObject->get('cfg_web_host');
  if ($cfg_web_host == '') {
    $cfg_web_host = $cfg_db_host;
  }

  ob_start();
  
  echo "\n<blockquote>\n<h1>" . $string['startingupdate'] . "</h1>";
  echo "<div>Starting at " . date("H:i:s") . "</div>\n<ol>";
  ob_flush();
  flush();

  $mysqli->autocommit(false);

  // 01/05/2013
  if (!$updater_utils->does_column_exist('users', 'password_expire')) {
    $updater_utils->execute_query("ALTER TABLE users ADD COLUMN password_expire int(11) unsigned", true);
  }

  // 02/05/2013 - Add password expire config file.
  $new_lines = array("\$cfg_password_expire = 30;    // Set in days\n");
  $target_line = '$authentication = array';
  $updater_utils->add_line($string, '$percent_decimals', $new_lines, 80, $cfg_web_root, $target_line, 7);



  // 09/05/2013 (brzsw) - Remove $protocol and insert $cfg_secure_connection
  $lines  = array();
  $cfg    = file($cfg_web_root . 'config/config.inc.php');
  $found  = false;
  foreach ($cfg as $line) {
    if (strpos($line, '$protocol = ') !== false) {
      $lines[] = "\$cfg_secure_connection = true;    // If true site must be accessed via HTTPS\n";
      $found = true;
    } else {
      $lines[] = $line;
    }
  }

  if ($found) {
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      rename($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.old.php');
    }

    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $lines) === false) {
      echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
    }
    echo "<li>Added \$cfg_secure_connection config file.</li>\n";
    ob_flush();
    flush();
  }

  // 15/05/2013 (brzsw) - Add in new variable to control number of decimals for percentages.
  $new_lines = array("//Reports\n", "  \$percent_decimals = 0;\n");
  $updater_utils->add_line($string, '$percent_decimals', $new_lines, 60, $cfg_web_root);

  // 17/05/2013 - nazrji -Add options column to questions
  if (!$updater_utils->does_column_exist('questions', 'settings')) {
    $updater_utils->execute_query("ALTER TABLE questions ADD COLUMN settings text", false);
    echo '<li>ALTER TABLE questions ADD COLUMN settings text<ul>';

    // Update Area questions
    $sql = "SELECT q_id, display_method FROM questions WHERE q_type = 'area' AND (settings = '' OR settings IS NULL) AND display_method != ''";

    // Get all area questions
    $area_qs = $mysqli->prepare($sql);
    $area_qs->execute();
    $area_qs->store_result();
    $area_qs->bind_result($q_id, $display_method);
    $count = 0;
    while ($area_qs->fetch()) {
      $parts = explode(',', $display_method);
      $extra = array('correct_full' => $parts[0], 'error_full' => $parts[1], 'correct_partial' => $parts[2], 'error_partial' => $parts[3]);
      $extra_json = json_encode($extra);
      $sql2 = "UPDATE questions SET display_method='', settings = ? WHERE q_id = ?";
      $area_upd = $mysqli->prepare($sql2);
      $area_upd->bind_param('si', $extra_json, $q_id);
      $area_upd->execute();
      $area_upd->close();
      $count++;
    }
    $area_qs->close();
    if ($count > 0) {
      echo '<li>Updated AREA questions</li>';
    }

    // Update Calculation questions
    $sql = "SELECT q_id, display_method FROM questions WHERE q_type = 'calculation' AND (settings = '' OR settings IS NULL) AND display_method != ''";

    // Get all calculation questions
    $area_qs = $mysqli->prepare($sql);
    $area_qs->execute();
    $area_qs->store_result();
    $area_qs->bind_result($q_id, $display_method);
    $count = 0;
    while ($area_qs->fetch()) {
      $parts = explode(',', $display_method);
      $extra = array('answer_decimals' => $parts[0], 'tolerance_full' => $parts[1], 'tolerance_partial' => $parts[2], 'units' => $parts[3]);
      $extra_json = json_encode($extra);
      $sql2 = "UPDATE questions SET display_method='', settings = ? WHERE q_id = ?";
      $area_upd = $mysqli->prepare($sql2);
      $area_upd->bind_param('si', $extra_json, $q_id);
      $area_upd->execute();
      $area_upd->close();
      $count++;
    }
    $area_qs->close();
    if ($count > 0) {
      echo '<li>Updated CALCULATION questions</li>';
    }

    // Update Textbox questions
    $sql = "SELECT q_id, display_method FROM questions WHERE q_type = 'textbox' AND (settings = '' OR settings IS NULL) AND display_method != ''";

    // Get all textbox questions
    $area_qs = $mysqli->prepare($sql);
    $area_qs->execute();
    $area_qs->store_result();
    $area_qs->bind_result($q_id, $display_method);
    $count = 0;
    while ($area_qs->fetch()) {
      $parts = explode('x', $display_method);
      $extra = array('columns' => $parts[0], 'rows' => $parts[1]);
      $extra_json = json_encode($extra);
      $sql2 = "UPDATE questions SET display_method = '', settings = ? WHERE q_id = ?";
      $area_upd = $mysqli->prepare($sql2);
      $area_upd->bind_param('si', $extra_json, $q_id);
      $area_upd->execute();
      $area_upd->close();
      $count++;
    }
    $area_qs->close();
    if ($count > 0) {
      echo '<li>Updated TEXTBOX questions</li>';
    }

    echo '</ul></li>';
  }


  // 17/05/2013 (brzsw) - Add cache_paper_stats table
  if (!$updater_utils->does_table_exist('cache_paper_stats')) {
    $sql = "CREATE TABLE cache_paper_stats (paperID mediumint(8) unsigned not null, cached int unsigned, max_mark decimal(10,5), max_percent decimal(10,5), min_mark decimal(10,5), min_percent decimal(10,5), q1 decimal(10,5), q2 decimal(10,5), q3 decimal(10,5), mean_mark decimal(10,5), mean_percent decimal(10,5), stdev_mark decimal(10,5), stdev_percent decimal(10,5), UNIQUE KEY `paperID` (`paperID`))";
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.cache_paper_stats TO \'' . $cfg_db_staff_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.cache_paper_stats TO \'' . $cfg_db_student_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);
  }
  if (!$updater_utils->has_grant($cfg_db_external_user, 'SELECT', 'cache_paper_stats', $cfg_web_host)) {
    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.cache_paper_stats TO \'' . $cfg_db_external_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);    
  }

  // 20/05/2013 (brzsw) - Add cache_student_paper_marks table
  if (!$updater_utils->does_table_exist('cache_student_paper_marks')) {
    $sql = "CREATE TABLE cache_student_paper_marks (paperID mediumint(8) unsigned not null, userID int(10) unsigned, mark decimal(10,5), percent decimal(10,5))";
    $updater_utils->execute_query($sql, true);

    $sql = "ALTER TABLE cache_student_paper_marks ADD CONSTRAINT pk_paperID_userID PRIMARY KEY (paperID, userID)";
    $updater_utils->execute_query($sql, false);

    $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.cache_student_paper_marks TO \'' . $cfg_db_staff_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.cache_student_paper_marks TO \'' . $cfg_db_student_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);
  }


  // 20/05/2013 (brzsw) - Add cache_median_question_marks table
  if (!$updater_utils->does_table_exist('cache_median_question_marks')) {
    $sql = "CREATE TABLE cache_median_question_marks (paperID mediumint(8) unsigned not null, questionID int(10) unsigned, median decimal(10,5), mean decimal(10,5) )";
    $updater_utils->execute_query($sql, true);

    $sql = "ALTER TABLE cache_median_question_marks ADD CONSTRAINT pk_paperID_questionID PRIMARY KEY (paperID, questionID)";
    $updater_utils->execute_query($sql, false);

    $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.cache_median_question_marks TO \'' . $cfg_db_staff_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.cache_median_question_marks TO \'' . $cfg_db_student_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);
  }

  // 04/06/2013 (cczsa1) - Add permission to denied log for rogo_auth
  if (!$updater_utils->has_grant($cfg_db_username, 'INSERT', 'denied_log', $cfg_web_host)) {
    $sql = "GRANT INSERT ON " . $cfg_db_database . ".denied_log TO '" . $cfg_db_username . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($sql, true);
  }

  // 03/06/2013 - nazrji - add mapping level column to modules and relatiosnips tables
  if (!$updater_utils->does_column_exist('modules', 'map_level')) {
    $updater_utils->execute_query("ALTER TABLE modules ADD COLUMN map_level smallint(2) NOT NULL DEFAULT 0", true);
  }
  if (!$updater_utils->does_column_exist('relationships', 'map_level')) {
    $updater_utils->execute_query("ALTER TABLE relationships ADD COLUMN map_level smallint(2) NOT NULL DEFAULT 0", true);
  }

  // 03/06/2013 - nazrji - Add VLE APIs to config file.
  $new_lines = array("\n// Objectives mapping\n", "\$vle_apis = array();\n");
  $target_line = '$cfg_password_expire';
  $updater_utils->add_line($string, '$vle_apis', $new_lines, 80, $cfg_web_root, $target_line, 1);


  // 24/06/2013 - nazrji - add new calculation question type to enum
  if (!$updater_utils->does_column_type_value_exist('questions', 'q_type', "enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false','area','enhancedcalc')")) {
    $updater_utils->execute_query("ALTER TABLE questions CHANGE q_type q_type enum('blank','calculation','dichotomous','flash','hotspot','labelling','likert','matrix','mcq','mrq','rank','textbox','info','extmatch','random','sct','keyword_based','true_false','area','enhancedcalc') DEFAULT NULL", true);
  }

  // 25/06/2013 (brzsw) - add adjmark field to log tables.
  if (!$updater_utils->does_column_exist('log0', 'adjmark')) {
    $updater_utils->execute_query("ALTER TABLE log0 ADD COLUMN adjmark float AFTER mark", true);
    $updater_utils->execute_query("UPDATE log0 SET adjmark = mark", false);
  }
  if (!$updater_utils->does_column_exist('log0_deleted', 'adjmark')) {
    $updater_utils->execute_query("ALTER TABLE log0_deleted ADD COLUMN adjmark float AFTER mark", true);
    $updater_utils->execute_query("UPDATE log0_deleted SET adjmark = mark", false);
  }
  if (!$updater_utils->does_column_exist('log1', 'adjmark')) {
    $updater_utils->execute_query("ALTER TABLE log1 ADD COLUMN adjmark float AFTER mark", true);
    $updater_utils->execute_query("UPDATE log1 SET adjmark = mark", false);
  }
  if (!$updater_utils->does_column_exist('log1_deleted', 'adjmark')) {
    $updater_utils->execute_query("ALTER TABLE log1_deleted ADD COLUMN adjmark float AFTER mark", true);
    $updater_utils->execute_query("UPDATE log1_deleted SET adjmark = mark", false);
  }
  if (!$updater_utils->does_column_exist('log2', 'adjmark')) {
    $updater_utils->execute_query("ALTER TABLE log2 ADD COLUMN adjmark float AFTER mark", true);
    $updater_utils->execute_query("UPDATE log2 SET adjmark = mark", false);
  }
  if (!$updater_utils->does_column_exist('log3', 'adjmark')) {
    $updater_utils->execute_query("ALTER TABLE log3 ADD COLUMN adjmark float AFTER mark", true);
    $updater_utils->execute_query("UPDATE log3 SET adjmark = mark", false);
  }
  if (!$updater_utils->does_column_exist('log5', 'adjmark')) {
    $updater_utils->execute_query("ALTER TABLE log5 ADD COLUMN adjmark float AFTER mark", true);
    $updater_utils->execute_query("UPDATE log5 SET adjmark = mark", false);
  }

  // 28/06/2013 (brzsw) - chaning the standards setting tables
  if (!$updater_utils->does_table_exist('std_set')) {
    $sql = "CREATE TABLE std_set (id int unsigned not null primary key auto_increment, setterID int(10) unsigned not null, paperID mediumint(8) unsigned not null, std_set datetime, method enum('Modified Angoff','Angoff (Yes/No)','Ebel','Hofstee'), group_review text, pass_score decimal(10,6), distinction_score decimal(10,6))";
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.std_set TO \'' . $cfg_db_staff_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.std_set TO \'' . $cfg_db_external_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.std_set TO \'' . $cfg_db_student_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    $sql = "CREATE TABLE std_set_questions (id int unsigned not null primary key auto_increment, std_setID int unsigned not null, questionID int(11) unsigned not null, rating text)";
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.std_set_questions TO \'' . $cfg_db_staff_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.std_set_questions TO \'' . $cfg_db_external_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    $sql = 'GRANT SELECT ON ' . $cfg_db_database . '.std_set_questions TO \'' . $cfg_db_student_user . '\'@\'' . $cfg_web_host . '\'';
    $updater_utils->execute_query($sql, true);

    // Query and then populate 'std_set' table.
    $insert_ids = array();
    $i = 0;
    $result = $mysqli->prepare("SELECT DISTINCT setterID, std_set, paperID, method, group_review FROM standards_setting");
    $result->execute();
    $result->store_result();
    $result->bind_result($setterID, $std_set, $paperID, $method, $group_review);
    while ($result->fetch()) {
      $update = $mysqli->prepare("INSERT INTO std_set VALUES (NULL, $setterID, $paperID, '$std_set', '$method', '$group_review', NULL, NULL)");
      $update->execute();
      $update->close();

      $insert_id = $mysqli->insert_id;

      $insert_ids[$setterID . $std_set . $paperID] = $insert_id;
      $ebel_ids[$setterID . $std_set] = $insert_id;
      $i++;
    }
    $result->close();

    $mysqli->commit();

    // Query and then populate 'std_set_questions' table.
    $result = $mysqli->prepare("SELECT setterID, std_set, paperID, questionID, rating FROM standards_setting");
    $result->execute();
    $result->store_result();
    $result->bind_result($setterID, $std_set, $paperID, $questionID, $rating);
    while ($result->fetch()) {
      $std_setID = $insert_ids[$setterID.$std_set.$paperID];

      $update = $mysqli->prepare("INSERT INTO std_set_questions VALUES (NULL, $std_setID, $questionID, '$rating')");
      $update->execute();
      $update->close();
    }
    $result->close();

    $mysqli->commit();

    // Update the 'ebel' table.
    if (!$updater_utils->does_column_exist('ebel', 'std_setID')) {
      $updater_utils->execute_query("ALTER TABLE ebel ADD COLUMN std_setID int unsigned not null AFTER id", true);
    }
    $mysqli->commit();
    $result = $mysqli->prepare("SELECT DISTINCT setterID, date_set FROM ebel");
    $result->execute();
    $result->store_result();
    $result->bind_result($setterID, $date_set);
    while ($result->fetch()) {
      if (isset($ebel_ids[$setterID . $date_set])) {
        $std_setID = $ebel_ids[$setterID . $date_set];

        $update = $mysqli->prepare("UPDATE ebel SET std_setID = $std_setID WHERE setterID = $setterID AND date_set = '$date_set'");
        $update->execute();
        $update->close();
      }
    }
    $result->close();
    $mysqli->commit();
		
		// Delete any records with std_setID still on zero (i.e. no matching parent std_set records).
    $update = $mysqli->prepare("DELETE FROM ebel WHERE std_setID = 0");
    $update->execute();
    $update->close();

  	// Update the 'properties' table.
    $result = $mysqli->prepare("SELECT property_id, marking FROM properties WHERE marking LIKE '2,%'");
    $result->execute();
    $result->store_result();
    $result->bind_result($property_id, $marking);
    while ($result->fetch()) {
      $parts = explode(',', $marking);

      $parts[2] = str_replace('-', '', $parts[2]);
      $parts[2] = str_replace(' ', '', $parts[2]);
      $parts[2] = str_replace(':', '', $parts[2]);

      $tmp_date = substr($parts[2],0,4) . '-' . substr($parts[2],4,2) . '-' . substr($parts[2],6,2) . ' ' . substr($parts[2],8,2) . ':' . substr($parts[2],10,2) . ':' . substr($parts[2],12,2);

      $search_date = $parts[1] . $tmp_date;

      if (isset($ebel_ids[$search_date])) {
        $std_setID = $ebel_ids[$search_date];

        $update = $mysqli->prepare("UPDATE properties SET marking = '2,$std_setID' WHERE property_id = $property_id");
        $update->execute();
        $update->close();
      } else {
        $update = $mysqli->prepare("UPDATE properties SET marking = '0' WHERE property_id = $property_id");
        $update->execute();
        $update->close();
      }

    }
    $result->close();

    $mysqli->commit();

    // Clear up a table.
    if ($updater_utils->does_table_exist('standards_setting')) {
      $sql = "DROP TABLE standards_setting";
      $updater_utils->execute_query($sql, true);
    }
    $mysqli->commit();

    // Clear up some columns
    if ($updater_utils->does_column_exist('ebel', 'id')) {
      $sql = "ALTER TABLE ebel DROP COLUMN id";
      $updater_utils->execute_query($sql, true);
    }
    if ($updater_utils->does_column_exist('ebel', 'setterID')) {
      $sql = "ALTER TABLE ebel DROP COLUMN setterID";
      $updater_utils->execute_query($sql, true);
    }
    if ($updater_utils->does_column_exist('ebel', 'date_set')) {
      $sql = "ALTER TABLE ebel DROP COLUMN date_set";
      $updater_utils->execute_query($sql, true);
    }

    if (!$updater_utils->does_table_exist('hofstee')) {
      $sql = "CREATE TABLE hofstee (std_setID int unsigned not null, whole_numbers tinyint, x1_pass tinyint, x2_pass tinyint, y1_pass tinyint, y2_pass tinyint, x1_distinction tinyint, x2_distinction tinyint, y1_distinction tinyint, y2_distinction tinyint, marking tinyint)";
      $updater_utils->execute_query($sql, true);

      $sql = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $cfg_db_database . '.hofstee TO \'' . $cfg_db_staff_user . '\'@\'' . $cfg_web_host . '\'';
      $updater_utils->execute_query($sql, true);
    }
    $mysqli->commit();

    // Query and then populate 'std_set' table.
    $result = $mysqli->prepare("SELECT id, setterID, std_set FROM std_set WHERE method = 'Modified Angoff'");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $setterID, $std_set);
    while ($result->fetch()) {
      $ebel_ids[$setterID . $std_set] = $id;
    }
    $result->close();

    // Update the 'group_review' column in std_set.
    $result = $mysqli->prepare("SELECT id, group_review FROM std_set WHERE group_review != 'no' AND group_review != 'yes'");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $group_review);
    while ($result->fetch()) {
      $ID_list = '';
      $reviews = explode(';', $group_review);
      foreach ($reviews as $review) {
        $parts = explode(',', $review);

        $tmp_date = substr($parts[1],0,4) . '-' . substr($parts[1],4,2) . '-' . substr($parts[1],6,2) . ' ' . substr($parts[1],8,2) . ':' . substr($parts[1],10,2) . ':' . substr($parts[1],12,2);

        $search_date = $parts[0] . $tmp_date;

        if ($ID_list == '') {
          $ID_list = $ebel_ids[$search_date];
        } else {
          $ID_list .= ',' . $ebel_ids[$search_date];
        }
      }

      $update = $mysqli->prepare("UPDATE std_set SET group_review = '$ID_list' WHERE id = $id");
      $update->execute();
      $update->close();

    }
    $result->close();
    $mysqli->commit();

    echo "<li>Updating standard_setting values in the properties table</li>\n";
    ob_flush();
    flush();

		// Call the standard_setting list page to populate the results in std_set table.
    $result = $mysqli->prepare("SELECT DISTINCT property_id, total_mark FROM properties WHERE marking LIKE '2,%'");
    $result->execute();
    $result->store_result();
    $result->bind_result($property_id, $total_mark);
    while ($result->fetch()) {
      $no_reviews = 0;
      $reviews = get_reviews($mysqli, 'index', $property_id, $total_mark, $no_reviews);
      foreach ($reviews as $review) {
        if ($review['method'] != 'Hofstee') {
          updateDB($review, $mysqli);
        }
      }

    }
    $result->close();
  }

  // 04/07/2013 (cczsa1) - enhanced question type config
  $new_lines = array("\n// Enhanced Calculation question config\n", "\$enhancedcalculation = array('host' => 'localhost', 'port'=>6311,'timeout'=>5); //default enhancedcalc Rserve config options\n","//but use phpEval as default for enhanced calculation questions\n","\$enhancedcalc_type = 'phpEval'; //set the enhanced calculation to use php for maths \n","\$enhancedcalculation = array(); //no config options for phpEval enhancedcalc plugin");

  $target_line = '$cfg_password_expire';
  $updater_utils->add_line($string, '$enhancedcalculation', $new_lines, 80, $cfg_web_root, $target_line, 1);

  // 04/07/2013 (cczsa1) - add new field to logs to indicate an error state
  if (!$updater_utils->does_column_exist('log0', 'errorstate')) {
    $updater_utils->execute_query("ALTER TABLE log0 ADD COLUMN errorstate tinyint unsigned NOT NULL DEFAULT '0' AFTER user_answer", true);
  }
  if (!$updater_utils->does_column_exist('log0_deleted', 'errorstate')) {
    $updater_utils->execute_query("ALTER TABLE log0_deleted ADD COLUMN errorstate tinyint unsigned NOT NULL DEFAULT '0' AFTER user_answer", true);
  }
  if (!$updater_utils->does_column_exist('log1', 'errorstate')) {
    $updater_utils->execute_query("ALTER TABLE log1 ADD COLUMN errorstate tinyint unsigned NOT NULL DEFAULT '0' AFTER user_answer", true);
  }
  if (!$updater_utils->does_column_exist('log1_deleted', 'errorstate')) {
    $updater_utils->execute_query("ALTER TABLE log1_deleted ADD COLUMN errorstate tinyint unsigned NOT NULL DEFAULT '0' AFTER user_answer", true);
  }
  if (!$updater_utils->does_column_exist('log2', 'errorstate')) {
    $updater_utils->execute_query("ALTER TABLE log2 ADD COLUMN errorstate tinyint unsigned NOT NULL DEFAULT '0' AFTER user_answer", true);
  }
  if (!$updater_utils->does_column_exist('log3', 'errorstate')) {
    $updater_utils->execute_query("ALTER TABLE log3 ADD COLUMN errorstate tinyint unsigned NOT NULL DEFAULT '0' AFTER user_answer", true);
  }

  // 09/07/2013 - Add hofstee default settings.
  $new_lines = array("// Standard Setting\n", "  \$hofstee_defaults = array('pass'=>array(0, 'median', 0, 100), 'distinction'=>array('median', 100, 0, 100));\n", "  \$hofstee_whole_numbers = true;\n");
  $target_line = '$percent_decimals';
  $updater_utils->add_line($string, '$hofstee_defaults', $new_lines, 64, $cfg_web_root, $target_line, 1);

  // 09/07/2013 (brzsw) - Add new log4_overallID field into the log4 table.
  if (!$updater_utils->does_column_exist('log4', 'log4_overallID')) {
    $updater_utils->execute_query("ALTER TABLE log4 ADD COLUMN log4_overallID int(11) unsigned", true);

    $mysqli->autocommit(false);
    $result = $mysqli->prepare("SELECT DISTINCT m.id, l.userID, l.q_paper, l.started FROM log4 l, log4_overall m WHERE l.userID = m.userID AND l.q_paper = m.q_paper AND l.started = m.started");
    $result->execute();
    $result->store_result();
    $result->bind_result($id, $userID, $paperID, $started);
    while ($result->fetch()) {
      if ($paperID > 0) {
        $updater_utils->execute_query("UPDATE log4 SET log4_overallID = $id WHERE userID = $userID AND q_paper = $paperID AND started = '$started'", false);
      }
    }
    $result->free_result();
    $result->close();
    $mysqli->commit();
    $mysqli->autocommit(true);


    // Remove the indexes for speed.
    $updater_utils->execute_query("DROP INDEX q_paper ON log4", false);
    $updater_utils->execute_query("DROP INDEX username ON log4", false);
    $updater_utils->execute_query("DROP INDEX started ON log4", false);

    // Drop columns we no longer need.
    $updater_utils->execute_query("ALTER TABLE log4 DROP q_paper, DROP userID, DROP started", true);
  }

  // 10/07/2013 (brzsw) - Add new marking column to hofstee.
  if (!$updater_utils->does_column_exist('hofstee', 'marking')) {
    $updater_utils->execute_query("ALTER TABLE hofstee ADD COLUMN marking tinyint DEFAULT NULL", true);
  }


  // 02/07/2013 - nazrji - Add table for question statuses
  if (!$updater_utils->does_table_exist('question_statuses')) {
    $sql = <<< QUERY
CREATE TABLE `question_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `exclude_marking` tinyint(4) NOT NULL DEFAULT '0',
  `exclude_search` tinyint(4) NOT NULL DEFAULT '0',
  `is_default` tinyint(4) NOT NULL DEFAULT '0',
  `change_locked` tinyint(3) NOT NULL DEFAULT '1',
  `validate` tinyint(3) NOT NULL DEFAULT '1',
  `display_order` tinyint(3) unsigned NOT NULL DEFAULT '255',
  PRIMARY KEY (`id`)) DEFAULT CHARSET={$cfg_db_charset};
QUERY;
    $updater_utils->execute_query($sql, true);

    $sql = <<<QUERY
INSERT INTO question_statuses (name, exclude_marking, exclude_search, is_default, change_locked, validate, display_order) VALUES
('Normal', false, false, true, true, true, 0),
('Retired', false, true, false, true, false, 1),
('Incomplete', false, false, false, false, false, 2),
('Experimental', true, false, false, false, true, 3),
('Beta', false, false, false, false, true, 4)
QUERY;
    $updater_utils->execute_query($sql, true);

    $sql = "GRANT SELECT ON " . $cfg_db_database . ".question_statuses TO '" . $cfg_db_student_user . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($sql, true);
    $sql = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".question_statuses TO '" . $cfg_db_staff_user . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($sql, true);
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".question_statuses TO '" . $cfg_db_external_user . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($sql, true);
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".question_statuses TO '" . $cfg_db_inv_username . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($sql, true);
    $sql = "GRANT SELECT ON " . $cfg_db_database . ".question_statuses TO '" . $configObject->get('cfg_db_sct_user') . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($sql, true);

    // Update existing statuses
    $statuses = array('Normal' => 1, 'Retired' => 2, 'Incomplete' => 3, 'Experimental' => 4, 'Beta' => 5);
    $count = 0;

    $sql = 'ALTER TABLE questions MODIFY COLUMN status varchar(40) NULL';
    $updater_utils->execute_query($sql, true);

    foreach ($statuses as $name => $id) {
      $sql = 'UPDATE questions SET status = ? WHERE status = ?';
      $status_upd = $mysqli->prepare($sql);
      $status_upd->bind_param('ss', $id, $name);
      $status_upd->execute();
      if ($mysqli->affected_rows > 0) $count++;
      $status_upd->close();
    }
    echo '<li>Updated question statuses</li>';
      $sql = 'UPDATE questions SET status = 3 WHERE status IS NULL';
      $updater_utils->execute_query($sql, true);

    $sql = 'ALTER TABLE questions MODIFY COLUMN status tinyint(3) NOT NULL';
    $updater_utils->execute_query($sql, true);
  }
  
  if (!$updater_utils->does_table_exist('sys_updates')) {
    $sql = <<< QUERY
CREATE TABLE `sys_updates` (
  `name` varchar(255),
  `updated` datetime NOT NULL,
  KEY `name` (`name`)
) AUTO_INCREMENT=0;
QUERY;
    $updater_utils->execute_query($sql, true);
    
    $filenames = array('stopfile_convert_calc_ans_done.txt', 'stopfile_sct_fix.txt', 'stopfile_textbox_fix.txt', 'stopfile_textbox_update.txt');
    foreach($filenames as $filename) {
      if (file_exists($filename)) {
        $update_name = str_replace('stopfile_', '', $filename);
        $update_name = str_replace('.txt' ,'', $update_name);
        $updater_utils->record_update($update_name);
      }
    }
    $mysqli->commit();
  }


  /*
   *****   ALL UPDATES SHOULD NOW BE PLACED IN DATESTAMPED FILES IN THE version5 FOLDER   *****
   *
   *****   UPDATE FILES CAN BE CREATED BY RUNNING /updates/create_update.php
   */

  // Run individual update files
  $files = scandir($migration_path);
  foreach ($files as $file) {
    if (StringUtils::ends_with($file, '.php')) {
      include $migration_path . '/' . $file;
      $mysqli->commit();
    }
  }

  $mysqli->commit();
  
  // 01/05/2013 - Update the online help files.
  $update_staff_help = param::optional('update_staff_help', false, param::BOOLEAN, param::FETCH_POST);
  if (!is_null($update_staff_help)) {
    $updater_utils->execute_query("TRUNCATE staff_help", true);

    $file = file_get_contents('../install/staff_help.sql');
    $mysqli->multi_query($file);
    if ($mysqli->error) {
      echo $string['showerror'] . "<br />";
      exit();
    }
    $ext = '';
    while ($mysqli->more_results()) {
      $mysqli->next_result();
      if ($mysqli->insert_id > 0) $ext = $ext . ' ' . $mysqli->insert_id;
    }
    // Ensure all help images are in the correct location.
    $staffhelp = rogo_directory::get_directory('help_staff');
    $staffhelp->create();
    $staffhelp->copy_from_default();
    // Fix path of help file images as may not be in root web dir.
    InstallUtils::correct_staff_path();
    echo "<li>LOADED staff_help: " . $ext . "</li>\n";
  }

  $update_student_help = param::optional('update_student_help', false, param::BOOLEAN, param::FETCH_POST);
  if ($update_student_help) {
    $updater_utils->execute_query("TRUNCATE student_help", true);

    $file = file_get_contents('../install/student_help.sql');
    $mysqli->multi_query($file);
    if ($mysqli->error) {
      echo $string['showerror'] . "<br />";
      exit();
    }
    $ext = '';
    while ($mysqli->more_results()) {
      $mysqli->next_result();
      if ($mysqli->insert_id > 0) $ext = $ext . ' ' . $mysqli->insert_id;
    }
    // Ensure all help images are in the correct location.
    $studenthelp = rogo_directory::get_directory('help_student');
    $studenthelp->create();
    $studenthelp->copy_from_default();
    // Fix path of help file images as may not be in root web dir.
    InstallUtils::correct_student_path();
    echo "<li>LOADED student_help: " . $ext . "</li>\n";
  }
  $mysqli->commit();

  // Update language packs.
  $update_translationpack = param::optional('update_translationpack', false, param::BOOLEAN, param::FETCH_POST);
  if ($update_translationpack) {
    InstallUtils::download_langpacks();
  }

	/*
   *****   NOW UPDATE THE INSTALLER SCRIPT   *****
   */

  // End of updates -----------------------------------------------------------------

  // Update npm and dependencies.
  try {
    $npm_method = npm_utils::INSTALL_NODEV;
    npm_utils::setup($npm_method);
  } catch (Exception $e) {
      echo "<li class=\"error\">" . $e->getMessage() . "</li>";
  }

  // Final housekeeping activities - put all updates above this line
  $updated = $updater_utils->update_version($version, $string, $cfg_web_root);
  if ($updated !== true) {
    echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
  }
  $updater_utils->execute_query('FLUSH PRIVILEGES', true);
  $updater_utils->execute_query('TRUNCATE sys_errors', true);
  echo "</ol>\n";

  $mysqli->close();
  echo "<div>Ended at " . date("H:i:s") . "</div>";
  echo "\n<h2>" . $string['actionrequired'] . "</h2>\n<ol>";
  echo "\n<li>" . $string['readonly'] . "</li>\n";
  echo "</ol>\n<div>" . $string['finished'] . "</div>\n<div style=\"text-align:center\"><input type=\"button\" class=\"ok\" value=\" " . $string['home'] . " \" onclick=\"go_home()\" /></div><blockquote>\n";
}
?>
<script>
function go_home() {
  window.location='../index.php';
}
</script>
