<?php

/**
 * @file
 * The finder fieldset element handler plugin.
 */

/**
 * The CTools plugin definition.
 */
$plugin = array(
  'fieldset' => array(
    'title' => t('Fieldset'),
    'description' => t('A container formatted as a form fieldset.'),
    'type' => 'container',
    'settings callback' => 'finder_fieldset_settings',
    'element callback' => 'finder_fieldset_element',
  ),
);

/**
 * Settings callback.
 */
function finder_fieldset_settings(&$data, $finder, $finder_element_id) {
  $items = &$data['items'];
  $element = &$finder->elements[$finder_element_id];

  unset($items['title_display']);

  $items['collapsible'] = array(
    '#group' => 'form',
    '#item' =>  array(
      '#title' => t('Collapse'),
      '#value' => $finder->esetting($element, 'collapsible') ? ($finder->esetting($element, 'collapsed') ? t('Collapsed') : t('Collapsible')) : t('No'),
    ),
    '#form' => array(
      'settings' => array(
        'collapsible' => array(
          '#type' => 'checkbox',
          '#title' => t('Collapsible'),
          '#default_value' => $finder->esetting($element, 'collapsible'),
          '#description' => t('Indicates whether or not the fieldset can be collapsed with JavaScript.'),
        ),
        'collapsed' => array(
          '#type' => 'checkbox',
          '#title' => t('Collapsed'),
          '#default_value' => $finder->esetting($element, 'collapsed'),
          '#description' => t('Indicates whether or not the fieldset is collapsed by default.'),
          '#states' => array(
            'visible' => array(
              ':input[name="settings[collapsible]"]' => array('checked' => TRUE),
            ),
          ),
        ),
      ),
    ),
  );

}

/**
 * Element callback.
 */
function finder_fieldset_element($element, &$form_element, $form_state) {
  $finder = $element->finder;
  $form_element['#type'] = 'fieldset';
  $form_element['#collapsible'] = $finder->esetting($element, 'collapsible');
  $form_element['#collapsed'] = $finder->esetting($element, 'collapsed');
}
