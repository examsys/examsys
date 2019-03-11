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
// Requirement functions
//
// @author Dr Joseph Bater <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

/**
 * Set up pdb object
 * @param string id object identifier
 * @param string file the object file
 * @param integer width width of renderer
 * @param integer height height of renderer
 * @param boolean delay flag to stop setup
 */
function initpdb(id, file, width, height, delay) {
    if (delay !== true) {
        threeaddscene(id);

        camera[id] = new THREE.PerspectiveCamera(70, width / height, 1, 5000);
        camera[id].position.z = 500;
        defaultcameraposition[id] = camera[id].position.z;
        scene[id].add(camera[id]);

        var light = new THREE.AmbientLight(0xffffff, 0.8);
        scene[id].add(light);

        root[id] = new THREE.Group();
        scene[id].add(root[id]);

        renderer[id] = new THREE.WebGLRenderer({antialias: true});
        renderer[id].setPixelRatio(window.devicePixelRatio);
        renderer[id].setSize(width, height);

        labelRenderer[id] = new THREE.CSS2DRenderer();
        labelRenderer[id].setSize(width, height);
        labelRenderer[id].domElement.style.position = 'relative';
        var top = '-' + (height + 5) + 'px';
        labelRenderer[id].domElement.style.top = top;
        labelRenderer[id].domElement.style.pointerEvents = 'none';

        loadpdb(id, file);
    }
}

/**
 * Load the pdb file
 * @param string id object identifier
 * @param string file the object file
 */
function loadpdb(id, file) {
    var manager = new THREE.LoadingManager();
    manager.onLoad = function ( ) {
        $("#" + id + "_threeloading").remove();
        container[id].appendChild(renderer[id].domElement);
        container[id].appendChild(labelRenderer[id].domElement);
        threesetcontrols(id);
        threeanimate(id);
    };
    var loader = new THREE.PDBLoader(manager);
    var offset = new THREE.Vector3();
    while (root[id].children.length > 0) {
        var object = root[id].children[ 0 ];
        object.parent.remove(object);
    }
    loader.load(file, function (molecule) {
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
        for (var i = 0; i < positions.count; i ++) {
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
            root[id].add(object2);
            var atom = json.atoms[ i ];
            var text = document.createElement( 'div' );
            text.className = 'label';
            text.style.color = "black";
            text.style.fontWeight = "bold";
            text.textContent = atom[ 4 ];
            var label = new THREE.CSS2DObject( text );
            label.position.copy(object2.position);
            root[id].add(label);
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
            root[id].add(object3);
        }
        threerender(id);
    });
}