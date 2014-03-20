<?php

/**
 * @file
 * The finder page element handler plugin.
 */

/**
 * The CTools plugin definition.
 */

$plugin = array(
  'page' => array(
    'title' => t('Wizard page'),
    'description' => t('A container that groups elements on a page for multistep forms.'),
    'type' => 'container',
    'settings callback' => 'finder_wizard_settings',
    'element callback' => 'finder_wizard_element',
    'skip element callback' => 'finder_wizard_skip_element',
  ),
);

/**
 * Settings callback.
 */
function finder_wizard_settings(&$data, $finder, $finder_element_id) {
  $items = &$data['items'];
  unset($items['title_display']);
  unset($items['description']);
}

/**
 * Element callback.
 */
function finder_wizard_element($element, &$form_element, $form_state) {
  $page_num = $element->finder->finder_wizard_page_num;

  $open = '<div class="finder-page-num-' . $page_num . ' finder-page-' . $element->id . '">';
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

/**
 * Skip element callback.
 */
function finder_wizard_skip_element($element, $form_state) {
  $finder = $element->finder;

  // Figure out which page this is, if it hasn't been done already.
  if (!isset($finder->finder_wizard_page_num) || !isset($finder->finder_wizard_page_id)) {
    finder_wizard_establish_page($finder);
  }

  reset($finder->finder_wizard);
  $element_page = key($finder->finder_wizard);
  foreach ($finder->finder_wizard as $page_id => $page_elements) {
    if (in_array($element->id, $page_elements)) {
      $element_page = $page_id;
      break;
    }
  }

  // If the current page is the same page as this element, return FALSE.
  return ($finder->finder_wizard_page_id != $element_page);
}
