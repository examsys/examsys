currMapLevel = 0;

$(function () {
  createMappingLevels();
  $('#vle_api').change(createMappingLevels);
});

function createMappingLevels() {
  $('#map_level_holder').html('');
  var currVLE = $('#vle_api').val();
  if (currVLE != '') {
    var currMapLevels = vle_apis[currVLE];
    var haveSelected = false;
    for (i = 0; i < currMapLevels.length; i++) {
      var selected = '';
      if (currMapLevel == currMapLevels[i]) {
        selected = ' checked="checked"';
        haveSelected = true;
      }
      $('<input type="radio" name="map_level" id="map_level' + currMapLevels[i] + '" value="' + currMapLevels[i] + '"' + selected + ' />').appendTo($('#map_level_holder'));
      $('#map_level_holder').append(' <label for="map_level' + currMapLevels[i] + '">' + mapLevels[currMapLevels[i]] + '</label>');
    }
    if (!haveSelected) {
      $('#map_level0').attr('checked', 'checked');
    }
  }
}
