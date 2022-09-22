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
// Initialise threejs media.
//
// @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
// @copyright Copyright (c) 2018 The University of Nottingham
//

requirejs(['rogoconfig', 'jquery'], function(config, $) {

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
        loading.setAttribute("style", "width:" + width + "px;height:" + height + "px;");
        $('#' + id).prepend(loading);
        //  Load object.
        if (delay === "") {
            delay = false;
        } else {
            delay = true;
        }
        if (type != "pdb" && type != "ply") {
            type = 'obj';
        }

        var threedobj;

        if (config.three) {
            requirejs([type], function (THREEDOBJ) {
                threedobj = new THREEDOBJ(id);
                if (type === 'obj') {
                    if (!threedobj.init(url, mtl, obj, width, height, delay)) {
                        threeerror(id, type, errorstring);
                    }
                } else {
                    if (!threedobj.init(url, width, height, delay)) {
                        threeerror(id, type, errorstring);
                    }
                }
                $("#" + id + "_resetbutton").on('click', function (e) {
                    threedobj.reset();
                    e.preventDefault();
                });
                $("#" + id + "_loadbutton").on('click', function (e) {
                    $(this).prop('disabled', true);
                    if (type === 'obj') {
                        if (!threedobj.init(url, mtl, obj, width, height, false)) {
                            threeerror(id, type, errorstring);
                        }
                    } else {
                        if (!threedobj.init(url, width, height, false)) {
                            threeerror(id, type, errorstring);
                        }
                    }
                    e.preventDefault();
                });
            });
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
            txt = document.createTextNode(loadstring);
            loadbutton.appendChild(txt);
            caption.appendChild(loadbutton);
        }
        // Create reset button.
        var resetbutton = document.createElement("button");
        resetbutton.setAttribute('id', id + "_resetbutton");
        resetbutton.setAttribute('class', 'threebutton');
        resetbutton.setAttribute('type', 'button');
        txt = document.createTextNode(resetstring);
        resetbutton.appendChild(txt);
        caption.appendChild(resetbutton);
    }

    $(function() {
        $.each($('.threeblock'), function (ind) {
            var id = 'three' + parseInt(ind + 1);
            $(this).attr('id', id);
            this.setAttribute("style", "width:" + $(this).data("width") + "px;height:" + $(this).data("height") + "px;");
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
     * Error handler
     * @param string id object identifier
     * @param string type file type
     * @param string errorstring error message
     */
    function threeerror(id, type, errorstring) {
        $("#" + id + "_threeloading").css('background-image', 'url("' + config.cfgrootpath + '/artwork/exclamation_64.png")');
        $("#" + id + "_threeloading").css('font-size', 'large');
        errorstring = errorstring.replace('%s', type);
        $("#" + id + "_threeloading").text(errorstring);
    }
});
