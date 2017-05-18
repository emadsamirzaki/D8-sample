<?php

namespace Drupal\authorize_pay\Service;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\contract\v1\MerchantAuthenticationType;
use net\authorize\api\controller as AnetController;
use Drupal\authorize_pay\Entity\AuthorizeTransaction;

/**
 * Description of AuthorizeConsumerService
 *
 * @author yousab
 */
class AuthorizeConsumerService {

  CONST ENTITY_TYPE = 'authorize_transaction';

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
   * Entity Storage
   */
  protected $entityStorage;

  /**
   * PostPayment Service
   */
  protected $postPaymentService;

  /**
   *
   * @param ConfigFactory $configFactory
   * @param EntityTypeManagerInterface $entityManager
   */
  public function __construct(ConfigFactory $configFactory, EntityTypeManagerInterface $entityManager, PostPaymentService $postPaymentService) {
    $this->configFactory = $configFactory;
    $this->entityManager = $entityManager;
    $this->entityStorage = $this->entityManager->getStorage(self::ENTITY_TYPE);
    $this->postPaymentService = $postPaymentService;
  }

  public function getService(AuthorizeTransaction $authorizeEntity) {
    $service = null;

    if ($authorizeEntity->getType() == 'basic_transaction') {
      $service = new RegularTransactionService($this, $this->entityManager);
    }
    else if ($authorizeEntity->getType() == 'monthly_subscription_transaction') {
      $service = new SubscribedTransactionService($this, $this->entityManager);
    }

    return $service;
  }

  /**
   * Get merhant authentication API credentials
   * @return MerchantAuthenticationType
   */
  public function getMerchantAuthentication() {
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $configEntity = $this->configFactory->get($this->getEnabledConfigEntity());
    $merchantAuthentication->setName($configEntity->get('apiId'));
    $merchantAuthentication->setTransactionKey($configEntity->get('transactionKey'));
    return $merchantAuthentication;
  }

  /**
   * Get enabled authorize.net API config entity
   * @return string
   */
  public function getEnabledConfigEntity() {
    return $this->configFactory->get('authorize_pay.config.settings')->
            get('authorize_pay.config.authorize_api');
  }

  /**
   * Map form submitted values to AuthorizeTransaction entity
   * @param FormStateInterface $form_state
   * @return AuthorizeTransaction
   */
  public function mapFormStateToEntity(FormStateInterface $form_state) {
    $authorizeParams = [];
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, 'donor') !== false) {
        $authorizeParams[strtolower(str_replace('donor', 'field_tx', $key))] = trim($value, ' ');
      }
    }

    $authorizeParams['field_tx_creditcardexpdate'] = $form_state->getValue('creditCardExpDateMonth') .
        $form_state->getValue('creditCardExpDateYear');

    $authorizeParams['type'] = 'basic_transaction';

    /** set bundle entity type with subscription transaction if checked * */
    if ($form_state->getValue('month_subscription')) {
      $authorizeParams['type'] = 'monthly_subscription_transaction';
    }

    if ($form_state->getValue('amount_list') != 'Other Amount') {
      $authorizeParams['field_tx_amount'] = $form_state->getValue('amount_list');
    }
    else {
      $authorizeParams['field_tx_amount'] = $form_state->getValue('amount');
    }

    return $this->entityStorage->create($authorizeParams);
  }

  public function authorizeWithCapture() {
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName('56S7w2r4fwEa');
    $merchantAuthentication->setTransactionKey('4mKg2bNMe429C78v');


    // Create the payment data for a credit card
    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber("4111111111111111");
    $creditCard->setExpirationDate("2038-12");
    $paymentOne = new AnetAPI\PaymentType();
    $paymentOne->setCreditCard($creditCard);

    // Create a transaction
    $transactionRequestType = new AnetAPI\TransactionRequestType();
    $transactionRequestType->setTransactionType("authCaptureTransaction");
    $transactionRequestType->setAmount(30);
    $transactionRequestType->setPayment($paymentOne);

    $request = new AnetAPI\CreateTransactionRequest();
    $request->setMerchantAuthentication($merchantAuthentication);
    $request->setTransactionRequest($transactionRequestType);
    $controller = new AnetController\CreateTransactionController($request);
    $response = $controller->executeWithApiResponse(ANetEnvironment::SANDBOX);

    if ($response != null) {
      $tresponse = $response->getTransactionResponse();

      if (($tresponse != null) && ($tresponse->getResponseCode() == "1")) {
        echo "Charge Credit Card AUTH CODE : " . $tresponse->getAuthCode() . "\n";
        echo "Charge Credit Card TRANS ID  : " . $tresponse->getTransId() . "\n";
      }
      else {
        echo "Charge Credit Card ERROR :  Invalid response\n";
      }
    }
    else {
      echo "Charge Credit card Null response returned";
    }
  }

  public function authorizeSubscription() {
    $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
    $merchantAuthentication->setName("56S7w2r4fwEa");
    $merchantAuthentication->setTransactionKey("4mKg2bNMe429C78v");
    // Subscription Type Info
    $subscription = new AnetAPI\ARBSubscriptionType();
    $subscription->setName("Donation Subscription");
    $interval = new AnetAPI\PaymentScheduleType\IntervalAType();
    $interval->setLength("1");
    $interval->setUnit("months");
    $paymentSchedule = new AnetAPI\PaymentScheduleType();
    $paymentSchedule->setInterval($interval);
    $paymentSchedule->setStartDate(new \DateTime());
    $paymentSchedule->setTotalOccurrences("12");
    $subscription->setPaymentSchedule($paymentSchedule);
    $subscription->setAmount("7");

    $creditCard = new AnetAPI\CreditCardType();
    $creditCard->setCardNumber("4111111111111111");
    $creditCard->setExpirationDate("2020-12");
    $payment = new AnetAPI\PaymentType();
    $payment->setCreditCard($creditCard);
    $subscription->setPayment($payment);
    $billTo = new AnetAPI\NameAndAddressType();
    $billTo->setFirstName("Karim");
    $billTo->setLastName("Shawky");
    $subscription->setBillTo($billTo);
    $request = new AnetAPI\ARBCreateSubscriptionRequest();
    $request->setmerchantAuthentication($merchantAuthentication);
    $request->setSubscription($subscription);
    $controller = new AnetController\ARBCreateSubscriptionController($request);
    $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);


    if (($response != null) && ($response->getMessages()->getResultCode() == "Ok")) {
      echo "SUCCESS: Subscription ID : " . $response->getSubscriptionId() . "\n";
      var_dump($response);
      die;
    }
    else {
      echo "ERROR :  Invalid response\n";
      echo "Response : " . $response->getMessages()->getMessage()[0]->getCode() . "  " . $response->getMessages()->getMessage()[0]->getText() . "\n";
    }
  }

}
