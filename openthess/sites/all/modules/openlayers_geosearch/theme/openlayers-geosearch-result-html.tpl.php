<?php
/**
 * @file: openlayers-geocoder-result-html.tpl.php
 *
 * Template file theming geosearch's response results.
 */

  $output .= '<table class="openlayers-geosearch-result-table">';
//  $output .= "<caption>" . $provider . "</caption>";
  $output .= "<tr>";
  if (isset($locations[0]->data['geocoder_address_components'])) {
  	$output .= "<th>" . t('Name') . "</th>";
    $output .= "<th>" . t('Address') . "</th>";
    $headers = array_keys($locations[0]->data['geocoder_address_components']);
    foreach ($headers as $header) {
      $output .= "<th>" . t($header) . "</th>";
    }
  }
  $output .= "</tr>";
  $fid = 1; // We are going to give every feature an id, this should never happen in a template file, so TODO fix this.
  foreach ($locations as $location) {
    $firstcolum = TRUE;
    $output .= "<tr>";
    if (isset($locations[0]->data['geocoder_address_components'])) {
      $output .= '<td><a class="openlayers-geosearch-result-link" href="?lat=' . $location->coords[0] .
                 '&lon=' . $location->coords[1] .
                 '&fid=' . $provider . '_' . $fid . '">' .
                   $location->data['geocoder_formatted_address'] .
                 '</a></td>';
      foreach ($location->data['geocoder_address_components'] as $key => $component) {
        if ($firstcolum) {
          $output .= '<td><a class="openlayers-geosearch-result-link" href="?lat=' . $location->coords[0] .
                     '&lon=' . $location->coords[1] .
//                     '&minx=' . $location['locations']['bounds']['southwest']->lng .
//                     '&miny=' . $location['locations']['bounds']['southwest']->lat .
//                     '&maxx=' . $location['locations']['bounds']['northeast']->lng .
//                     '&maxy=' . $location['locations']['bounds']['northeast']->lat .
                     '&fid=' . $provider . '_' . $fid. '">' . 
                       $component->long_name .
                     '</a></td>';
          $firstcolum = FALSE;
          $fid++; 
        }
        else {
          $output .= "<td>" . $component->long_name ."</td>";
        }
      }
    }
    $output .= "</tr>";
    $fid++;
  }
  $output .= "</table>";

  echo $output;
