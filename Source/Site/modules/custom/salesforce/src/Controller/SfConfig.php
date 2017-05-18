<?php

/**
 * @file
 * Contains \Drupal\salesforce\Controller\SfConfig.
 */

namespace Drupal\salesforce\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\salesforce\Service\ConsumerService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SfConfig.
 *
 * @package Drupal\salesforce\Controller
 */
class SfConfig extends ConfigFormBase {

  protected $sf_config;

  /**
   * Salesforce Consumer Service
   *
   * @var  ConsumerService
   */
  protected $sfService;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param ConfigFactoryInterface*   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ConsumerService $sfService) {
    $this->setConfigFactory($config_factory);
    $this->sf_config = $this->config('salesforce.config.settings');
    $this->sfService = $sfService;
  }

  protected function getEditableConfigNames() {
    return array('salesforce.config.settings');
  }

  public function getFormId() {
    return 'salesforce_config_settings';
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('config.factory'), $container->get('salesforce.consumer')
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
    $config = $this->config('salesforce.config.settings');
    $form['sf_login_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Login URL'),
      '#default_value' => $config->get('salesforce.config.login_url')
    );

    $form['sf_client_id'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client Id'),
      '#default_value' => $config->get('salesforce.config.client_id')
    );

    $form['sf_client_secret'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('salesforce.config.client_secret')
    );

    if (!empty($config->get('salesforce.config.access_token'))) {
      $form['sf_api'] = array(
        '#type' => 'select',
        '#title' => $this->t('Select API Version'),
        '#options' => $this->getSalesforceAPIs(),
        '#default_value' => $config->get('salesforce.config.api_url')
      );
    }

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Authorize'),
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
    global $base_url;
    $config = $this->config('salesforce.config.settings');
    $config->set('salesforce.config.login_url', $form_state->getValue('sf_login_url'));
    $config->set('salesforce.config.client_id', $form_state->getValue('sf_client_id'));
    $config->set('salesforce.config.client_secret', $form_state->getValue('sf_client_secret'));
    $config->set('salesforce.config.api_url', $form_state->getValue('sf_api'));
    $config->save();

    $client = $this->sfService->getClient();
    $url = Url::fromRoute('salesforce.authorization_endpoint', [], ['https' => TRUE])
        ->toString();
    $redirect_url = $client->getLoginUrl($base_url . $url);

    $form_state->setResponse(new TrustedRedirectResponse($redirect_url));
    return drupal_set_message($this->t('The configuration parameters have been saved.'));
  }

  public function getSalesforceAPIs() {
    $client = $this->sfService->getClient();
    $apisList = $client->getAPIs();
    $apis = [];
    foreach ($apisList as $api) {
      $apiVersion = $api['version'];
      $apis[$api['url']] = $api['label'] . " (V.$apiVersion)";
    }

    return $apis;
  }

  /**
   * Retrieve client id from configuration object
   * @return string
   */
  private function getClientId() {
    return $this->sf_config->get('salesforce.config.client_id');
  }

  /**
   * Retrieve client secret from configuration object
   * @return string
   */
  private function getClientSecret() {
    return $this->sf_config->get('salesforce.config.client_secret');
  }

  /**
   * Retrieve login_url from configuration object
   * @return string
   */
  private function getLoginUrl() {
    return $this->sf_config->get('salesforce.config.login_url');
  }

}
