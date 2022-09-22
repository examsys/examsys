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
 * This file configures Grunt tasks for ExamSys, such as mimification of JavaScript.
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
      },
      corejs: {
        src: [
          'js/src/**/*.js',
          '!js/src/jcalc98.js'
        ]
      },
      questionsjs: {
        src: ['plugins/questions/**/js/src/*.js']
      },
      ltijs: {
        src: ['LTI/js/src/*.js']
      },
      toolsjs: {
        src: ['tools/**/js/src/*.js']
      },
      authjs: {
        src: ['plugins/auth/**/js/src/*.js']
      },
    },
    uglify: {
      options: {
        mangle: false,
        sourceMap: true
      },
      admin: {
        files: [{
          expand: true,
          cwd: 'admin/',
          src: '**/js/src/*.js',
          dest: 'admin/',
          rename: buildName
        }]
      },
      corejs: {
        files: [{
          expand: true,
          cwd: 'js/',
          src: 'src/**/*.js',
          dest: 'js/',
          rename: buildName
        }]
      },
      questionsjs: {
        files: [{
          expand: true,
          cwd: 'plugins/questions/',
          src: '**/js/src/**/*.js',
          dest: 'plugins/questions/',
          rename: buildName
        }]
      },
      ltijs: {
        files: [{
          expand: true,
          cwd: 'LTI/',
          src: 'js/src/*.js',
          dest: 'LTI/',
          rename: buildName
        }]
      },
      toolsjs: {
        files: [{
          expand: true,
          cwd: 'tools/',
          src: '**/js/src/*.js',
          dest: 'tools/',
          rename: buildName
        }]
      },
      authjs: {
        files: [{
          expand: true,
          cwd: 'plugins/auth/',
          src: '**/js/src/*.js',
          dest: 'plugins/auth/',
          rename: buildName
        }]
      },
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
          ext: '.css'
        }]
      },
    },
    sprite: {
      html5canvas: {
        dest: 'js/images/combined.png',
        destCss: 'js/src/modules/html_questions/html5.image.js',
        src: [
          'js/images/toolbar/*.png',
        ],
        cssTemplate: 'js/images/html5.template.js'
     }
    }
  });

  // Load plugins.
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-eslint');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-spritesmith');

  // Register tasks.
  grunt.registerTask('css', ['cssmin:standard']);
  grunt.registerTask('admin', ['eslint:admin', 'uglify:admin']);
  grunt.registerTask('corejs', ['sprite:html5canvas', 'eslint:corejs', 'uglify:corejs']);
  grunt.registerTask('questionsjs', ['eslint:questionsjs', 'uglify:questionsjs']);
  grunt.registerTask('ltijs', ['eslint:ltijs', 'uglify:ltijs']);
  grunt.registerTask('toolsjs', ['eslint:toolsjs', 'uglify:toolsjs']);
  grunt.registerTask('authjs', ['eslint:authjs', 'uglify:authjs']);
  grunt.registerTask('default', ['admin', 'css', 'corejs', 'questionsjs', 'ltijs', 'toolsjs', 'authjs']);
}
