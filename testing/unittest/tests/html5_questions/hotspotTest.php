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

use testing\unittest\UnitTest;

/**
 * Test hotspot marking.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2016 onwards The University of Nottingham
 * @package tests
 *
 * @group html5
 * @group hotspot
 */
class html5hotspottest extends UnitTest
{
    /**
     * Test that correct answers have their coordinates removed by the correct_to_answer_mode method.
     *
     * This tests mupliple shapes with 1 shape per layers.
     */
    public function test_correct_to_answer_mode1()
    {
        // Three layers are defined, the first contains an ellipse, the second a recangle, the third a polygon.
        $input = 'Deer~16776960~ellipse~384,335,51b,3c3~0~|birds~45136~rectangle~fa,51,1db,121~0~|AT-AT~12582912~polygon~15d,154,167,150,18d,144,220,11e,2a1,13e,2a5,153,2e7,183~0~';
        $expected = 'Deer~16776960~|birds~45136~|AT-AT~12582912~';
        $result = hotspot_helper::get_instance()->correct_to_answer_mode($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that correct answers have their coordinates removed by the correct_to_answer_mode method.
     *
     * This tests mupliple shapes with 3 shapes in one layer.
     */
    public function test_correct_to_answer_mode2()
    {
        // Three layers are defined, the first contains an ellipse, the second a recangle, the third a polygon.
        $input = 'Deer~16776960~ellipse~384,335,51b,3c3~0~rectangle~fa,51,1db,121~1~polygon~15d,154,167,150,18d,144,220,11e,2a1,13e,2a5,153,2e7,183~2~';
        $expected = 'Deer~16776960~';
        $result = hotspot_helper::get_instance()->correct_to_answer_mode($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the marking result is removed from a stored user answer.
     */
    public function test_answer_strip_correct_information()
    {
        // Two layers, the first layer the user has answered correctly, the second layer the user has answered incorrectly.
        $input = '1,300,50|0,24,80';
        $expected = '300,50|24,80';
        $result = hotspot_helper::get_instance()->answer_strip_correct_information($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point inside an ellipse is marked as correct.
     */
    public function test_mark_ellipse_correct()
    {
        // The bounding box coordinates are encoded as hexidecimal.
        $correct = 'Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '1115,891';
        $expected = '1,1115,891';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point outside an ellipse is marked as incorrect.
     */
    public function test_mark_ellipse_incorrect()
    {
        // The bounding box coordinates are encoded as hexidecimal.
        $correct = 'Deer~16776960~ellipse~384,335,51b,3c3~0~';
        // One pixel inside the bounding rectangle.
        $answer = '901,821';
        $expected = '0,901,821';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that if an ellipse is one dimensional it is marked as incorrect.
     */
    public function test_mark_ellipse_one_dimensional_incorrect1()
    {
        // The bounding box coordinates are encoded as hexidecimal.
        $correct = 'Deer~16776960~ellipse~1,1,1,9~0~';
        // One pixel inside the bounding rectangle.
        $answer = '1,5';
        $expected = '0,1,5';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that if an ellipse is one dimensional it is marked as incorrect.
     */
    public function test_mark_ellipse_one_dimensional_incorrect2()
    {
        // The bounding box coordinates are encoded as hexidecimal.
        $correct = 'Deer~16776960~ellipse~1,1,9,1~0~';
        // One pixel inside the bounding rectangle.
        $answer = '5,1';
        $expected = '0,5,1';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point inside an rectangle is marked as correct.
     */
    public function test_mark_rectangle_correct()
    {
        // The bounding box coordinates are encoded as hexidecimal.
        $correct = 'birds~45136~rectangle~fa,51,1db,121~0~';
        // Top corner.
        $answer = '250,81';
        $expected = '1,250,81';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point outside an rectangle is marked as incorrect.
     */
    public function test_mark_rectangle_incorrect()
    {
        // The bounding box coordinates are encoded as hexidecimal.
        $correct = 'birds~45136~rectangle~fa,51,1db,121~0~';
        // One pixel outside the top corner.
        $answer = '249,80';
        $expected = '0,249,80';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point inside an polygon is marked as correct.
     */
    public function test_mark_polygon_correct()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~';
        // Top corner.
        $answer = '250,81';
        $expected = '1,250,81';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that answers that intersect the polygon line count as inside.
     */
    public function test_mark_polygon_correct2()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'birds~45136~polygon~1,1,1,9,9,9,9,1~0~';

        $answer = '0.9,0.9';
        $expected = '0,0.9,0.9';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
        $answer = '9.1,9.1';
        $expected = '0,9.1,9.1';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
        $answer = '1.1,1.1';
        $expected = '1,1.1,1.1';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
        $answer = '8.9,8.9';
        $expected = '1,8.9,8.9';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);

        $answer = '1,1';
        $expected = '1,1,1';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);

        $answer = '9,9';
        $expected = '1,9,9';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);

        $answer = '1,9';
        $expected = '1,1,9';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);

        $answer = '9,1';
        $expected = '1,9,1';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that a point outside an polygon is marked as incorrect.
     */
    public function test_mark_polygon_incorrect()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~';
        // One pixel outside the top corner.
        $answer = '249,80';
        $expected = '0,249,80';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * A one dimensional polygon.
     *
     * This test can fail if the edge detection is wrong.
     */
    public function test_mark_1d_polygon_incorrect()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'Question~16711680~polygon~e4,3f,e4,3f~2~';
        // The point is not in the shape.
        $answer = '163,134';
        $expected = '0,163,134';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * A one dimensional polygon.
     *
     * This test can fail if the edge detection is wrong.
     */
    public function test_mark_1d_polygon_correct()
    {
        // A rectangular polygon, that is the same dimensions as the rectangle test shape.
        $correct = 'Question~16711680~polygon~e4,3f,e4,3f~2~';
        // The point is on the line.
        $answer = '228,63';
        $expected = '1,228,63';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that when there are multiple layers that each layer is marked correctly.
     */
    public function test_mark_multiple_layers()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '250,81|901,821';
        $expected = '1,250,81|0,901,821';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test when the user answer is in the first of multiple shapes.
     */
    public function test_mark_multiple_shapes1()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~ellipse~384,335,51b,3c3~1~';
        $answer = '250,81';
        $expected = '1,250,81';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test when the user answer is in the last of multiple shapes.
     */
    public function test_mark_multiple_shapes2()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~ellipse~384,335,51b,3c3~1~';
        $answer = '1115,891';
        $expected = '1,1115,891';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test when the user answer is not in any of multiple shapes.
     */
    public function test_mark_multiple_shapes3()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~ellipse~384,335,51b,3c3~1~';
        $answer = '901,821';
        $expected = '0,901,821';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * No answer for a one layer question.
     */
    public function test_mark_unaswered1()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~ellipse~384,335,51b,3c3~1~';
        $answer = '';
        $expected = 'u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * This is a possible remarking scenario, or not attempted at all.
     */
    public function test_mark_unaswered2()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '';
        $expected = 'u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * No answer, but some interaction.
     */
    public function test_mark_unaswered3()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '|';
        $expected = 'u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }

    /**
     * Partially answered.
     */
    public function test_mark_partial_unaswered()
    {
        $correct = 'birds~45136~polygon~fa,51,fa,121,1db,121,1db,51~0~|Deer~16776960~ellipse~384,335,51b,3c3~0~';
        $answer = '250,81|';
        $expected = '1,250,81|u';
        $result = hotspot_helper::get_instance()->mark($answer, $correct);
        $this->assertEquals($expected, $result);
    }
}
