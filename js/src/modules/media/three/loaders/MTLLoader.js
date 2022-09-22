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
// Loader for obj MTL 3d objects
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
// A modularised version of the example from angelxuanchang
// https://github.com/mrdoob/three.js/blob/master/examples/js/loaders/MTLLoader.js

define(['three', 'MaterialCreator'], function (THREE, MaterialCreator) {
    /**
     * A OBJ MTL 3d object loader
     * @param object manager loading manager
     */
    return function (manager) {
        this.manager = (manager !== undefined) ? manager : THREE.DefaultLoadingManager;

        /**
         * Loads and parses a MTL asset from a URL.
         *
         * @param {String} url - URL to the MTL file.
         * @param {Function} [onLoad] - Callback invoked with the loaded object.
         * @param {Function} [onProgress] - Callback for download progress.
         * @param {Function} [onError] - Callback for download errors.
         *
         * @see setPath setTexturePath
         *
         * @note In order for relative texture references to resolve correctly
         * you must call setPath and/or setTexturePath explicitly prior to load.
         */
        this.load = function (url, onLoad, onProgress, onError) {

            var scope = this;

            var loader = new THREE.FileLoader(this.manager);
            loader.setPath(this.path);
            loader.load(url, function (text) {

                onLoad(scope.parse(text));

            }, onProgress, onError);

        };

        /**
         * Set base path for resolving references.
         * If set this path will be prepended to each loaded and found reference.
         *
         * @see setTexturePath
         * @param {String} path
         *
         * @example
         *     mtlLoader.setPath( 'assets/obj/' );
         *     mtlLoader.load( 'my.mtl', ... );
         */
        this.setPath = function (path) {

            this.path = path;

        };

        /**
         * Set base path for resolving texture references.
         * If set this path will be prepended found texture reference.
         * If not set and setPath is, it will be used as texture base path.
         *
         * @see setPath
         * @param {String} path
         *
         * @example
         *     mtlLoader.setPath( 'assets/obj/' );
         *     mtlLoader.setTexturePath( 'assets/textures/' );
         *     mtlLoader.load( 'my.mtl', ... );
         */
        this.setTexturePath = function (path) {

            this.texturePath = path;

        };

        this.setBaseUrl = function (path) {

            this.setTexturePath(path);

        };

        this.setCrossOrigin = function (value) {

            this.crossOrigin = value;

        };

        this.etMaterialOptions = function (value) {

            this.materialOptions = value;

        };

        /**
         * Parses a MTL file.
         *
         * @param {String} text - Content of MTL file
         * @return {THREE.MTLLoader.MaterialCreator}
         *
         * @see setPath setTexturePath
         *
         * @note In order for relative texture references to resolve correctly
         * you must call setPath and/or setTexturePath explicitly prior to parse.
         */
        this.parse = function (text) {

            var lines = text.split('\n');
            var info = {};
            var delimiter_pattern = /\s+/;
            var materialsInfo = {};

            for (var i = 0; i < lines.length; i++) {

                var line = lines[i];
                line = line.trim();

                if (line.length === 0 || line.charAt(0) === '#') {

                    // Blank line or comment ignore
                    continue;

                }

                var pos = line.indexOf(' ');

                var key = (pos >= 0) ? line.substring(0, pos) : line;
                key = key.toLowerCase();

                var value = (pos >= 0) ? line.substring(pos + 1) : '';
                value = value.trim();

                if (key === 'newmtl') {

                    // New material

                    info = {name: value};
                    materialsInfo[value] = info;

                } else if (info) {

                    if (key === 'ka' || key === 'kd' || key === 'ks') {

                        var ss = value.split(delimiter_pattern, 3);
                        info[key] = [parseFloat(ss[0]), parseFloat(ss[1]), parseFloat(ss[2])];

                    } else {

                        info[key] = value;

                    }

                }

            }

            var materialCreator = new MaterialCreator(this.texturePath || this.path, this.materialOptions);
            materialCreator.setCrossOrigin(this.crossOrigin);
            materialCreator.setManager(this.manager);
            materialCreator.setMaterials(materialsInfo);
            return materialCreator;

        };

    }
});
