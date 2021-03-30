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
// Loader for obj 3d objects
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//
// A modularised version of the example from mrdoob / http://mrdoob.com/
// https://github.com/mrdoob/three.js/blob/master/examples/js/loaders/OBJLoader.js

define(['three', 'ParserState'], function (THREE, ParserState) {
    /**
     * A OBJ 3d object loader
     * @param object manager loading manager
     */
    return function (manager) {
        this.manager = ( manager !== undefined ) ? manager : THREE.DefaultLoadingManager;

        this.materials = null

        // o object_name | g group_name
        var object_pattern = /^[og]\s*(.+)?/;
        // mtllib file_reference
        var material_library_pattern = /^mtllib /;
        // usemtl material_name
        var material_use_pattern = /^usemtl /;

        this.load =function ( url, onLoad, onProgress, onError ) {

            var scope = this;

            var loader = new THREE.FileLoader( scope.manager );
            loader.setPath( this.path );
            loader.load( url, function ( text ) {

                onLoad( scope.parse( text ) );

            }, onProgress, onError );

        };

        this.setPath = function ( value ) {

            this.path = value;

        };

        this.setMaterials = function ( materials ) {

            this.materials = materials;

            return this;

        };

        this.parse = function ( text ) {

            var state = new ParserState();

            if ( text.indexOf( '\r\n' ) !== - 1 ) {

                // This is faster than String.split with regex that splits on both
                text = text.replace( /\r\n/g, '\n' );

            }

            if ( text.indexOf( '\\\n' ) !== - 1 ) {

                // join lines separated by a line continuation character (\)
                text = text.replace( /\\\n/g, '' );

            }

            var lines = text.split( '\n' );
            var line = '', lineFirstChar = '';
            var lineLength = 0;
            var result = [];

            // Faster to just trim left side of the line. Use if available.
            var trimLeft = ( typeof ''.trimLeft === 'function' );

            for ( var i = 0, l = lines.length; i < l; i ++ ) {

                line = lines[ i ];

                line = trimLeft ? line.trimLeft() : line.trim();

                lineLength = line.length;

                if ( lineLength === 0 ) continue;

                lineFirstChar = line.charAt( 0 );

                // @todo invoke passed in handler if any
                if ( lineFirstChar === '#' ) continue;

                if ( lineFirstChar === 'v' ) {

                    var data = line.split( /\s+/ );

                    switch ( data[ 0 ] ) {

                        case 'v':
                            state.vertices.push(
                                parseFloat( data[ 1 ] ),
                                parseFloat( data[ 2 ] ),
                                parseFloat( data[ 3 ] )
                            );
                            if ( data.length === 8 ) {

                                state.colors.push(
                                    parseFloat( data[ 4 ] ),
                                    parseFloat( data[ 5 ] ),
                                    parseFloat( data[ 6 ] )

                                );

                            }
                            break;
                        case 'vn':
                            state.normals.push(
                                parseFloat( data[ 1 ] ),
                                parseFloat( data[ 2 ] ),
                                parseFloat( data[ 3 ] )
                            );
                            break;
                        case 'vt':
                            state.uvs.push(
                                parseFloat( data[ 1 ] ),
                                parseFloat( data[ 2 ] )
                            );
                            break;

                    }

                } else if ( lineFirstChar === 'f' ) {

                    var lineData = line.substr( 1 ).trim();
                    var vertexData = lineData.split( /\s+/ );
                    var faceVertices = [];

                    // Parse the face vertex data into an easy to work with format

                    for ( var j = 0, jl = vertexData.length; j < jl; j ++ ) {

                        var vertex = vertexData[ j ];

                        if ( vertex.length > 0 ) {

                            var vertexParts = vertex.split( '/' );
                            faceVertices.push( vertexParts );

                        }

                    }

                    // Draw an edge between the first vertex and all subsequent vertices to form an n-gon

                    var v1 = faceVertices[ 0 ];

                    for ( var jj = 1, jjl = faceVertices.length - 1; jj < jjl; jj ++ ) {

                        var v2 = faceVertices[ jj ];
                        var v3 = faceVertices[ jj + 1 ];

                        state.addFace(
                            v1[ 0 ], v2[ 0 ], v3[ 0 ],
                            v1[ 1 ], v2[ 1 ], v3[ 1 ],
                            v1[ 2 ], v2[ 2 ], v3[ 2 ]
                        );

                    }

                } else if ( lineFirstChar === 'l' ) {

                    var lineParts = line.substring( 1 ).trim().split( " " );
                    var lineVertices = [], lineUVs = [];

                    if ( line.indexOf( "/" ) === - 1 ) {

                        lineVertices = lineParts;

                    } else {

                        for ( var li = 0, llen = lineParts.length; li < llen; li ++ ) {

                            var parts = lineParts[ li ].split( "/" );

                            if ( parts[ 0 ] !== "" ) lineVertices.push( parts[ 0 ] );
                            if ( parts[ 1 ] !== "" ) lineUVs.push( parts[ 1 ] );

                        }

                    }
                    state.addLineGeometry( lineVertices, lineUVs );

                } else if ( lineFirstChar === 'p' ) {

                    var lineData2 = line.substr( 1 ).trim();
                    var pointData = lineData2.split( " " );

                    state.addPointGeometry( pointData );

                } else if ( ( result = object_pattern.exec( line ) ) !== null ) {

                    // o object_name
                    // or
                    // g group_name

                    // WORKAROUND: https://bugs.chromium.org/p/v8/issues/detail?id=2869
                    // var name = result[ 0 ].substr( 1 ).trim();
                    var name = ( " " + result[ 0 ].substr( 1 ).trim() ).substr( 1 );

                    state.startObject( name );

                } else if ( material_use_pattern.test( line ) ) {

                    // material

                    state.object.startMaterial( line.substring( 7 ).trim(), state.materialLibraries );

                } else if ( material_library_pattern.test( line ) ) {

                    // mtl file

                    state.materialLibraries.push( line.substring( 7 ).trim() );

                } else if ( lineFirstChar === 's' ) {

                    result = line.split( ' ' );

                    // smooth shading

                    // @todo Handle files that have varying smooth values for a set of faces inside one geometry,
                    // but does not define a usemtl for each face set.
                    // This should be detected and a dummy material created (later MultiMaterial and geometry groups).
                    // This requires some care to not create extra material on each smooth value for "normal" obj files.
                    // where explicit usemtl defines geometry groups.
                    // Example asset: examples/models/obj/cerberus/Cerberus.obj

                    /*
                     * http://paulbourke.net/dataformats/obj/
                     * or
                     * http://www.cs.utah.edu/~boulos/cs3505/obj_spec.pdf
                     *
                     * From chapter "Grouping" Syntax explanation "s group_number":
                     * "group_number is the smoothing group number. To turn off smoothing groups, use a value of 0 or off.
                     * Polygonal elements use group numbers to put elements in different smoothing groups. For free-form
                     * surfaces, smoothing groups are either turned on or off; there is no difference between values greater
                     * than 0."
                     */
                    if ( result.length > 1 ) {

                        var value = result[ 1 ].trim().toLowerCase();
                        state.object.smooth = ( value !== '0' && value !== 'off' );

                    } else {

                        // ZBrush can produce "s" lines #11707
                        state.object.smooth = true;

                    }
                    var material = state.object.currentMaterial();
                    if ( material ) material.smooth = state.object.smooth;

                } else {

                    // Handle null terminated files without exception
                    if ( line === '\0' ) continue;

                    throw new Error( 'THREE.OBJLoader: Unexpected line: "' + line + '"' );

                }

            }

            state.finalize();

            var container = new THREE.Group();
            container.materialLibraries = [].concat( state.materialLibraries );

            for ( var ii = 0, ll = state.objects.length; ii < ll; ii ++ ) {

                var object = state.objects[ ii ];
                var geometry = object.geometry;
                var materials = object.materials;
                var isLine = ( geometry.type === 'Line' );
                var isPoints = ( geometry.type === 'Points' );
                var hasVertexColors = false;

                // Skip o/g line declarations that did not follow with any faces
                if ( geometry.vertices.length === 0 ) continue;

                var buffergeometry = new THREE.BufferGeometry();

                buffergeometry.setAttribute( 'position', new THREE.Float32BufferAttribute( geometry.vertices, 3 ) );

                if ( geometry.normals.length > 0 ) {

                    buffergeometry.setAttribute( 'normal', new THREE.Float32BufferAttribute( geometry.normals, 3 ) );

                } else {

                    buffergeometry.computeVertexNormals();

                }

                if ( geometry.colors.length > 0 ) {

                    hasVertexColors = true;
                    buffergeometry.setAttribute( 'color', new THREE.Float32BufferAttribute( geometry.colors, 3 ) );

                }

                if ( geometry.uvs.length > 0 ) {

                    buffergeometry.setAttribute( 'uv', new THREE.Float32BufferAttribute( geometry.uvs, 2 ) );

                }

                // Create materials

                var createdMaterials = [];

                for ( var mi = 0, miLen = materials.length; mi < miLen; mi ++ ) {

                    var sourceMaterial = materials[ mi ];
                    var material2 = undefined;

                    if ( this.materials !== null ) {

                        material2 = this.materials.create( sourceMaterial.name );

                        // mtl etc. loaders probably can't create line materials correctly, copy properties to a line material.
                        if ( isLine && material2 && ! ( material2 instanceof THREE.LineBasicMaterial ) ) {

                            var materialLine = new THREE.LineBasicMaterial();
                            materialLine.copy( material );
                            materialLine.lights = false; // TOFIX
                            material2 = materialLine;

                        } else if ( isPoints && material2 && ! ( material2 instanceof THREE.PointsMaterial ) ) {

                            var materialPoints = new THREE.PointsMaterial( { size: 10, sizeAttenuation: false } );
                            materialLine.copy( material2 );
                            material2 = materialPoints;

                        }

                    }

                    if ( ! material2 ) {

                        if ( isLine ) {

                            material2 = new THREE.LineBasicMaterial();

                        } else if ( isPoints ) {

                            material2 = new THREE.PointsMaterial( { size: 1, sizeAttenuation: false } );

                        } else {

                            material2 = new THREE.MeshPhongMaterial();

                        }

                        material2.name = sourceMaterial.name;

                    }

                    material2.flatShading = sourceMaterial.smooth ? false : true;
                    material2.vertexColors = hasVertexColors ? THREE.VertexColors : THREE.NoColors;

                    createdMaterials.push( material2 );

                }

                // Create mesh

                var mesh;

                if ( createdMaterials.length > 1 ) {

                    for ( var mii = 0, miiLen = materials.length; mii < miiLen; mii ++ ) {

                        var sourceMaterial2 = materials[ mii ];
                        buffergeometry.addGroup( sourceMaterial2.groupStart, sourceMaterial2.groupCount, mii );

                    }

                    if ( isLine ) {

                        mesh = new THREE.LineSegments( buffergeometry, createdMaterials );

                    } else if ( isPoints ) {

                        mesh = new THREE.Points( buffergeometry, createdMaterials );

                    } else {

                        mesh = new THREE.Mesh( buffergeometry, createdMaterials );

                    }

                } else {

                    if ( isLine ) {

                        mesh = new THREE.LineSegments( buffergeometry, createdMaterials[ 0 ] );

                    } else if ( isPoints ) {

                        mesh = new THREE.Points( buffergeometry, createdMaterials[ 0 ] );

                    } else {

                        mesh = new THREE.Mesh( buffergeometry, createdMaterials[ 0 ] );

                    }

                }

                mesh.name = object.name;

                container.add( mesh );

            }

            return container;

        };
    }
});
