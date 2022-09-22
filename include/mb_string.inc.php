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
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

if (!function_exists('mb_ucasefirst')) {
    function mb_ucasefirst($str, $enc = 'UTF-8')
    {
        $first = mb_substr($str, 0, 1, $enc);
        $rest = mb_substr($str, 1, mb_strlen($str, $enc) - 1, $enc);

        return mb_strtoupper($first, $enc) . $rest;
    }
}

// Taken from:
// SiT (Support Incident Tracker) - Support call tracking system
// Copyright (C) 2010-2011 The Support Incident Tracker Project
// Copyright (C) 2000-2009 Salford Software Ltd. and Contributors
if (!function_exists('mb_substr_replace')) {

    function mb_substr_replace($string, $replacement, $start, $length = null, $encoding = null)
    {
        if ($encoding == null) {
            if ($length == null) {
                return mb_substr($string, 0, $start) . $replacement;
            } else {
                return mb_substr($string, 0, $start) . $replacement . mb_substr($string, $start + $length);
            }
        } else {
            if ($length == null) {
                return mb_substr($string, 0, $start, $encoding) . $replacement;
            } else {
                return mb_substr($string, 0, $start, $encoding) . $replacement . mb_substr($string, $start + $length, mb_strlen($string, $encoding), $encoding);
            }
        }
    }
}
