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
 * A component to serve localised ExamSys language strings in JavaScript
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define (['log'], function(log) {
  return {
    /**
     * Each property of the object should be a component for the strings.
     *
     * @type {Object}
     */
    components: {},

    /**
     * Get a string.
     *
     * Will log an error if the string does not exist and a warning
     * if a parameter in a string foes not have a value passed.
     *
     * @param {String} name The name of the string
     * @param {String} component The component the string is part of
     * @param {Array} args An array of strings that are used to replace any instances of %s in the string.
     * @returns {String}
     */
    get_string: function (name, component, args) {
      component = component || 'default';
      if (!this.components[component] || !this.components[component][name]) {
        log('Missing string: ' + component + ':' + name, 'error');
        // String not found, print out the component and name in a way that makes it obvious which one is missing.
        return '[[' + component + ':' + name + ']]';
      }
      var string = this.components[component][name];
      var match_count = 0;
      // Replace each '%s' in the string with a value from the arguments array.
      return string.replace(/%s/g, function (match) {
        if (Array.isArray(args) && args[match_count]) {
          // Return the new value for string and increment the match count.
          return args[match_count++];
        }
        // No replacement sent, highlight this.
        log('Missing parameter for string: ' + component + ':' + name, 'warn');
        return match;
      });
    },

    /**
     * Set a string.
     *
     * @param {String} string The value of the string
     * @param {String} name The name of the string
     * @param {String} component The component the string is part of.
     * @returns {void}
     */
    set_string: function (string, name, component) {
      component = component || 'default';
      if (!this.components[component]) {
        // Create the component.
        this.components[component] = {};
      }
      this.components[component][name] = string;
    },

    /**
     * Set a batch of strings in one go.
     *
     * @param {Object} strings The properties should be the string name, the value the text to be displayed.
     * @param {String} component The component the string is part of.
     * @returns {void}
     */
    set_strings: function (strings, component) {
      component = component || 'default';
      for (var name in strings) {
        this.set_string(strings[name], name, component);
      }
    }
  }
});
