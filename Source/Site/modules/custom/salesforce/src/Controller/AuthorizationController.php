<?php

/**
 * @file
 * Contains \Drupal\salesforce\Controller\AuthorizationController.
 */

namespace Drupal\salesforce\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\salesforce\Service\ConsumerService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Url;

/**
 * Class AuthorizationController.
 *
 * @package Drupal\salesforce\Controller
 */
class AuthorizationController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Salesforce Consumer Service
   *
   * @var  ConsumerService
   */
  protected $sfService;

  public function __construct(ConsumerService $sfService) {
    $this->sfService = $sfService;
  }

  /**
   * @return static
   *   Returns an instance of the container.
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('salesforce.consumer')
    );
  }

  /**
   * Authorization.
   *
   * @return RedirectResponse
   */
  public function authorize(Request $request) {
    $code = $request->query->get('code');
    $client = $this->sfService->getClient();
    if (isset($code) && $this->sfService->authorizeClientWithCode($client, $code)) {
      drupal_set_message("Authorization Successful...");
      return new RedirectResponse(Url::fromRoute('salesforce.sf_config_settings')->toString());
      }

      throw new AccessDeniedHttpException('You are not authorized to access this page');
  }

}
