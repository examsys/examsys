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
 * A media object
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 * @package core
 */
class MediaObject
{
    /** @var int The database id of the media object. */
    public $id;

    /** @var string The file name of the media object. */
    public $source;

    /** @var int The width of the media object. */
    public $width;

    /** @var int The height of the media object. */
    public $height;

    /** @var string The alternate text of the media object. */
    public $alt;

    /** @var int The owner of the media object. */
    public $owner;

    /** @var int The display number of the media object. */
    public $num;

    /**
     * Media Object constructor.
     *
     * @param int $id Database id of the media object.
     * @param string $source The file name of the media object.
     * @param ?int $width The width of the media object.
     * @param ?int $height The height of the media object.
     * @param ?string $alt The alt of the media object.
     * @param int $owner The owner of the media object.
     * @param int $num The display number the media object.
     */
    public function __construct(int $id, string $source, ?int $width, ?int $height, ?string $alt, int $owner, int $num)
    {
        $this->id = $id;
        $this->source = $source;
        $this->width = $width;
        $this->height = $height;
        $this->alt = $alt;
        $this->owner = $owner;
        $this->num = $num;
    }
}
