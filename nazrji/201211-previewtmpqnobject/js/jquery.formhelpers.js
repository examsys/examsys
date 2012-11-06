/*
 * Allows form text input fields to display 'hints' to the user that will be removed
 * when the element gains focus.
 * 
 * To apply this to a form you must:
 *  - Include this file
 *  - Add the class 'clearinput' to the input elements for which you want hints
 *  - Add the hint text in the 'title' attribute of the input element
 *  - Add the class 'clearinput' to the form containing the input elements. This will
 *    prevent the hint text from being submitted as part of the form data
 *    
 * Note - this code applies the class 'note' to the input elements whenever they contain
 * the hint text 
 */
$(function () {
  $('input.clearinput').each(setupInput).focus(clearOnEntry).blur(restoreData);

  $('form.clearinput').each(function() {
    $(this).submit(clearDefaults);
  });
});
function setupInput() {
  if($(this).val() == '' || $(this).val() == $(this).attr("title")) {
    $(this).addClass('note');
    $(this).val($(this).attr("title"));
  }
}
function clearOnEntry() {
  if($(this).val() == $(this).attr("title")) {
    $(this).val('');
    $(this).removeClass('note');
  }
}
function restoreData() {
  if($(this).val() == '') {
    $(this).addClass('note');
    $(this).val($(this).attr("title"));
  }
}
function clearDefaults() {
  $('input.clearinput').each(function() {
    if($(this).val() == $(this).attr("title")) {
      $(this).val('');
    }
  });
}