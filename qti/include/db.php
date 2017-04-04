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
require_once '../../include/errors.php';

class Database {
  var $type = "SELECT";
  var $table = "";
  var $table_alias = "";

  var $fields = array(); // only for SELECT
  var $leftjoins = array(); // only for SELECT
  var $innerjoins = array(); // only for SELECT
  var $wheres = array(); // only for SELECT/UPDATE
  var $orders = array(); // only for SELECT
  var $values = array(); // only for INSERT/REPLACE
  var $groups = array(); // only for SELECT
  var $set = array(); // only for UPDATE
  var $limit = 0;
  var $limitoffset = 0;
  var $rawquery = ""; // only for RAW

  var $query;

  var $blankrows = array();

  function DoError($error) {
    echo "<font color='red'>$error</font><br />";
  }

  function SetType($type) {
    $this->type = $type;
  }

  function SetTable($table, $alias = "") {
    $this->table = $table;
    $this->table_alias = $alias;

    $this->fields = array(); // only for SELECT
    $this->leftjoins = array(); // only for SELECT
    $this->innerjoins = array(); // only for SELECT
    $this->wheres = array(); // only for SELECT/UPDATE
    $this->orders = array(); // only for SELECT
    $this->values = array(); // only for INSERT/REPLACE
    $this->groups = array(); // only for SELECT
    $this->set = array(); // only for UPDATE

  }

  function AddField($fieldname) {
    if ($this->type != "SELECT") return $this->DoError("Can only add output field when in SELECT mode");

    if (is_array($fieldname)) {
      foreach ($fieldname as $field) {
        $this->fields[] = $field;
      }
    } else {
      $this->fields[] = $fieldname;
    }
  }

  function AddLeftJoin($table, $alias, $sourcekey, $destkey) {
    if ($this->type != "SELECT") return $this->DoError("Can only add LEFT JOIN when in SELECT mode");

    if ($this->table_alias == "") return $this->DoError("You must have a table alias for the main table to do a LEFT JOIN");

    $join = array();
    $join['table'] = $table;
    $join['alias'] = $alias;
    $join['sk'] = $sourcekey;
    $join['dk'] = $destkey;

    $this->leftjoins[] = $join;
  }

  function AddInnerJoin($table, $alias, $sourcekey, $destkey) {
    if ($this->type != "SELECT") return $this->DoError("Can only add INNER JOIN when in SELECT mode");

    if ($this->table_alias == "") return $this->DoError("You must have a table alias for the main table to do an INNER JOIN");

    $join = array();
    $join['table'] = $table;
    $join['alias'] = $alias;
    $join['sk'] = $sourcekey;
    $join['dk'] = $destkey;

    $this->innerjoins[] = $join;
  }

  function AddWhere($field, $value, $type, $wheretype = "=") {
    if ($this->type != "SELECT" && $this->type != "UPDATE") return $this->DoError("Can only add WHERE when in SELECT or UPDATE mode");

    $where = array();
    $where['field'] = $field;
    $where['value'] = $value;
    $where['type'] = $type;
    $where['wheretype'] = $wheretype;

    $this->wheres[] = $where;
  }

  function AddOrder($field, $asc = "") {
    if ($this->type != "SELECT") return $this->DoError("Can only add ORDER BY when in SELECT mode");

    $order = array();
    $order['field'] = $field;
    $order['dir'] = $asc;

    $this->orders[] = $order;
  }

  function AddValue($field, $value, $type) {
    if ($this->type != "INSERT" && $this->type != "REPLACE") return $this->DoError("Can only add field to set when in INSERT or REPLACE mode");

    $valuea = array();
    $valuea['field'] = $field;
    $valuea['value'] = $value;
    $valuea['type'] = $type;

    $this->values[] = $valuea;
  }

  function AddGroup($field) {
    if ($this->type != "SELECT") return $this->DoError("Can only add GROUP BY when in SELECT mode");

    $this->groups[] = $field;
  }

  function AddLimit($limit, $offset = 0) {
    if ($this->type != "SELECT" && $this->type != "UPDATE") return $this->DoError("Can only add LIMIT when in SELECT or UPDATE mode");

    $this->limit = $limit;
    $this->limitoffset = $offset;
  }

  function AddSet($field, $value, $type) {
    if ($this->type != "UPDATE") return $this->DoError("Can only add SET when in UPDATE mode");

    $set = array();

    $set['field'] = $field;
    $set['value'] = $value;
    $set['type'] = $type;

    $this->sets[] = $set;
  }

  function _BuildQuery() {
    if ($this->type == "SELECT") $this->_BuildQuerySelect();
    if ($this->type == "UPDATE") $this->_BuildQueryUpdate();
    if ($this->type == "INSERT") $this->_BuildQueryInsert();
    if ($this->type == "REPLACE") $this->_BuildQueryReplace();

    //echo "<br>" . $this->query . "<br>";
    }

  function _BuildQuerySelect() {
    $qry = "SELECT ";

    $qry .= implode(",", $this->fields);

    $qry .= " FROM ";
    $qry .= $this->table;
    if ($this->table_alias != "") $qry .= " AS ".$this->table_alias;

    foreach ($this->leftjoins as $leftjoin) $qry .= sprintf(" LEFT JOIN %s AS %s ON %s.%s = %s.%s ", $leftjoin['table'], $leftjoin['alias'], $this->table_alias, $leftjoin['sk'], $leftjoin['alias'], $leftjoin['dk']);

    foreach ($this->innerjoins as $innerjoin) $qry .= sprintf(" INNER JOIN %s AS %s ON %s.%s = %s.%s ", $innerjoin['table'], $innerjoin['alias'], $this->table_alias, $innerjoin['sk'], $innerjoin['alias'], $innerjoin['dk']);

    $qry .= $this->_BuildWhere();

    if (count($this->groups) > 0) $qry .= " GROUP BY ".implode(", ", $this->groups);

    if (count($this->orders) > 0) {
      $qry .= " ORDER BY ";

      $orderbits = array();

      foreach ($this->orders as $order) $orderbits[] = $order['field']." ".$order['dir'];

      $qry .= implode(", ", $orderbits);
    }

    if ($this->limit > 0) {
      $qry .= " LIMIT ".$this->limit;
      if ($this->limit_offset > 0) $qry .= ", ".$this->limit_offset;
    }

    $this->query = $qry;
  }

  function _BuildWhere() {
    $qry = "";

    if (count($this->wheres) > 0) {
      $qry .= " WHERE ";

      $wherelist = array();

      foreach ($this->wheres as $where) {
        if ($where['wheretype'] == "=") {
          $wherelist[] = sprintf("%s = ?", $where['field']);
        } else if ($where['wheretype'] == "IN") {
          $valuecount = count($where['value']);
          foreach ($where['value'] as $value) $valuelist[] = "?";
          $wherelist[] = sprintf("%s IN (%s)", $where['field'], implode(", ", $valuelist));
        }
      }

      $qry .= implode(" AND ", $wherelist);
    }

    return $qry;
  }

  function _BuildQueryUpdate() {

  }

  function _BuildQueryInsert() {

  }

  function _BuildQueryReplace() {

  }

  function SetRawQuery($qry) {
    $this->rawquery = $qry;
  }

  function _BindWhere($stmt) {
    if (count($this->wheres) == 0) return;

    $params = array();
    $params[0] = $stmt;
    $params[1] = "";

    for ($i = 0; $i < count($this->wheres); $i++) {
      $where = $this->wheres[$i];

      if ($where['wheretype'] == "=") {
        $params[1] .= $where['type'];

        // Params to mysqli_stmt_bind_param now need to be reference
        $params[] =& $this->wheres[$i]['value'];

        // $params[] = &$where['value'];
        } else if ($where['wheretype'] == "IN") {
        foreach ($where['value'] as $value) {
          $params[1] .= $where['type'];
          $params[] = $value;
        }
      }
    }
    /*		foreach ($this->wheres as $where)
     {
     if ($where['wheretype'] == "=")
     {
     $params[1] .= $where['type'];
    				
     // Params to mysqli_stmt_bind_param now need to be reference
     //$params[] = &($this->wheres[$where]['value']);
    				
     $params[] = &$where['value'];
     } else if ($where['wheretype'] == "IN")
     {
     foreach ($where['value'] as $value)
     {
     $params[1] .= $where['type'];
     $params[] = $value;
     }
     }
     }
     */

    @call_user_func_array('mysqli_stmt_bind_param', $params);
  }

  function _BindSet() {

  }

  function _BindInsert() {

  }

  function Execute() {

  }

  function GetSingleRow() {
    global $mysqli;

    $this->_BuildQuery();

    //echo "QRY : " . $this->query . "<BR>";
    $stmt = $mysqli->prepare($this->query);

    $this->_BindWhere($stmt);

    $stmt->execute();

    $data = mysqli_stmt_result_metadata($stmt);

    $fields = array();
    $out = array();

    $fields[0] = $stmt;

    while ($field = mysqli_fetch_field($data)) $fields[] =& $out[$field->name];

    call_user_func_array('mysqli_stmt_bind_result', $fields);

    if ($stmt->fetch()) {
      return $out;
    } else {
      $this->DoError("GetSingleRow::Error in query : ".$this->query."<br>");
    }
  }

  function GetMultiRow() {
    global $mysqli;

    $this->_BuildQuery();

    //echo "QRY : " . $this->query . "<BR>";
    $stmt = $mysqli->prepare($this->query);

    $this->_BindWhere($stmt);

    $stmt->execute();

    $data = mysqli_stmt_result_metadata($stmt);

    $fields = array();
    $out = array();

    $fields[0] = $stmt;

    while ($field = mysqli_fetch_field($data)) $fields[] =& $out[$field->name];

    call_user_func_array('mysqli_stmt_bind_result', $fields);

    $results = array();

    while ($stmt->fetch()) {
      $row = array();
      foreach ($out as $key => $value) $row[$key] = $value;
      $results[] = $row;
    }

    return $results;
  }

  function GetBlankTableRow($table) {
    return array();
    if (array_key_exists($table, $this->blankrows)) {
      return $this->blankrows[$table];
    }
    $this->SetTable($table);
    $this->AddField('*');
    $this->AddLimit(1);
    $q_row = $this->GetSingleRow();

    $output = array();
    foreach ($q_row as $field => $value) {
      if (is_int($value) || $value == '0') {
        $output[$field] = 0;
      } else {
        $output[$field] = "";
      }
    }
    $this->blankrows[$table] = $output;
    return $output;
  }

  function InsertRow($table, $pri_key, &$row) {
    global $mysqli;

    $query = "INSERT INTO $table (";
    $fieldnames = array();
    $qmarks = array();

    $params = array();
    $params[0] = '';
    $params[1] = "";

    foreach ($row as $field => $value) {
      if ($field != $pri_key && $value !== '') {
        $fieldnames[] = $field;

        // Params to mysqli_stmt_bind_param now need to be reference
        $params[] =& $row[$field];

        $qmarks[] = '?';
        if (is_int($value)) {
          $params[1] .= "i";
        } else if (is_numeric($value)) {
          $params[1] .= "d";
        } else {
          $params[1] .= "s";
        }
      }
    }
    $query .= implode(",", $fieldnames);
    $query .= ") VALUES (";
    $query .= implode(",", $qmarks);
    $query .= ")";
    $stmt = $mysqli->prepare($query);
    if ($mysqli->error) {
      echo $string['showerror'] . "<br >";
      exit();
    }
    $params[0] = $stmt;
    call_user_func_array('mysqli_stmt_bind_param', $params);
    $stmt->execute();

    $row[$pri_key] = $stmt->insert_id;
  }
}
