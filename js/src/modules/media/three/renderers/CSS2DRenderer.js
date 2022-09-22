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
// CSS2 rendering functions for 3d objects
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
// A modularised version of the example from mrdoob / http://mrdoob.com/
// https://github.com/mrdoob/three.js/blob/master/examples/js/renderers/CSS2DRenderer.js

define(['three', 'CSS2DObject'], function (THREE, CSS2DObject) {
    return function() {
        var _width, _height;
        var _widthHalf, _heightHalf;

        var vector = new THREE.Vector3();
        var viewMatrix = new THREE.Matrix4();
        var viewProjectionMatrix = new THREE.Matrix4();

        var domElement = document.createElement( 'div' );
        domElement.style.overflow = 'hidden';

        this.domElement = domElement;

        this.getSize = function () {

            return {
                width: _width,
                height: _height
            };

        };

        this.setSize = function ( width, height ) {

            _width = width;
            _height = height;

            _widthHalf = _width / 2;
            _heightHalf = _height / 2;

            domElement.style.width = width + 'px';
            domElement.style.height = height + 'px';

        };

        var renderObject = function ( object, camera ) {

            if ( object instanceof CSS2DObject ) {

                vector.setFromMatrixPosition( object.matrixWorld );
                vector.applyMatrix4( viewProjectionMatrix );

                var element = object.element;
                var style = 'translate(-50%,-50%) translate(' + ( vector.x * _widthHalf + _widthHalf ) + 'px,' + ( - vector.y * _heightHalf + _heightHalf ) + 'px)';

                element.style.WebkitTransform = style;
                element.style.MozTransform = style;
                element.style.oTransform = style;
                element.style.transform = style;

                if ( element.parentNode !== domElement ) {

                    domElement.appendChild( element );

                }

            }

            for ( var i = 0, l = object.children.length; i < l; i ++ ) {

                renderObject( object.children[ i ], camera );

            }

        };

        this.render = function ( scene, camera ) {

            scene.updateMatrixWorld();

            if ( camera.parent === null ) camera.updateMatrixWorld();

            viewMatrix.copy( camera.matrixWorldInverse );
            viewProjectionMatrix.multiplyMatrices( camera.projectionMatrix, viewMatrix );

            renderObject( scene, camera );

        };
    }
});
