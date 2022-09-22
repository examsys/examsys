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
 * Re-order questions based on AJAX call from drag and drop list from paper.details.php
 *
 * @author Rob Ingram
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require '../../include/staff_auth.inc';

if (isset($_GET['paperID']) and $_GET['paperID'] != '' and isset($_GET['link']) and is_array($_GET['link'])) {
    $paper_id = $_GET['paperID'];
    $new_order = process_new($_GET['link']);
    $old_order = array();

    $result = $mysqli->prepare('SELECT p_id, question, screen, display_pos FROM papers WHERE paper = ? ORDER BY display_pos;');
    $result->bind_param('i', $paper_id);
    $result->execute();
    $result->store_result();
    $result->bind_result($p_id, $question, $screen, $display_pos);

    while ($result->fetch()) {
        $old_order[$display_pos] = array('screen' => $screen, 'p_id' => $p_id, 'q_id' => $question);
    }
    $result->close();

    $screen_inc = array();
    $screen_dec = array();
    $screen_update = array();
    $position_inc = array();
    $position_dec = array();
    $position_update = array();
    for ($index = 1; $index <= count($new_order); $index++) {
        if ($new_order[$index]['screen'] == ($old_order[$index]['screen'] - 1)) {
            $screen_dec[] = $old_order[$index];
        } elseif ($new_order[$index]['screen'] == ($old_order[$index]['screen'] + 1)) {
            $screen_inc[] = $old_order[$index];
        } elseif ($new_order[$index]['screen'] != $old_order[$index]['screen']) {
            $old_order[$index]['new_screen'] = $new_order[$index]['screen'];
            $screen_update[] = $old_order[$index];
        }

        if ($new_order[$index]['new_pos'] == ($index - 1)) {
            $position_dec[] = $old_order[$index];
        } elseif ($new_order[$index]['new_pos'] == ($index + 1)) {
            $position_inc[] = $old_order[$index];
        } elseif ($new_order[$index]['new_pos'] != $index) {
            $old_order[$index]['new_pos'] = $new_order[$index]['new_pos'];
            $position_update[] = $old_order[$index];
        }
    }

    $ok = true;

    if (($decs = count($screen_dec)) > 0) {
        $dec_list = '';
        // Make list of IDs of questions to decrement
        for ($i = 0; $i < $decs; $i++) {
            if ($i > 0) {
                $dec_list .= ',';
            }
            $dec_list .= $screen_dec[$i]['p_id'];
        }

        $result = $mysqli->prepare('UPDATE papers SET screen = screen-1 WHERE p_id IN (' . $dec_list . ')');
        if ($result) {
            $result->execute();
            $result->close();
        } else {
            $ok = false;
        }
    }

    if ($ok and ($incs = count($screen_inc)) > 0) {
        $inc_list = '';
        // Make list of IDs of questions to increment
        for ($i = 0; $i < $incs; $i++) {
            if ($i > 0) {
                $inc_list .= ',';
            }
            $inc_list .= $screen_inc[$i]['p_id'];
        }

        $result = $mysqli->prepare('UPDATE papers SET screen = screen+1 WHERE p_id IN (' . $inc_list . ')');
        if ($result) {
            $result->execute();
            $result->close();
        } else {
            $ok = false;
        }
    }

    if ($ok and ($upds = count($screen_update)) > 0) {
        for ($i = 0; $i < $upds; $i++) {
            $new_screen = $screen_update[$i]['new_screen'];
            $upd_id = $screen_update[$i]['p_id'];
            $result = $mysqli->prepare('UPDATE papers SET screen = ? WHERE p_id = ?');
            if ($result) {
                $result->bind_param('ii', $new_screen, $upd_id);
                $result->execute();
                $result->close();
            } else {
                $ok = false;
            }
        }
    }

    if ($ok and ($decs = count($position_dec)) > 0) {
        $dec_list = '';
        // Make list of IDs of questions to decrement
        for ($i = 0; $i < $decs; $i++) {
            if ($i > 0) {
                $dec_list .= ',';
            }
            $dec_list .= $position_dec[$i]['p_id'];
        }

        $result = $mysqli->prepare('UPDATE papers SET display_pos = display_pos-1 WHERE p_id IN (' . $dec_list . ')');
        if ($result) {
            $result->execute();
            $result->close();
        } else {
            $ok = false;
        }
    }

    if ($ok and ($incs = count($position_inc)) > 0) {
        $inc_list = '';
        // Make list of IDs of questions to increment
        for ($i = 0; $i < $incs; $i++) {
            if ($i > 0) {
                $inc_list .= ',';
            }
            $inc_list .= $position_inc[$i]['p_id'];
        }

        $result = $mysqli->prepare('UPDATE papers SET display_pos = display_pos+1 WHERE p_id IN (' . $inc_list . ')');
        if ($result) {
            $result->execute();
            $result->close();
        } else {
            $ok = false;
        }
    }

    if ($ok and ($upds = count($position_update)) > 0) {
        for ($i = 0; $i < $upds; $i++) {
            $new_pos = $position_update[$i]['new_pos'];
            $upd_id = $position_update[$i]['p_id'];
            $result = $mysqli->prepare('UPDATE papers SET display_pos = ? WHERE p_id = ?');
            $result->bind_param('ii', $new_pos, $upd_id);
            $result->execute();
            $result->close();
        }
    }

    if (!$ok) {
        echo 'ERROR';
    }
}

function process_new($raw)
{
    $new_order = array();
    $screen = 1;
    $new_pos = 1;

    foreach ($raw as $item) {
        if (mb_strpos($item, 'break') !== false) {
            $screen++;
        } else {
            $new_order[$item] = array('screen' => $screen, 'new_pos' => $new_pos);
            $new_pos++;
        }
    }

    return $new_order;
}
