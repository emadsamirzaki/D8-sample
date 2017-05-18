<?php

namespace Drupal\authorize_pay\Service;

use Drupal\authorize_pay\Entity\AuthorizeTransaction;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use \Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Description of RegularTransactionService
 *
 * @author yousab
 */
class RegularTransactionService extends TransactionType {

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
    $creditCard->setCardCode($params->getFieldData('field_tx_creditcardverf'));

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
    $customer = new AnetAPI\CustomerAddressType();
    $customer->setFirstName($params->getFieldData('field_tx_first_name'));
    $customer->setLastName($params->getFieldData('field_tx_last_name'));
    $customer->setCountry($params->getFieldData('field_tx_country'));
    $customer->setCity($params->getFieldData('field_tx_city'));
    $customer->setState($params->getFieldData('field_tx_state'));
    $customer->setAddress($params->getFieldData('field_tx_address'));
    $customer->setZip($params->getFieldData('field_tx_zip'));
    $customer->setPhoneNumber($params->getFieldData('field_tx_phone'));

    return $customer;
  }

  /**
   * Set customer email
   * @param AuthorizeParams $params
   * @return CustomerDataType
   */
  public function setCustomerDataType(AuthorizeTransaction $params) {
    $customer = new AnetAPI\CustomerDataType();
    $customer->setEmail($params->getFieldData('field_tx_email'));

    return $customer;
  }

  /**
   * Set order Description
   * @return OrderType
   */
  public function setOrderTypeInfo() {
    $orderType = new AnetAPI\OrderType();
    $orderType->setDescription('Donation');

    return $orderType;
  }

  /**
   * Create transaction request
   * @param AuthorizeTransaction $transactionEntity
   * @param string $transactionType
   * @return CreateTransactionController
   */
  public function createTranscationRequest(AuthorizeTransaction $transactionEntity) {
    $params = clone $transactionEntity;
    self::$transactionEntityId = $transactionEntity->storeTransaction();

    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType('authCaptureTransaction');
    $transactionRequestType->setCustomer($this->setCustomerDataType($params));
    $transactionRequestType->setBillTo($this->setCustomerAddress($params));
    $transactionRequestType->setOrder($this->setOrderTypeInfo());
    $transactionRequestType->setAmount($params->getFieldData('field_tx_amount'));
    $transactionRequestType->setPayment($this->setCreditCard($params));

    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($this->authorizeService->getMerchantAuthentication());
    $request->setTransactionRequest($transactionRequestType);
    $transactionController = new AnetController\CreateTransactionController($request);
    //store basic data in transaction entity
    return $transactionController;
  }

  /**
   * Process transaction with Authorize.net API
   * @param CreateTransactionController $transactionController
   * @return TransactionResponseType
   */
  public function processTransaction($transactionController) {
    $env = (strpos($this->authorizeService->getEnabledConfigEntity(), 'sandbox') !== false) ? ANetEnvironment::SANDBOX :
        ANetEnvironment::PRODUCTION;
    $response = $transactionController->executeWithApiResponse($env);

//    $transId = $response->getTransactionResponse()->getTransId();
    return $response;
  }

  /**
   * Get a transaction details response
   *
   * @return TransactionDetailsType
   */
  public function getTransactionDetailsResponse($transId) {
    $request = new AnetAPI\GetTransactionDetailsRequest();
    $request->setMerchantAuthentication($this->authorizeService->getMerchantAuthentication());
    $request->setTransId($transId);

    $transactionController = new AnetController\GetTransactionDetailsController($request);
    $env = (strpos($this->authorizeService->getEnabledConfigEntity(), 'sandbox') !== false) ? ANetEnvironment::SANDBOX :
        ANetEnvironment::PRODUCTION;
    $response = $transactionController->executeWithApiResponse($env);

    return $response->getTransaction();
  }

  /**
   * Handle authorize.net response to check with entity and template rendering
   * @param TransactionResponseType $transactionResponse
   */
  public function handleResponse($response) {
    global $base_url;

    if ($response != NULL) {
      $transactionResponse = $response->getTransactionResponse();
      $params = [
        'field_tx_transaction_hash' => $transactionResponse->getTransHash(),
      ];

      $entity = $this->entityStorage->load(self::$transactionEntityId);
      if ($transactionResponse != NULL && $transactionResponse->getResponseCode() == 1) {

        $transactionDetailsResponse = $this->getTransactionDetailsResponse(
            $transactionResponse->getTransId());

        $responseParams = $this->getTransactionResponseParams($transactionDetailsResponse, $params);
        $entity->updateTransaction($responseParams);
        $this->createSFOpportunity($entity);
        drupal_set_message($transactionResponse->getMessages()[0]->getDescription());
        $url = $base_url . '/donate/thank-you';
        $res = new RedirectResponse($url);
        return $res->send();
      }
      else {
        $responseParams = $this->getTransactionResponseParams($transactionResponse, $params);
        $entity->updateTransaction($responseParams);
        return drupal_set_message($transactionResponse->getErrors()[0]->getErrorText(), 'error');
      }
    }
    else {
      return drupal_set_message('Your credit card hasn\'t been charged. Please try donation again', 'error');
    }
  }

  /**
   * Map authorize.net transaction response to authorizeParams array
   * @return array AuthorizeParams
   */
  public function getTransactionResponseParams($transactionResponse, array $params = []) {
    $authorizeParams = [];

    if ($transactionResponse instanceof AnetAPI\TransactionDetailsType) {
      $creditCardData = $transactionResponse->getPayment()->getCreditCard();
      $authorizeParams['field_tx_creditcardtype'] = $creditCardData->getCardType();
      $authorizeParams['field_tx_creditcardnum'] = $creditCardData->getCardNumber();
      $authorizeParams['field_tx_creditcardexpdate'] = $creditCardData->getExpirationDate();

      $authorizeParams['field_tx_auth_code'] = $transactionResponse->getAuthCode();
      $authorizeParams['field_tx_transaction_id'] = $transactionResponse->getTransId();
      $authorizeParams['field_tx_transaction_type'] = $transactionResponse->getTransactionType();

      $authorizeParams['field_tx_response_code'] = $transactionResponse->getResponseCode();
      $authorizeParams['field_tx_response_desc'] = $transactionResponse->getResponseReasonDescription();
      $authorizeParams['field_tx_avs_response_code'] = $transactionResponse->getAVSResponse();
      $authorizeParams['field_tx_cvv_result_code'] = $transactionResponse->getCardCodeResponse();
      $authorizeParams['field_tx_cavv_result_code'] = $transactionResponse->getCAVVResponse();
    }
    else {
      $authorizeParams['field_tx_creditcardnum'] = $transactionResponse->getAccountNumber();
      $authorizeParams['field_tx_response_code'] = $transactionResponse->getResponseCode();
      $authorizeParams['field_tx_avs_response_code'] = $transactionResponse->getAvsResultCode();
      $authorizeParams['field_tx_response_desc'] = $transactionResponse->getErrors()[0]->getErrorText();
    }

    foreach ($params as $key => $value) {
      $authorizeParams[$key] = $value;
    }

    return $authorizeParams;
  }

}
