<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\Form\AuthorizeConfigAPIForm.
 */

namespace Drupal\authorize_pay\Form;

use Drupal\authorize_pay\Entity\AuthorizeConfigAPI;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class AuthorizeConfigAPIForm.
 *
 * @package Drupal\authorize_pay\Form
 */
class AuthorizeConfigAPIForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /* @var $authorize_config_api AuthorizeConfigAPI */
    $authorize_config_api = $this->entity;

    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $authorize_config_api->label(),
      '#description' => $this->t("Label for the Authorize config api."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $authorize_config_api->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\authorize_pay\Entity\AuthorizeConfigAPI::load',
      ),
      '#disabled' => !$authorize_config_api->isNew(),
    );

    /* You will need additional form elements for your custom properties. */
    $form['apiId'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Authorize.net API Login ID'),
      '#default_value' => $authorize_config_api->getAPIid(),
      '#required' => TRUE
    );

    $form['transactionKey'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Authorize.net Transcation Key'),
      '#default_value' => $authorize_config_api->getTranscationKey(),
      '#required' => TRUE
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $authorize_config_api = $this->entity;
    $status = $authorize_config_api->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Authorize config api.', [
              '%label' => $authorize_config_api->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Authorize config api.', [
              '%label' => $authorize_config_api->label(),
        ]));
    }
    $form_state->setRedirectUrl($authorize_config_api->urlInfo('collection'));
  }

}
