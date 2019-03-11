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
 * Set up ply object
 * @param string id object identifier
 * @param string file the object file
 * @param integer width width of renderer
 * @param integer height height of renderer
 * @param boolean delay flag to stop setup
 * @return boolean false on error
 */
function initply(id, file, width, height, delay) {
    if (delay !== true) {
        threeaddscene(id);

        camera[id] = new THREE.PerspectiveCamera(70, width / height, 1, 5000);

        scene[id].add(camera[id]);

        var light = new THREE.AmbientLight(0xffffff);
        scene[id].add(light);

        renderer[id] = new THREE.WebGLRenderer({antialias: true});

        // Check required webgl extension exists.
        if (!threedetectweblextensions(id, "WEBGL_depth_texture")) {
            return false;
        }
        renderer[id].setPixelRatio(window.devicePixelRatio);
        renderer[id].setSize(width, height);
        renderer[id].gammaInput = true;
        renderer[id].gammaOutput = true;

        renderer[id].shadowMap.enabled = true;

        loadply(id, file);
    }
    return true;
}

/**
 * Load the ply file
 * @param string id object identifier
 * @param string file the object file
 */
function loadply(id, file) {
    var manager = new THREE.LoadingManager();
    manager.onLoad = function ( ) {
        $("#" + id + "_threeloading").remove();
        container[id].appendChild(renderer[id].domElement);
        threesetcontrols(id);
        threeanimate(id);
    };
    var loader = new THREE.PLYLoader(manager);
    loader.load(file, function (geometry) {
        var material = new THREE.MeshStandardMaterial({vertexColors: THREE.VertexColors});
        geometry.center();
        geometry.computeBoundingBox();
        var boundingbox = geometry.boundingBox;
        threepositioncamera(id, boundingbox);
        var mesh = new THREE.Mesh(geometry, material);
        scene[id].add(mesh);
        threerender(id);
    });
}
