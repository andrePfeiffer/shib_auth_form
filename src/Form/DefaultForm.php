<?php

namespace Drupal\shib_auth_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DefaultForm.
 */
class DefaultForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'default_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $institution_list = array();
    $institutions = \Drupal::state()->get('shib_auth_form_metadata_institutions');;
    foreach ($institutions as $institution) {
      if (isset($institution['@attributes']['entityID']) && isset($institution['Organization'])) {
        $entity_id = $institution['@attributes']['entityID'];
        $organization_name = $institution['Organization']['OrganizationDisplayName'];
        $organization_url = $institution['Organization']['OrganizationURL'];

        $institution_list[$entity_id] = $organization_name;
      }
    }
    asort($institution_list);
    $default_institution = isset($_COOKIE['Drupal_visitor_shib_auth_form_default_institution']) ? $_COOKIE['Drupal_visitor_shib_auth_form_default_institution'] : FALSE;

    $default_institution = FALSE;


    $form['institution'] = [
      '#type' => 'select',
      '#title' => $this->t('Select an institution'),
      '#options' => $institution_list,
      '#default_value' => $default_institution,
    ];
    $form['remember'] = [
      '#type' =>  'checkbox',
      '#title' => t('Remember your choice'),
      '#default_value' => ($default_institution ? TRUE : FALSE),
    ];
      $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    arrray('ola');
  }

}
