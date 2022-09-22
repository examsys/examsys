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
 *
 * Data container for a Lab entity
 *
 * @author Ben Parish
 * @version 1.0
 * @copyright Copyright (c) 2014 string University of Nottingham
 * @package
 */
class Lab
{
    private $id;
    private $name;
    private $campus;
    private $building;
    private $room_no;
    private $timetabling;
    private $it_support;
    private $plagiarism;

    /**
     * @return string $id
     */
    public function &get_id()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function set_id($id)
    {
        $this->id = $id;
    }

    /**
     * @return string $name
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function set_name($name)
    {
        $this->name = $name;
    }

    /**
     * @return string $campus
     */
    public function get_campus()
    {
        return $this->campus;
    }

    /**
     * @param string $campus
     */
    public function set_campus($campus)
    {
        $this->campus = $campus;
    }

    /**
     * @return string $building
     */
    public function get_building()
    {
        return $this->building;
    }

    /**
     * @param string $building
     */
    public function set_building($building)
    {
        $this->building = $building;
    }

    /**
     * @return string $room_no
     */
    public function get_room_no()
    {
        return $this->room_no;
    }

    /**
     * @param string $room_no
     */
    public function set_room_no($room_no)
    {
        $this->room_no = $room_no;
    }

    /**
     * @return string $timetabling
     */
    public function get_timetabling()
    {
        return $this->timetabling;
    }

    /**
     * @param string $timetabling
     */
    public function set_timetabling($timetabling)
    {
        $this->timetabling = $timetabling;
    }

    /**
     * @return string $it_support
     */
    public function get_it_support()
    {
        return $this->it_support;
    }

    /**
     * @param string $it_support
     */
    public function set_it_support($it_support)
    {
        $this->it_support = $it_support;
    }

    /**
     * @return string $plagiarism
     */
    public function get_plagiarism()
    {
        return $this->plagiarism;
    }

    /**
     * @param string $plagiarism
     */
    public function set_plagiarism($plagiarism)
    {
        $this->plagiarism = $plagiarism;
    }
}
