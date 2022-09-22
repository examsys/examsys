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
 * Base class for searching.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
abstract class Search
{
    /** The number of users to fetch at a time. */
    public const DEFAULT_LIMIT = 100;

    /** @var int The number of results  */
    protected $limit = 100;

    /** @var int The first result to return. */
    protected $offset = 0;

    /** @var int The batch of results to return. */
    protected $page = 1;

    /**
     * Gets the total number of records that match the criteria.
     *
     * @return int
     */
    abstract protected function totalResults(): int;

    /**
     * Runs the query for the search.
     *
     * @return \mysqli_stmt
     */
    abstract protected function doQuery(): mysqli_stmt;

    /**
     * Gets the results for the search.
     *
     * @return \SearchResult
     */
    public function execute(): SearchResult
    {
        $result = new SearchResult();
        $result->total = $this->totalResults();
        $result->query = $this->doQuery();
        $result->last = min(($this->offset + $this->limit), $result->total);
        // When there are no results first should be 0.
        $result->first = ($result->last === 0) ? 0 : ($this->offset + 1);
        $result->pages = ceil($result->total / $this->limit);
        return $result;
    }

    /**
     * Sets the number of results per page.
     *
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        if ($limit < 1) {
            $limit = static::DEFAULT_LIMIT;
        }
        $this->limit = $limit;
        $this->calculateOffeset();
    }

    /**
     * Sets the page of results to be displayed.
     *
     * @param int $page
     */
    public function setPage(int $page): void
    {
        if ($page < 1) {
            $page = 1;
        }
        $this->page = $page;
        $this->calculateOffeset();
    }

    /**
     * Recalculates the offset.
     */
    protected function calculateOffeset(): void
    {
        $this->offset = ($this->page - 1) * $this->limit;
    }
}
