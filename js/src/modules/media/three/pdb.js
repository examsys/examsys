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
// pdb 3D object rendering
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

define(['three', 'threeshared', 'CSS2DRenderer', 'PDBLoader', 'CSS2DObject', 'TrackballControls', 'jquery'], function(THREE, SHARED, CSS2DRenderer, PDBLoader, CSS2DObject, TrackballControls, $) {
    /**
     * A pdb 3D object
     * @param id 3d object identifier
     */
    return function(id) {
        this.id = id;
        /**
         * Set up pdb object
         * @param string file the object file
         * @param integer width width of renderer
         * @param integer height height of renderer
         * @param boolean delay flag to stop setup
         */
        this.init = function(file, width, height, delay) {
            this.file = file;
            if (delay !== true) {
                $("#" + this.id + "_threeloading").show();
                this.scene = new THREE.Scene()
                this.scene.background = new THREE.Color(0xffffff);
                this.camera = new THREE.PerspectiveCamera(70, width / height, 1, 5000);

                this.camera.position.z = 500;
                this.defaultcameraposition = this.camera.position.z;
                this.scene.add(this.camera);

                var light = new THREE.AmbientLight(0xffffff, 0.8);
                this.scene.add(light);
                this.root = new THREE.Group();
                this.scene.add(this.root);

                this.renderer = new THREE.WebGLRenderer({antialias: true});
                this.renderer.setPixelRatio(window.devicePixelRatio);
                this.renderer.setSize(width, height);

                this.labelRenderer = new CSS2DRenderer();
                this.labelRenderer.setSize(width, height);
                this.labelRenderer.domElement.style.position = 'relative';
                var top = '-' + (height + 5) + 'px';
                this.labelRenderer.domElement.style.top = top;
                this.labelRenderer.domElement.style.pointerEvents = 'none';

                this.load();
            }
            return true;
        },

        /**
         * Load the pdb file
         */
        this.load = function() {
            var scope = this;
            var manager = new THREE.LoadingManager();
            manager.onLoad = function () {
                $("#" + scope.id + "_threeloading").remove();
                $("#" + scope.id).append(scope.renderer.domElement);
                $("#" + scope.id).append(scope.labelRenderer.domElement);
                scope.controls = new TrackballControls(scope.camera, scope.renderer.domElement);
                scope.controls.minDistance = 0;
                scope.controls.maxDistance = 2000;
                SHARED.animate(scope.renderer, scope.labelRenderer, scope.scene, scope.camera, scope.controls);
            };
            var loader = new PDBLoader(manager);
            var offset = new THREE.Vector3();
            while (scope.root.children.length > 0) {
                var object = scope.root.children[0];
                object.parent.remove(object);
            }
            loader.load(scope.file, function (molecule) {
                var geometryAtoms = molecule.geometryAtoms;
                var geometryBonds = molecule.geometryBonds;
                var json = molecule.json;
                var boxGeometry = new THREE.BoxBufferGeometry(1, 1, 1);
                var sphereGeometry = new THREE.IcosahedronBufferGeometry(1, 2);
                geometryAtoms.computeBoundingBox();
                geometryAtoms.boundingBox.getCenter(offset).negate();
                geometryAtoms.translate(offset.x, offset.y, offset.z);
                geometryBonds.translate(offset.x, offset.y, offset.z);
                var positions = geometryAtoms.getAttribute('position');
                var colors = geometryAtoms.getAttribute('color');
                var position = new THREE.Vector3();
                var color = new THREE.Color();
                for (var i = 0; i < positions.count; i++) {
                    position.x = positions.getX(i);
                    position.y = positions.getY(i);
                    position.z = positions.getZ(i);
                    color.r = colors.getX(i);
                    color.g = colors.getY(i);
                    color.b = colors.getZ(i);
                    var material = new THREE.MeshPhongMaterial({color: color});
                    var object2 = new THREE.Mesh(sphereGeometry, material);
                    object2.position.copy(position);
                    object2.position.multiplyScalar(75);
                    object2.scale.multiplyScalar(25);
                    scope.root.add(object2);
                    var atom = json.atoms[i];
                    var text = document.createElement('div');
                    text.className = 'label';
                    text.style.color = "black";
                    text.style.fontWeight = "bold";
                    text.textContent = atom[4];
                    var label = new CSS2DObject(text);
                    label.position.copy(object2.position);
                    scope.root.add(label);
                }
                positions = geometryBonds.getAttribute('position');
                var start = new THREE.Vector3();
                var end = new THREE.Vector3();
                for (var j = 0; j < positions.count; j += 2) {
                    start.x = positions.getX(j);
                    start.y = positions.getY(j);
                    start.z = positions.getZ(j);
                    end.x = positions.getX(j + 1);
                    end.y = positions.getY(j + 1);
                    end.z = positions.getZ(j + 1);
                    start.multiplyScalar(75);
                    end.multiplyScalar(75);
                    var object3 = new THREE.Mesh(boxGeometry, new THREE.MeshPhongMaterial(0xffffff));
                    object3.position.copy(start);
                    object3.position.lerp(end, 0.5);
                    object3.scale.set(5, 5, start.distanceTo(end));
                    object3.lookAt(end);
                    scope.root.add(object3);
                }
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