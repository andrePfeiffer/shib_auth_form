<?php

namespace Drupal\shib_auth_form\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'ShibAuth' block.
 *
 * @Block(
 *  id = "shib_auth_form_block",
 *  admin_label = @Translation("Shibboleth WAYF block"),
 * )
 */
class ShibAuthBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\shib_auth_form\Form\ShibAuthForm');
    return $form;
  }

}
