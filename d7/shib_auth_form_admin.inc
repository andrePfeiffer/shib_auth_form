<?php

/**
 * Form constructor for the shib_auth_form admin page.
 */
function shib_auth_form_admin($form, &$form_state) {
  $form = array();

  $form['shib_auth_form_metadata'] = array(
    '#type' => 'fieldset',
    '#title' => t('Metadata'),
    '#weight' => 0,
    '#collapsible' => FALSE,
  );

  $form['shib_auth_form_metadata']['shib_auth_form_metadata_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Metadata URL'),
    '#default_value' => variable_get('shib_auth_form_metadata_url'),
    '#required' => TRUE,
  );

  $form['shib_auth_form_metadata']['shib_auth_form_metadata_elements'] = array(
    '#type' => 'radios',
    '#title' => t("Select which elements you want to list in the form"),
    '#options' => array(
      'IDPSSODescriptor' => 'Identity Provider (IdP)',
      'SPSSODescriptor' => 'Service Provider (SP)',
      'all' => 'All (SP and IdP)',
    ),
    '#default_value' => (variable_get('shib_auth_form_metadata_elements') ? variable_get('shib_auth_form_metadata_elements') : ''),
    '#required' => TRUE,
  );

  if (_shib_auth_form_metadata_check()) {
    $form['shib_auth_form_metadata']['institutions'] = array(
      '#type' => 'fieldset',
      '#title' => t('Institutions'),
      '#weight' => 1,
      '#collapsible' => FALSE,
    );

    $count_institutions = count(_shib_auth_form_metadata_institutions());
    $institutions_info = ($count_institutions > 0) ? format_plural($count_institutions, t('1 institution found for the given XML.'), t('@count institutions found for the given XML.')) : t('No institution was found for the given XML.');

    $form['shib_auth_form_metadata']['institutions']['info'] = array(
      '#type' => 'item',
      '#markup' => $institutions_info,
    );

    $form['shib_auth_form_metadata']['institutions']['scan'] = array(
      '#type' => 'submit',
      '#value' => t('Rescan metadata'),
    );
  }

  // Triggers the action to "Rescan institutions".
  $form['#submit'] = array('shib_auth_form_rescan_institutions');

  return system_settings_form($form);
}

/**
 * Triggers to "Rescan institutions".
 */
function shib_auth_form_rescan_institutions($form, &$form_state) {
  _shib_auth_form_metadata_institutions(TRUE, $form_state);

  drupal_set_message(t('The list of institutions has been updated.'), 'status');
}
