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
 * Class that searches audit history.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 */
class AuditSearch extends Search
{
    /** @var string The ordering clause for the query. */
    protected $orderby = 'time DESC, id ASC';

    /** @var string A time to search from. */
    protected $time;

    /**
     * Gets the fields that will be used in the query.
     *
     * @return array|string[]
     */
    protected function getFields(): array
    {
        return [
            'userID',
            'action',
            'time',
            'sourceID',
            'source',
            'details',
        ];
    }
    /**
     * Returns the SQL to filter out deleted users.
     *
     * @return string
     */
    protected function generateTimeSQL(): SQLFragment
    {
        $sql = new SQLFragment();
        $sql->sql = 'time > ?';
        $sql->addParameter($this->time, SQLFragment::TYPE_STRING);
        return $sql;
    }

    /**
     * Sets the time that should be searched from.
     *
     * @param string $time
     */
    public function setTime(string $time): void
    {
        $this->time = $time;
    }

    /**
     * @see \Search::doQuery()
     *
     * @return \mysqli_stmt
     */
    public function doQuery(): mysqli_stmt
    {
        // Generate the fields we wish to return.
        $fields = implode(', ', $this->getFields());

        $time = $this->generateTimeSQL();
        $where = SQLFragment::combine(' AND ', $time);
        $sql = 'SELECT ' . $fields . ' FROM audit_log WHERE '  . $where->sql . ' ORDER BY ' . $this->orderby
            . ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
        $query = Config::get_instance()->db->prepare($sql);

        if ($query === false) {
            throw new \RuntimeException(Config::get_instance()->db->error);
        }

        // Prepare the query.
        $types = [$where->param_types];

        $arguments = [];
        foreach ($where->params as &$param) {
            $arguments[] = &$param;
        }

        if (call_user_func_array(array($query, 'bind_param'), array_merge($types, $arguments)) === false) {
            throw new \RuntimeException($query->error);
        }

        if ($query->execute() === false) {
            throw new \RuntimeException($query->error);
        }

        return $query;
    }

    /**
     * Counts the total number of results.
     *
     * @return int
     */
    protected function totalResults(): int
    {
        $time = $this->generateTimeSQL();
        $where = SQLFragment::combine(' AND ', $time);
        $sql = 'SELECT COUNT(*) FROM audit_log WHERE ' . $where->sql;
        $query = Config::get_instance()->db->prepare($sql);

        if ($query === false) {
            throw new \RuntimeException(Config::get_instance()->db->error);
        }

        // Prepare the query.
        $types = [$where->param_types];

        $arguments = [];
        foreach ($where->params as &$param) {
            $arguments[] = &$param;
        }

        if (call_user_func_array(array($query, 'bind_param'), array_merge($types, $arguments)) === false) {
            throw new \RuntimeException($query->error);
        }

        if ($query->execute() === false) {
            throw new \RuntimeException($query->error);
        }

        $counter = 0;
        // fetch total items count
        $query->bind_result($count);
        while ($query->fetch()) {
            $counter += $count;
        }
        $query->close();

        return $counter;
    }
}
