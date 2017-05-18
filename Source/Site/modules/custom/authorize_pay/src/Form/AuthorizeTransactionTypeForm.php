<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\Form\AuthorizeTransactionTypeForm.
 */

namespace Drupal\authorize_pay\Form;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AuthorizeTransactionTypeForm.
 *
 * @package Drupal\authorize_pay\Form
 */
class AuthorizeTransactionTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $authorize_transaction_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $authorize_transaction_type->label(),
      '#description' => $this->t("Label for the Authorize transaction type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $authorize_transaction_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\authorize_pay\Entity\AuthorizeTransactionType::load',
      ),
      '#disabled' => !$authorize_transaction_type->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $authorize_transaction_type = $this->entity;
    $status = $authorize_transaction_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Authorize transaction type.', [
          '%label' => $authorize_transaction_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Authorize transaction type.', [
          '%label' => $authorize_transaction_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($authorize_transaction_type->urlInfo('collection'));
  }

}
