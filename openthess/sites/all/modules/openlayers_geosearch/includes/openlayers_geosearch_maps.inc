<?php

/**
 * @file
 * This file contains map implementations
 *
 * @ingroup openlayers
 */

/**
 * Map Implementation
 *
 * Internal callback for openlayers map implementation.
 *
 * @return
 *   Array of maps
 */
function _openlayers_geosearch_openlayers_maps() {
  // Create full map array
  $default = new stdClass();
  $default->api_version = 1;
  $default->name = 'geosearch';
  $default->title = t('Default Geosearch Map');
  $default->description = t('This is the default map that comes with the OpenLayers Geosearch module.');
  $default->data = array(
    'projection' => '900913',
    'width' => 'auto',
    'height' => '400px',
    'default_layer' => 'osm_mapnik',
    'center' => array(
      'initial' => array(
        'centerpoint' => '0,0',
        'zoom' => '2',
      ),
    ),
    'displayProjection' => '4326',
    'maxExtent' => openlayers_get_extent('EPSG','4326'),
    'behaviors' => array(
      'openlayers_behavior_panzoombar' => array(),
      'openlayers_behavior_layerswitcher' => array(),
      'openlayers_behavior_attribution' => array(),
      'openlayers_behavior_keyboarddefaults' => array(),
      'openlayers_behavior_navigation' => array(),
      'openlayers_behavior_tooltip' => array(
        'layers' => array(
          'openlayers_searchresult_layer' => 'openlayers_searchresult_layer',
        ),
      ),
    ),
    'default_layer' => 'osm_mapnik',
    'layers' => array(
      'osm_mapnik' => 'osm_mapnik',
      'openlayers_searchresult_layer' => 'openlayers_searchresult_layer',
    ),
    'layer_styles' => array(),
    'layer_activated' => array(
      'openlayers_searchresult_layer' => 'openlayers_searchresult_layer',
    ),
  );
  return array('geosearch' => $default);
}
