<?php

namespace Drupal\salesforce\Library;

/**
 * Description of SfClient
 * This is an extendable class for salesforce library Client @var Client
 * @author yousab
 */

use Carbon\Carbon;
use Crunch\Salesforce\AccessToken;
use Crunch\Salesforce\Client;
use Crunch\Salesforce\ClientConfigInterface;
use Crunch\Salesforce\Exceptions\AuthenticationException;
use GuzzleHttp\Client as Client2;

class SfClient extends Client {

  /** @var string * */
  private $_baseUrl;

  /** @var AccessToken * */
  private $_accessToken;

  public function __construct(ClientConfigInterface $clientConfig, Client2 $guzzleClient) {
    parent::__construct($clientConfig, $guzzleClient);
  }

  public function getUpdatedRecords($objectType, $lastSyncDate) {
    $startDate = urlencode(date("Y-m-d\TH:i:s+H:i", strtotime($lastSyncDate)));
    $endDate = urlencode(date("Y-m-d\TH:i:s+H:i", strtotime(Carbon::tomorrow())));
//    print("startDate: ".$startDate.'#####endDate: '.$endDate);die;
    $url = $this->_baseUrl . '/services/data/v37.0/sobjects/' . $objectType . '/updated/?start=' . $startDate . '&end=' . $endDate;
    $response = parent::makeRequest('get', $url, ['headers' => ['Authorization' => $this->_getAuthHeader()]]);

    $data = json_decode($response->getBody(), true);
    $results = $data['ids'];

    return $results;
  }

  public function setAccessToken(AccessToken $accessToken) {
    $this->_baseUrl = $accessToken->getApiUrl();
    $this->_accessToken = $accessToken;
    parent::setAccessToken($accessToken);
  }

  private function _getAuthHeader() {
    if ($this->_accessToken === null) {
      throw new AuthenticationException(0, "Access token not set");
    }

    return 'Bearer ' . $this->_accessToken->getAccessToken();
  }

}
