<?php

/**
 * @file
 * Contains \Drupal\ceres_core\Form\DonateForm.
 */

namespace Drupal\ceres_core\Form;

use Drupal\authorize_pay\Service\AuthorizeConsumerService;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Locale\CountryManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DonateForm.
 *
 * @package Drupal\ceres_core\Form
 */
class DonateForm extends FormBase {

  /**
   * AuthorizeNet Consumer Service
   *
   * @var  ConsumerService
   */
  protected $authorizeService;

  /**
   *
   * @param ConsumerService AuthorizeNet Service
   */
  public function __construct(AuthorizeConsumerService $authorizeService) {
    $this->authorizeService = $authorizeService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('authorize_net.consumer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'donate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['form-title'] = array(
      '#type' => 'markup',
      '#markup' => '<h2 class="universal-section-title text-center">Select Gift Amount</h2>',
    );
    $form['month_subscription'] = array(
      '#type' => 'checkbox',
      '#title' => 'Monthly Donation',
      '#prefix' => '<div class="centered-checkbox text-center">',
      '#suffix' => '</div>'
    );

    $form['amount_list'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Donation Amount'),
      '#title_display' => "invisible",
      '#required' => TRUE,
      '#attributes' => array(
        'class' => array('button-radios', 'mar-bottom-15')
      ),
      '#options' => array(
        '35' => '$35',
        '75' => '$75',
        '500' => '$500',
        '1000' => '$1000',
        'Other Amount' => 'Other Amount'
      ),
    );
    $form['amount_list']['Other Amount']["#attributes"]['class'][] = "no-back-color";

    $form['amount_fieldset'] = array(
      '#type' => 'fieldset',
      '#attributes' => array(
        'class' => array(
          'full-width-item',
          'remove-panel-style',
          'clearfix',
          'clear',
        ),
      ),
      '#states' => array(
        'visible' => array(
          ':input[name="amount_list"]' => array('value' => 'Other Amount')
        ),
      ),
    );
    $form['amount_fieldset']['amount'] = array(
      '#type' => 'number',
      '#title' => 'Specify amount',
      '#min' => 5,
      '#step' => 1
    );

    $form['donor_honor'] = array(
      '#type' => 'fieldset',
      '#title' => 'Honor Someone With Your Contribution',
      '#collapsible' => TRUE,
      '#attributes' => array(
        'class' => array(
          'black-white-panel',
          'no-pad-right'
        ),
      ),
    );

    $form['donor_honor']['donor_honoree_type'] = array(
      '#type' => 'select',
      '#options' => [
        'Choose One' => '-- Choose One --',
        'In honor of' => 'In honor of',
        'In memory of' => 'In memory of',
      ],
    );
    $form['donor_honor']['donor_honoree'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Honoree Name'),
      '#states' => array(
        'invisible' => array(
          ':input[name="donor_honoree_type"]' => array('value' => 'Choose One')
        ),
      ),
    );
    $form['donor_honor']['donor_honoree_email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Notify by Email'),
      '#attributes' => [
        'placeholder' => $this->t("Enter honoree's email address"),
      ],
      '#states' => array(
        'invisible' => array(
          ':input[name="donor_honoree_type"]' => array('value' => 'Choose One')
        ),
      ),
    );
    $form['donor_honor']['donor_honoree_mail'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Notify by Mail'),
      '#attributes' => [
        'placeholder' => $this->t("Enter honoree's full mailing address"),
      ],
      '#states' => array(
        'invisible' => array(
          ':input[name="donor_honoree_type"]' => array('value' => 'Choose One')
        ),
      ),
    );

    $form['personal-title'] = array(
      '#type' => 'markup',
      '#markup' => '<h2 class="universal-section-title mar-top-50 text-center">Personal Info</h2>',
    );

    $form['donor_first_Name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('First Name'),
      '#default_value' => $form_state->getValue('donor_firstName'),
      '#required' => TRUE,
      '#maxlength' => 40,
    );

    $form['donor_last_Name'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Last Name'),
      '#required' => TRUE,
      '#maxlength' => 40,
    );

    $form['donor_email'] = array(
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#required' => TRUE,
    );

    $form['donor_phone'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Phone'),
      '#maxlength' => 25,
    );

    $form['donor_address'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Street Address'),
      '#required' => TRUE,
      '#maxlength' => 60,
    );

    $form['donor_city'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#required' => TRUE,
      '#maxlength' => 40,
    );
    $form['country-fieldset'] = array(
      '#type' => 'fieldset',
      '#attributes' => array(
        'class' => array(
          'three-items',
          'remove-panel-style',
          'clearfix',
          'clear',
        ),
      ),
    );
    $form['country-fieldset']['donor_state'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('State'),
      '#required' => TRUE,
      '#maxlength' => 40,
    );

    $form['country-fieldset']['donor_zip'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Postal Code'),
      '#required' => TRUE,
      '#maxlength' => 20,
    );
    $form['country-fieldset']['donor_country'] = array(
      '#type' => 'select',
      '#title' => $this->t('Country'),
      '#title_display' => "invisible",
      '#default_value' => 'US',
      '#options' => array_merge(array("" => t('Country')), CountryManager::getStandardList()),
    );

    $form['donor_creditCardType'] = array(
      '#type' => 'select',
      '#title' => 'Credit Card Type',
      '#options' => array(
        'Visa' => 'Visa',
        'MasterCard' => 'MasterCard',
        'American Express' => 'American Express',
        'Discover' => 'Discover'
      ),
    );

    $form['donor_creditCardNum'] = array(
      '#type' => 'textfield',
      '#size' => 16,
      '#title' => $this->t('Credit Card Number'),
      '#required' => TRUE,
    );

    $form['donor_creditCardVerf'] = array(
      '#type' => 'textfield',
      '#maxlength' => 4,
      '#title' => $this->t('Credit Card Verification Number'),
      '#required' => TRUE,
    );

    $form['donor_creditCardExpDate'] = array(
      '#type' => 'fieldset',
      '#required' => TRUE,
      '#title' => 'Credit Card Expiration Date',
      '#attributes' => array(
        'class' => array(
          'black-white-panel',
          'no-pad-right',
          'clearfix',
          'clear',
        ),
      ),
    );

    $form['donor_creditCardExpDate']['creditCardExpDateMonth'] = array(
      '#type' => 'select',
      '#options' => $this->getMonthsOptions(),
      '#prefix' => '<div class="form-inline">'
    );

    $form['donor_creditCardExpDate']['creditCardExpDateYear'] = array(
      '#type' => 'select',
      '#options' => array_combine(range(date('Y') - 4, date('Y') + 15, 1), range(date('Y') - 4, date('Y') + 15, 1)),
      '#suffix' => '</div>'
    );

    $form['captcha'] = array(
      '#type' => 'captcha',
      '#captcha_type' => 'recaptcha/reCAPTCHA',
      '#prefix' => '<div class="donation-capatcha">',
      '#suffix' => '</div>'
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Donate'),
      '#button_type' => 'primary',
      '#prefix' => '<div class="form-actions">',
      '#suffix' => '</div>'
    );

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($form_state->getValue('amount_list') == 'Other Amount') {
      if (empty($form_state->getValue('amount')) || !is_numeric($form_state->getValue('amount')) || $form_state->getValue('amount') < 5) {
        $form_state->setErrorByName('amount', 'Sorry, we cannot accept online donations of less than $5.'
            . ' Please consider supporting us at one of the following levels.');
      }
    }

    if ($form_state->getValue('donor_creditCardType') == 'American Express' &&
        strlen(trim($form_state->getValue('donor_creditCardVerf'),' ')) != 4) {
      $form_state->setError($form['donor_creditCardVerf'], 'The credit card '
          . 'verification number should be presented in 4 digits');
    }
    else if (strlen(trim($form_state->getValue('donor_creditCardVerf'),' ')) != 3 &&
        $form_state->getValue('donor_creditCardType') != 'American Express') {
      $form_state->setError($form['donor_creditCardVerf'], 'The credit card '
          . 'verification number should be presented in 3 digits');
    }
    if (!is_numeric($form_state->getValue('donor_creditCardNum'))) {
      $form_state->setError($form['donor_creditCardNum'], t('Credit Card has to be only numbers'));
    }
    if (strlen(trim($form_state->getValue('donor_creditCardNum'), ' ')) > 16 || strlen(trim($form_state->getValue('donor_creditCardNum'), ' ')) < 13) {
      $form_state->setError($form['donor_creditCardNum'], t('Credit Card is invalid'));
    }
    if (!is_numeric($form_state->getValue('donor_creditCardVerf'))) {
      $form_state->setError($form['donor_creditCardVerf'], t('Credit Card Verification Number has to be only numbers'));
    }
    if (!empty($form_state->getValue('donor_honoree_email')) && !filter_var($form_state->getValue('donor_honoree_email'), FILTER_VALIDATE_EMAIL)) {
      $form_state->setError($form['donor_honor']['donor_honoree_email'], t('Enter a valid email address'));
    }
    if (strlen($form_state->getValue('donor_honoree_mail')) > 255) {
      $form_state->setError($form['donor_honor']['donor_honoree_mail'], t('Honoree mail must be no longer than 255 characters'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
//    $entity = $this->authorizeService->mapFormStateToEntity($form_state);
//    $authorizeParams = new AuthorizeParams();
//    $authorizeParams->mapParams($this->embedFormValues($form_state));

    /** @var AuthorizeTransaction $authorizeParams * */
    $authorizeParams = $this->authorizeService->mapFormStateToEntity($form_state);
    $service = $this->authorizeService->getService($authorizeParams);
    $transaction = $service->createTranscationRequest($authorizeParams);
    $response = $service->processTransaction($transaction);
    $status = $service->handleResponse($response);
    if (key($status) == 'error') {
      $form_state->disableRedirect();
    }
  }

  public function getMonthsOptions() {
    $months = array();
    $currentMonth = (int) date('m');

    for ($x = 1; $x <= 12; $x++) {
      $months[sprintf('%02d', $x)] = date('F', mktime(0, 0, 0, $x, 1));
    }
    return $months;
  }

  public function embedFormValues(FormStateInterface $form_state) {
    $formValues = [];
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, 'donor') !== false) {
        $formValues[str_replace('donor_', '', $key)] = $value;
      }
    }

    $formValues ['creditCardExpDate'] = $form_state->getValue('creditCardExpDateMonth') . $form_state->getValue('creditCardExpDateYear');

    return $formValues;
  }

}
