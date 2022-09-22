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
 * Passed through the postauthfail callbacks. Stores settings of what it does when it finishes the callback.
 *
 * @author Simon Atack
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package Authentication
 */
class postauthfailreturn extends stdClass
{
    public $attempt;
    public $form;
    public $url;
    public $callback;
    public $stop;
    public $exit;

    public function __construct()
    {
        $this->attempt = $_SESSION['authenticationObj']['attempt'];
        $this->stop = false;
        $this->exit = false;
    }
}
