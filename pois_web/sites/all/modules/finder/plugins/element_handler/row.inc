<?php

/**
 * @file
 * The finder row element handler plugin.
 */

/**
 * The CTools plugin definition.
 */

$plugin = array(
  'row' => array(
    'title' => t('Row'),
    'description' => t('A container that renders form elements inline.'),
    'type' => 'container',
    'settings callback' => 'finder_row_settings',
    'element callback' => 'finder_row_element',
  ),
);

/**
 * Settings callback.
 */
function finder_row_settings(&$data, $finder, $finder_element_id) {
  $items = &$data['items'];
  unset($items['title_display']);
  unset($items['description']);
}

/**
 * Element callback.
 */
function finder_row_element($element, &$form_element, $form_state) {
  $open = '<div class="container-inline">';
  $close = '</div>';
  if (!empty($form_element['#prefix'])) {
    $form_element['#prefix'] = $open . $form_element['#prefix'];
  }
  else {
    $form_element['#prefix'] = $open;
  }

  if (!empty($form_element['#suffix'])) {
    $form_element['#suffix'] = $form_element['#suffix'] . $close;
  }
  else {
    $form_element['#suffix'] = $close;
  }
}
