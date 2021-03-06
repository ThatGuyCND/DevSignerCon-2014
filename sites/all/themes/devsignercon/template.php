<?php

/**
 * @file
 * Template overrides as well as (pre-)process and alter hooks for the
 * DevSignerCon theme.
 */

/**
 * Implements hook_preprocess_view()
 */
function devsignercon_preprocess_views_view_list(&$vars) {
  if (isset($vars['view']->name)) {
    $function = __FUNCTION__ . '__' . $vars['view']->name . '__' . $vars['view']->current_display;
   
    if (function_exists($function)) {
     $function($vars);
    }
  }
}

function devsignercon_preprocess_views_view_fields(&$vars) {
  if (isset($vars['view']->name)) {
    $function = __FUNCTION__ . '__' . $vars['view']->name . '__' . $vars['view']->current_display;
   
    if (function_exists($function)) {
     $function($vars);
    }
  }
}

/**
 * Override or insert variables into the node templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("node" in this case.)
 *
 * Implements hook_preprocess_node().
 */
function devsignercon_preprocess_node(&$vars) {
  $view_mode = $vars['view_mode'];
  $type = $vars['type'];
  $nid = $vars['nid'];
  
  // Create view_mode tpls
  if( !empty($view_mode) ) {
    $vars['theme_hook_suggestions'][] = 'node__' . $type . '__' . $view_mode;
    $vars['theme_hook_suggestions'][] = 'node__' . $nid . '__' . $view_mode;
    $vars['classes_array'][] = 'node--' . $type . '__' . $view_mode;
  }
}

/**
 * Implements hook_preprocess_user_profile()
 */
function devsignercon_preprocess_user_profile(&$vars) {
  $account = $vars['elements']['#account'];
  $view_mode = $vars['elements']['#view_mode'];

  // Helpful $user_profile variable for templates.
  foreach (element_children($vars['elements']) as $key) {
    $vars['user_profile'][$key] = $vars['elements'][$key];
  }

  // Preprocess fields.
  field_attach_preprocess('user', $account, $vars['elements'], $vars);
  
  // Create templates for each view_mode
  if ( !empty($view_mode) ) {
    $vars['theme_hook_suggestions'][] = 'user__' . $view_mode;
  }
}

/**
 * Implements theme_menu_tree__menu_id()
 *
 * @funcSet:
 *   Return HTML for Primary Menu and Link wrappers [ ul & li ].
 *
 * @func:
 * - Main UL
 *
 */
function devsignercon_menu_tree__main_menu($vars) {
  return '<nav id="main-menu" role="navigation" tabindex="-1"><input type="checkbox" id="toggle-button"><label for="toggle-button" onclick></label><ul class="menu--main-menu menu links">' . $vars['tree'] . '</ul></nav>';
}

/** @func:
 * - Main LI
 */
function devsignercon_menu_link__main_menu(array $vars) {
  $element = $vars['element'];
  $sub_menu = '';
  $path = current_path();

  if ($element['#below']) {
    foreach ($element['#below'] as $key => $val) {
      if (is_numeric($key)) {
        $element['#below'][$key]['#theme'] = 'menu_link__main_menu_inner'; // Second level LI
        if ($val['#href'] == $path) {
          $element['#localized_options']['attributes']['class'][] = 'active-trail';
          $element['#attributes']['class'][] = 'active-trail';
        }
      }
    }
    $element['#below']['#theme_wrappers'][0] = 'menu_tree__main_menu_inner'; // Second level UL
    $sub_menu = drupal_render($element['#below']);
    $element['#attributes']['class'][] = 'dropdown';
  }
  $element['#attributes']['class'][] = 'menu-item';
  $element['#attributes']['class'][] = 'level-' . $element['#original_link']['depth'];

  if ($element['#below']) {
    $element['#localized_options']['attributes']['data-toggle'] = 'dropdown';
    $element['#localized_options']['attributes']['aria-haspopup'] ='true';
    $output = '<input type="checkbox" />' . l($element['#title'] . '<i class="icon theme-icon-arrow-down"></i>', $element['#href'], array('attributes' => $element['#localized_options']['attributes'], 'html' => TRUE));
  } else {
    $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  }

  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . '</li>';
}

/** @func:
 * - Inner UL
 */
function devsignercon_menu_tree__main_menu_inner($vars) {
    return '<ul class="menu--main-menu_sub dropdown-menu menu">' . $vars['tree'] . '</ul>';
}

/** @func:
 * - Inner LI
 */
function devsignercon_menu_link__main_menu_inner($vars) {
  $element = $vars['element'];
  $sub_menu = '';
  if ($element['#below']) {
    $sub_menu = drupal_render($element['#below']);
  }
  $element['#attributes']['class'][] = 'level-' . $element['#original_link']['depth'];
  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . '</li>';
}

/**
 * Implements hook_form_FORM_ID_alter()
 */
function devsignercon_form_session_node_form_alter(&$form, &$form_state, $form_id) {
  $speakers = $form['field_speakers'][LANGUAGE_NONE];
  $form['title']['#size'] = 34;
/*
  foreach ($speakers as $key) {
    $form['field_speakers'][LANGUAGE_NONE][$key]['target_id']['#size'] = 34;
  }
*/
  // Add AJAX Processing to session node add form
  $form['actions']['submit']['#ajax'] = array(
                                          'callback' => 'node_form_submit',
                                        );
  dsm($form);
}
