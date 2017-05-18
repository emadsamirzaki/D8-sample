<?php

namespace Drupal\authorize_pay\Service;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Handle Authorize.net payment response
 *
 * @author yousab
 */
class PostPaymentService implements PostPaymentServiceInterface{

  public function sendSuccessResponse($entityId) {
    $routeParams = array(
      'id' => $entityId,
      'twigName' => 'success_payment.html.twig'
    );

    var_dump(Url::fromRoute('authorize_payment.success_payment', $routeParams)->toString());die;
    return new RedirectResponse(Url::fromRoute('authorize_payment.success_payment', $routeParams)->toString());
  }

}
