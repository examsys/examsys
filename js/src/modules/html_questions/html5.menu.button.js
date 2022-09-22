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
 * A menu_item displayed as a button to the user
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2016 The University of Nottingham
 */
define(['html5_menuitem', 'jquery'], function(Menu_item, $) {
    /**
     * Creates a menu item that acts as a button.
     *
     * @param {String} image The name of the css class containing the details of the items image.
     * @param {String} help A description of what the menu item does.
     * @param {String} id The unique identifier for the menu item.
     * @returns {.menu_button}
     */
    function menu_button(image, help, id) {
        Menu_item.call(this, image, help, id);
    }

    menu_button.prototype = Object.create(Menu_item.prototype);

    /**
     * Creates the html for the menu item.
     *
     * @returns {Element}
     */
    menu_button.prototype.create = function () {
        var button = Menu_item.prototype.create.call(this),
            item = $(button),
            attributes = {
                role: 'button',
                tabindex: -1
            };
        if (this.togglable) {
            attributes['aria-pressed'] = false;
        }
        item.attr(attributes);
        return button;
    };

    return menu_button;
});
