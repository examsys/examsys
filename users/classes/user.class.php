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
 * Stores user details.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */
class User
{
    /** @var string The first names og the user. */
    public $firstname;

    /** @var string The course of a student or role of staff. */
    public $grade;

    /** @var int The database id of the user. */
    public $id;

    /** @var The last name of the user. */
    public $lastname;

    /** @var The role(s) of the user. */
    public $role;

    /** @var string The student id of the user. */
    public $studentid;

    /** @var string The title of the user.*/
    public $title;

    /** @var string The username.*/
    public $username;

    /** @var int The year of study for a student. */
    public $year;
}
