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
 * Controls user interactions with role-lists when a user has editing permissions.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2020 The University of Nottingham
 */
define(['jquery'], function ($) {
    /**
     * Stores selectors used by the module.
     *
     * @type {{ENABLED: string, EDITAREA: string, CONTROL: string}}
     */
    var SELECTORS = {
        EDITAREA: '#editroles',
        CONTROL: '.role input[type=checkbox]',
        ENABLED: '.role input[type=checkbox]:checked',
        SAVE: 'input[type=submit]'
    };

    /**
     * Stores a list of groupings as properties with the group that is selected.
     *
     * @type {Object}
     */
    var groupings = {};

    /**
     * Stores if the user is a system administrator.
     *
     * @type {boolean}
     */
    var sysAdmin = false;

    /**
     * Stores the role controls.
     *
     * @type {jQuery}
     */
    var controls;

    /**
     * Stores the edit roles form.
     *
     * @type {jQuery}
     */
    var area;

    /**
     * Stores the submit button.
     *
     * @type {jQuery}
     */
    var submit;

    /**
     * Handle changes to the controls.
     *
     * @param {Event} event
     */
    var changeListener = function(event)
    {
        var grouping = event.target.getAttribute('data-grouping');
        var group = event.target.getAttribute('data-group');

        if (event.target.checked) {
            groupings[grouping] = group;
        } else {
            groupings = {};
            area.find(SELECTORS.ENABLED).each(recordGroups);
        }

        controls.each(setupControls);
        submit.each(setupSave);
    };

    /**
     * Callback function that ensures submit buttons are in the correct state.
     *
     * @param {Number} index
     * @param {Element} element
     */
    var setupSave = function(index, element)
    {
        if (Object.keys(groupings).length) {
            // Roles selected.
            enableControl(element);
        } else {
            // No roles selected.
            disableControl(element);
        }
    }

    /**
     * Callback function that records the group of an element in the groupings object.
     *
     * @param {Number} index
     * @param {Element} element
     */
    var recordGroups = function(index, element)
    {
        var grouping  = element.getAttribute('data-grouping');
        var group = element.getAttribute('data-group');

        // Store the group.
        groupings[grouping] = group;
    };

    /**
     * Enables a control.
     *
     * @param {Element} element
     */
    var enableControl = function(element)
    {
        element.removeAttribute('disabled');
    };

    /**
     * Disables a control.
     *
     * @param {Element} element
     */
    var disableControl = function(element)
    {
        element.setAttribute('disabled', 'disbaled');
    };

    /**
     * Callback function sets up a control.
     *
     * @param {Number} index
     * @param {Element} element
     */
    var setupControls = function(index, element)
    {
        var grouping  = element.getAttribute('data-grouping');
        var group = element.getAttribute('data-group');
        var role = element.getAttribute('value');

        if (!sysAdmin && (role === 'SysAdmin' || role === 'SysCron')) {
            // The SysAdmin and cron role can only be changed by SysAdmins.
            disableControl(element);
        } else if (!groupings[grouping]) {
            // There are no selections in the grouping so all roles are valid.
            enableControl(element);
        } else if (groupings[grouping] === group) {
            // This option is in a group that has bee selected.
            enableControl(element);
        } else {
            // The role is in a group that should not be selected right now.
            disableControl(element);
        }
    };

    /**
     * Initialises the roles editing form.
     *
     * Adds listeners and disables checkboxes that should not be selectable.
     *
     * @param {boolean} isSysAdmin
     */
    var init = function(isSysAdmin)
    {
        sysAdmin = isSysAdmin;

        area = $(SELECTORS.EDITAREA);
        area.find(SELECTORS.ENABLED).each(recordGroups);

        submit = area.find(SELECTORS.SAVE);
        controls = area.find(SELECTORS.CONTROL);

        // Disable any roles that should not be selected.
        controls.each(setupControls);

        // Disable the save button if the user has no roles.
        submit.each(setupSave);

        // Listen for changes to the radio buttons.
        controls.change(changeListener);
    };

    return {
        init: init
    };
});
