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
 * Set up obj (with mtl) object
 * @param string id object identifier
 * @param string file the archive file
 * @param string obj the object file
 * @param string mtl the material file
 * @param integer width width of renderer
 * @param integer height height of renderer
 * @param boolean delay flag to stop setup
 */
function initobj(id, file, mtl, obj, width, height, delay) {
    if (delay !== true) {
        threeaddscene(id);

        camera[id] = new THREE.PerspectiveCamera(70, width / height, 1, 5000);

        scene[id].add(camera[id]);

        var light = new THREE.AmbientLight(0xffffff, 0.8);
        scene[id].add(light);

        renderer[id] = new THREE.WebGLRenderer({antialias: true});
        renderer[id].setPixelRatio(window.devicePixelRatio);
        renderer[id].setSize(width, height);

        return loadobj(id, file, mtl, obj);
    }
}

/**
 * Load the obj file
 * @param string id object identifier
 * @param string file the object file
 * @param string mtl the material file
 * @param string obj the object file
 * @return boolean
 */
function loadobj(id, file, mtl, obj) {
    var manager = new THREE.LoadingManager();
    manager.onLoad = function ( ) {
        $("#" + id + "_threeloading").remove();
        container[id].appendChild(renderer[id].domElement);
        threesetcontrols(id);
        threeanimate(id);
    };
    // Only obj file supplied so display skeleton.
    if (mtl === "" && obj === "") {
        var loader = new THREE.OBJLoader(manager);
        loader.load(file, function (object) {
            var boundingbox = new THREE.Box3().setFromObject(object);
            threepositioncamera(id, boundingbox);
            scene[id].add(object);
            threerender(id);
        });
    // Archive include obj file but not materials, try to load jpg texture.
    } else if (mtl === "") {
        // Check required webgl extension exists.
        if (!threedetectweblextensions(id, "WEBGL_depth_texture")) {
            return false;
        }
        file = file.substr(file.indexOf('filename=') + 9);
        var dir = file.replace('.zip', '');
        // Loader.
        loader = new THREE.OBJLoader(manager);
        loader.setPath('/getfile.php?type=media&filename=' + dir + '/');
        loader.load(obj, function(object) {
            var textureloader = new THREE.TextureLoader();
            textureloader.setPath('/getfile.php?type=media&filename=' + dir + '/');
            var texture = textureloader.load(obj.replace('.obj', '.jpg'));
            object.traverse(function(child) {
                if (child.isMesh) child.material.map = texture;
            });
            var boundingbox = new THREE.Box3().setFromObject(object);
            threepositioncamera(id, boundingbox);
            scene[id].add(object);
            threerender(id);
        });
    // Archive include obj file and materials.
    } else {
        file = file.substr(file.indexOf('filename=') + 9);
        dir = file.replace('.zip', '');
        THREE.Loader.Handlers.add(/\.dds$/i, new THREE.DDSLoader());
        loader = new THREE.MTLLoader(manager);
        loader.setPath('/getfile.php?type=media&filename=' + dir + '/');
        loader.load(mtl, function (materials) {
            materials.preload();
            var loader2 = new THREE.OBJLoader();
            loader2.setMaterials(materials);
            loader2.setPath('/getfile.php?type=media&filename=' + dir + '/');
            loader2.load(obj, function (object) {
                var boundingbox = new THREE.Box3().setFromObject(object);
                threepositioncamera(id, boundingbox);
                scene[id].add(object);
            });
            threerender(id);
        });
    }
    return true;
}
