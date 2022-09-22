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

/**
 * html5 question image map.
 *
 * This file is generated via grunt from /js/images/html5.template.js
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['log'], function(Log) {
  return {
    /**
     * The location of the combined image relative to the root ExamSys directory.
     *
     * @type {String}
     */
    location: 'js/images/combined.png',

    /**
     * The combined image.
     *
     * @type {Image}
     */
    image: new Image(),

    /**
     * Loads the combined image.
     *
     * @return {void}
     */
    load: function(webroot, questions) {
      this.image = new Image();
      this.image.src = webroot + this.location;
      this.image.onload = function() {
        Log('HTML5 combined image loaded.', 'info');
        for (var i in questions) {
          questions[i].redraw();
        }
      };
    },

    /**
     * Defines the co-ordinates of images in the combined image
     *
     * To reduce the amount of network traffic by the html5 questions all
     * of the UI images have be combined into a single image. This object
     * maps the images name to it's location on the combined image.
     *
     * The combined image is /js/images/combined.png
     *
     * The individual images are in sub directories of /js/images/
     *
     * @type {Object}
     */
    map: {
      {{#block "sprites"}}
      {{#each sprites}}
        'toolbar/{{{name}}}.png': { left: {{x}}, top: {{y}}, width: {{width}}, height: {{height}} },
      {{/each}}
      {{/block}}
    }
  }
});