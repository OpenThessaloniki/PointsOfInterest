<?php
/**
 * @file
 * element.inc
 *
 * Provides the Finder UI element related functions.
 */

finder_inc('common', 'finder_ui');

/**
 * Finder UI element page.
 *
 * @param $finder
 *  The finder object.
 * @param $feid
 *  The finder element id or 'new'.
 * @param $js
 *  The $js parameter required by ctools.
 */
function finder_ui_element_page($finder, $feid, $js) {
  ctools_include('modal');
  ctools_include('ajax');
  ctools_include('object-cache');
  $finder = ctools_object_cache_get('finder', $finder->name);

  $form_state = array(
    'finder' => $finder,
    'feid' => $feid,
    'ajax' => $js,
  );

  $output = ctools_modal_form_wrapper('finder_ui_element_form', $form_state);

  if ($form_state['executed'] && $js) {
    if ($form_state['triggering_element']['#parents'][0] == 'submit' && finder_ui_check_lock($finder)) {
      $finder = ctools_object_cache_get('finder', $finder->name);
      if ($feid == 'new') {
        $element = &$finder->create_element($form_state['values']['id']);
      }
      else {
        $element = &$finder->elements[$feid];
      }
      foreach ($form_state['values'] as $key => $value) {
        if ($key != 'settings') {
          $element->{$key} = $value;
        }
      }
      foreach ($form_state['values']['settings'] as $key => $value) {
        $element->settings[$key] = $value;
      }
      //$finder->save_element($element);
      $finder->build_elements();
      $finder->load_element_handlers();
      ctools_object_cache_set('finder', $finder->name, $finder);
      $output = drupal_render(finder_ui_element_list($finder));
      $output .= '<div class="finder-ui-needs-reload"></div>';
      $commands[] = ajax_command_html('#finder-ui-elements-list', $output);
      $commands[] = ajax_command_css('#edit-changed', array('display' => 'block'));
    }
    $commands[] = ctools_modal_command_dismiss();
    print ajax_render($commands);
    exit;
  }

  if ($js) {
    print ajax_render($output);
    exit;
  }

  // Otherwise, just return the output.
  return $output;
}

/**
 * Finder UI element form.
 *
 * @param $form
 *  The form array.
 * @param &$form_state
 *  The form state array.
 * @return
 *  The form.
 */
function finder_ui_element_form($form, &$form_state) {
  $feid = &$form_state['feid'];
  $finder = &$form_state['finder'];

  if ($feid != 'new') {
    $element = &$finder->elements[$feid];
  }

  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#default_value' => !empty($element->title) ? $element->title : NULL,
    '#size' => 40,
    '#maxlength' => 255,
    '#required' => TRUE,
    '#description' => t('The label on the form element.'),
  );

  $form['id'] = array(
    '#type' => 'machine_name',
    '#default_value' => !empty($element->id) ? $element->id : NULL,
    '#maxlength' => 64,
    '#machine_name' => array(
      'source' => array('title'),
      'exists' => 'finder_ui_element_exists',
    ),
  );

  $element_help = '';
  $element_help_list = finder_ui_get_element_help();
  foreach ($element_help_list as $type => $help_list) {
    $element_help .= '<strong>' . $type . '</strong>';
    $element_help .= theme('item_list', array('items' => $help_list));
    $element_help .= '<br>';
  }

  $form['element'] = array(
    '#type' => 'select',
    '#title' => t('Element'),
    '#default_value' => !empty($element->element) ? $element->element : NULL,
    '#options' => finder_ui_get_element_options(),
    '#required' => TRUE,
    '#empty_option' => ' - ' . t('Choose an element') . ' - ',
    '#description' => '<p>' . t(
      'Choose a form element, or a container to group child elements.  The ' .
      'drag and drop list of elements can be organized like a tree when ' .
      'using containers, by indenting child elements to the right.') . '</p>'
      . $element_help,
  );

/*
  // This doesn't correctly reflect the drag-n-drop, so it screws things up
  // Perhaps we need an 'operations' link on the finder admin page to manually
  // set weights and tree structure

  $potential_parents = array();
  foreach ($finder->elements as $finder_element) {
    if ($finder_element->id != $element->id && $finder_element->element_handler['type'] == 'container') {
      $hyphens = '';
      for ($i = 0; $i <= $finder->element_depth($finder_element); $i++) {
        $hyphens .= '-';
      }
      $potential_parents[$finder_element->id] = $hyphens . ' ' . $finder_element->title;
    }
  }
  if (count($potential_parents)) {
    $form['parent'] = array(
      '#type' => 'select',
      '#title' => t('Parent'),
      '#default_value' => $element->parent,
      '#options' => $potential_parents,
      '#empty_option' => ' - ' . t('None') . ' - ',
    );
  }
*/
  // Add buttons.
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Apply'),
  );
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
  );
  $form['#pre_render'] = array('finder_ui_modal_form_pre_render');

  return $form;
}

/**
 * Finder UI element form submit.
 *
 * @param $form
 *  The form array.
 * @param &$form_state
 *  The form state array.
 */
function finder_ui_element_form_submit($form, &$form_state) {
 // @todo: Move the submit stuff into here.
}

/**
 * Finder UI element delete page.
 *
 * @param $finder
 *  The finder object.
 * @param $feid
 *  The finder element id or 'new'.
 * @param $js
 *  The $js parameter required by ctools.
 * @return
 *  The page output.
 */
function finder_ui_element_delete_page($finder, $feid, $js) {
  ctools_include('modal');
  ctools_include('ajax');
  ctools_include('object-cache');
  $finder = ctools_object_cache_get('finder', $finder->name);

  $form_state = array(
    'finder' => $finder,
    'feid' => $feid,
    'ajax' => $js,
  );

  $output = ctools_modal_form_wrapper('finder_ui_element_delete_form', $form_state);

  if ($form_state['executed'] && $js) {
    if ($form_state['triggering_element']['#parents'][0] == 'submit' && finder_ui_check_lock($finder)) {
      $finder = ctools_object_cache_get('finder', $finder->name);
      $element = &$finder->elements[$feid];
      // Delete the element and remove it from the finder.
      $finder->delete_element($element);
      ctools_object_cache_set('finder', $finder->name, $finder);
      $output = drupal_render(finder_ui_element_list($finder));
      $output .= '<div class="finder-ui-needs-reload"></div>';
      $commands[] = ajax_command_html('#finder-ui-elements-list', $output);
      $commands[] = ajax_command_css('#edit-changed', array('display' => 'block'));
    }
    $commands[] = ctools_modal_command_dismiss();
    print ajax_render($commands);
    exit;
  }

  if ($js) {
    print ajax_render($output);
    exit;
  }

  // Otherwise, just return the output.
  return $output;
}


/**
 * Finder UI element delete form.
 *
 * @param $form
 *  The form array.
 * @param &$form_state
 *  The form state array.
 * @return
 *  The form array.
 */
function finder_ui_element_delete_form($form, &$form_state) {
  $feid = &$form_state['feid'];
  $finder = &$form_state['finder'];
  $element = &$finder->elements[$feid];

  if (finder_ui_check_lock($finder)) {
    $form = confirm_form(
      $form,
      t('Are you sure you want to delete %title?', array('%title' => check_plain($element->title))),
      NULL,
      ''
    );
  }

  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
  );
  $form['#pre_render'] = array('finder_ui_modal_form_pre_render');

  return $form;
}


/**
 * Finder UI element.
 *
 * @param $finder
 *  The finder object.
 * @param $finder_element_id
 *  The finder machine name.
 * @return
 *  The data array.
 */
function finder_ui_element($finder, $finder_element_id) {
  ctools_include('dependent');

  $element = &$finder->elements[$finder_element_id];

  $displays = finder_ui_views_displays($finder->views_view);

  $groups = array();
  $groups[1]['form'] = finder_ui_element_type($element->element_handler);
  if ($element->element_handler['type'] == 'form') {
    $groups[2]['choices'] = t('Choices');
    $groups[2]['fields'] = t('Fields');
    $groups[3]['results'] = t('Results');
  }
  elseif ($element->element_handler['type'] == 'container') {
    $groups[2]['results'] = t('Results');
  }

  $items = array();

  $items['description'] = array(
    '#group' => 'form',
    '#item' =>  array(
      '#title' => t('Description'),
      '#value' => $finder->esetting($element, 'description') ? $finder->esetting($element, 'description') : t('No'),
    ),
    '#form' => array(
      'settings' => array(
        'description' => array(
          '#type' => 'textfield',
          '#title' => t('Description'),
          '#default_value' => $finder->esetting($element, 'description'),
          '#description' => t('The description or help message to display with the form element.'),
        ),
      ),
    ),
  );

  $items['title_display'] = array(
    '#group' => 'form',
    '#item' => array(
      '#title' => t('Title display'),
      '#value' => $finder->esetting($element, 'title_display') ? t(ucfirst($finder->esetting($element, 'title_display'))) : t('Before'),
    ),
    '#form' => array(
      'settings' => array(
        'title_display' => array(
          '#type' => 'radios',
          '#title' => t('Title display'),
          '#default_value' => $finder->esetting($element, 'title_display'),
          '#description' => t('Indicates how the element title should be displayed.'),
          '#options' => array(
            'before' => t('Before') . ': ' . t('The title is rendered as a label before the element.'),
            'after' => t('After') . ': ' . t('The title is rendered as a label after the element.'),
            'invisible' => t('Invisible') . ': ' . t('The title is rendered as a label before the element, but it is made invisible with the Drupal <em>element-invisible</em> system. Compatible with screen-readers.'),
            'attribute' => t('Attribute') . ': ' . t('The title is set as an attribute on the element to create a tooltip.  Note: By default, Drupal core only applies this to radios and checkboxes.'),
          ),
        ),
      ),
    ),
  );

  $header = $finder->esetting($element, 'header');
  $header_format = filter_format_load($header['format']);
  $items['header'] = array(
    '#group' => 'form',
    '#item' => array(
      '#title' => t('Header'),
      '#value' => $header['value'] ? $header_format->name : t('No'),
    ),
    '#form' => array(
      'settings' => array(
        'header' => array(
          '#type' => 'text_format',
          '#base_type' => 'textarea',
          '#format' => $header['format'],
          '#title' => t('Header'),
          '#default_value' => $header['value'],
          '#description' => t('Displayed before the form element.'),
        ),
      ),
    ),
  );

  $footer = $finder->esetting($element, 'footer');
  $footer_format = filter_format_load($footer['format']);
  $items['footer'] = array(
    '#group' => 'form',
    '#item' => array(
      '#title' => t('Footer'),
      '#value' => $footer['value'] ? $footer_format->name : t('No'),
    ),
    '#form' => array(
      'settings' => array(
        'footer' => array(
          '#type' => 'text_format',
          '#base_type' => 'textarea',
          '#format' => $footer['format'],
          '#title' => t('Footer'),
          '#default_value' => $footer['value'],
          '#description' => t('Displayed after the form element.'),
        ),
      ),
    ),
  );

  if ($element->element_handler['type'] == 'form') {

    $items['field_prefix'] = array(
      '#group' => 'form',
      '#item' => array(
        '#title' => t('Field prefix'),
        '#value' => $finder->esetting($element, 'field_prefix') ? $finder->esetting($element, 'field_prefix') : t('No'),
      ),
      '#form' => array(
        'settings' => array(
          'field_prefix' => array(
            '#type' => 'textfield',
            '#title' => t('Field prefix'),
            '#default_value' => $finder->esetting($element, 'field_prefix'),
            '#description' => t('Displayed directly before the form input field.'),
          ),
        ),
      ),
    );

    $items['field_suffix'] = array(
      '#group' => 'form',
      '#item' => array(
        '#title' => t('Field suffix'),
        '#value' => $finder->esetting($element, 'field_suffix') ? $finder->esetting($element, 'field_suffix') : t('No'),
      ),
      '#form' => array(
        'settings' => array(
          'field_suffix' => array(
            '#type' => 'textfield',
            '#title' => t('Field suffix'),
            '#default_value' => $finder->esetting($element, 'field_suffix'),
            '#description' => t('Displayed directly after the form input field.'),
          ),
        ),
      ),
    );

    $items['default_value'] = array(
      '#group' => 'form',
      '#item' =>  array(
        '#title' => t('Default value'),
        '#value' => $finder->esetting($element, 'default_value') ? $finder->esetting($element, 'default_value') : t('No'),
      ),
      '#form' => array(
        'settings' => array(
          'default_value' => array(
            '#type' => 'textfield',
            '#title' => t('Default value'),
            '#default_value' => $finder->esetting($element, 'default_value'),
            '#description' => t('Default value of the form element.'),
            '#translatable' => FALSE,
          ),
        ),
      ),
    );

    $items['required'] = array(
      '#group' => 'form',
      '#item' =>  array(
        '#title' => t('Required'),
        '#value' => $finder->esetting($element, 'required') ? t('Yes') : t('No'),
      ),
      '#form' => array(
        'settings' => array(
          'required' => array(
            '#type' => 'checkbox',
            '#title' => t('Required'),
            '#default_value' => $finder->esetting($element, 'required'),
            '#description' => t('Enforce user input in this field.'),
          ),
        ),
      ),
    );

    $items['skip_empty'] = array(
      '#group' => 'form',
      '#item' => array(
        '#title' => t('Skip empty'),
        '#value' => $finder->esetting($element, 'skip_empty') ? t('Yes') : t('No'),
      ),
      '#form' => array(
        'settings' => array(
          'skip_empty' => array(
            '#type' => 'checkbox',
            '#title' => t('Skip element when one or zero choices exist.'),
            '#default_value' => $finder->esetting($element, 'skip_empty'),
            '#description' => t('Usually when there is only one or zero choices there is no point in asking for input.'),
          ),
        ),
      ),
    );

    $ajax_text = array();
    if ($finder->esetting($element, 'ajax_load')) {
      $ajax_text[] = t('Load');
    }
    if ($finder->esetting($element, 'ajax_auto')) {
      $ajax_text[] = t('Autosubmit');
    }

    $items['ajax'] = array(
      '#group' => 'form',
      '#item' => array(
        '#title' => t('Ajax'),
        '#value' => empty($ajax_text) ? t('No') : implode(', ', $ajax_text),
      ),
      '#form' => array(
        'settings' => array(
          'ajax_load' => array(
            '#type' => 'checkbox',
            '#title' => t('Load with Ajax.'),
            '#default_value' => $finder->esetting($element, 'ajax_load'),
            '#description' => t('You can speed up page loads by loading elements with Ajax, but there will be a slight delay before they appear.  You can also automatically update form elements with Ajax at: <em>(Element) -> Choices -> Reduce</em>'),
          ),
        ),
      ),
    );

  }

  if ($element->element_handler['type'] == 'form') {

    $choices_style_options = array(
      'used_values' => t('Used values'),
      'available_options' => t('Available options'),
      'available_options_php' => t('Available options PHP'),
    );
    $fields = $finder->fields();


    $items['choices_style'] = array(
      '#group' => 'choices',
      '#item' => array(
        '#title' => t('Choices style'),
        '#value' => $choices_style_options[$finder->esetting($element, 'choices_style', 'used_values')],
        '#refresh' => TRUE,
      ),
      '#form' => array(
        'settings' => array(
          'choices_style' => array(
            '#type' => 'radios',
            '#default_value' => $finder->esetting($element, 'choices_style', 'used_values'),
            '#title' => t('Choices style'),
            '#options' => $choices_style_options,
            '#description' => t('If this element needs to display a list of choices, where do they come from?  <em>Used values</em> is the traditional finder choices style, it is recommended unless there is a specific reason it is unsuitable.'),
          ),

          'available_options' => array(
            '#type' => 'textarea',
            '#title' => t('Available options'),
            '#rows' => 6,
            '#default_value' => $finder->esetting($element, 'available_options'),
            '#process' => array('ctools_dependent_process'),
            '#dependency' => array('radio:settings[choices_style]' => array('available_options')),
            '#description' => t('A list of values that are available for selection. Enter one value per line, in the format key|label. The key is the value that will be submitted in the form, and the label is what will be displayed to the user.'),
          ),

          'available_options_php' => finder_ui_php_setting(
            array(
              '#type' => 'textarea',
              '#title' => t('Available options PHP'),
              '#rows' => 6,
              '#default_value' => $finder->esetting($element, 'available_options_php'),
              '#description' => t('PHP code that prints or returns a string with one value per line (i.e. separated by a line break or "\n"), in the format key|label. The key is the value that will be submitted in the form, and the label is what will be displayed to the user.  Plain text and PHP can be mixed, but PHP must be enclosed in PHP tags.'),
              '#process' => array('ctools_dependent_process'),
              '#dependency' => array('radio:settings[choices_style]' => array('available_options_php')),
            ),
            array(
              'finder_element' => t('Object containing data about this finder element.'),
            )
          )
        ),
      ),
    );

    if ($finder->esetting($element, 'choices_style', 'used_values') == 'used_values') {

       $views_display = NULL;
       if (!$finder->esetting($element, 'display')) {
         $views_display = t('Inherit');
       }
       elseif (!empty($displays[$finder->esetting($element, 'display')])) {
         $views_display = $displays[$finder->esetting($element, 'display')];
       }
       $items['display'] = array(
         '#group' => 'choices',
         '#item' =>  array(
           '#title' => t('Views display'),
           '#value' => $views_display,
         ),
         '#form' => array(
           'settings' => array(
             'display' => array(
               '#type' => 'select',
               '#title' => t('Views display'),
               '#default_value' => $finder->esetting($element, 'display'),
               '#options' => array(t('Inherit from finder')) + $displays,
               '#required' => TRUE,
               '#description' => t('
                   Allows you to use a specific display to calculate the choices list,
                   you can use this to add sort/filter settings via Views that do not
                   affect other elements or the results.'),
             ),
           ),
         ),
       );
       $items['contextual_filter'] = array(
         '#group' => 'choices',
         '#item' => array(
           '#title' => t('Contextual filter'),
           '#value' => $finder->esetting($element, 'contextual_filter') ? $finder->esetting($element, 'contextual_filter') : t('No'),
         ),
         '#form' => array(
           'settings' => array(
             'contextual_filter' => finder_ui_php_setting(
               array(
                 '#type' => 'textarea',
                 '#default_value' => $finder->esetting($element, 'contextual_filter'),
                 '#title' => t('Contextual filter'),
                 '#rows' => 1,
                 '#description' => t('
                   This can be used to provide contextual filter arguments to the Views display.
                   You can provide a list of arguments seperated by a forward-slash.
                   e.g: <em>term_1/term_2</em>.<br />Or insert PHP code to generate the
                   list of arguments. e.g: <em>term_1/&lt;?php print "term_x/term_y";
                   ?&gt;/term_2</em>.'),
               ),
               array(
                 'finder' => t('Object containing data about this finder.'),
               )
             ),
           ),
         ),
       );
       $choice_display = array(
         'field' => t('Field value'),
         'rendered' => t('Rendered row from Views display'),
       );
       $items['choice_display'] = array(
         '#group' => 'choices',
         '#item' =>  array(
           '#title' => t('Choice display'),
           '#value' => $choice_display[$finder->esetting($element, 'choice_display', 'field')],
         ),
         '#form' => array(
           'settings' => array(
             'choice_display' => array(
               '#type' => 'radios',
               '#title' => t('What to use as the display of the choice'),
               '#default_value' => $finder->esetting($element, 'choice_display', 'field'),
               '#options' => $choice_display,
               '#description' => t('If using the <em>Rendered row</em> ensure the <em>sanitize</em> setting on the fields allows the HTML to go through, or alternatively strips the HTML.  <em>Rendered row</em> allows you to configure your Views display for choices in a way that it displays something other than the finder fields and the results page rows.  Tip: You can further modify the choice display through the <em>Rewrite</em> setting.'),
             ),
           ),
         ),
       );

       $items['sort'] = array(
         '#group' => 'choices',
         '#item' =>  array(
           '#title' => t('Sort'),
           '#value' => $finder->esetting($element, 'sort') ? t('Yes') : t('No'),
         ),
         '#form' => array(
           'settings' => array(
             'sort' => array(
               '#type' => 'checkbox',
               '#title' => t('Sort'),
               '#default_value' => $finder->esetting($element, 'sort'),
               '#description' => t('Records are returned to Finder using the sorting method set in the Views display.  However, the displayed choices may need to be resorted for easier scanning, particularly when using multiple fields.  This option does a basic sort on the display value of the choices.'),
             ),
           ),
         ),
       );


       $reduce_elements = array('' => ' - ' . t('None') . ' - ');
       foreach ($finder->elements as $reduce_element) {
         if ($reduce_element->id != $element->id && $reduce_element->element_handler['type'] == 'form') {
           $reduce_elements[$reduce_element->id] = $reduce_element->title;
         }
       }
       $reduce = $finder->esetting($element, 'reduce', array());
       $reduce_chosen = array();
       foreach ($reduce as $reduce_element_id) {
         $reduce_chosen[] = $reduce_elements[$reduce_element_id];
       }

       $items['reduce'] = array(
         '#group' => 'choices',
         '#item' =>  array(
           '#title' => t('Reduce'),
           '#value' => array_filter($finder->esetting($element, 'reduce', array())) ? implode(', ', $reduce_chosen) : t('No'),
         ),
         '#form' => array(
           'settings' => array(
             'reduce' => array(
               '#type' => 'select',
               '#title' => t('Reduce choices based on these elements'),
               '#default_value' => $finder->esetting($element, 'reduce'),
               '#options' => $reduce_elements,
               '#multiple' => TRUE,
               '#dependency' => array('radio:settings[choices_style]' => array('used_values')),
               '#description' => t("Reduce choices to used values in the results of elements where values have already been set. Typically you would only pick elements that come <em>before</em> this one, and arrange your finder elements so that the more results that are produced by an element's values the earlier it is presented in the form."),
             ),
           ),
         ),
       );

       $choices_rewrite_text = t('No');
       if ($finder->esetting($element, 'choices_rewrite')) {
         $choices_rewrite_bits[] = t('Display');
       }
       if ($finder->esetting($element, 'value_rewrite')) {
         $choices_rewrite_bits[] = t('Value');
       }
       if (!empty($choices_rewrite_bits)) {
         $choices_rewrite_text = implode(', ', $choices_rewrite_bits);
       }
       $items['choices_rewrite'] = array(
         '#group' => 'choices',
         '#item' =>  array(
           '#title' => t('Rewrite'),
           '#value' => $choices_rewrite_text,
         ),
         '#form' => array(
           'settings' => array(
             'choices_rewrite' => finder_ui_php_setting(
               array(
                 '#type' => 'textarea',
                 '#title' => t('Rewrite display with PHP'),
                 '#rows' => 6,
                 '#default_value' => $finder->esetting($element, 'choices_rewrite'),
                 '#description' => t('PHP code that returns a display value for the choice based on the incoming variables.  PHP must be enclosed in PHP tags.'),
                 '#process' => array('ctools_dependent_process'),
               ),
               array(
                 'element' => t('Object containing data about this finder element.'),
                 'value' => t('The key value that will be submitted.'),
                 'display' => t('The default display value for the choice - this is what you need to change and return!'),
                 'row' => t('Object containing the result of the database record.'),
                 'field' => t('Object containing data about the relevant field.'),
                 'delta' => t('The array key of <em>field</em> properties that come as an array.'),
               )
             ),
             'value_rewrite' => finder_ui_php_setting(
               array(
                 '#type' => 'textarea',
                 '#title' => t('Rewrite value with PHP'),
                 '#rows' => 6,
                 '#default_value' => $finder->esetting($element, 'value_rewrite'),
                 '#description' => t('PHP code that returns a value for the choice based on the incoming variables.  PHP must be enclosed in PHP tags.'),
                 '#process' => array('ctools_dependent_process'),
                ),
               array(
                 'element' => t('Object containing data about this finder element.'),
                 'value' => t('The key value that will be submitted - this is what you need to change and return!'),
                 'display' => t('The default display value for the choice.'),
                 'row' => t('Object containing the result of the database record.'),
                 'field' => t('Object containing data about the relevant field.'),
                 'delta' => t('The array key of <em>field</em> properties that come as an array.'),
               )
             ),
           ),
         ),
       );

     }

     if ($element->element_handler['type'] == 'form') {

       $items['delimit'] = array(
         '#group' => 'results',
         '#item' =>  array(
           '#title' => t('Delimit value'),
           '#value' => $finder->esetting($element, 'delimit') ? $finder->esetting($element, 'delimit') : t('No'),
         ),
         '#form' => array(
           'settings' => array(
             'delimit' => array(
               '#type' => 'textfield',
               '#title' => t('Delimit value'),
               '#default_value' => $finder->esetting($element, 'delimit'),
               '#description' => t('Treat delimited values as separate keywords upon submission.  For example, if you type a space here, the submitted value will be expanded into a value for each word.  Leave empty to disable this feature.'),
               '#translatable' => FALSE,
             ),
           ),
         ),
       );

       $items['field_logic'] = array(
         '#group' => 'results',
         '#item' =>  array(
           '#title' => t('Field logic'),
           '#value' => $finder->esetting($element, 'field_logic'),
         ),
         '#form' => array(
           'settings' => array(
             'field_logic' => array(
               '#type' => 'radios',
               '#title' => t('Field logic'),
               '#default_value' => $finder->esetting($element, 'field_logic'),
               '#description' => t('When multiple fields are selected for this element, how should the fields be combined when calculating results?'),
               '#options' => array(
                 'AND' => t('Match all fields using the AND operator. (Conjunction)'),
                 'OR' => t('Match any field using the OR operator. (Disjunction)'),
               ),
             ),
           ),
         ),
       );

       $items['value_logic'] = array(
         '#group' => 'results',
         '#item' =>  array(
           '#title' => t('Value logic'),
           '#value' => $finder->esetting($element, 'value_logic'),
         ),
         '#form' => array(
           'settings' => array(
             'value_logic' => array(
               '#type' => 'radios',
               '#title' => t('Value logic'),
               '#default_value' => $finder->esetting($element, 'value_logic'),
               '#description' => t('When multiple values are submitted for this element, how should the values be combined when calculating results?'),
               '#options' => array(
                 'AND' => t('Match all values using the AND operator. (Conjunction)'),
                 'OR' => t('Match any value using the OR operator. (Disjunction)'),
               ),
             ),
           ),
         ),
       );

       $items['nesting_order'] = array(
         '#group' => 'results',
         '#item' =>  array(
           '#title' => t('Nesting order'),
           '#value' => $finder->esetting($element, 'nesting_order') ? t('Values first') : t('Fields first'),
         ),
         '#form' => array(
           'settings' => array(
             'nesting_order' => array(
               '#type' => 'radios',
               '#title' => t('Nesting order'),
               '#default_value' => $finder->esetting($element, 'nesting_order'),
               '#description' => t('With multiple values or fields, how should fields and values be matched together?  This is difficult to explain.  Suppose you have an element that selects two fields and a user submits two values (X and Y), the first option here will do matching like this: <em>(field_1 matches X; field_2 matches X), (field_1 matches Y; field_2 matches Y)</em> whereas the second will do it like this: <em>(field_1 matches X, field_1 matches Y); (field_2 matches X, field_2 matches Y)</em>.  The semicolons represent <em>field logic</em>, the commas represent <em>value logic</em>, and the word <em>matches</em> refers to the <em>matching</em> option.  So simple.'),
               '#options' => array(
                 0 => t('Match multiple fields for each value first, then combine the results of multiple values. (recommended)'),
                 1 => t('Match multiple values for each field first, then combine the results of multiple fields.'),
               ),
             ),
           ),
         ),
       );

    }

    $default_match = NULL;
    $matches = $finder->matches();
    $matches['x'] = array(
      'name' => t('Custom'),
      'description' => t('Specify below like: <code> field [operator] [prefix]value[suffix]</code>'),
    );
    foreach ($matches as $key => $match) {
      $matches[$key] = finder_ui_matches_label($match, t('results'), t('submitted values'));
      if ($key == $finder->esetting($element, 'match')) {
        $default_match = $match['name'];
      }
    }

    $match_custom_operator_size = 5;
    if ($finder->esetting($element, 'match_custom_operator')) {
      $match_custom_operator_size = min(drupal_strlen($finder->esetting($element, 'match_custom_operator')), 30);
    }

    $match_custom_prefix_size = 3;
    if ($finder->esetting($element, 'match_custom_prefix')) {
      $match_custom_prefix_size = min(drupal_strlen($finder->esetting($element, 'match_custom_prefix')), 30);
    }

    $match_custom_suffix_size = 3;
    if ($finder->esetting($element, 'match_custom_suffix')) {
      $match_custom_suffix_size = min(drupal_strlen($finder->esetting($element, 'match_custom_suffix')), 30);
    }

    $items['match'] = array(
      '#group' => 'results',
      '#item' =>  array(
        '#title' => t('Matching'),
        '#value' => $default_match,
      ),
      '#form' => array(
        'settings' => array(
          'match' => array(
            '#type' => 'radios',
            '#title' => t('Matching'),
            '#default_value' => $finder->esetting($element, 'match'),
            '#options' => $matches,
            '#description' => t('<em>Equals</em> is the most common result matching method.'),
          ),
          'match_custom_operator' => array(
            '#type' => 'textfield',
            '#title' => t('Operator'),
            '#title_display' => 'none',
            '#default_value' => $finder->esetting($element, 'match_custom_operator'),
            '#size' => $match_custom_operator_size,
            '#maxlength' => 512,
            '#field_prefix' => t('field'),
            '#prefix' => '<div class="finder-ui-match-custom"><span class="finder-ui-match-custom-operator">',
            '#suffix' => '</span>',
            '#process' => array('ctools_dependent_process'),
            '#dependency' => array('radio:settings[match]' => array('x')),
            '#translatable' => FALSE,
          ),
          'match_custom_prefix' => array(
            '#type' => 'textfield',
            '#title' => t('Prefix'),
            '#title_display' => 'none',
            '#default_value' => $finder->esetting($element, 'match_custom_prefix'),
            '#size' => $match_custom_prefix_size ,
            '#maxlength' => 512,
            '#field_suffix' => t('value'),
            '#prefix' => '<span class="finder-ui-match-custom-value-prefix">',
            '#suffix' => '</span>',
            '#process' => array('ctools_dependent_process'),
            '#dependency' => array('radio:settings[match]' => array('x')),
            '#translatable' => FALSE,
          ),
          'match_custom_suffix' => array(
            '#type' => 'textfield',
            '#title' => t('Suffix'),
            '#title_display' => 'none',
            '#default_value' => $finder->esetting($element, 'match_custom_suffix'),
            '#size' => $match_custom_suffix_size,
            '#maxlength' => 512,
            '#prefix' => '<span class="finder-ui-match-custom-value-suffix">',
            '#suffix' => '</span></div>',
            '#process' => array('ctools_dependent_process'),
            '#dependency' => array('radio:settings[match]' => array('x')),
            '#translatable' => FALSE,
          ),
        ),
      ),
    );

  }
  elseif ($element->element_handler['type'] == 'container') {
    $items['skip_empty'] = array(
      '#group' => 'form',
      '#item' => array(
        '#title' => t('Skip empty'),
        '#value' => $finder->esetting($element, 'skip_empty') ? t('Yes') : t('No'),
      ),
      '#form' => array(
        'settings' => array(
          'skip_empty' => array(
            '#type' => 'checkbox',
            '#title' => t('Skip empty'),
            '#default_value' => $finder->esetting($element, 'skip_empty'),
            '#description' => t("Skip this container if it contains no elements.  Useful if skip empty is used on all child elements."),
          ),
        ),
      ),
    );

    $items['element_logic'] = array(
      '#group' => 'results',
      '#item' => array(
        '#title' => t('Element logic'),
        '#value' => $finder->esetting($element, 'element_logic'),
      ),
      '#form' => array(
        'settings' => array(
          'element_logic' => array(
            '#type' => 'radios',
            '#title' => t('Element logic'),
            '#default_value' => $finder->esetting($element, 'element_logic'),
            '#description' => t("With multiple elements, how should the results of each element be combined to get the results?"),
            '#options' => array(
              'AND' => t('Match all elements using the AND operator. (Conjunction)'),
              'OR' => t('Match any element using the OR operator. (Disjunction)'),
            ),
          ),
        ),
      ),
    );

  }

  $data = array(
    'items' => $items,
    'groups' => $groups,
  );

  if (!empty($element->element_handler) && $callback = ctools_plugin_get_function($element->element_handler, 'settings callback')) {
    $callback($data, $finder, $finder_element_id);
  }

  drupal_alter('finder_ui_element', $data, $finder, $finder_element_id);

  return $data;
}

/**
 * Finder UI element list.
 *
 * @param $finder
 *  The finder object.
 * @return
 *  The form array.
 */
function finder_ui_element_list($finder) {
  finder_inc('element-item', 'finder_ui');
  finder_inc('field', 'finder_ui');

  if (!empty($finder->elements)) {

    $form['elements'] = array(
      '#prefix' => '<div class="description finder-ui-elements-table-desc">' . t('Elements can be organised here in a tree.  Drag elements to the right when they are under container elements to group them within that container.  Drag elements up and down to change their order.') . '</div>',
      '#theme' => 'finder_ui_elements_table',
    );
    foreach ($finder->elements as $key => $element) {

      // Reset messed up parent property.
      if (empty($finder->elements[$element->parent])) {
        $finder->elements[$key]->parent = NULL;
      }

      $columns = array();

      $form['elements'][$element->id]['leaf']['#type'] = 'value';
      $form['elements'][$element->id]['leaf']['#value'] = ($element->element_handler['type'] == 'form');

      $form['elements'][$element->id]['value']['#prefix'] = '<div id="finder-ui-element-' . $element->id . '" class="finder-element">';
      $form['elements'][$element->id]['value']['#suffix'] = '</div>';

      $form['elements'][$element->id]['value']['info']['#prefix'] = '<div class="finder-ui-element-info">';
      $form['elements'][$element->id]['value']['info']['#suffix'] = '</div>';

      $menu = array(
        'links' => array(
          'edit' => array(
            'title' => t('Edit element info'),
            'href' => 'admin/structure/finder/list/' . $finder->name . '/element/' . $element->id . '/nojs',
            'html' => TRUE,
            'attributes' => array('class' => array('ctools-use-modal', 'ctools-modal-finder-modal-style')),
          ),
          'delete' => array(
            'title' => t('Delete element'),
            'href' => 'admin/structure/finder/list/' . $finder->name . '/delete-element/' . $element->id . '/nojs',
            'html' => TRUE,
            'attributes' => array('class' => array('ctools-use-modal', 'ctools-modal-finder-modal-style')),
          ),
        ),
      );

      $form['elements'][$element->id]['value']['info']['extra']['#markup'] = theme('links__ctools_dropbutton', $menu);

      $form['elements'][$element->id]['value']['info']['title']['#prefix'] = '<div class="finder-element-title">';
      $form['elements'][$element->id]['value']['info']['title']['#markup'] = '<strong>' . check_plain($element->title) . '</strong> ';
      $form['elements'][$element->id]['value']['info']['title']['#suffix'] = '</div>';

      $form['elements'][$element->id]['value']['info']['type']['#prefix'] = '<div class="finder-element-type">';
      $form['elements'][$element->id]['value']['info']['type']['#markup'] = '<span>' . $finder->element_handler($element, 'title') . '</span> ';
      $form['elements'][$element->id]['value']['info']['type']['#suffix'] = '</div>';


      $form['elements'][$element->id]['value']['settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('Settings'),
        '#collapsible' => TRUE,
        '#collapsed' => !($element->element_handler['type'] == 'form' && !$finder->esetting($element, 'fields')),
        '#attached' => array(
          'js' => array(
            'misc/form.js',
            'misc/collapse.js',
          ),
        ),
        '#attributes' => array(
          'class' => array('collapsible', 'collapsed'),
        ),
      );

      $columns['#prefix'] = '<div class="finder-ui-columns">';
      $columns['#suffix'] = '</div>';

      $finder_ui = finder_ui_element($finder, $element->id);
      $finder_items = array();
      $stripe = 'odd';

      foreach ($finder_ui['items'] as $item_key => $item) {
        $stripe = $stripe == 'odd' ? 'even' : 'odd';
        $finder_items[$item['#group']][$item_key]['#prefix'] = '<div class="finder-ui-item ' . $stripe . '" id="finder-ui-element-' . $element->id . '-item-' . $item_key . '">';
        $finder_items[$item['#group']][$item_key]['#markup'] = finder_ui_element_item($finder, $element->id, $item_key, $item);
        $finder_items[$item['#group']][$item_key]['#suffix'] = '</div>';
      }

      // Fields group is a special case.
      $finder_items['fields']['fields'] = finder_ui_field_list($finder, $element->id);
      $finder_items['fields']['fields']['#prefix'] = '<div id="finder-ui-element-' . $element->id . '-fields-list" class="finder-ui-item finder-ui-field-list">';
      $finder_items['fields']['fields']['#suffix'] = '</div>';

      foreach (array_keys($finder_ui['groups']) as $column) {
        $columns[$column]['#prefix'] = '<div class="finder-ui-column" id="finder-ui-column-' . $column . '">';
        $columns[$column]['#suffix'] = '</div>';
        foreach ($finder_ui['groups'][$column] as $group_key => $group) {
          $extra = '';

          if ($group_key == 'fields') {
            // Fields group is a special case.
            $menu = array(
              'links' => array(
                'edit' => array(
                  'title' => t('Add'),
                  'href' => 'admin/structure/finder/list/' . $finder->name . '/field/' . $element->id . '/new/nojs',
                  'html' => TRUE,
                  'attributes' => array('class' => array('ctools-use-modal', 'ctools-modal-finder-modal-style')),
                ),
              ),
            );

            $extra = theme('links__ctools_dropbutton', $menu);
          }

          $columns[$column][$group_key]['#prefix'] = '<div class="finder-ui-group" id="finder-ui-group-' . $group_key . '">';
          $columns[$column][$group_key]['#suffix'] = '</div>';
          $columns[$column][$group_key]['title']['#prefix'] = '<div class="finder-ui-group-title" id="finder-ui-group-' . $group_key . '-title">';
          $columns[$column][$group_key]['title']['#markup'] = $extra . '<h3>' . $group . '</h3>';
          $columns[$column][$group_key]['title']['#suffix'] = '</div>';

          $columns[$column][$group_key]['items'] = $finder_items[$group_key];

        }
      }
      $form['elements'][$element->id]['value']['settings']['columns'] = $columns;

      $form['elements'][$element->id]['depth'] = array('#type' => 'value', '#value' => $finder->element_depth($element));

      $form['elements'][$element->id]['weight'] = array(
        '#title' => t('Weight'),
        '#type' => 'weight',
        '#delta' => count($finder->elements) + 1,
        '#default_value' => isset($element->weight) ? $element->weight : count($finder->elements) + 1,
      );
      $form['elements'][$element->id]['source'] = array(
        '#type' => 'hidden',
        '#value' => $element->id,
      );
      $form['elements'][$element->id]['parent'] = array(
        '#type' => 'hidden',
        '#default_value' => isset($element->parent) ? $element->parent : NULL,
      );
    }

  }
  else {
    $form['no_elements'] = array(
      '#prefix' => '<div class="messages error">',
      '#markup' => t('There are currently no elements configured for this finder.'),
      '#suffix' => '</div>',
    );
  }

  return $form;
}

