<?php

namespace Drupal\ceres_core\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\salesforce\Service\ConsumerService;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\user\PrivateTempStoreFactory;

class ReportController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Salesforce Consumer Service
   *
   * @var  ConsumerService
   */
  protected $sfService;

  /**
   * Private Temp Storage service
   *
   * @var  PrivateTempStoreFactory
   */
  protected $tempStore;

  public function __construct(ConsumerService $sfService, PrivateTempStoreFactory $tempStore) {
    $this->sfService = $sfService;
    $this->tempStore = $tempStore;
  }

  /**
   * @return static
   *   Returns an instance of the container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('salesforce.consumer'), $container->get('user.private_tempstore')
    );
  }

  public function downloadAction(NodeInterface $node) {
    $user = User::load(Drupal::currentUser()->id());

    if ($user->isAuthenticated()) {

      // integrate with salesforce
      if (!empty($node->field_salesforce_id->value)) {
        $this->trackReportWithSalesforce($node, $user);
      }

      $pdfExternalLink = $node->field_file_external_url->getString();
      $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => true])->toString(true);
      $url = ($url instanceof \Drupal\Core\GeneratedUrl) ? $url->getGeneratedUrl() : $url;

      if (!empty($node->field_report_file_upload->entity) && !empty($node->field_file_external_url->getValue())) {
        $url = file_create_url($node->field_file_external_url->getString());
      }
      else if (!empty($node->field_report_file_upload->entity)) {
        $url = file_create_url($node->field_report_file_upload->entity->getFileUri());
      }
      else if (!empty($node->field_file_external_url->getValue())) {
        $url = file_create_url($node->field_file_external_url->getString());
      }
      return new TrustedRedirectResponse($url);
    }
    else {
      $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => true]);
      return new RedirectResponse($url->toString());
    }
  }

  public function viewAction(NodeInterface $node) {

    $user = User::load(Drupal::currentUser()->id());
    $client = $this->sfService->getClient();

    if ($user->isAuthenticated()) {

      //integrate with salesforce
      if (!empty($node->field_salesforce_id->value) && empty($this->tempStore->get('ceres_core.report_views')->get('report_views_' . $node->id()))) {
        $this->tempStore->get('ceres_core.report_views')->set('report_views_' . $node->id(), 1);
        $this->trackReportWithSalesforce($node, $user);
        $this->sfService->updateReportViews($client, $node->field_salesforce_id->value);
      }

      $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id(), 'report' => 'view'], ['absolute' => true]);
      return new RedirectResponse($url->toString());
    }
    else {
      $url = Url::fromRoute('entity.node.canonical', ['node' => $node->id()], ['absolute' => true]);
      return new RedirectResponse($url->toString());
    }
  }

  protected function trackReportWithSalesforce($node, $user) {
    $client = $this->sfService->getClient();

    $data = [
      'LastName' => $user->field_user_register_last_name->value,
      'Email' => $user->getEmail(),
      'Company' => (!empty($user->field_user_register_organization->value)) ? $user->field_user_register_organization->value : 'N/A',
      'LeadSource' => 'Website'
    ];

    $campaign = $this->sfService->getCampaignById($client, $node->field_salesforce_id->value);
    $campaignName = ($campaign) ? $campaign[0]['Name'] : 'Ceres eCommunity';
    $lead = $this->sfService->getObjectByFieldName($client, 'Lead', array_slice($data, 1, 1))[0];
    $leadIsConverted = $lead['IsConverted'];
    if ($lead && !$leadIsConverted) {
      $leadId = $this->sfService->updateObject($client, 'Lead', $lead['Id'], $data);
    }
    else {
      $leadId = $this->sfService->createObject($client, 'Lead', $data);
    }

    return ($leadIsConverted) ? $this->sfService->assignLeadToCampaign($client, 'Contact', $lead['ConvertedContactId'], $campaignName) :
        $this->sfService->assignLeadToCampaign($client, 'Lead', (($lead) ? $lead['Id'] : $leadId), $campaignName);
  }

}
