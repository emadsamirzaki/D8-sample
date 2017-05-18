<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\AuthorizeTransactionListBuilder.
 */

namespace Drupal\authorize_pay;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\LinkGeneratorTrait;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Authorize transaction entities.
 *
 * @ingroup authorize_pay
 */
class AuthorizeTransactionListBuilder extends EntityListBuilder {
  use LinkGeneratorTrait;
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Authorize transaction ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\authorize_pay\Entity\AuthorizeTransaction */
    $row['id'] = $entity->id();
    $row['name'] = $this->l(
      $entity->label(),
      new Url(
        'entity.authorize_transaction.edit_form', array(
          'authorize_transaction' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
