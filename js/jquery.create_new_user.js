/*
 * Create New User jQuery
 *
 * @author Richard Whitefoot (UEA)
 * @version 1.0
 */
$(document).ready(function() {

  $(function () {
  
    $('#theform').validate({
      errorClass: 'errfield',
      errorPlacement: function(error,element) {
        return true;
      }
    });
    
    $('form').removeAttr('novalidate');
      
    $('#ldaplookup').click(function() {
      notice = window.open("ldaplookup.php","ldap","width=650,height=300,left=30,top=20,scrollbars=yes,toolbar=no,location=no,directories=no,status=no,menubar=no,resizable");
      notice.moveTo(screen.width/2-325, screen.height/2-150);
      
      if(window.focus) {
        notice.focus();
      }        
    });

    // Call for Type/Course dropdown
    var options = {
      parentField : "#new_roles", 
      childField : "#new_grade", 
      parentLabel : "#typecourse"
    };

    TypeCourseFilter.init(options);

  });

});