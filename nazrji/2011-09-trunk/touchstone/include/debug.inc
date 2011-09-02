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
* debug mysqli override etc.
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

//global debug settings
$debug_pagestart = microtime();
register_shutdown_function('outputLoadTimes');
$dbclass = 'debug_mysqli';
  
$queryInfo = array();
Class debug_mysqli extends mysqli {
    var $queryCount = 1;
	var $closed = false;
    function __construct($db_host , $db_username, $db_passwd, $db_database) {
	  parent::__construct($db_host , $db_username, $db_passwd, $db_database);
	  $this->query('set profiling_history_size=200',false);
      $this->query('set profiling=1',false);
	}
	
	function cacheSqlProfile() {
	  global $queryInfo;
	  $res = $this->query('show profiles', false);
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
	
	private function getDetails($qid){
        
		$res = $this->query('select min(seq) seq,state,count(*) numb_ops, '
                . 'round(sum(duration),5) sum_dur, round(avg(duration),5) avg_dur, '
                . 'round(sum(cpu_user),5) sum_cpu, round(avg(cpu_user),5) avg_cpu '
                . 'from information_schema.profiling '
                . 'where query_id = ' . $qid
                . ' group by state order by seq',false);
		//$res = $this->query(' show profile for query ' . $qid,false);
        $ret = array();
        while($row = $res->fetch_assoc()) {
          $ret[] = $row;
		}
		return $ret;
    }
	
	function prepare($sql,$log = true) {
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
	  //return parent::prepare($sql);
	}
	
	function query($sql,$log = true) {
	  global $queryInfo;
	  if($log) {
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
  global $debug_pagestart, $userroles, $mysqli, $queryInfo;
  
  if(!is_object($mysqli)) {
    return;
  }
  
  if(!$mysqli->closed) {
    $mysqli->cacheSqlProfile();
  }
  echo  '<br style="clear:both;" /><div style="clear:both; font-size:90%; margin: 20px; padding:15px; background-color:#ffffff; width:90%">';
  $start = explode(' ',$debug_pagestart);
  $end = explode(' ', microtime());
  $usec = floatval($end[0]) - floatval($start[0]);
  $sec = $end[1] - $start[1];
  $res=(float)$sec+(float)$usec;
  echo '<p>Queries:  ' . count($queryInfo) .', generation time:' . round($res,3) . 's' . " <a href=\"#\" onclick=\"document.getElementById('allQueries').style.display='block';return false;\">More ...</a></p>";
  
  
  $errors = false;
  foreach($queryInfo as $id => $query) {
    if (isset($query['sql']['error'])) $errors = true;
  }
  
  
  if ($errors) {
    echo '<div id="allQueries" style="display:block">';
  } else {
    echo '<div id="allQueries" style="display:none">';
  }
  echo '<hr /><ol>';
  foreach($queryInfo as $id => $query) {
    //$style = '';
    //if(isset($query['sql']['error']) AND $query['sql']['error'] != '') $style = 'style="border:1px solid red"';
    if (isset($query['sql']['error'])) {
      echo "<li><div style=\"background-color:#C00000; color:white\">";
    } else {
      echo "<li>";
    }
    echo "<p>" . $query['sql']['Query'] . ' [' . $query['sql']['Duration'] . "] <a href=\"#\" onclick=\"document.getElementById('debug_$id').style.display='block';return false;\">More ...</a></p>";
    
    echo "<div id=\"debug_$id\" style=\"display:none\"><pre>"; 
      var_dump($query['stack']);
      if(isset($query['sql']['INFO'])) {
        var_dump($query['sql']['INFO']);
      }
    echo "</pre></div>";
    if (isset($query['sql']['error'])) echo "</div>";
    echo "</li>";
  }
  //echo '<div>';
  echo '</ol></div>';
}
//END Debug

?>