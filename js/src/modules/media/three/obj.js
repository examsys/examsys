// This file is part of Rogo
//
// Rogo is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogo is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogo.  If not, see <http://www.gnu.org/licenses/>.
//
// obj 3D object rendering
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

define(['three', 'threeshared', 'OBJLoader', 'MTLLoader', 'DDSLoader', 'TrackballControls', 'jquery'], function(THREE, SHARED, OBJLoader, MTLLoader, DDSLoader, TrackballControls, $) {
    /**
     * An obj 3D object
     * @param id 3d object identifier
     */
    return function(id) {
        this.id = id;
        /**
         * Set up obj (with mtl) object
         * @param string id object identifier
         * @param string file the archive file
         * @param string obj the object file
         * @param string mtl the material file
         * @param integer width width of renderer
         * @param integer height height of renderer
         */
        this.init = function (file, mtl, obj, width, height, delay) {
            this.file = file;
            this.mtl = mtl;
            this.obj = obj;
            if (delay !== true) {
                $("#" + this.id + "_threeloading").show();
                this.scene = new THREE.Scene()
                this.scene.background = new THREE.Color(0xffffff);
                this.camera = new THREE.PerspectiveCamera(70, width / height, 1, 5000);
                this.scene.add(this.camera);

                var light = new THREE.AmbientLight(0xffffff, 0.8);
                this.scene.add(light);

                this.renderer = new THREE.WebGLRenderer({antialias: true});
                this.renderer.setPixelRatio(window.devicePixelRatio);
                this.renderer.setSize(width, height);

                return this.load();
            }
        },
        /**
         * Load the obj file
         * @return boolean
         */
        this.load = function () {
            var file, dir;
            var scope = this;
            var manager = new THREE.LoadingManager();
            manager.onLoad = function () {
                $("#" + scope.id + "_threeloading").remove();
                $("#" + scope.id).append(scope.renderer.domElement);scope.controls = new TrackballControls(scope.camera, scope.renderer.domElement);
                scope.controls.minDistance = 0;
                scope.controls.maxDistance = 2000;
                SHARED.animate(scope.renderer, scope.labelRenderer, scope.scene, scope.camera, scope.controls);
            };
            // Only obj file supplied so display skeleton.
            if (scope.mtl === "" && scope.obj === "") {
                var loader = new OBJLoader(manager);
                loader.load(scope.file, function (object) {
                    var boundingbox = new THREE.Box3().setFromObject(object);
                    scope.camera.position.z = scope.defaultcameraposition = SHARED.positioncamera(boundingbox, scope.camera);
                    scope.scene.add(object);
                    SHARED.render(scope.renderer, scope.labelRenderer, scope.scene, scope.camera);
                });
                // Archive include obj file but not materials, try to load jpg texture.
            } else if (scope.mtl === "") {
                // Check required webgl extension exists.
                if (!SHARED.detectweblextensions(this.renderer, "WEBGL_depth_texture")) {
                    return false;
                }
                file = scope.file.substr(scope.file.indexOf('filename=') + 9);
                dir = file.replace('.zip', '');
                // Loader.
                loader = new OBJLoader(manager);
                loader.setPath('/getfile.php?type=media&filename=' + dir + '/');
                loader.load(scope.obj, function (object) {
                    var textureloader = new THREE.TextureLoader();
                    textureloader.setPath('/getfile.php?type=media&filename=' + dir + '/');
                    var texture = textureloader.load(scope.obj.replace('.obj', '.jpg'));
                    object.traverse(function (child) {
                        if (child.isMesh) child.material.map = texture;
                    });
                    var boundingbox = new THREE.Box3().setFromObject(object);
                    scope.camera.position.z = scope.defaultcameraposition = SHARED.positioncamera(boundingbox, scope.camera);
                    scope.scene.add(object);
                    SHARED.render(scope.renderer, scope.labelRenderer, scope.scene, scope.camera);
                });
                // Archive include obj file and materials.
            } else {
                file = scope.file.substr(scope.file.indexOf('filename=') + 9);
                dir = file.replace('.zip', '');
                manager.addHandler(/\.dds$/i, new DDSLoader());
                loader = new MTLLoader(manager);
                loader.setPath('/getfile.php?type=media&filename=' + dir + '/');
                loader.load(scope.mtl, function (materials) {
                    materials.preload();
                    var loader2 = new OBJLoader();
                    loader2.setMaterials(materials);
                    loader2.setPath('/getfile.php?type=media&filename=' + dir + '/');
                    loader2.load(scope.obj, function (object) {
                        var boundingbox = new THREE.Box3().setFromObject(object);
                        scope.camera.position.z = scope.defaultcameraposition = SHARED.positioncamera(boundingbox, scope.camera);
                        scope.scene.add(object);
                    });
                    SHARED.render(scope.renderer, scope.labelRenderer, scope.scene, scope.camera);
                });
            }
            return true;
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
