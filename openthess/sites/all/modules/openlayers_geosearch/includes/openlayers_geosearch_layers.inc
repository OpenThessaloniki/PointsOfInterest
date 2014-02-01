<?php

/**
 * @file
 * This file contains layers implementations
 *
 * @ingroup openlayers
 */

/**
 * Preset Implementation
 *
 * Internal callback for openlayers layers implementation.
 *
 * @return
 *   Array of layers
 */
function _openlayers_geosearch_openlayers_layers() {
  $items = array();
  $openlayers_layers = new stdClass;
  $openlayers_layers->disabled = FALSE; /* Edit this to true to make a default openlayers_layers disabled initially */
  $openlayers_layers->api_version = 1;
  $openlayers_layers->name = 'openlayers_searchresult_layer';
  $openlayers_layers->title = 'Geosearch Results';
  $openlayers_layers->description = 'Shows the results of a Geosearch. Set styles on this map';
  $openlayers_layers->data = array(
    'projection' => array('EPSG:900913'),
    'layer_type' => 'openlayers_layer_type_raw',
  );
  $items["openlayers_searchresult_layer"] = $openlayers_layers;
  return $items;
}

