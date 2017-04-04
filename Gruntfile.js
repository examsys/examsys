// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file configures Grunt tasks for Rogo, such as mimification of JavaScript.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */

module.exports = function(grunt) {
  /**
   * Utility function to remanme Javascript files.
   * 
   * @param {String} destination The current destination path
   * @param {String} source The source path
   * @returns {String}
   */
  var buildName = function(destination, source) {
    destination = destination + source.replace('src/', '');
    destination = destination.replace('.js', '.min.js');
    return destination;
  }

  grunt.initConfig({
    eslint: {
      options: {
        configFile: 'testing/eslint/eslint.json'
      },
      admin: {
        src: ['admin/**/js/src/*.js']
      }
    },
    uglify: {
      options: {
        mangle: false
      },
      admin: {
        files: [{
          expand: true,
          cwd: 'admin/',
          src: '**/js/src/*.js',
          dest: 'admin/',
          rename: buildName
        }]
      }
    },
    cssmin: {
      options: {
        report: true
      },
      standard: {
        files: [{
          expand: true,
          cwd: 'css/source',
          src: '*.css',
          dest: 'css',
          ext: '.css',
        }]
      }
    }
  });

  // Load plugins.
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-eslint');
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  // Register tasks.
  grunt.registerTask('css', ['cssmin:standard']);
  grunt.registerTask('admin', ['eslint:admin', 'uglify:admin']);
  grunt.registerTask('default', ['admin', 'css']);
}
