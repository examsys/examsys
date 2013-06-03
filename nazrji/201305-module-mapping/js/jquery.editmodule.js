currMapLevel = 0;
function createMappingLevels() {
  $('#map_level_holder').html('');
  var currVLE = $('#vle_api').val();
  var currMapLevels = vle_apis[currVLE];
  var haveSelected = false;
  var selected = '';
  for (i = 0; i < currMapLevels.length; i++) {
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
