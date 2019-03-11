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

var container = [];
var scene = [];
var camera = [];
var renderer = [];
var labelRenderer = [];
var controls = [];
var root = [];
var defaultcameraposition = [];

/**
 * Initialise the threejs object
 * @param string id identifier
 * @param string type object type
 * @param string url path to object
 * @param string mtl material file
 * @param string obj object file
 * @param integer width canvas width
 * @param integer height canvas height
 * @param boolean delay flag to pause loading of object
 * @param string loadstring loading UI message
 * @param string resetstring reset UI message
 * @param string infostring information UI message
 * @param string errorstring information UI message
 */
function threeinit(id, type, url, mtl, obj, width, height, delay, loadstring, resetstring, infostring, errorstring) {
    // Create loading overlay.
    var loading = document.createElement("div");
    loading.setAttribute('id', id + "_threeloading");
    loading.setAttribute('class', 'threeloading');
    loading.setAttribute("style","width:" + width + "px;height:" + height + "px;");
    $('#' + id).prepend(loading);
    //  Load object.
    if (delay === "") {
        delay = false;
    } else {
        delay = true;
    }
    switch (type) {
        case 'ply':
            var load = 'initply("' + id + '","' + url + '",' + width + ',' + height + ', false)';
            if (!initply(id, url, width, height, delay)) {
               threeerror(id, type, errorstring);
            }
            break;
        case 'pdb':
            initpdb(id, url, width, height, delay);
            load = 'initpdb("' + id + '","' + url + '",' + width + ',' + height + ', false)';
            break;
        default:
            load = 'initobj("' + id + '","' + url + '","' + mtl + '","' + obj + '",' + width + ',' + height + ', false)';
            if (!initobj(id, url, mtl, obj, width, height, delay)) {
                threeerror(id, 'obj', errorstring);
            }
            break;
    }
    // Create info area.
    var caption = document.createElement("div");
    caption.setAttribute('id', id + "_threeinfo");
    caption.setAttribute('class', 'threecaption');
    var txt = document.createTextNode(infostring);
    caption.appendChild(txt);
    $(caption).insertAfter('#' + id);
    // Create load button.
    if (delay) {
        var loadbutton = document.createElement("button");
        loadbutton.setAttribute('id', id + "_loadbutton");
        loadbutton.setAttribute('class', 'threebutton');
        loadbutton.setAttribute('type', 'button');
        loadbutton.setAttribute('onclick', load + ";threedisableload('" + id + "');");
        txt = document.createTextNode(loadstring);
        loadbutton.appendChild(txt);
        caption.appendChild(loadbutton);
    }
    // Create reset button.
    var resetbutton = document.createElement("button");
    resetbutton.setAttribute('id', id + "_resetbutton");
    resetbutton.setAttribute('class', 'threebutton');
    resetbutton.setAttribute('type', 'button');
    resetbutton.setAttribute('onclick', "threereset('" + id + "')");
    txt = document.createTextNode(resetstring);
    resetbutton.appendChild(txt);
    caption.appendChild(resetbutton);
}

$(document).ready(function() {
    $.each($('.threeblock'), function(ind) {
        var id = 'three' + parseInt(ind + 1);
        $(this).attr('id', id);
        container[id] = this;
        container[id].setAttribute("style","width:" + $(this).data("width") + "px;height:" + $(this).data("height") + "px;");
        threeinit(id,
            $(this).data("ext"),
            $(this).data("url"),
            $(this).data("mtl"),
            $(this).data("obj"),
            $(this).data("width"),
            $(this).data("height"),
            $(this).data("delay"),
            $(this).data("loadstring"),
            $(this).data("resetstring"),
            $(this).data("infostring"),
            $(this).data("errorstring")
        )
    });
});

/**
 * Animate the object
 * @param string id object identifier
 */
function threeanimate(id) {
    controls[id].update();
    threerender(id);
    requestAnimationFrame(function() {
        threeanimate(id);
    });
}

/**
 * Render the object
 * @param string id object identifier
 */
function threerender(id) {
    renderer[id].render(scene[id], camera[id]);
    // Render labels if present.
    if (labelRenderer[id] !== undefined) {
        labelRenderer[id].render(scene[id], camera[id]);
    }
}

/**
 * Reset the camera
 * @param string id object identifier
 */
function threereset(id) {
    controls[id].reset();
    camera[id].position.z = defaultcameraposition[id];
}

/**
 * Set object controls
 * @param string id object identifier
 */
function threesetcontrols(id) {
    controls[id] = new THREE.TrackballControls(camera[id], renderer[id].domElement);
    controls[id].minDistance = 0;
    controls[id].maxDistance = 2000;
}

/**
 * Set the scene
 * @param string id object identifier
 */
function threeaddscene(id) {
    $("#" + id + "_threeloading").show();
    scene[id] = new THREE.Scene();
    scene[id].background = new THREE.Color(0xffffff);
}

/**
 * Position the camera based on the objects width/height
 * @param string id object identifier
 * @param box3 boundingbox boundingbox of of object
 */
function threepositioncamera(id, boundingbox) {
    var width  = boundingbox.max.x - boundingbox.min.x;
    var height = boundingbox.max.y - boundingbox.min.y;
    camera[id].position.z = Math.max(height, width) * 2;
    defaultcameraposition[id] = camera[id].position.z;
}

/**
 * Disable the load button
 * @param string id object identifier
 */
function threedisableload(id) {
    $("#" + id + "_loadbutton").prop('disabled', true);
}

/**
 * Detect if the web browser supports the required web gl extenstions
 * @param string id object identifier
 * @param string extension the webgl extension
 * @return bool
 */
function threedetectweblextensions(id, extension) {
    var gl = renderer[id].getContext();
    var ext = gl.getExtension(extension);
    if (!ext) {
        return false;
    }
    return true;
}

/**
 * Error handler
 * @param string id object identifier
 * @param string type file type
 * @param string errorstring error message
 */
function threeerror(id, type, errorstring) {
    $("#" + id + "_threeloading").css('background-image', 'url("/artwork/exclamation_64.png")');
    $("#" + id + "_threeloading").css('font-size', 'large');
    errorstring = errorstring.replace('%s', type);
    $("#" + id + "_threeloading").text(errorstring);
}