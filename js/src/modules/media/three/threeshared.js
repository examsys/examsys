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
//
// shared three js helper functions
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

define([], function () {
    return {
        /**
         * Animate the object
         * @param object renderer object renderer
         * @param object labelRenderer object label renderer
         * @param object scene object scene
         * @param object camera object camera
         * @param object controls object controls
         */
        animate: function(renderer, labelRenderer, scene, camera, controls) {
            var scope = this;
            controls.update();
            scope.render(renderer, labelRenderer, scene, camera);
            requestAnimationFrame(function () {
                scope.animate(renderer, labelRenderer, scene, camera, controls);
            });
        },
        /**
         * Render the object
         * @param object renderer object renderer
         * @param object labelRenderer object label renderer
         * @param object scene object scene
         * @param object camera object camera
         */
        render: function(renderer, labelRenderer, scene, camera) {
            renderer.render(scene, camera);
            // Render labels if present.
            if (labelRenderer !== undefined) {
                labelRenderer.render(scene, camera);
            }
        },
        /**
         * Position the camera based on the objects width/height
         * @param box3 boundingbox boundingbox of of object
         * @return float
         */
        positioncamera: function(boundingbox) {
            var width = boundingbox.max.x - boundingbox.min.x;
            var height = boundingbox.max.y - boundingbox.min.y;
            return Math.max(height, width) * 2;
        },
        /**
         * Detect if the web browser supports the required web gl extenstions
         * @param object renderer object renderer
         * @param string extension the webgl extension
         * @return bool
         */
        detectweblextensions: function (renderer, extension) {
            var canvas = document.createElement( 'canvas' );
            if (!(window.WebGL2RenderingContext && canvas.getContext('webgl2'))) {
                var gl = renderer.getContext();
                var ext = gl.getExtension(extension);
                if (!ext) {
                    return false;
                }
            }
            return true;
        }
    }
});
