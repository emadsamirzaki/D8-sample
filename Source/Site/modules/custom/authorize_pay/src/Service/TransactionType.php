<?php

namespace Drupal\authorize_pay\Service;

use Drupal;
use Drupal\authorize_pay\Entity\AuthorizeTransaction;
use Drupal\salesforce\Service\ConsumerService;

/**
 *
 * @author yousab
 */
abstract class TransactionType {

  protected function createSFOpportunity(AuthorizeTransaction $tx) {
    /* @var $sfService ConsumerService */
    $sfService = Drupal::service('salesforce.consumer');
    $client = $sfService->getClient();
    $contact = $this->getOpportunityContact($sfService, $tx);
    $contactHousehold = $contact[0]['HouseholdName__c'];

    $data = [
      'CloseDate' => date('Y-m-d', $tx->getCreatedTime()),
      'Name' => $contactHousehold . date(' Y', $tx->getCreatedTime()) . ' Gift',
      'StageName' => 'Won, Not Thanked',
      'Type' => 'Online Payment',
      'Amount' => $tx->getFieldData('field_tx_amount'),
      'Primary_Contact__c' => $contact[0]['Id'],
      'Online_Transaction_ID__c' => ($tx->hasField('field_tx_transaction_id') && $tx->getFieldData('field_tx_transaction_id')) ?
          $tx->getFieldData('field_tx_transaction_id') : '',
    ];

    if ('monthly_subscription_transaction' == $tx->getType()) {
      $data['Monthly_Gift__c'] = 1;
      $data['Monthly_Gift_Amount__c'] = $tx->getFieldData('field_tx_amount');
      $data['Amount'] = $tx->getFieldData('field_tx_amount') * (13 - date('m'));
      $data['Online_Transaction_ID__c'] = $tx->getFieldData('field_sub_id');
    }

    if ($tx->getFieldData('field_tx_honoree_type') != 'Choose One') {
      $data['Tribute_Gift__c'] = 1;
      $data['Tribute_Gift_Type__c'] = $tx->getFieldData('field_tx_honoree_type');
      $data['Tribute_Name__c'] = $tx->getFieldData('field_tx_honoree') ? $tx->getFieldData('field_tx_honoree') : '';
      $data['Tribute_Gift_Email__c'] = $tx->getFieldData('field_tx_honoree_email') ? $tx->getFieldData('field_tx_honoree_email') : '';
      $data['Tribute_Gift_Address__c'] = $tx->getFieldData('field_tx_honoree_mail') ? $tx->getFieldData('field_tx_honoree_mail') : '';
    }

    $optId = $sfService->createObject($client, 'Opportunity', $data);
    $sfService->createObject($client, 'OpportunityContactRole', [
      'ContactId' => $contact[0]['Id'],
      'OpportunityId' => $optId,
      'IsPrimary' => 1,
      'Role' => 'Individual Donor'
    ]);
    if ('monthly_subscription_transaction' == $tx->getType()) {
      $this->createOpportunityInstallments($sfService, $tx, $optId);
    }
    return $tx->updateTransaction(['field_tx_salesforce_id' => $optId]);
  }

  protected function getOpportunityContact(ConsumerService $sfService, AuthorizeTransaction $tx) {
    $client = $sfService->getClient();
    $data = [
      'FirstName' => $tx->getFieldData('field_tx_first_name'),
      'LastName' => $tx->getFieldData('field_tx_last_name'),
      'Email' => $tx->getFieldData('field_tx_email'),
    ];
    $contact = $sfService->getContactHousehold($client, $data);
    if (empty($contact)) {
      $contactId = $sfService->createObject($client, 'Contact', $data);
      return $sfService->getContactHousehold($client, $data);
    }
    return $contact;
  }

  protected function calculateInstallmentDate($time) {
    $month = date('m', $time) + 1;
    $day = (date('d', $time) > 28) ? 28 : date('d', $time);
    $year = date('Y', $time);
    $date = strtotime("$year-$month-$day");

    return $date;
  }

  protected function createOpportunityInstallments(ConsumerService $sfService, AuthorizeTransaction $tx, $oppId) {
    $client = $sfService->getClient();
    $data = [
      'Opportunity__c' => $oppId,
      'Opp_For_Installment__c' => $oppId,
      'Online_Transaction_ID__c' => ($tx->hasField('field_tx_transaction_id') && $tx->getFieldData('field_tx_transaction_id')) ?
          $tx->getFieldData('field_tx_transaction_id') : '',
      'Amount__c' => $tx->getFieldData('field_tx_amount'),
      'Date__c' => date('Y-m-d', $tx->getCreatedTime()),
      'IsInstallment__c' => 1,
      'Paid__c' => 1,
    ];
    $sfService->createObject($client, 'OppPayment__c', $data);
    for ($i = 1; $i < (13 - date('m')); $i++) {
      $data['Paid__c'] = 0;
      $data['Date__c'] = date('Y-m-d', $this->calculateInstallmentDate(strtotime($data['Date__c'])));
      $sfService->createObject($client, 'OppPayment__c', $data);
    }

    // Remove the first opportunity installment created by default
    $conditions = [
      [
        'name' => 'IsInstallment__c',
        'value' => 'False',
        'operation' => '=',
      ],
      [
        'name' => 'Opportunity__c',
        'value' => $oppId,
        'operation' => '=',
      ]
    ];
    return $sfService->removeObject($client, 'OppPayment__c', $conditions);
  }

  abstract function createTranscationRequest(AuthorizeTransaction $authorizeTransaction);

  abstract function processTransaction($transactionController);

  abstract function handleResponse($response);
}
