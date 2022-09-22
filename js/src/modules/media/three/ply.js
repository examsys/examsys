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
// ply 3D object rendering
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

define(['three', 'threeshared', 'PLYLoader', 'TrackballControls', 'jquery'], function(THREE, SHARED, PLYLoader, TrackballControls, $) {
    /**
     * A ply 3D object
     * @param id 3d object identifier
     */
    return function(id) {
        this.id = id;
        /**
         * Set up ply object
         * @param string file the object file
         * @param integer width width of renderer
         * @param integer height height of renderer
         * @param boolean delay flag to stop setup
         * @return boolean false on error
         */
        this.init = function (file, width, height, delay) {
            this.file = file;
            if (delay !== true) {
                $("#" + this.id + "_threeloading").show();
                this.scene = new THREE.Scene()
                this.scene.background = new THREE.Color(0xffffff);
                this.camera = new THREE.PerspectiveCamera(70, width / height, 1, 5000);
                this.scene.add(this.camera);
                var light = new THREE.AmbientLight(0xffffff);
                this.scene.add(light);

                this.renderer = new THREE.WebGLRenderer({antialias: true});

                // Check required webgl extension exists.
                if (!SHARED.detectweblextensions(this.renderer, "WEBGL_depth_texture")) {
                    return false;
                }
                this.renderer.setPixelRatio(window.devicePixelRatio);
                this.renderer.setSize(width, height);

                this.renderer.shadowMap.enabled = true;

                this.load();
            }
            return true;
        },
        /**
         * Load the ply file
         */
        this.load = function () {
            var scope = this;
            var manager = new THREE.LoadingManager();
            manager.onLoad = function () {
                $("#" + scope.id + "_threeloading").remove();
                $("#" + scope.id).append(scope.renderer.domElement);
                scope.controls = new TrackballControls(scope.camera, scope.renderer.domElement);
                scope.controls.minDistance = 0;
                scope.controls.maxDistance = 2000;
                SHARED.animate(scope.renderer, scope.labelRenderer, scope.scene, scope.camera, scope.controls);
            };
            var loader = new PLYLoader(manager);
            loader.load(scope.file, function (geometry) {
                var material = new THREE.MeshStandardMaterial({vertexColors: THREE.VertexColors});
                geometry.center();
                geometry.computeBoundingBox();
                var boundingbox = geometry.boundingBox;
                scope.camera.position.z = scope.defaultcameraposition = SHARED.positioncamera(boundingbox, scope.camera);
                var mesh = new THREE.Mesh(geometry, material);
                scope.scene.add(mesh);
                SHARED.render(scope.renderer, scope.labelRenderer, scope.scene, scope.camera);
            });
        },
        /**
         * Reset the camera
         */
        this.reset = function() {
            this.controls.reset();
            this.camera.position.z = this.defaultcameraposition;
        }
    }
});
