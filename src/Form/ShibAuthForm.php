<?php

namespace Drupal\shib_auth_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Class DefaultForm.
 */
class ShibAuthForm extends FormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shib_auth_form_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#theme'] = 'shib_auth_form_form';

    $institution_list = array();
    $institutions = \Drupal::state()->get('shib_auth_form_metadata_institutions');;
    foreach ($institutions as $institution) {
      if (isset($institution['@attributes']['entityID']) && isset($institution['Organization'])) {
        $entity_id = $institution['@attributes']['entityID'];
        $organization_name = $institution['Organization']['OrganizationDisplayName'];
        $institution_list[$entity_id] = $organization_name;
      }
    }
    asort($institution_list);
    $default_institution = !empty(\Drupal::state()->get('shib_auth_form_metadata_default_institution')) ? \Drupal::state()->get('shib_auth_form_metadata_default_institution') : FALSE;


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
    $institution = $form_state->getValue('institution');

    $remember = $form_state->getValue('remember');
    if ($remember) {
      \Drupal::state()->set('shib_auth_form_metadata_default_institution', $institution);
    }
    else {
      \Drupal::state()->set('shib_auth_form_metadata_default_institution', null);
    }
    $response = new RedirectResponse($this->createLink($institution));
    $response->send();
    return;
  }

  /**
   * {@inheritdoc}
   */
  private function createLink(string $institution){
    $config = \Drupal::config('shib_auth.shibbolethsettings');
    $url = $config->get('shibboleth_login_handler_url');
    $force_https = $config->get('force_https_on_login');

    $config = \Drupal::config('shib_auth.advancedsettings');
    $redirect = $config->get('url_redirect_login');

    if ($redirect) {
      $redirect = Url::fromUserInput($redirect)->toString();
    }
    else {
      // Not set, use current page.
      $redirect = Url::fromRoute('<current>')->toString();
    }
    if ($force_https) {
      $redirect = preg_replace('~^http://~', 'https://', $redirect);
    }

    $options = [
      'absolute' => TRUE,
      'query' => [
        'destination' => $redirect,
      ],
    ];

    if ($force_https) {
      $options['https'] = TRUE;
    }

    // This is the callback to process the Shib login with the destination for
    // the redirect when done.
    $shib_login_url = Url::fromRoute('shib_auth.login_controller_login', [], $options)->toString();

    $options = [
      'query' => [
        'target' => $shib_login_url,
        'entityID' => $institution,
      ],
    ];

    if ($force_https) {
      $options['https'] = TRUE;
      if (empty($_SERVER['HTTPS'])) {
        $options['absolute'] = TRUE;
      }
    }
    $options['absolute'] = TRUE;
      if (parse_url($url, PHP_URL_HOST)) {
      $url = Url::fromUri($url, $options);
    }
    else {
      $url = Url::fromUserInput($url, $options);
    }
    return $url->toString();
  }

}
