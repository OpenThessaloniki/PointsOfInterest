<?php

/**
 * @file
 * Theme functions for the Finder UI module.
 */

/**
 * Theme the admin table of draggable elements.
 *
 * @param $element
 *   The form element to theme.
 */
function theme_finder_ui_elements_table($variables) {
  $element = $variables['element'];
  $output = '';
  $children = element_children($element);
  if (!empty($children)) {
    $css_id = 'finder-ui-elements-table';
    $order_class = $css_id . '-order';
    $parent_class = $css_id . '-parent';
    $source_class = $css_id . '-source';
    drupal_add_tabledrag($css_id, 'match', 'parent', $parent_class, $parent_class, $source_class);
    drupal_add_tabledrag($css_id, 'order', 'sibling', $order_class);

    $rows = array();

    foreach ((array)$children as $key) {
      $value = &$element[$key];
      $value['parent']['#attributes']['class'] = array($parent_class);
      $value['source']['#attributes']['class'] = array($source_class);
      $value['weight']['#attributes']['class'] = array($order_class);
      $row = array(
        'data' => array(
          array(
            'data' => theme('indentation', array('size' => $value['depth']['#value'])) . drupal_render($value['value']),
            'class' => array('finder-ui-elements-table-value'),
          ),
          array(
            'data' => drupal_render($value['weight']). drupal_render($value['parent']) . drupal_render($value['source']),
            'class' => array('finder-ui-elements-table-weight'),
          )
        ),
        'class' => array('draggable'),
      );

      if ($value['leaf']['#value']) {
        $row['class'][] = 'tabledrag-leaf';
      }

      $rows[] = $row;
    }

    $output = theme('table', array('rows' => $rows, 'attributes' => array('id' => $css_id)));
  }

  return $output;
}