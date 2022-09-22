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
// CSS2 object for the CSS2 renderer
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
// A modularised version of the example from mrdoob / http://mrdoob.com/
// https://github.com/mrdoob/three.js/blob/master/examples/js/renderers/CSS2DRenderer.js

define(['three'], function (THREE) {

    function CSS2DObject (element) {
        THREE.Object3D.call(this);

        this.element = element;
        this.element.style.position = 'absolute';

        this.addEventListener('removed', function () {
            if (this.element.parentNode !== null) {
                this.element.parentNode.removeChild(this.element);
            }
        });
    }

    CSS2DObject.prototype = Object.create(THREE.Object3D.prototype);
    CSS2DObject.prototype.constructor = this.CSS2DObject;

    return CSS2DObject;
});