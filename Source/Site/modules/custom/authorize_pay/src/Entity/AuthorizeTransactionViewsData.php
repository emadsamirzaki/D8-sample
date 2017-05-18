<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\Entity\AuthorizeTransaction.
 */

namespace Drupal\authorize_pay\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Authorize transaction entities.
 */
class AuthorizeTransactionViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['authorize_transaction']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Authorize transaction'),
      'help' => $this->t('The Authorize transaction ID.'),
    );

    return $data;
  }

}
