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
 * The 3D media handler
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2018 The University of Nottingham
 */
class threed_handler extends media_handler
{
    /**
     * Render three js files
     * @param $string translation array
     */
    public static function render_js($string)
    {
        $configObject = Config::get_instance();
        $render = new render($configObject);
        $data['threeenabled'] = $configObject->get_setting('core', 'paper_threejs');
        $render->render($data, $string, 'threejs.html');
    }

    /**
     * Get the js files required to load 3D images.
     * @return array
     */
    public static function get_js()
    {
        $js = array(
        '/js/threeinit.min.js',
        );
        return $js;
    }

    /**
     * Get the css files required to load 3D images.
     * @return array
     */
    public static function get_css()
    {
        return array('/css/three.css');
    }
}
