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

namespace testing;

use Symfony\Component\Yaml\Yaml;
use mysqli;

/**
 * Test database access functions
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 onwards The University of Nottingham
 */
trait testcasetrait
{

    /**
     * Load/Generate test data
     */
    abstract protected function setup_dataset(): void;

    /**
     * load data generators
     * @param string $name The name of the generator.
     * @param string $component The component the generator is from (optional).
     * @return generator
     */
    abstract public function get_datagenerator(string $name, string $component = 'core'): \testing\datagenerator\generator;

    /**
     * Set-up db connections.
     * @return mysqli
     */
    abstract protected function get_db_connection(): mysqli;

    /**
     * Setup database connection
     * @param array $dbconfig database configuration
     * @return mysqli
     */
    protected function set_db_connection(array $dbconfig): mysqli
    {
        return new mysqli(
            $dbconfig['host'],
            $dbconfig['user'],
            $dbconfig['password'],
            $dbconfig['database'],
            $dbconfig['port']
        );
    }

    /**
     * Generate data for test.
     * @throws Exception
     */
    protected function base_datageneration(): void
    {
        // Always need this academic year.
        $datagenerator = $this->get_datagenerator('academic_year', 'core');
        $calendaryear = date('Y');
        $academicyear = date('Y') . '/' . (date('y') + 1);
        $datagenerator->create_academic_year(array('calendar_year' => $calendaryear, 'academic_year' => $academicyear));
        // Base users.
        $datagenerator = $this->get_datagenerator('users', 'core');
        $datagenerator->create_user(array('surname' => 'Administrator', 'username' => 'admin', 'roles' => 'Staff,SysAdmin', 'grade' => 'University Lecturer',
            'initials' => 'S', 'title' => 'Miss', 'email' => 'admin@example.com', 'first_names' => 'System', 'gender' => null));
        $datagenerator->create_user(array('surname' => 'User1', 'username' => 'test1', 'roles' => 'Student', 'grade' => 'TEST2',
            'initials' => 'A', 'title' => 'Dr', 'first_names' => 'A', 'sid' => '1234567890', 'gender' => null, 'special_needs' => array('breaks' => 'yes')));
        $datagenerator->create_user(array('surname' => 'User2', 'username' => 'test2', 'roles' => 'Student', 'grade' => 'TEST2',
            'initials' => 'A', 'title' => 'Dr', 'first_names' => 'A', 'sid' => '00000001', 'gender' => null));
        $fixtures = glob("$this->fixture_base*.yml");
        if (count($fixtures) < 1) {
            // We should find some yml files!
            throw new \Exception('Could not find base fixture files');
        }
        foreach ($fixtures as $yamlfile) {
            $data = Yaml::parseFile($yamlfile);
            $this->insert_into_db($data);
        }
    }

    /**
     * Run a select query on test database and return the result set as an array
     * @param array $querydata
     *  ['table'] table we are selecting from
     *  ['orderby'] to order by
     *  ['columns'] columns we are interested in
     *  ['where'] where filter to apply (column, value, operator)
     *
     *  i.e. array('table' => 'test', 'columns' => array('c1','c2'), 'orderby' => array('c2'), 'where' => array(array('column' => 'c2', 'value' => 1, 'operator' => '='))
     *
     * @return array
     */
    protected function query(array $querydata): array
    {
        // Generate columns.
        $columnsstring = '*';
        if (isset($querydata['columns']) && is_array($querydata['columns'])) {
            $columnsstring = implode(',', $querydata['columns']);
        }
        // Genereate order by.
        $orderbystring = '';
        if (isset($querydata['orderby']) && is_array($querydata['orderby'])) {
            $orderbystring = ' ORDER BY ' . implode(',', $querydata['orderby']);
        }
        // Generate where clause.
        $wherestring = '';
        if (isset($querydata['where']) && is_array($querydata['where'])) {
            $count = 0;
            foreach ($querydata['where'] as $w) {
                $operator = '=';
                if (isset($w['operator'])) {
                    $operator = $w['operator'];
                }
                if ($count === 0) {
                    $wherestring .= ' WHERE ';
                } else {
                    $wherestring .= ' AND ';
                }
                if (is_array($w['value'])) {
                    foreach ($w['value'] as $i => $v) {
                        if (is_string($v)) {
                            $w['value'][$i] = $this->get_db_connection()->real_escape_string($v);
                            $w['value'][$i] = "'" . $w['value'][$i] . "'";
                        }
                    }
                    $w['value'] = '(' . implode(',', $w['value']) . ')';
                } elseif (is_string($w['value'])) {
                    $w['value'] = $this->get_db_connection()->real_escape_string($w['value']);
                    $w['value'] = "'" . $w['value'] . "'";
                } elseif (is_null($w['value'])) {
                    $w['value'] = 'NULL';
                }
                $wherestring .= $w['column'] . ' ' . $operator . ' ' . $w['value'];
                $count++;
            }
        }
        $query = 'SELECT ' . $columnsstring . ' FROM ' . $querydata['table'] . $wherestring . $orderbystring;
        $sql = $this->get_db_connection()->prepare($query);
        if ($sql === false) {
            $error = $this->get_db_connection()->error;
            throw new \Exception("Query failed: $error");
        }
        $sql->execute();
        $result = $sql->get_result();
        $data = array();
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $data[] = $row;
        }
        $sql->close();
        return $data;
    }

    /**
     * Get row count for table
     * @param string $table the table
     * @return int
     */
    protected function rowcount(string $table): int
    {
        $sql = $this->get_db_connection()->prepare('SELECT count(*) FROM ' . $table);
        if ($sql === false) {
            $error = $this->get_db_connection()->error;
            throw new \Exception("Row count failed: $error");
        }
        $sql->execute();
        $sql->bind_result($count);
        $sql->fetch();
        $sql->close();
        return $count;
    }

    /**
     * Get database table names
     */
    protected function get_table_names(): void
    {
        $tablelist = $this->get_db_connection()->query('SHOW TABLES');
        foreach ($tablelist->fetch_all(MYSQLI_NUM) as $table) {
            $this->table_names[] = $table[0];
        }
    }

    /**
     * Disable foreign key checks on database
     */
    protected function disable_fk_check(): void
    {
        $this->get_db_connection()->query('SET FOREIGN_KEY_CHECKS = 0');
    }

    /**
     * Enable foreign key checks on database
     */
    protected function enable_fk_check(): void
    {
        $this->get_db_connection()->query('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Delete data from a table
     * @param string $table table name
     */
    protected function delete_from_table(string $table): void
    {
        $sql = $this->get_db_connection()->prepare('DELETE FROM ' . $table);
        if ($sql === false) {
            $error = $this->get_db_connection()->error;
            throw new \Exception("Delete failed: $error");
        }
        $sql->execute();
        $sql->close();
    }

    /**
     * Insert data into test db
     * @param array $data data to insert
     */
    private function insert_into_db(array $data): void
    {
        foreach ($data as $tableName => $rows) {
            if (!is_null($rows)) {
                foreach ($rows as $idx => $val) {
                    $keys = implode(',', array_keys($val));
                    $values = '';
                    foreach (array_values($val) as $v) {
                        if (is_string($v)) {
                            $v = $this->get_db_connection()->real_escape_string($v);
                            $v = "'" . $v . "'";
                        } elseif (is_null($v)) {
                            $v = 'NULL';
                        }
                        $values .= $v . ',';
                    }
                    $values = rtrim($values, ',');
                    $sql = $this->get_db_connection()->prepare('INSERT INTO ' . $tableName . '(' . $keys . ')' . ' values (' . $values . ')');
                    if ($sql === false) {
                        $error = $this->get_db_connection()->error;
                        throw new \Exception("Insert failed: $error");
                    }
                    $sql->execute();
                    $sql->close();
                }
            }
        }
    }
}
