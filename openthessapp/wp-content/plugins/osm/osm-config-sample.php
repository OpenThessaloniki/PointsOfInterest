<?php
// SERVER_EMBEDDED   ... loaded by the plugin for each map (default)
// SERVER_WP_ENQUEUE ... registered and loaded by WordPress
define ("Osm_LoadLibraryMode", SERVER_EMBEDDED); 
// OpenStreetMap scripts
//define ("Osm_OSM_LibraryLocation", 'http://www.openstreetmap.org/openlayers/OpenStreetMap.js');
define ("Osm_OSM_LibraryLocation", OSM_PLUGIN_URL.'js/OSM/openlayers/OpenStreetMap.js');
// OpenLayers scripts
//define ("Osm_OL_LibraryLocation", 'http://www.openlayers.org/api/OpenLayers.js');
//define ("Osm_OL_LibraryLocation", 'http://openlayers.org/api/2.12/OpenLayers.js');
define ("Osm_OL_LibraryPath", OSM_PLUGIN_URL.'js/OL/2.12/');
define ("Osm_OL_LibraryLocation", OSM_PLUGIN_URL."js/OL/2.12/OpenLayers.js");
define ("Osm_GOOGLE_LibraryLocation", 'http://maps.google.com/maps/api/js?sensor=false');
// OpenSeaMap scripts
define ("Osm_harbours_LibraryLocation", OSM_PLUGIN_URL.'js/OSeaM/harbours.js');
define ("Osm_map_utils_LibraryLocation", OSM_PLUGIN_URL.'js/OSeaM/map_utils.js');
define ("Osm_utilities_LibraryLocation", OSM_PLUGIN_URL.'js/OSeaM/utilities.js');
// OpenWeather scripts
define ("Osm_openweather_LibraryLocation", 'http://openweathermap.org/js/OWM.OpenLayers.1.3.4.js');
?>
