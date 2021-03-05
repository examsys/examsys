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
 * Test that the html5.images.js file works correctly.
 */
requirejs(['qunit', 'html5images'], function(QUnit, images) {
  QUnit.start();
  QUnit.test("HTML5 images object", function (assert) {
    var done = assert.async();
    // Check the object if contains exists as we expect.
    assert.ok(images.map, 'map is defined');
    for (var image in images.map) {
      if (image !== 'zzz') {
        // Every menu image must have a left, top, width and height property.
        assert.deepEqual(typeof images.map[image].left, 'number', image + '.left is a number');
        assert.deepEqual(typeof images.map[image].top, 'number', image + '.top is a number');
        assert.deepEqual(typeof images.map[image].width, 'number', image + '.width is a number');
        assert.deepEqual(typeof images.map[image].height, 'number', image + '.height is a number');
        // The value of left and top must be 0 or greater.
        assert.ok(images.map[image].left >= 0, image + '.left is greater or equal to zero');
        assert.ok(images.map[image].top >= 0, image + '.top is greater or equal to zero');
        // The value of width and height must be positive.
        assert.ok(images.map[image].width > 0, image + '.width is greater than zero');
        assert.ok(images.map[image].height > 0, image + '.height is greater than zero');
      } else {
        assert.deepEqual(images.map[image], 'zzz', 'zzz is correct');
      }
    }
    done();
  });
});
