<?php

namespace Drupal\authorize_pay\Service;

use Drupal\authorize_pay\Entity\AuthorizeTransaction;

/**
 * @author yousab
 */
interface PostPaymentServiceInterface {

  public function sendSuccessResponse($entityId);
}
