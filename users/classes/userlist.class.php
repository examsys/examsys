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

namespace users;

/**
 * A list of users.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */
class UserList implements \Iterator
{
    /** @var User[] The users in the list. */
    protected $users = [];

    /** @var int The position of iteration in the class. */
    protected $position = 0;

    /**
     * Adds a user to the list.
     *
     * @param \users\User $user
     * @return void
     */
    public function add(User $user): void
    {
        $this->users[] = $user;
    }

    /**
     * @see \Iterator::current()
     * @link http://php.net/manual/en/iterator.current.php
     */
    public function current()
    {
        return ($this->valid()) ? $this->users[$this->position] : null;
    }

    /**
     * Gets all the users.
     *
     * @return User[]
     */
    public function get_all(): array
    {
        return $this->users;
    }

    /**
     * @see \Iterator::key()
     * @link http://php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        return ($this->valid()) ? $this->position : null;
    }

    /**
     * Get the number of users in the list.
     *
     * @return int
     */
    public function length(): int
    {
        return count($this->users);
    }

    /**
     * @see \Iterator::next()
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * @see \Iterator::rewind()
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @see \Iterator::valid()
     * @link http://php.net/manual/en/iterator.valid.php
     */
    public function valid(): bool
    {
        return isset($this->users[$this->position]);
    }
}
