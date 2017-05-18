<?php

namespace Drupal\salesforce\Service;

/**
 * Description of ConsumerService
 *
 * @author yousab
 */
use Crunch\Salesforce\AccessTokenGenerator;
use Crunch\Salesforce\Client;
use Crunch\Salesforce\Exceptions\AuthenticationException;
use Crunch\Salesforce\Exceptions\RequestException;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\salesforce\Config\SalesforceConfig;
use GuzzleHttp\Client as Client2;

class ConsumerService {

  /**
   * Configuration Factory
   *
   * @var ConfigFactory
   */
  protected $configFactory;

  /**
   * Entity Manager
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * Direction of Drupal salesforce syncing
   * 0 -> from Drupal to salesforce
   * 1 -> from salesforce to drupal
   * @var int
   */
  static $direction;

  public function __construct(ConfigFactory $config_factory, EntityTypeManagerInterface $entityManager) {
    $this->configFactory = $config_factory;
    $this->entityManager = $entityManager;
    self::$direction = 0;
  }

  /**
   * @return Client PreConfigured Salesforce Client
   */
  public function getClient() {
    $config = new SalesforceConfig($this->configFactory);
    $client = new Client($config, new Client2());
    if ($config->hasAccessToken()) {
      $accessToken = \Drupal::state()->get('salesforce.access_token') ? $this->getAccessTokenFromConfig(\Drupal::state()->get('salesforce.access_token')) : $this->getAccessTokenFromConfig($config->getAccessToken());
      $client->setAccessToken($accessToken);
      if ($accessToken->needsRefresh()) {
        $accessToken = $this->getRefreshToken($client);
        \Drupal::state()->set('salesforce.access_token', $accessToken->toJson());
      }
    }
    return $client;
  }

  public function authorizeClientWithCode(Client $client, $code) {
    global $base_url;
    try {
      $url = Url::fromRoute('salesforce.authorization_endpoint', [], ['https' => TRUE])
          ->toString();
      $redirectUrl = $base_url . $url;
      $requestToken = $client->authorizeConfirm($code, $redirectUrl);
      $tokenGenerator = new AccessTokenGenerator();
      $accessToken = $tokenGenerator->createFromSalesforceResponse($requestToken);
      $client->setAccessToken($accessToken);
      \Drupal::state()->set('salesforce.access_token', $accessToken->toJson());
      return $client;
    }
    catch (AuthenticationException $e) {
      print $e->getMessage();
    }
  }

  public function getLoginUrl() {
    return $this->libClient->getLoginUrl('https://dev.ceres.org/salesforce/oauth_callback');
  }

  /**
   * Storting access token in Session variable by json_encode
   */
  protected function storeAccessToken($accessToken) {
    $this->configFactory->getEditable(SalesforceConfig::CONFIG_ID)
        ->set(SalesforceConfig::CONFIG_ACCESS_TOKEN, $accessToken)
        ->save();
  }

  protected function getAccessTokenFromConfig($configAccessToken) {
    $tokenGenerator = new AccessTokenGenerator();
    return $tokenGenerator->createFromJson($configAccessToken);
  }

  protected function getRefreshToken(Client $client) {
    try {
      $refreshToken = $client->refreshToken();
    }
    catch (RequestException $e) {
      print $e->getMessage();
    }
    return $refreshToken;
  }

  /**
   * Get all salesforce campaign objects
   * @param Client $client
   * @return array campaginObjects
   */
  public function getCampaigns(Client $client) {
    return $client->search('SELECT Name,CreatedDate,LastModifiedDate,Type FROM Campaign WHERE IsActive = true');
  }

  public function getObjects(Client $client, \Drupal\salesforce\Model\SFQuery $query) {
    return $client->search($query->getQuery());
  }

  public function getSObjects(Client $client) {
    return $client->getSObjects();
  }

  /**
   * Get only salesforce campaign object from campaign name
   * @param Client $client
   * @param string $campaignName
   * @return array campaignObject
   */
  public function getCampagin(Client $client, $campaignName) {
    return $client->search("SELECT Id,Name,CreatedDate,LastModifiedDate,Type FROM Campaign WHERE Name = '$campaignName'");
  }

  /**
   * Get salesforce contact objects
   * @param Client $client
   * @return array Contacts
   */
  public function getContacts(Client $client) {
    return $client->search('SELECT Description,Email,FirstName,Id,LastModifiedDate,LastName FROM Contact');
  }

  /**
   * Get salesforce contact object by last name
   * @param Client $client
   * @param string $lname
   * @return array Contact
   */
  public function getContactByLastName(Client $client, $lname) {
    $contact = $client->search("SELECT Id,LastName FROM Contact WHERE LastName = '$lname'");
    return empty($contact) ? FALSE : $contact;
  }

  /**
   * Get salesforce contact object if existed, relating to opportunity creation. Create a new contact object if not existed
   * @param Client $client
   * @param string $lname
   * @return array Contact
   */
  public function getContactHousehold(Client $client, $data) {
    $firstName = $data['FirstName'][0];
    $lastName = $data['LastName'];
    $email = $data['Email'];
    $query = "SELECT Id,HouseholdName__c FROM Contact WHERE Email = '$email' AND LastName = '$lastName' AND FirstName LIKE '$firstName%'";
    return $client->search($query);
  }

  /**
   *
   * @param Client $client
   * @param string $objectName Salesforce object Name
   * @param array $data
   * @return mixed Salesforce Object Id
   */
  public function createObject(Client $client, $objectName, $data) {
    return $client->createRecord($objectName, $data);
  }

  /**
   * Remove object based on provided conditions
   * @param Client $client
   * @param string $objectName Salesforce object Name
   * @param array $conditions
   * @return mixed Salesforce Object Id
   */
  public function removeObject(Client $client, $objectName, $conditions) {
    $query = "SELECT Id FROM $objectName WHERE ";
    foreach ($conditions as $condition) {
      $name = $condition['name'];
      $operation = $condition['operation'];
      $values = explode(',', $condition['value']);
      foreach ($values as $value) {
        $query .= ('True' == $value || 'False' == $value) ? "$name $operation $value AND " :
            "$name $operation '$value' AND ";
      }
    }

    $query = rtrim($query, 'AND ');
    $object = $client->search($query);
    return !empty($object) ? $client->deleteRecord($objectName, $object[0]['Id']) : FALSE;
  }

  /**
   * Update salesforce record
   * @param Client $client
   * @param string $objectName Salesforce object Name
   * @param string $id Salesforce object Id
   * @param array $data
   * @return boolean
   */
  public function updateObject(Client $client, $objectName, $id, $data) {
    try {
      return $client->updateRecord($objectName, $id, $data);
    }
    catch (RequestException $e) {
      watchdog_exception('Salesfroce Lead', $e, $e->getMessage());
    }
  }

  /**
   * Get salesforce object by field name
   * @param Client $client
   * @param string $lname
   * @return array Contact
   */
  public function getObjectByFieldName(Client $client, $objectName, array $field) {
    $fieldName = key($field);
    $fieldValue = $field[$fieldName];
    $query = "SELECT Id,$fieldName";
    if ('Lead' == $objectName) {
      $query .= ",IsConverted,ConvertedContactId";
    }
    $query .= " FROM $objectName WHERE $fieldName = '$fieldValue'";
    $object = $client->search($query);
    return empty($object) ? FALSE : $object;
  }

  /**
   * Assign salesforce to specific campaign (contact,lead)
   * @param Client $client
   * @param array $object @contains name, id
   * @param type $campaignName
   * @return type
   */
  public function assignLeadToCampaign(Client $client, $objectName, $objectd, $campaignName) {
    $campaign = $this->getCampagin($client, $campaignName)[0];
    $data = [
      'CampaignId' => $campaign['Id'],
    ];
    ('Lead' == $objectName) ? $data['LeadId'] = $objectd : $data['ContactId'] = $objectd;
    try {
      if ($this->LeadIsAlreadyAssigned($client, array_slice($data, 1, 1), $data['CampaignId'])) {
        return FALSE;
      }
      return $client->createRecord('CampaignMember', $data);
    }
    catch (RequestException $e) {
      watchdog_exception('Salesfroce Lead', $e, $e->getMessage());
    }
  }

  /**
   * Check if object is already assigned to this campaign
   * @param Client $client
   * @param string $contactId
   * @param string $campaignName
   * @return bool
   */
  public function LeadIsAlreadyAssigned(Client $client, array $field, $campaignId) {
    $fieldKey = key($field);
    $fieldValue = $field[$fieldKey];
    $campaignMember = $client->search("SELECT CampaignId,$fieldKey FROM CampaignMember"
        . " WHERE CampaignId = '$campaignId' AND $fieldKey = '$fieldValue'");
    return empty($campaignMember) ? FALSE : TRUE;
  }

  /**
   * Get network members
   * @param Client $client
   * @param array $conditions
   * @param string $order
   * @param bool $withImages
   * @return mixed
   */
  public function getNetworkMembers(Client $client, array $conditions, $order, $withImages = false) {
    $query = ($withImages) ? 'SELECT Name,Website,Logo__c FROM Account WHERE ' :
        'SELECT Name,Website FROM Account WHERE ';
    foreach ($conditions as $condition) {
      $name = $condition['name'];
      $operation = $condition['operation'];
      $values = explode(',', $condition['values']);
      foreach ($values as $value) {
        $query .= ('True' == $value || 'False' == $value) ? "$name $operation $value OR " :
            "$name $operation '$value' OR ";
      }
    }

    $query = rtrim($query, 'OR ') . " ORDER BY Name $order";
    $networkMembers = $client->search($query);
    return !empty($networkMembers) ? $networkMembers : FALSE;
  }

  /**
   * Get campaign name from its id
   * @param Client $client
   * @param type $campaignId
   * @return mixed
   */
  public function getCampaignById(Client $client, $campaignId) {
    $campaign = $client->search("SELECT Name FROM Campaign WHERE id = '$campaignId'");
    return !empty($campaign) ? $campaign : FALSE;
  }

  /**
   * Update report number of views
   * @param Client $client
   * @param type $campaignId
   * @return mixed
   */
  public function updateReportViews(Client $client, $campaignId) {
    $query = $client->search("SELECT Name,Report_Views__c FROM Campaign WHERE Id = '$campaignId'");
    $reportViews = $query ? $query[0]['Report_Views__c'] : '';
    $views = $reportViews ? $reportViews + 1 : 1;
    return $query ? $client->updateRecord('Campaign', $campaignId, ['Report_Views__c' => $views]) : FALSE;
  }

  /**
   * Get salesforce contact object by id
   * @param Client $client
   * @param string $lname
   * @return array Contact
   */
  public function getContactById(Client $client, $id) {
    return $client->search("SELECT Email, Id, LastModifiedDate, LastName, FirstName FROM Contact "
            . "WHERE Id = '$id'");
  }

  /**
   *
   * @param Client $client
   * @param mixed $data
   * @return string salesforce recode Id
   */
  public function createContact(Client $client, $data) {
    if ($data instanceof EntityInterface) {
      $values = $this->mapContactFields($data);
      $contactId = $client->createRecord('Contact', $values);
    }
    else {
      $contactId = $client->createRecord('Contact', $data);
      $data['salesforceId'] = $contactId;
      $values = $this->mapContactFields($data);
      $this->createDrupalContactEntity('contact', $values);
    }
    return $contactId;
  }

  public function updateContactInSalesforce(Client $client, $data) {
    $values = $this->mapContactFields($data);
    $salesforceId = $data->get('field_contact_salesforce_id')->getString();
    return $client->updateRecord('Contact', $salesforceId, $values);
  }

  public function updateDrupalContactEntity($entityType, $entityId, $data) {
    $node = $this->entityManager->getStorage('node')->load($entityId);
    $values = $this->mapContactFields($data);
    self::$direction = 1;
    foreach ($values as $key => $value) {
      $node->getTypedData()->set($key, $value);
    }
    return $node->save();
  }

  public function getContactUpdatedRecords(Client $client, $lastSyncDate, $endDate) {
    return $client->getUpdatedRecords('Contact', $lastSyncDate, $endDate);
  }

  /**
   * Check if contact is already assigned to this campaign
   * @param Client $client
   * @param string $contactId
   * @param string $campaignName
   * @return bool
   */
  public function contactIsAlreadyAssigned(Client $client, $contactId, $campaignName) {
    $campaign = $this->getCampagin($client, $campaignName)[0];
    $campaignId = $campaign['Id'];
    $campaignMember = $client->search("SELECT CampaignId, ContactId FROM CampaignMember"
        . " WHERE CampaignId = '$campaignId' AND ContactId = '$contactId'");
    return empty($campaignMember) ? FALSE : TRUE;
  }

  public function assignContactToCampaign(Client $client, $contactId, $campaignName) {
    $campaign = $this->getCampagin($client, $campaignName)[0];
    $data = [
      'CampaignId' => $campaign['Id'],
      'ContactId' => $contactId
    ];

    return $client->createRecord('CampaignMember', $data);
  }

  public function createDrupalContactEntity($entity_type, $values) {
    $storage = $this->entityManager->getStorage('node');
    $values['type'] = $entity_type;
    return $storage->create($values)->save();
  }

  /**
   *
   */
  public function cronAddItemstoQueue(Client $client) {
    $lastCronDate = date("Y-m-d\TH:i:s+H:i", \Drupal::state()->get('system.cron_last'));
    $endDate = \Carbon\Carbon::tomorrow();
    $salesforceUpdatedContactsRecords = $client->getUpdatedRecords('Contact', $lastCronDate, $endDate);

    foreach ($salesforceUpdatedContactsRecords as $recordId) {
      $queue_factory = \Drupal::service('queue');
      /** @var QueueInterface $queue */
      $queue = $queue_factory->get('cron_salesforce_pull');
      $item['salesforceId'] = $recordId;
      $item['entityType'] = 'contact';
      $queue->createItem($item);
    }
  }

  /**
   * Build mapping array either for drupal entity creation
   * or salesforce pushing object
   *
   * @param type $data
   * @return array mapping values
   */
  public function mapContactFields($data) {
    $values = [];
    if ($data instanceof EntityInterface) {
      $data = $data->getTypedData();
      $values = [
        'LastName' => $data->get('field_contact_last_name')->getString(),
        'FirstName' => $data->get('field_contact_first_name')->getString(),
        'Email' => !empty($data->get('field_contact_email')->getString()) ? $data->get('field_contact_email')->getString() : '',
      ];
    }
    else {
      $values = [
        'title' => !empty($data['FirstName']) ? $data['FirstName'] . ' ' . $data['LastName'] : $data['LastName'],
        'field_contact_last_name' => $data['LastName'],
        'field_contact_first_name' => !empty($data['FirstName']) ? $data['FirstName'] : '',
        'field_contact_email' => !empty($data['email']) ? $data['email'] : '',
        'field_contact_salesforce_id' => !empty($data['salesforceId']) ? $data['salesforceId'] : $data['Id'],
      ];
    }

    return $values;
  }

  public static function getDirection() {
    return self::$direction;
  }

}
