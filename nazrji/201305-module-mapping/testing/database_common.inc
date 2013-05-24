<?php
function make_db_connections() {
  global $mysqli, $master_mysqli, $test_mysqli, $configObject, $notice;
  
  $mysqli->close();

  $master_username = $_POST['master_username'];
  $master_password = $_POST['master_password'];
  $master_mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $master_username, $master_password, $_POST['master_dbname'], $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));

  $test_username = $_POST['test_username'];
  $test_password = $_POST['test_password'];
  $test_mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $test_username, $test_password, $_POST['test_dbname'], $configObject->get('cfg_db_charset'), $notice, $configObject->get('dbclass'));

}

function format_text($txt) {
  $txt = str_replace("','", "', '", $txt);
  if ($txt == '') {
    $txt = '&nbsp;';
  }
  return $txt;
}

function get_tables($db_name, $db) {
  $tables = array();

  $result = $db->prepare("SHOW TABLES FROM $db_name");
  $result->execute();
  $result->store_result();
  $result->bind_result($name);
  while ($result->fetch()) {
    $tables[] = $name;
  }
  $result->close();
  
  return $tables;
}

function display_form() {
  $html = '';
  
  $html .= "<form method=\"post\" action=\"". $_SERVER['PHP_SELF'] . "\">\n";

  $html .= <<<END
  <table>
  <tr>
    <td colspan="2"><strong>Master Database</strong></td>
    <td colspan="2"><strong>Test Database</strong></td>
  </tr>
  <tr>
    <td>Name</td>
    <td><input type="text" name="master_dbname" size="20" value="rogo" /></td>
    <td>Name</td>
    <td><input type="text" name="test_dbname" size="20" value="rogo2" /></td>
  </tr>
  <tr>
    <td>Username</td>
    <td><input type="text" name="master_username" size="20" value="root" /></td>
    <td>Username</td>
    <td><input type="text" name="test_username" size="20" value="root" /></td>
  </tr>
  <tr>
    <td>Password</td>
    <td><input type="password" name="master_password" size="20" /></td>
    <td>Password</td>
    <td><input type="password" name="test_password" size="20" /></td>
  </tr>
  <tr>
    <td colspan="4"><input type="submit" name="submit" value=" Test " /></td>
  </tr>
  </table>
  </form>
END;
  
  return $html;
}
?>