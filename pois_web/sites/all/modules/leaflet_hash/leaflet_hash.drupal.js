/**
 * As per the Leaflet Hash project page (https://github.com/mlevans/leaflet-hash)
 * all we need to do is call L.Hash on the map object.
 */

(function ($) {

  $(document).ready(function() {
    // Iterate over the maps setting a hash.
    for (var i = 0; i < Drupal.settings.leaflet.length; i++) {
      var map = Drupal.settings.leaflet[i].lMap;
      new L.Hash(map);
    }
  });

})(jQuery);
