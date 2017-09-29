/*
 * Type Course Filter Dropdown
 *
 * @author Richard Whitefoot (UEA)
 * @version 1.0
 */

/**
* @namespace TypeCourseFilter
*/
var TypeCourseFilter = (function() {

  /**
  * Default properties
  */
  var settings = { 
      parentField : "#new_roles", 
      childField : "#new_grade", 
      parentLabel : "#typecourse",
      disableClass : "grey"
  },

  /**
  * Disable the drop-down and grey out the associated field lable
  * @private
  */
  _disable = function() {
    $(settings.childField).attr("disabled", "disabled");
    $(settings.parentLabel).addClass(settings.disableClass);
  },

  /**
  * Enable the drop-down and remove the grey out class from the associated field lable
  * @private
  */
  _enable = function() {
    $(settings.childField).removeAttr("disabled");      
    $(settings.parentLabel).removeClass(settings.disableClass);
  },

  /**
  * Make changes to options within the Type/Course drop-down based on the Status drop-down
  * @private
  */
  _setFilter = function() {

    $(settings.parentField).change(function(){

      var correspondingID = $(this).find(":selected").data("parent");

      if(!correspondingID) {
        correspondingID = "";
      }

      // Reset Type/Course and store previous value
      if($(settings.childField).data("prev-parent") != correspondingID) {
    
        $(settings.childField).val("");

        $(settings.childField + " optgroup").hide();

        if(!correspondingID) {
          _disable();
        } else {
          $(settings.childField + " optgroup[data-role='" + correspondingID + "']").show();
          _enable();
        }
      }

      $(settings.childField).data("prev-parent", correspondingID);

    });
  },

  /**
  # Initialise module
  * @public
  */
  init = function(options) {
    
    // Extend and override default properties
    settings = $.extend({}, settings, options);
    
    _disable();
    _setFilter();
  };

  return {
    init: init
  }
      
})();