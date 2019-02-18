<?php

namespace Drupal\shib_auth_form\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class MetadataSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'shib_auth_form_metadata\'',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metadata_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

//    $config = $this->config('shib_auth_form.metadata');

    $form['shib_auth_form_metadata'] = array(
      '#type' => 'fieldset',
      '#title' => t('Metadata'),
      '#weight' => 0,
      '#collapsible' => FALSE,
    );

    $form['shib_auth_form_metadata']['shib_auth_form_metadata_url'] = array(
      '#type' => 'textfield',
      '#title' => t('Metadata URL'),
      '#default_value' => \Drupal::state()->get('shib_auth_form_metadata_url'),
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
      '#default_value' => (\Drupal::state()->get('shib_auth_form_metadata_elements') ? \Drupal::state()->get('shib_auth_form_metadata_elements') : ''),
      '#required' => TRUE,
    );

    $institutions = \Drupal::state()->get('shib_auth_form_metadata_institutions');
    if(empty($institutions)){
      $institutions_info = t('No institution was found for the given XML.');
    }
    $count_institutions = count(\Drupal::state()->get('shib_auth_form_metadata_institutions'));
    $institutions_info = ($count_institutions > 0) ? t('@count institutions found for the given XML.', array('@count' => $count_institutions)) : t('No institution was found for the given XML.');

    $form['shib_auth_form_metadata']['institutions']['info'] = array(
      '#type' => 'item',
      '#markup' => $institutions_info,
    );

    $form['shib_auth_form_metadata']['institutions']['scan'] = array(
      '#type' => 'submit',
      '#value' => t('Rescan metadata'),
    );

    return parent::buildForm($form, $form_state);

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
    \Drupal::state()->set('shib_auth_form_metadata_elements', $form_state->getValue('shib_auth_form_metadata_elements'));
    \Drupal::state()->set('shib_auth_form_metadata_url', $form_state->getValue('shib_auth_form_metadata_url'));
    $this->metadataInstitutions();
  }

  /**
   * {@inheritdoc}
   */
  private function metadataInstitutions() {
    try {

      $institutions = \Drupal::state()->get('shib_auth_form_metadata_url');
      $elements = \Drupal::state()->get('shib_auth_form_metadata_elements');

      if (empty($institutions) || empty($elements)) {
        throw new \Exception();
      }

      $shib_auth_form_metadata_url = $institutions;
      $shib_auth_form_metadata = json_decode(json_encode((array) simplexml_load_file($shib_auth_form_metadata_url)), TRUE);

      $institutions = $shib_auth_form_metadata['EntityDescriptor'];
      if ($elements != 'all') {
        foreach ($institutions as $key => $institution) {
          if (isset($institution[$elements])) {
            $filtered_institutions[] = $institution;
          }
        }

        $institutions = $filtered_institutions;
      }
      \Drupal::state()->set('shib_auth_form_metadata_institutions', $institutions);
      return $institutions;
    }
    catch (\Exception $e) {
      throw new \Exception('Cannot load metadata settings.');
    }
  }
}
