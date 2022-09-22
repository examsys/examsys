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
 * authobjreturn is the object passed to the auth plugins auth callback
 * and holds the current state of the auth
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package Authentication
 */
class authobjreturn
{
    public $returned;
    public $returneds;
    public $rogoid;
    public $rogoids;
    public $data;
    public $datas;
    public $statuses;
    public $username;
    public $postredirect = false;

    public function __construct()
    {
        $this->returned     = ROGO_AUTH_OBJ_FAILED;
        $this->returneds    = array();
        $this->statuses     = array();
        $this->rogoid           = 0;
        $this->rogoids      = array();
        $this->data             = new stdClass();
        $this->datas            = array();
    }

    /*
     * set the authobjreturn objet to fail state
     * @param int $number - Internal ID of the plugin in the stack.
     */
    public function fail($number)
    {
        $this->returned = ROGO_AUTH_OBJ_FAILED;
        $this->returneds[] = $this->returned;
        $this->statuses[$number] = $this->returned;
        $this->rogoid = 0;
    }

    /*
     * Set the authobjreturn object to success state
     * @param int $number - Internal ID of the plugin in the stack.
     * @param int $rogoid - User ID of the successful user.
     */
    public function success($number, $rogoid)
    {
        $this->rogoid = $rogoid;
        $this->rogoids[] = $this->rogoid;
        $this->returned = ROGO_AUTH_OBJ_SUCCESS;
        $this->returneds[] = $this->returned;
        $this->statuses[$number] = $this->returned;
    }

    /*
     * Set the authobjreturn object to lookup state
     * @param int $number  - Internal ID of the plugin in the stack.
     * @param object $data - Data for user to be looked up.
     */
    public function lookupmissing($number, $data)
    {
        $this->rogoid = 0;
        $this->returned = ROGO_AUTH_OBJ_LOOKUPONLY;
        $this->returneds[] = $this->returned;
        $this->statuses[$number] = $this->returned;
        $this->data = $data;
        $this->datas[] = $this->data;
    }
}
