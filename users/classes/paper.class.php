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
 * Stores paper assessment details.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2019 The University of Nottingham
 */
class Paper
{
    /** @var string The time the paper was started. */
    public $log_started;

    /** @var int The paper attempt unique id. */
    public $metadataID;

    /** @var int The highest screen the user got to on the paper. */
    public $max_screen;

    /** @var float The users highest mark from all attempts at paper. */
    public $max_mark;

    /** @var string The paper type. */
    public $paper_type;

    /** @var string The original paper type. Used by Progress tests converted to formatives so exam script can be displayed */
    public $original_paper_type;

    /** @var string The human readble version of $log_started. */
    public $human_log_started;

    /** @var string The percentage mark.  */
    public $percent;
}
