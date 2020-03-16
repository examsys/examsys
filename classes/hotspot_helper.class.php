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
 * Class of helper functions for html5 hotspot questions.
 *
 * @author Neill Magill
 * @copyright Copyright (c) 2016 The University of Nottingham
 * @package core
 */
class hotspot_helper extends RogoStaticSingleton
{
    /** The character that separates layers in the correct answer. */
    const LAYER_SEPARATOR = '|';

    /** The character that separates information in the correct answer. */
    const CORRECT_SEPARATOR = '~';

    /** The character that separates information in the user anser. */
    const ANSWER_SEPARATOR = ',';

    /** An unaswered question */
    const UNSWERED_QUESTION = array('u');

    /**
     * The active instance of this class.
     *
     * @var html5_helper
     */
    protected static $inst;

    /**
     * The name of the class that is instantiated as the singleton.
     *
     * @var string
     */
    protected static $class_name = 'hotspot_helper';

    /**
     * Change the correct answer into a form that is sutiable for the hotspot answer mode.
     *
     * @param string $correct The correct answer stored in the database.
     * @return string
     */
    public function correct_to_answer_mode($correct)
    {
        $layers = explode(self::LAYER_SEPARATOR, $correct);
        foreach ($layers as $key => $layer) {
            $layers[$key] = $this->remove_layer_coordinates($layer);
        }
        return implode(self::LAYER_SEPARATOR, $layers);
    }

    /**
     * Get the parts of the correct answer that are of interest to a question in answer mode.
     *
     * @param string $layer
     * @return string
     */
    protected function remove_layer_coordinates($layer)
    {
        $parts = explode(self::CORRECT_SEPARATOR, $layer);
        $return = array();
        if (count($parts) > 1) {
            // Get the name.
            $return[] = array_shift($parts);
            $secondpart = array_shift($parts);
            if ($secondpart !== 'polygon' && $secondpart !== 'rectangle' && $secondpart !== 'ellipse') {
                // There is no colour, only shape information.
                $return[] = $secondpart;
            }
            $return[] = '';
        }
        return implode(self::CORRECT_SEPARATOR, $return);
    }

    /**
     * Strips if the answer is correct from the answer stored in Rogo.
     *
     * The result is the format needed by questions in answer mode.
     *
     * @param string $answer
     * @return string
     */
    public function answer_strip_correct_information($answer)
    {
        $layers = explode(self::LAYER_SEPARATOR, $answer);
        foreach ($layers as $key => $layer) {
            $layers[$key] = $this->layer_answer_strip_correct_information($layer);
        }
        return implode(self::LAYER_SEPARATOR, $layers);
    }

    /**
     * Strips if the answer is correct from a single layers answer.
     *
     * The result is the format needed by questions in answer mode.
     *
     * @param string $answer
     * @return string
     */
    public function layer_answer_strip_correct_information($answer)
    {
        // A hotspot answer is stored in the form:
        // correct,x,y
        // The answer mode questions are in the form:
        // x,y
        // Unanswered questions are in the form:
        // u
        $parts = explode(self::ANSWER_SEPARATOR, $answer);
        if ($parts[0] != 'u' and count($parts) === 3) {
            array_shift($parts);
        }
        return implode(self::ANSWER_SEPARATOR, $parts);
    }

    /**
     * Marks a user's answer.
     *
     * @param string $answers
     * @param string $correct
     * @return string
     */
    public function mark($answers, $correct)
    {
        // Assume all the parts are unanswered.
        $all_unanswered = true;
        $layer_answers = explode(self::LAYER_SEPARATOR, $answers);
        $layer_correct = explode(self::LAYER_SEPARATOR, $correct);
        // The number of answers and layers should match.
        foreach ($layer_correct as $key => $correct_answer) {
            if (!isset($layer_answers[$key])) {
                $layer_answers[$key] = 'u';
            }
            $layer_answers[$key] = $this->mark_layer($layer_answers[$key], $correct_answer);
            if ($layer_answers[$key] !== 'u') {
                // An answer was provided.
                $all_unanswered = false;
            }
        }

        if ($all_unanswered) {
            return 'u';
        }
        return implode(self::LAYER_SEPARATOR, $layer_answers);
    }

    /**
     * Tests if the answer for the layer is correct.
     *
     * @param string $answer
     * @param string $correct
     * @return string
     */
    protected function mark_layer($answer, $correct)
    {
        $answer_parts = explode(self::ANSWER_SEPARATOR, $this->layer_answer_strip_correct_information($answer));
        $correct_parts = explode(self::CORRECT_SEPARATOR, $correct);
        if (count($answer_parts) !== 2) {
            // Not a valid answer so mark it as unanswered.
            return 'u';
        }
        // The first value of the correct parts will be the layer name, followed by the colour of the layer.
        // everything after this point until describes the shapes.
        $shapes = array_slice($correct_parts, 2);
        // Each shape will have two entries, the type followed by the coordinates.
        $number_of_shapes = floor(count($shapes) / 3);
        $correct = false;
        for ($i = 0; $i < $number_of_shapes; $i++) {
            // The in_shape method will no longer be called if the answer was found in a preceeding shape.
            $correct = $correct || $this->in_shape($answer_parts, $shapes[$i * 3], $shapes[($i * 3) + 1]);
        }
        if ($correct) {
            array_unshift($answer_parts, '1');
        } else {
            array_unshift($answer_parts, '0');
        }
        return implode(self::ANSWER_SEPARATOR, $answer_parts);
    }
  
    /**
     * Test if the answer coordinate is inside the shape.
     *
     * @param array $answer
     * @param string $shape
     * @param string $coordinates
     * @return boolean
     */
    protected function in_shape(array $answer, $shape, $coordinates)
    {
        switch ($shape) {
            case 'ellipse':
                return $this->in_ellipse($answer, $coordinates);
            case 'rectangle':
                return $this->in_rectangle($answer, $coordinates);
            case 'polygon':
                return $this->in_polygon($answer, $coordinates);
            default:
                // Not a shape we can test so assume it is incorrect.
                return false;
        }
    }

    /**
     * Test if the answer is inside an ellipse described by the bounding box.
     *
     * @param array $answer
     * @param string $coordinates
     * @return boolean
     */
    protected function in_ellipse($answer, $coordinates)
    {
        $boundingbox = $this->decode_coordinates(explode(self::ANSWER_SEPARATOR, $coordinates));
        $centre_x = ($boundingbox[0] + $boundingbox[2]) / 2;
        $centre_y = ($boundingbox[1] + $boundingbox[3]) / 2;
        $radius_x = abs($boundingbox[0] - $boundingbox[2]) / 2;
        $radius_y = abs($boundingbox[1] - $boundingbox[3]) / 2;
        if ($radius_x === 0 or $radius_y === 0) {
            // This is a 1 dimensional shape, so the answer cannot be inside it.
            return false;
        }
        // The equation for determining is a point is in an ellipse is:
        // ((x - centre_x)^2/(x_radius)^2) - ((y - centre_y)^2/(y_radius)^2) <= 1
        $check_x = pow($answer[0] - $centre_x, 2) / pow($radius_x, 2);
        $check_y = pow($answer[1] - $centre_y, 2) / pow($radius_y, 2);
        $check = $check_x + $check_y;
        return ($check <= 1);
    }

    /**
     * Test if the answer is within a rectangle.
     *
     * @param array $answer
     * @param string $coordinates
     * @return boolean
     */
    protected function in_rectangle($answer, $coordinates)
    {
        $rectangle = $this->decode_coordinates(explode(self::ANSWER_SEPARATOR, $coordinates));
        $contains_x = false;
        $contains_y = false;
        if ($rectangle[0] < $rectangle[2] && $rectangle[0] <= $answer[0] && $rectangle[2] >= $answer[0]) {
            // The rectangle was draw left to right.
            $contains_x = true;
        } elseif ($rectangle[0] > $rectangle[2] && $rectangle[0] >= $answer[0] && $rectangle[2] <= $answer[0]) {
            // The rectangle was drawn right to left.
            $contains_x = true;
        }
        if ($rectangle[1] < $rectangle[3] && $rectangle[1] <= $answer[1] && $rectangle[3] >= $answer[1]) {
            // The rectangle was drawn top to bottom.
            $contains_y = true;
        } elseif ($rectangle[1] > $rectangle[3] && $rectangle[1] >= $answer[1] && $rectangle[3] <= $answer[1]) {
            // The rectangle was drawn bottom to top.
            $contains_y = true;
        }
        return $contains_x && $contains_y;
    }

    /**
     * Test if the answer is with in a polygon.
     *
     * It uses the winding number method:
     * http://geomalgorithms.com/a03-_inclusion.html
     *
     * Copyright 2000 softSurfer, 2012 Dan Sunday
     * This code may be freely used and modified for any purpose
     * providing that this copyright notice is included with it.
     * SoftSurfer makes no warranty for this code, and cannot be held
     * liable for any real or imagined damage resulting from its use.
     * Users of this code must verify correctness for their application.
     *
     * It has been modified to also mark points on the edge line as correct.
     *
     * @param array $answer
     * @param string $coordinates
     * @return boolean
     */
    protected function in_polygon($answer, $coordinates)
    {
        $vertices = $this->decode_coordinates(explode(self::ANSWER_SEPARATOR, $coordinates));
        if (count($vertices) < 2) {
            // An invalid polygon. Some seem to have a value of 'undefined' in their coordinates. No idea why...
            return false;
        }
        $edges = (count($vertices) / 2);
        $winding_counter = 0;
        // The winding method will not always mark a point as correct if it is on an edge.
        $on_edge = false;
        for ($edge = 0; $edge < $edges; $edge++) {
            $start_point_x = $vertices[2 * $edge];
            $start_point_y = $vertices[(2 * $edge) + 1];
            // The last coordinate will be the same as the first one, so we need to wrap round the coordinate array.
            $index2 = (2 * ($edge + 1)) % count($vertices);
            $end_point_x = $vertices[$index2];
            $end_point_y = $vertices[$index2 + 1];
            // Tests if the point is to the left, right or on the edge (assuming it is infinite in length line)
            // when positive then it is on the left
            // when negative on the right
            // when 0 it intersects the line.
            $intersect = (($start_point_x - $answer[0]) * ($end_point_y - $answer[1])) - (($end_point_x - $answer[0]) * ($start_point_y - $answer[1]));
            if ($start_point_y <= $answer[1]) {
                if ($end_point_y > $answer[1]) {
                    // An upwards crossing.
                    if ($intersect > 0) {
                        // The point is to the left of the edge.
                        $winding_counter++;
                    }
                }
            } else {
                if ($end_point_y <= $answer[1]) {
                    // A downwards crossing.
                    if ($intersect < 0) {
                        // The point is to the right of the edge.
                        $winding_counter--;
                    }
                }
            }
            if ($intersect === 0) {
                // The answer would intersect the edge if it's length was infinate,
                // so we now need to test if the answer is between them.
                // If we assume that the answer is between two points then the
                // distance between those two points should equal the sum of the distance
                // between the user answer and each of the points.
                // This assumption fails if the start and end point are the same.
                $x_distance = $start_point_x - $end_point_x;
                $y_distance = $start_point_y - $end_point_y;
                $x_sum = ($start_point_x - $answer[0]) + ($answer[0] - $end_point_x);
                $y_sum = ($start_point_y - $answer[1]) + ($answer[1] - $end_point_y);
                if ($x_distance === 0 and $y_distance === 0 and $start_point_x == $answer[0] and $start_point_y == $answer[1]) {
                    // The answer is equal to the start and end point.
                    $on_edge = true;
                } elseif (($x_distance === $x_sum) and ($y_distance === $y_sum) and ($x_distance !== 0 or $y_distance !== 0)) {
                    // Intercects and is between the two points. The start and end point are not the same.
                    $on_edge = true;
                }
            }
            // Work out the distance in pixels from the line... if it is less than 1
        }
        // If the counter is equal to 0 the point is outside of the polygon.
        return (($winding_counter !== 0) or $on_edge);
    }

    /**
     * Coordinates are encoded as hexidecimal numbers,
     * this converts them to decimal so our maths will work.
     *
     * @param array $coordinates
     * @return array
     */
    protected function decode_coordinates(array $coordinates)
    {
        foreach ($coordinates as $key => $value) {
            $coordinates[$key] = hexdec($value);
        }
        return $coordinates;
    }

    /**
     * Get an array of language strings used by hotspot questions.
     *
     * @return array
     */
    public function get_lang_strings()
    {
        $lang = LangUtils::loadlangfile('question/html5_hotspot.php');
        return array(
        'allhotspots' => $lang['allhotspots'],
        'correctanswers' => $lang['correctanswers'],
        'hotspots' => $lang['hotspots'],
        'incorrectanswers' => $lang['incorrectanswers'],
        'viewall' => $lang['viewall'],
        'addlayer' => $lang['addlayer'],
        'removelayer' => $lang['removelayer'],
        'removelayerconfirm' => $lang['removelayerconfirm'],
        'move' => $lang['move'],
        'ellipse' => $lang['ellipse'],
        'rectangle' => $lang['rectangle'],
        'polygon' => $lang['polygon'],
        'delete' => $lang['delete'],
        'help' => $lang['help'],
        'colourselect' => $lang['colourselect'],
        'cancel' => $lang['cancel'],
        );
    }
}
