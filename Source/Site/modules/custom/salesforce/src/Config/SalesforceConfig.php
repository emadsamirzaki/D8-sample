<?php


namespace Drupal\salesforce\Config;

use Crunch\Salesforce\ClientConfigInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Description of SalesforceConfig
 *
 * @author yousab
 */
class SalesforceConfig implements ClientConfigInterface{

  const CONFIG_ID = 'salesforce.config.settings';
  const CONFIG_ACCESS_TOKEN = 'salesforce.config.access_token';

  private $clienId;

  private $clientSecret;

  private $loginUrl;

  private $apiUrl;

  /** @var ConfigFactoryInterface */
  protected $configFactory;

  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
    $settings = $configFactory->get(self::CONFIG_ID);
    $this->loginUrl = $settings->get('salesforce.config.login_url');
    $this->clienId = $settings->get('salesforce.config.client_id');
    $this->clientSecret = $settings->get('salesforce.config.client_secret');
    $this->apiUrl = $settings->get('salesforce.config.api_url');
  }

  public function getClientSecret() {
    return $this->clientSecret;
  }

  public function getLoginUrl() {
    return $this->loginUrl;
  }

  public function getClientId() {
    return $this->clienId;
  }

  public function getAccessToken() {
    return $this->configFactory->get(self::CONFIG_ID)->get(self::CONFIG_ACCESS_TOKEN);
  }

  public function hasAccessToken() {
    return !empty($this->getAccessToken());
  }

  public function getApiUrl() {
    return $this->apiUrl;
  }

}
