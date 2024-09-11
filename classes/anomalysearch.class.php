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
 * Class that searches anomaly history.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 */
class AnomalySearch extends Search
{
    /** @var string The ordering clause for the query. */
    protected string $orderby = 'a.time DESC';

    /** @var int A time to search from. */
    protected int $starttime;

    /** @var int A time to search to. */
    protected int $endtime;

    /** @var int A paper to search. */
    protected int $paperid;

    /** @var int The paper type that is being searched for. */
    protected int $papertype;

    /** @var bool Filter by students only. */
    protected bool $studentonly;

    /**
     * Constructor for the anomaly search.
     *
     * @param int $papertype
     */
    public function __construct(int $papertype)
    {
        $this->papertype = $papertype;
    }

    /**
     * Gets the fields that will be used in the query.
     *
     * @return array|string[]
     */
    protected function getFields(): array
    {
        return [
            'u.id',
            'u.first_names',
            'u.surname',
            's.student_id',
            'a.type',
            'a.time',
            'a.details',
            'a.screen'
        ];
    }

    /**
     * Returns the SQL for the start time filter
     *
     * @return SQLFragment
     */
    protected function generateStartTimeSQL(): SQLFragment
    {
        $interval = \log::getStartInterval($this->papertype) * 60;
        $sql = new SQLFragment();
        $sql->sql = "(a.time + $interval) >= ?";
        $sql->addParameter($this->starttime, SQLFragment::TYPE_INTEGER);
        return $sql;
    }

    /**
     * Returns the SQL for the end time filter
     *
     * @return SQLFragment
     */
    protected function generateEndTimeSQL(): SQLFragment
    {
        $sql = new SQLFragment();
        $sql->sql = 'a.time <= ?';
        $sql->addParameter($this->endtime, SQLFragment::TYPE_INTEGER);
        return $sql;
    }

    /**
     * Returns the SQL for the start time filter
     *
     * @return SQLFragment
     */
    protected function generatePaperSQL(): SQLFragment
    {
        $sql = new SQLFragment();
        $sql->sql = 'a.paperID = ?';
        $sql->addParameter($this->paperid, SQLFragment::TYPE_INTEGER);
        return $sql;
    }

    /**
     * Returns the SQL user filter
     *
     * @return SQLFragment
     */
    protected function generateUserSQL(): SQLFragment
    {
        $sql = new SQLFragment();

        $roles = '';
        if ($this->studentonly) {
            $roles = \log::get_student_only('u.id');
        }
        $sql->sql = $roles . 'LEFT JOIN sid s ON s.userID = u.id';
        return $sql;
    }

    /**
     * Sets the time that should be searched from/to.
     *
     * @param int $start the start time
     * @param int $end the end time
     * @param int $paper the paper
     * @param bool $studentonly filter by students
     */
    public function setParameters(int $start, int $end, int $paper, bool $studentonly): void
    {
        $this->starttime = $start;
        $this->endtime = $end;
        $this->paperid = $paper;
        $this->studentonly = $studentonly;
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

        $start = $this->generateStartTimeSQL();
        $end = $this->generateEndTimeSQL();
        $paper = $this->generatePaperSQL();
        $where = SQLFragment::combine(' AND ', $paper, $start, $end);
        $join = $this->generateUserSQL();
        $sql = 'SELECT ' . $fields . ' FROM anomaly a JOIN users u ON u.id = a.userID '
            . $join->sql . ' WHERE ' . $where->sql . ' ORDER BY ' . $this->orderby
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
        $start = $this->generateStartTimeSQL();
        $end = $this->generateEndTimeSQL();
        $paper = $this->generatePaperSQL();
        $where = SQLFragment::combine(' AND ', $paper, $start, $end);
        $join = $this->generateUserSQL();
        $sql = 'SELECT count(*) FROM anomaly a JOIN users u ON u.id = a.userID '
            . $join->sql . ' WHERE ' . $where->sql;
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
