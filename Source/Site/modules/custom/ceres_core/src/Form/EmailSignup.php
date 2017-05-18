<?php

/**
 * @file
 * Contains \Drupal\ceres_core\Form\EmailSignup.
 */

namespace Drupal\ceres_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\salesforce\Service\ConsumerService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmailSignup.
 *
 * @package Drupal\ceres_core\Form
 */
class EmailSignup extends FormBase {

  /**
   * Salesforce Consumer Service
   *
   * @var  ConsumerService
   */
  protected $sfService;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'email_signup';
  }

  /**
   *
   * @param ConsumerService Salesforce Service
   */
  public function __construct(ConsumerService $sfService) {
    $this->sfService = $sfService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('salesforce.consumer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $campaign_type = []) {
    $form['#attributes']['class'][] = 'sign-up-form';
    $form['#attributes']['class'][] = 'form-inline';

    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['form-fields-slide-container']],
    ];

    $form['container']['last_name'] = [
      '#type' => 'textfield',
      '#maxlength' => 150,
      '#required' => true,
      '#prefix' => '<div class"form-group">',
      '#suffix' => '</div>',
      '#attributes' => ['id' => ['sign-name']],
      '#attributes' => ['class' => ['form-control']],
      '#attributes' => ['placeholder' => ['Last Name']],
    ];

    $form['container']['email'] = [
      '#type' => 'email',
      '#required' => true,
      '#prefix' => '<div class"form-group">',
      '#suffix' => '</div>',
      '#attributes' => ['id' => ['sign-mail']],
      '#attributes' => ['class' => ['form-control']],
      '#attributes' => ['placeholder' => ['Email']],
    ];

    $form['campaign_type'] = [
      '#type' => 'value',
      '#value' => $campaign_type,
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Subscribe'),
      '#attributes' => ['class' => ['form-control', 'read-more-btn', 'sign-slide-action', 'subscribr-btn', 'text-uppercase']],
      '#myVar' => true,
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
    parent::submitForm($form, $form_state);
  }

}
