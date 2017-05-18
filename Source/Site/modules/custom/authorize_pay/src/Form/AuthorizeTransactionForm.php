<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\Form\AuthorizeTransactionForm.
 */

namespace Drupal\authorize_pay\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Authorize transaction edit forms.
 *
 * @ingroup authorize_pay
 */
class AuthorizeTransactionForm extends ContentEntityForm {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\authorize_pay\Entity\AuthorizeTransaction */
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Authorize transaction.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Authorize transaction.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.authorize_transaction.canonical', ['authorize_transaction' => $entity->id()]);
  }

}
