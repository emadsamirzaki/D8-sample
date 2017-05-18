<?php

namespace Drupal\authorize_pay\Service;

use Drupal\authorize_pay\Entity\AuthorizeTransaction;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use net\authorize\api\constants\ANetEnvironment;
use \Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Description of SubscribedTransactionService
 *
 * @author yousab
 */
class SubscribedTransactionService extends TransactionType {

  static $transactionEntityId;

  /**
   * AuthorizeNet Consumer Service
   *
   * @var  ConsumerService
   */
  protected $authorizeService;

  /**
   * Entity Storage
   */
  protected $entityStorage;

  public function __construct(AuthorizeConsumerService $authorizeService, EntityTypeManagerInterface $entityManager) {
    $this->authorizeService = $authorizeService;
    $this->entityStorage = $entityManager->getStorage('authorize_transaction');
  }

  /**
   * Set credit card data
   * @param AuthorizeTransaction $params
   * @return PaymentType
   */
  public function setCreditCard(AuthorizeTransaction $params) {
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber($params->getFieldData('field_tx_creditcardnum'));
    $creditCard->setExpirationDate($params->getFieldData('field_tx_creditcardexpdate'));

    $payment = new AnetAPI\PaymentType();
    $payment->setCreditCard($creditCard);

    return $payment;
  }

  /**
   * Set customer address information
   * @param AuhtorizeTransaction $params
   * @return CustomerAddressType
   */
  public function setCustomerAddress(AuthorizeTransaction $params) {
    $customer = new AnetAPI\NameAndAddressType();
    $customer->setFirstName($params->getFieldData('field_tx_first_name'));
    $customer->setLastName($params->getFieldData('field_tx_last_name'));
    $customer->setCountry($params->getFieldData('field_tx_country'));
    $customer->setCity($params->getFieldData('field_tx_city'));
    $customer->setState($params->getFieldData('field_tx_state'));
    $customer->setAddress($params->getFieldData('field_tx_address'));
    $customer->setZip($params->getFieldData('field_tx_zip'));

    return $customer;
  }

  /**
   * Set customer email
   * @param AuthorizeParams $params
   * @return CustomerDataType
   */
  public function setCustomerType(AuthorizeTransaction $params) {
    $customer = new AnetAPI\CustomerType();
    $customer->setEmail($params->getFieldData('field_tx_email'));
    $customer->setPhoneNumber($params->getFieldData('field_tx_phone'));

    return $customer;
  }

  public function setPaymentSchedule() {
    $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
    $interval->setLength('1');
    $interval->setUnit('months');

    $paymentSchedule = new AnetAPI\PaymentScheduleType();
    $paymentSchedule->setInterval($interval);
    $paymentSchedule->setStartDate(new \DateTime());
    $paymentSchedule->setTotalOccurrences('9999');

    return $paymentSchedule;
  }

  /**
   * Set order Description
   * @return OrderType
   */
  public function setOrderTypeInfo() {
    $orderType = new AnetAPI\OrderType();
    $orderType->setDescription('Monthly Donation');

    return $orderType;
  }

  public function createTranscationRequest(AuthorizeTransaction $subscriptionEntity) {
    $params = clone $subscriptionEntity;
    self::$transactionEntityId = $subscriptionEntity->storeTransaction();

    $subscription = new AnetAPI\ARBSubscriptionType();
    $subscription->setName('Monthly Donation Subscription');
    $subscription->setPaymentSchedule($this->setPaymentSchedule());
    $subscription->setAmount($params->getFieldData('field_tx_amount'));
    $subscription->setPayment($this->setCreditCard($params));
    $subscription->setBillTo($this->setCustomerAddress($params));
    $subscription->setOrder($this->setOrderTypeInfo());
    $subscription->setCustomer($this->setCustomerType($params));

    $request = new AnetAPI\ARBCreateSubscriptionRequest();
    $request->setmerchantAuthentication($this->authorizeService->getMerchantAuthentication());
    $request->setSubscription($subscription);
    $subscriptionController = new AnetController\ARBCreateSubscriptionController($request);

    return $subscriptionController;
  }

  /**
   * Process transaction with Authorize.net API
   * @param AnetController\ARBCreateSubscriptionController $transactionController
   * @return TransactionResponseType
   */
  public function processTransaction($transactionController) {
    $env = (strpos($this->authorizeService->getEnabledConfigEntity(), 'sandbox') !== false) ? ANetEnvironment::SANDBOX :
        ANetEnvironment::PRODUCTION;
    $response = $transactionController->executeWithApiResponse($env);
//    $transId = $response->getTransactionResponse()->getTransId();
    return $response;
  }

  public function handleResponse($response) {
    global $base_url;

    if (!empty($response)) {

      $entity = $this->entityStorage->load(self::$transactionEntityId);

      if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
        $subscriptionId = $response->getSubscriptionId();
        $params = ['field_sub_id' => $subscriptionId];
        $responseParams = $this->getSubscriptionResponseParams($response, $params);
        $entity->updateTransaction($responseParams);
        $this->createSFOpportunity($entity);
        drupal_set_message('Your information has been successfully passed to our payment processor.');
        $url = $base_url . '/donate/thank-you';
        $res = new RedirectResponse($url);
        return $res->send();
      }
      else {
        $responseParams = $this->getSubscriptionResponseParams($response);
        $entity->updateTransaction($responseParams);
        return drupal_set_message($response->getMessages()->getMessage()[0]->getText(), 'error');
      }
    }
    else {
      return drupal_set_message('Your credit card hasn\'t been charged. Please try donation again', 'error');
    }
  }

  /**
   * Get a transaction details response
   *
   * @return TransactionDetailsType
   */
  public function getSubscriptionDetailsResponse($subscriptionId) {
    $request = new AnetAPI\ARBGetSubscriptionRequest();
    $request->setMerchantAuthentication($this->authorizeService->getMerchantAuthentication());
    $request->setSubscriptionId($subscriptionId);

    $subscriptionController = new AnetController\ARBGetSubscriptionController($request);
    $env = (strpos($this->authorizeService->getEnabledConfigEntity(), 'sandbox') !== false) ? ANetEnvironment::SANDBOX :
        ANetEnvironment::PRODUCTION;
    $response = $subscriptionController->executeWithApiResponse($env);

    return $response;
  }

  /**
   * Map authorize.net transaction response to authorizeParams array
   * @return array AuthorizeParams
   */
  public function getSubscriptionResponseParams($response, array $params = []) {
    $authorizeParams = [];

    if ($response->getMessages()->getResultCode() == "Ok") {
      $subscription = $this->getSubscriptionDetailsResponse($params['field_sub_id'])->getSubscription();
      $creditCardData = $subscription->getProfile()->getPaymentProfile()->getPayment()->getCreditCard();
      $authorizeParams['field_tx_creditcardnum'] = $creditCardData->getCardNumber();
      $authorizeParams['field_tx_creditcardexpdate'] = $creditCardData->getExpirationDate();

      $authorizeParams['field_sub_customer_payment_id'] = $subscription->getProfile()
              ->getPaymentProfile()->getCustomerPaymentProfileId();
      $authorizeParams['field_sub_customer_profile_id'] = $subscription->getProfile()
              ->getPaymentProfile()->getCustomerProfileId();


      $authorizeParams['field_sub_status'] = $subscription->getStatus();
      $authorizeParams['field_tx_response_code'] = $response->getMessages()->getResultCode();
      $authorizeParams['field_tx_response_desc'] = $response->getMessages()->getMessage()[0]->getText();
    }
    else {
      $authorizeParams['field_tx_creditcardnum'] = '';
      $authorizeParams['field_tx_creditcardexpdate'] = '';
      $authorizeParams['field_tx_response_code'] = $response->getMessages()->getResultCode();
      $authorizeParams['field_tx_response_desc'] = $response->getMessages()->getMessage()[0]->getText();
    }

    foreach ($params as $key => $value) {
      $authorizeParams[$key] = $value;
    }

    return $authorizeParams;
  }

}
