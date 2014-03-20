<?php

/**
 * @file
 * The finder group element handler plugin.
 */

/**
 * The CTools plugin definition.
 */
$plugin = array(
  'group' => array(
    'title' => t('Group'),
    'description' => t('A container without any formatting, useful for logically grouping elements.'),
    'type' => 'container',
    'settings callback' => 'finder_group_settings',
  ),
);

/**
 * Settings callback.
 */
function finder_group_settings(&$data, $finder, $finder_element_id) {
  $items = &$data['items'];
  $element = &$finder->elements[$finder_element_id];

  // There are no settings for 'group' in the first column, move col 2 to col 1.
  $data['groups'][1] = $data['groups'][2];
  unset($data['groups'][2]);
}