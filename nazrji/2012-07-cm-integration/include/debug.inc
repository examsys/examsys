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
* debug mysqli override etc.
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2012 The University of Nottingham
* @package
*/

//global debug settings
$debug_pagestart = microtime();
register_shutdown_function('outputLoadTimes');
$dbclass = 'debug_mysqli';
$displayDebug = true;
  
$queryInfo = array();
Class debug_mysqli extends mysqli {
  var $queryCount = 1;
	var $closed = false;
  var $dbusername;
  var $dbname;
  function __construct($db_host , $db_username, $db_passwd, $db_database) {
	  parent::__construct($db_host , $db_username, $db_passwd, $db_database);
	  $this->query('SET profiling_history_size=200',false);
    $this->query('SET profiling=1',false);
    $this->dbusername=$db_username;
    $this->dbname=$db_database;
	}
	
	function cacheSqlProfile() {
	  global $queryInfo;
    // THIS CAN NOT BE "prepare" on all versions of php so is left "unprepare" for now!
    $res = $this->query('SHOW PROFILES', false);
    if(!$res) {
      return 'ERROR';
    } else {
      $i = 0;
      while($row = $res->fetch_assoc()){
        if($i > 0) {
          if(isset($queryInfo[$i]) and $queryInfo[$i]['type'] != 'prepare') {
            $queryInfo[$i]['sql']['Duration'] = $row['Duration'];
            $queryInfo[$i]['sql']['INFO'] = $this->getDetails($row['Query_ID']);
          } else {
            $queryInfo[$i]['sql']['INFO'] = array('prepared statment no info');
          }
        }
        $i++;
      }
      $res->close();
    }
  }
	
	private function getDetails($qid) { 
    $details = array();
    $result = $this->prepare("SELECT MIN(seq) AS seq, state, count(*) AS numb_ops, ROUND(SUM(duration),5) AS sum_dur, ROUND(avg(duration),5) AS avg_dur, ROUND(SUM(cpu_user),5) AS sum_cpu, ROUND(AVG(cpu_user),5) AS avg_cpu FROM information_schema.profiling WHERE query_id = ? GROUP BY state ORDER BY seq");
    $result->bind_param('i', $qid);
    $result->execute();
    $result->bind_result($seq, $state, $numb_ops, $sum_dur, $avg_dur, $sum_cpu, $avg_cpu);
    while($result->fetch()) {
      $details[] = array($seq, $state, $numb_ops, $sum_dur, $avg_dur, $sum_cpu, $avg_cpu);
		}
    $result->close();
    
		return $details;
  }
	
	function prepare($sql, $log = true) {
	  global $queryInfo;
	  $stmt = new debug_mysqli_stmt($this, $sql);
	  if ($log) {
      if (isset($stmt->error) and $stmt->error != '') {
	      $queryInfo[$this->queryCount]['sql']['error'] = $stmt->error;
		  }
	    $queryInfo[$this->queryCount]['sql']['Query'] = $sql;	
	    $queryInfo[$this->queryCount]['sql']['Duration'] = 0;	
	    $queryInfo[$this->queryCount]['stack'] = debug_backtrace();	
      $queryInfo[$this->queryCount]['type'] = 'prepare';		
      $this->queryCount++;	
	  }
	  return $stmt;
	}

  function change_user( $user , $password , $database )
  {
    $this->dbusername=$user;
    $this->dbname=$database;
    return parent::change_user($user,$password,$database);
  }

	function query($sql,$log = true) {
	  global $queryInfo;
	  if ($log) {
      if (isset($stmt->error) and $stmt->error != '') {
	      $queryInfo[$this->queryCount]['sql']['error'] = $stmt->error;
		  }
	    $queryInfo[$this->queryCount]['sql']['Query'] = $sql;	
	    $queryInfo[$this->queryCount]['sql']['Duration'] = 0;	
	    $queryInfo[$this->queryCount]['stack'] = debug_backtrace();
      $queryInfo[$this->queryCount]['type'] = 'query';		
	    $this->queryCount++;
	  }
	  return parent::query($sql);
	}

	function close() {
	  $this->queryInfo = $this->cacheSqlProfile();
	  $this->query('set profiling=0',false);
	  $this->closed = true;
	  parent::close();
	}
}

Class debug_mysqli_stmt extends mysqli_stmt {
    
	function __construct($link, $query) {
	  parent::__construct($link, $query);
	}

    function execute() {
      global $queryInfo;
      $start = explode(' ', microtime());
      parent::execute();
      $end =  explode(' ', microtime());
      $usec = floatval($end[0]) - floatval($start[0]);
      $sec = $end[1] - $start[1];
      $res=(float)$sec+(float)$usec;
      $queryInfo[count($queryInfo)]['sql']['Duration'] = $res;
    }
	
}

function outputDebug() {
  if (strpos($userroles,'SysAdmin') !== false) {
    echo outputLoadTimes();
  }
}

function outputLoadTimes() {
  global $debug_pagestart, $userroles, $mysqli, $queryInfo, $displayDebug;
  
  if (!is_object($mysqli)) {
    return;
  }
  
  if(!$displayDebug) {
    return;
  }
  
  if (!$mysqli->closed) {
    $mysqli->cacheSqlProfile();
  }
  echo  '<br style="clear:both" /><div style="clear:both; font-size:90%; margin:20px; padding:15px; background-color:#ffffff; width:90%">';
  $start = explode(' ',$debug_pagestart);
  $end = explode(' ', microtime());
  $usec = floatval($end[0]) - floatval($start[0]);
  $sec = $end[1] - $start[1];
  $res=(float)$sec+(float)$usec;
  echo '<p>Time: ' . round($res,3) . ' secs, Memory: ' . round(((memory_get_peak_usage()/1024)/1024),1) . 'Mb, Queries: ' . count($queryInfo) . " <a href=\"#\" onclick=\"document.getElementById('allQueries').style.display='block'; return false;\">More ...</a></p>";
  
  
  $errors = false;
  foreach($queryInfo as $id => $query) {
    if (isset($query['sql']['error'])) $errors = true;
  }
  
  
  if ($errors) {
    echo '<div id="allQueries" style="display:block">';
  } else {
    echo '<div id="allQueries" style="display:none">';
  }
  echo '<hr noshade="noshade" style="height:1px; border:0px" /><ol>';
  foreach ($queryInfo as $id => $query) {
    if (isset($query['sql']['error'])) {
      echo "<li><div style=\"background-color:#C00000; color:white\">";
    } else {
      echo "<li>";
    }
    echo "<p>" . $query['sql']['Query'] . ' [' . $query['sql']['Duration'] . "] <a href=\"#\" onclick=\"document.getElementById('debug_$id').style.display='block'; return false;\">More ...</a></p>";
    
    echo "<div id=\"debug_$id\" style=\"display:none\"><pre>"; 
      var_dump($query['stack']);
      if (isset($query['sql']['INFO'])) {
        var_dump($query['sql']['INFO']);
      }
    echo "</pre></div>";
    if (isset($query['sql']['error'])) echo '</div>';
    echo "</li>";
  }
  echo '</ol>';
  echo "<p><strong>Included files:</strong></p>";
  echo '<hr noshade="noshade" style="height:1px; border:0px" /><ol>';
  $included_files = get_included_files();
  foreach ($included_files as $filename) {
    echo "<li>$filename</li>\n";
  }

  echo '</ol></div>';
}
//END Debug

?>