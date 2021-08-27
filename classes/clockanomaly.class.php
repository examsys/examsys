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
 * Utility class for clock anomaly related functions.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */
class ClockAnomaly extends Anomaly
{
    /**
     * Clock Anomaly constructor.
     *
     * @param array $data The data.
     */
    public function __construct(array $data)
    {
        $this->type = Anomaly::CLOCK;
        parent::__construct($data);
    }

    /**
     * inheritDoc
     */
    public function getData(): array
    {
        return array('previous' => $this->data['previous'], 'current' => $this->data['current']);
    }

    /**
     * @inheritDoc
     */
    public static function detect($data)
    {
        // Not implemented as detection in javascript.
    }
}
