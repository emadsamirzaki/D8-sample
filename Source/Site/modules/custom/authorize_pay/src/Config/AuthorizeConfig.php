<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\Controller\AuhtorizeConfigsss.
 */

namespace Drupal\authorize_pay\Config;

use Drupal\authorize_pay\AuthorizeParams;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SfConfig.
 *
 * @package Drupal\authorize_pay\Controller
 */
class AuthorizeConfig extends ConfigFormBase {

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->setConfigFactory($config_factory);
  }

  protected function getEditableConfigNames() {
    return array('authorize_pay.config.settings');
  }

  public function getFormId() {
    return 'authorize_pay_config_settings';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('config.factory')
    );
  }

  /**
   * struct config form
   *
   * @param array $form
   * @param array $form_state
   * @return array $form
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('authorize_pay.config.settings');

    $form['authorize_api'] = array(
      '#type' => 'select',
      '#options' => $this->getAuthorizeConfigEntities(),
      '#title' => $this->t('Authorize.net API'),
      '#default_value' => $config->get('authorize_pay.config.authorize_api')
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   *
   * @param array $form
   * @param FormStateInterface $form_state
   * @return string
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('authorize_pay.config.settings');
    $config->set('authorize_pay.config.authorize_api', $form_state->getValue('authorize_api'));
    $config->save();

    return drupal_set_message($this->t('The configuration parameters have been saved.'));
  }

  protected function getAuthorizeConfigEntities() {
    $entities = $this->configFactory()->listAll('authorize_pay.authorize_config_api');
    $configs = [];
    foreach ($entities as $entity) {
      $configs[$entity] = $this->config($entity)->get('label');
    }
    return $configs;
  }

}
