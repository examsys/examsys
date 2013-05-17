<?php
// This file is part of Rog?
//
// Rog? is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rog? is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rog?.  If not, see <http://www.gnu.org/licenses/>.

/**
*
* Utility class for updater related functionality
*
* @author Ben Parish, Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

Class UpdaterUtils {

  private $mysqli;
  private $db_name;

  public function __construct($mysqli, $db_name) {
    $this->mysqli  = $mysqli;
    $this->db_name = $db_name;
  }

  public function does_table_exist($table_name) {
    $result  = $this->mysqli->prepare('SELECT table_name FROM information_schema.tables WHERE table_schema = ? AND table_name = ?');
    $result->bind_param('ss', $this->db_name, $table_name);
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if ($num_rows < 1){
      return false;
    }

    return true;
  }
  
  public function does_column_type_value_exist($table_name, $column_name, $column_type_value) {
    $result = $this->mysqli->prepare('SELECT column_type FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? AND column_type = ?');
    $result->bind_param('ssss', $this->db_name, $table_name, $column_name, $column_type_value);
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if ($num_rows < 1) {
      return false;
    }

    return true;
  }

  public function does_column_exist($table_name, $column_name) {
    $result = $this->mysqli->prepare('SELECT column_name FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?');
    $result->bind_param('sss', $this->db_name, $table_name, $column_name);
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if ($num_rows < 1) {
      return false;
    }

    return true;
  }
  
  public function does_index_exist($table_name, $index_name) {
    $result = $this->mysqli->prepare("SHOW INDEXES IN $table_name WHERE key_name = ?");
    $result->bind_param('s', $index_name);
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    if ($num_rows < 1) {
      return false;
    }

    return true;
  }

  public function does_tables_priv_exist($user, $table, $privileges) {
    $this->mysqli->select_db('mysql');

    $privileges = str_replace(' ', '', $privileges);
    $privileges = ucwords($privileges);

    $result = $this->mysqli->prepare('SELECT * FROM tables_priv WHERE db = ? AND user = ? AND table_name = ? AND table_priv = ?');
    $result->bind_param('ssss', $this->db_name, $user, $table, $privileges);
    $result->execute();
    $result->store_result();
    $num_rows =  $result->num_rows;

    $result->close();

    $this->mysqli->select_db($this->db_name);

    if ($num_rows < 1) {
      return false;
    }

    return true;
  }
  
  public function has_grant($user, $grant, $table, $host) {
    $found_grant = '';
    
    $result = $this->mysqli->query("SHOW GRANTS FOR '$user'@'$host'");
    echo $this->mysqli->error;
    
    while ($existing_grant = $result->fetch_array()) {
      if (stripos($existing_grant[0], ".`$table` TO") !== false) {
        $found_grant = $existing_grant[0];
      }
    }
    $result->close();
    
    if ($found_grant != '') {
      $parts = explode(' ON ', $found_grant);
      $found_grant = $parts[0];
      $found_grant = str_replace('GRANT ', '', $found_grant);
    }

    if ($found_grant == $grant) {
      return true;
    } else {
      return false;
    }
  }
  
  public function execute_query($sql, $update_display) {
    $this->mysqli->query($sql);
    
    if ($this->mysqli->errno == 0) {      
      if ($update_display) {
        echo "<li>$sql</li>\n";
        ob_flush();
        flush();
      }
    } elseif ($this->mysqli->warning_count>0) {
      echo '<li class="warning">WARNING: ' . $sql . '</li>';
      $e = $this->mysqli->get_warnings();
      do {
        echo "Warning No: $e->errno: - $e->message <br />\n";
      } while ($e->next());
    } else {
      echo '<li class="error">ERROR: ' . $sql . '</li>';
      if ($this->mysqli->error) {
        try {
          $err = $this->mysqli->error;
          $mess = $this->mysqli->errno;
          throw new Exception("MySQL error $err", $mess);
        } catch (Exception $e) {
          echo "Error No: " . $e->getCode() . " - " . $e->getMessage() . "<br />";
        }
      }
    }
  }
  
  public function update_version($version, $string, $cfg_web_root) {
    $cfg_new = array();
    $cfg = file($cfg_web_root . 'config/config.inc.php');
    foreach ($cfg as $line) {
      if (strpos($line, 'rogo_version') !== false) {
        $cfg_new[] = "\$rogo_version = '$version';\n";
      } else {
        $cfg_new[] = $line;
      }
    }
    if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg_new) === false) {
      return $string['couldnotwrite'];
    } else {
      return true;
    }
  }
  
  public function add_line($search, $new_lines, $default_line, $cfg_web_root, $target_line = '', $offset = 1) {
    $cfg = file($cfg_web_root . 'config/config.inc.php');
    $found = false;
    $line_no = 0;
    foreach ($cfg as $line) {
      if (strpos($line, $search) !== false) {
        $found = true;
      }
      if ($target_line != '' and strpos($line, '$authentication = array') !== false) {
        $default_line = $line_no + $offset;
      }
      $line_no++;
    }

    if (!$found) {
      array_splice($cfg, $default_line, 0, $new_lines);

      if (file_put_contents($cfg_web_root . 'config/config.inc.php', $cfg) === false) {
        echo "<li class=\"error\">" . $string['couldnotwrite'] . "</li>";
      }
      //echo "<li>Adding line to config file: $default_text</li>\n";
      ob_flush();
      flush();
    }
  }
  
  public function backup_file($cfg_web_root, $old_version) {
    if (file_exists($cfg_web_root . 'config/config.inc.php')) {
      copy($cfg_web_root . 'config/config.inc.php', $cfg_web_root . 'config/config.inc.' . $old_version . '.php');
    }
  }

}































