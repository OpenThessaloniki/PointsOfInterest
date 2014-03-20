<?php

/**
 * @file
 * The finder form.
 */

/**
 * FAPI definition for the finder form.
 *
 * @param &$form
 *   The Forms API form array.
 * @param &$form_state
 *   The Forms API form state.
 * @param $finder
 *   The finder object.
 * @see finder_form_submit()
 */
function finder_form($form, &$form_state, $finder) {
  global $user;

  ctools_include('plugins');

  // Make sure drupal core loads this file when handling this form.
  form_load_include($form_state, 'inc', 'finder', 'includes/form');

  $form_state['finder'] = $finder;

  // see if there is already a form state we should be using.
  $finder_form_state = finder_form_state($finder);
  if (is_array($finder_form_state)) {
    $form_state = array_merge($form_state, $finder_form_state);
  }

  $finder->form_state = $form_state;

  module_invoke_all('finder_form', $finder);

  $form['finder_name'] = array(
    '#type' => 'value',
    '#value' => $finder->name,
  );

  $form['#action'] = url($finder->path);
  if ($user->uid) {
    $form['#token'] = FALSE;
  }
  $form['finder_form'] = array(
    '#weight' => 0,
    '#prefix' => '<div class="finder-form finder-' . $finder->name . '">',
    '#suffix' => '</div>',
  );

  $header = $finder->setting('header');

  if (!empty($header['value']) && isset($header['format'])) {
    $form['finder_form']['#prefix'] .=
      '<div class="prefix">'
      . check_markup(
          $header['value'],
          $header['format'],
          FALSE
        )
      . '</div>';
  }
  $max_weight = 0;

  $rendered_elements = array();

  $ajax_settings = array();

  foreach ($finder->elements as $element) {

    // Ensure the module plugin file is always loaded.
    if (isset($element->element_handler['file'])) {
      $file = $element->element_handler['file'];
      if (isset($element->element_handler['path'])) {
        $file = $element->element_handler['path'] . '/' . $file;
      }
      $pathinfo = pathinfo($file);
      form_load_include(
        $form_state,
        $pathinfo['extension'],
        $element->element_handler['plugin module'],
        str_replace(
          drupal_get_path('module', $element->element_handler['plugin module']) . '/', '', $pathinfo['dirname']
        ) . '/' . $pathinfo['filename']
      );
    }

    // Skip element callback - decide whether to render this element now.
    if (!empty($element->parent) && !in_array($element->parent, $rendered_elements)) {
      continue;
    }
    if ($handler_function = ctools_plugin_get_function($element->element_handler, 'skip element callback')) {
      $skip_element_result = $handler_function($element, $form_state);
      if ($skip_element_result) {
        continue;
      }
    }

    // Register this element to signify that it is being rendered.
    $rendered_elements[] = $element->id;

    $max_weight = max($max_weight, $element->weight);

    if ($finder->esetting($element, 'reduce')) {
      $ajax_settings['reduce'][$element->id] = $finder->esetting($element, 'reduce');
    }

    if ($finder->esetting($element, 'ajax_load')) {
      $form_element = array(
        '#markup' => '<div class="finder-form-element finder-form-element-' . $element->id . '"></div>',
      );
      $ajax_settings['load'][] = $element->id;
    }
    else {
      $form_element = finder_form_element($finder, $element, $form_state);
    }

    $parents = array_reverse($finder->element_parents($element));
    $value = &$form['finder_form'];
    foreach ($parents as $p) {
      $value = &$value[$p];
    }
    $value[$element->id] = $form_element;

  }

  if ($finder->setting('autosubmit')) {
    $ajax_settings['autosubmit'][] = $finder->build_id;
  }

  if (!empty($form_state['finished']) && $finder->setting('ajax_results_load')) {
    $ajax_settings['results_load'][] = $finder->build_id;
  }

  if ($finder->setting('ajax_results_update')) {
    $ajax_settings['results_update'][] = $finder->build_id;
  }

  if (!empty($ajax_settings)) {
    drupal_add_js(array('finder' => array($finder->name => $ajax_settings)), 'setting');
    drupal_add_js(drupal_get_path('module', 'finder') . '/finder.js');
  }

  // Skip empty.
  $form['finder_form'] = finder_form_skip_empty($form['finder_form'], $finder);

  $form['finder_form']['actions']['#weight'] = $max_weight + 100;

  if ($finder->setting('find_button')) {

    $form['finder_form']['actions']['find'] = array(
      '#type' => 'submit',
      '#name' => 'find',
      '#value' => $finder->setting('find_text'),
    );

    if ($finder->setting('ajax')) {

      $wrapper = $finder->build_id;
      $form_state['ajax_display'] = 'block';

      // Check if the 'ajax_remote' setting is on, that we are on the finder page, and that we are in a block.
      if ($finder->setting('ajax_remote') && strpos($_GET['q'], $finder->path) === 0 && $finder->build_display === 'block') {
        // We are using the block as a remote control for the page.  Change the wrapper for Ajax purposes.
        $wrapper = 'finder-page-' . $finder->name . '-wrapper';
        $form_state['ajax_display'] = 'page';
      }

      $form['finder_form']['actions']['find']['#ajax'] = array(
        'callback' => 'finder_ajax',
        'wrapper' => $wrapper,
        'method' => 'replace',
        'effect' => $finder->setting('ajax_effect'),
      );

    }

  }

  if ($finder->setting('go_button')) {
    $form['finder_form']['actions']['go'] = array(
      '#type' => 'submit',
      '#name' => 'go',
      '#value' => $finder->setting('go_text'),
    );
  }

  $footer = $finder->setting('footer');
  if (!empty($footer['value']) && isset($footer['format'])) {
    $form['finder_form']['#suffix'] =
      '<div class="suffix">'
      . check_markup(
          $footer['value'],
          $footer['format'],
          FALSE
        )
      . '</div>' . $form['finder_form']['#suffix'];
  }

  $form['#submit'] = array(
    'finder_form_submit',
  );
  $form['#validate'] = array(
    'finder_form_validate',
  );

  return $form;

}

/**
 * Get the form array for an element.
 *
 * @param $finder
 *  A loaded finder object.
 * @param $element
 *  The element object
 * @param $form_state
 *  The form state.  Should only use $form_state['values'] in this function
 *  because of finder_element_ajax().
 * @return
 *  The form element array.
 */
function finder_form_element(&$finder, &$element, &$form_state) {
  if (isset($form_state['values'][$element->id])) {
    $element_default = $form_state['values'][$element->id];
  }
  else {
    $element_default = $finder->esetting($element, 'default_value');
    $finder->form_state['values'][$element->id] = $form_state['values'][$element->id] = $element_default;
  }

  $form_element = array(
    '#title' => check_plain($element->title),
    '#weight' => $element->weight,
    '#description' => check_markup($finder->esetting($element, 'description')),
    '#default_value' => $element_default,
    '#required' => $finder->esetting($element, 'required'),
    '#executes_submit_callback' => TRUE,
    '#field_prefix' => check_markup($finder->esetting($element, 'field_prefix')),
    '#field_suffix' => check_markup($finder->esetting($element, 'field_suffix')),
    '#title_display' => $finder->esetting($element, 'title_display', 'before'),
    '#attributes' => array(
      'class' => array('finder-element', 'finder-element-' . $element->id),
    ),
  );

  $header = $finder->esetting($element, 'header');
  $form_element['#prefix'] = '';
  if (!empty($header['value']) && isset($header['format'])) {
    $form_element['#prefix'] = check_markup($header['value'], $header['format'], FALSE);
  }
  $form_element['#prefix'] = '<div class="finder-form-element finder-form-element-' . $element->id . '">' . $form_element['#prefix'];

  $footer = $finder->esetting($element, 'footer');
  $form_element['#suffix'] = '';
  if (!empty($footer['value']) && isset($footer['format'])) {
    $form_element['#suffix'] = check_markup($footer['value'], $footer['format'], FALSE);
  }
  $form_element['#suffix'] .= '</div>';

  if ($handler_function = ctools_plugin_get_function($element->element_handler, 'element callback')) {
    $handler_function($element, $form_element, $form_state);
  }

  return $form_element;
}

/**
 * Skip empty.
 *
 * Recursively handle skip empty setting.
 */
function finder_form_skip_empty($form, $finder) {
  $children = element_children($form);
  foreach ($children as $child) {
    if (isset($finder->elements[$child])) {
      $form[$child] = finder_form_skip_empty($form[$child], $finder);
      if ($finder->elements[$child]->element_handler['type'] == 'container') {
        // Containers are considered empty if they contain no child elements.
        if ($finder->esetting($finder->elements[$child], 'skip_empty')) {
          $child_children = element_children($form[$child]);
          if (empty($child_children)) {
            unset($form[$child]);
          }
        }
      }
      else {
        if ($finder->esetting($finder->elements[$child], 'skip_empty')) {
          // Forms are considered empty if there are zero or one choices.
          $finder->find = array(
            'mode' => 'choices',
            'keywords' => array($finder->elements[$child]->id => array(NULL)),
            'element' => $finder->elements[$child],
          );
          $finder->find();
          if (count($finder->find['results']) <= 1) {
            unset($form[$child]);
          }
        }
      }
    }
  }
  return $form;
}

/**
 * Validate function for finder form.
 *
 * Implements the 'validate_empty' functionality.
 *
 * @see finder_form()
 */
function finder_form_validate($form, &$form_state) {
  $finder = $form_state['finder'];
  if ($finder->setting('validate_empty')) {
    $all_empty = TRUE;
    foreach ($finder->elements as $element) {
      if (!empty($form_state['values'][$element->id])) {
        $all_empty = FALSE;
        break;
      }
    }
    if ($all_empty) {
      form_set_error(
        'form',
        t(
          'Please complete the %finder form.',
          array('%finder' => $finder->title)
        )
      );
    }
  }
}

/**
 * Submit function for finder form.
 *
 * Adds some needed data to $form_state and calls finder_form_state().
 *
 * @see finder_form()
 */
function finder_form_submit($form, &$form_state) {
  $form_state['rebuild'] = TRUE;
  $finder = $form_state['finder'];
  $form_state['finished'] = TRUE;
  if (!$finder->setting('ajax') || $form_state['clicked_button']['#name'] != 'find') {
    finder_form_state($finder, $form_state);
  }
}

/**
 * Statically 'get' or 'set' the FAPI form state in a per-finder cache.
 *
 * When used to 'set' the form state it will also check to see if a redirect
 * is required to go to the results path with arguments.  When used to 'get'
 * the form state it will check the static cache for a stored form state, then
 * it will check the session for a form state carried over from another page,
 * and finally it will attempt to build a form state out of the path arguments.
 *
 * @param $finder
 *   The finder.
 * @param $form_state
 *   The Forms API form state (if supplied, will 'set' the form state).
 * @return
 *   A copy of the Forms API form state.
 */
function finder_form_state($finder, $form_state = NULL) {

  static $finder_form_state = NULL;

  if ($form_state) { // we are setting the form_state in a submit.
    // last chance for modules to intefere before potential redirect.
    drupal_alter('finder_form_state', $form_state, $finder);
    $finder_form_state[$finder->name] = $form_state;

    // handle URL stuff.
    if ($finder->setting('url') == 'enabled') {
      $sep = $finder->setting('url_delimiter');
      $empty_symbol = $finder->setting('url_empty') ? $finder->setting('url_empty') : ' ';
      $query = array();
      $keywords = array();
      foreach ($finder->elements as $element) {
        if ($element->element_handler['type'] == 'form' && isset($form_state['values'][$element->id])) {
           $keyword = (array)$form_state['values'][$element->id];
           foreach ($keyword as $k => $v) {

             // Handle forward slashes in input.
             $v = str_replace("/", "%2f%2f", $v);

             // Handle new lines in input.
             $v = str_replace("\r\n", "\n", $v);
             $v = str_replace("\n", urlencode("***BREAK***"), $v);

             if (strpos($v, $sep) !== FALSE) {
               $v = '"' . $v . '"';
             }
             $keyword[$k] = $v ? trim($v) : $empty_symbol;
           }
           $keywords[$element->id] = implode(',', $keyword);
         }
      }

      // Trim the keywords from the end to neaten it up.
      $keywords = array_reverse($keywords);
      $keywords_count = count($keywords);
      foreach ($keywords as $keywords_key => $keywords_value) {
        // If the keyword is empty, and it's not the first keyword.
        $keywords_count--;
        if ($keywords_value == $empty_symbol && $keywords_count) {
          unset($keywords[$keywords_key]);
        }
        else {
          break;
        }
      }
      $keywords = array_reverse($keywords);

      if ($form_state['clicked_button']['#name'] == 'go') {
         $query['go'] = '1';
      }
      $context['sep'] = $sep;
      $context['path'] = $finder->path . '/' . implode('/', $keywords);
      $context['query'] = $query;
      $context['form_state'] = $form_state;
      drupal_alter('finder_form_redirect', $context);
      finder_form_goto($context['sep'], $context['path'], $context['query']);
    }

  }
  elseif (!isset($finder_form_state[$finder->name])) {

    if ($finder->setting('url') == 'disabled' && isset($_GET['finder'])) {  // check the session
      $finder_form_state[$finder->name] = $_SESSION['finder'][$_GET['finder']];
    }
    elseif (!isset($_GET['finder']) &&
            strlen($finder->path) < strlen($_GET['q']) &&
            stripos($_GET['q'], $finder->path) === 0) { // check the URL

      // Get the seperator for element values - this is usually a comma.
      $sep = $finder->setting('url_delimiter');

      // Get the finder arguments.
      $args = str_replace($finder->path . '/', '', $_GET['q']);

      // Handle new lines from input.
      $args = str_replace("***BREAK***", "\n", $args);

      // Forward slashes were encoded as double forward slashes.  We must temporarily replace those here to prevent the explode() affecting this.
      // Rawurlencode() doesn't work but can be fixed using Apache's AllowEncodedSlashes Directive, but how do you tell people to switch that on?
      $args = str_replace('//', '[-finder-forward-slash-]', $args);

      // Double seperators break the $csv_regex below, and I'm not clever enough to fix the regex.
      $args = str_replace($sep . $sep, '[-finder-double-sep-]', $args);

      // Break arguments apart into a string for each element.
      $args = explode('/', $args);

      $form_state['finished'] = TRUE;

      $empty_symbol = !$finder->setting('url_empty') ? $finder->setting('url_empty') : ' ';
      $csv_regex = "/" . $sep . "(?!(?:[^\\\"" . $sep . "]|[^\\\"]" . $sep . "[^\\\"])+\\\")/";

      $args_key = 0;
      foreach ($finder->elements as $key => $element) {
        if ($element->element_handler['type'] == 'form') {
           $keywords = array();
           if (isset($args[$args_key])) {
             $keywords = preg_split($csv_regex, $args[$args_key]);
           }
           $args_key++;
           foreach ($keywords as $k => $v) {
             $v = str_replace('[-finder-double-sep-]', $sep . $sep, $v);
             $v = str_replace('[-finder-forward-slash-]', '/', $v);
             $v = str_replace(urlencode($sep), $sep, trim($v));
             if (trim($v) == trim($empty_symbol)) {
               $v = NULL;
             }
             if (strpos($v, $sep) !== FALSE && $v[0] == '"' && $v[strlen($v) - 1] == '"') {
               $v = substr($v, 1, strlen($v) - 2);
             }
             unset($keywords[$k]);
             if ($v) {
               $keywords[$v] = $v;
             }
           }
           if (count($keywords) === 1) {
             $keywords = current($keywords);
           }
           elseif (!count($keywords)) {
             $keywords = NULL;
           }
           $form_state['values'][$element->id] = $keywords;
         }
      }
      $finder_form_state[$finder->name] = $form_state;
    }

  }
  if (!empty($finder_form_state[$finder->name])) {
    return $finder_form_state[$finder->name];
  }
}

/**
 * Redirect from a finder form.
 *
 * The difference between this and drupal_goto() is that this undoes the
 * encoding of the arguments seperator, as such encoding inteferes with finder.
 *
 * @param $sep
 *   The arguments seperator string.
 * @param $path
 *   A Drupal path or a full URL.
 * @param $query
 *   A query string component, if any.
 * @param $fragment
 *   A destination fragment identifier (named anchor).
 * @param $http_response_code
 *   Valid values for an actual "goto" as per RFC 2616 section 10.3 are:
 *   - 301 Moved Permanently (the recommended value for most redirects)
 *   - 302 Found (default in Drupal and PHP, sometimes used for spamming search
 *         engines)
 *   - 303 See Other
 *   - 304 Not Modified
 *   - 305 Use Proxy
 *   - 307 Temporary Redirect (alternative to "503 Site Down for Maintenance")
 *   Note: Other values are defined by RFC 2616, but are rarely used and poorly
 *   supported.
 * @see drupal_goto()
 */
function finder_form_goto($sep, $path = '', $query = NULL, $fragment = NULL, $http_response_code = 302) {

  if (isset($_REQUEST['destination'])) {
    extract(parse_url(urldecode($_REQUEST['destination'])));
  }
  elseif (isset($_REQUEST['edit']['destination'])) {
    extract(parse_url(urldecode($_REQUEST['edit']['destination'])));
  }

  $url = url($path, array('query' => $query, 'fragment' => $fragment, 'absolute' => TRUE));

  // custom changes - undo separator encoding
  $url = str_replace(urlencode($sep), $sep, $url);

  // Remove newlines from the URL to avoid header injection attacks.
  $url = str_replace(array("\n", "\r"), '', $url);

  // Allow modules to react to the end of the page request before redirecting.
  // We do not want this while running update.php.
  if (!defined('MAINTENANCE_MODE') || MAINTENANCE_MODE != 'update') {
    module_invoke_all('exit', $url);
  }

  // Even though session_write_close() is registered as a shutdown function, we
  // need all session data written to the database before redirecting.
  session_write_close();

  header('Location: ' . $url, TRUE, $http_response_code);

  // The "Location" header sends a redirect status code to the HTTP daemon. In
  // some cases this can be wrong, so we make sure none of the code below the
  // drupal_goto() call gets executed upon redirection.
  exit();
}