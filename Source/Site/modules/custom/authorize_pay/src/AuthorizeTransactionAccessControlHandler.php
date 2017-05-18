<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\AuthorizeTransactionAccessControlHandler.
 */

namespace Drupal\authorize_pay;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Authorize transaction entity.
 *
 * @see \Drupal\authorize_pay\Entity\AuthorizeTransaction.
 */
class AuthorizeTransactionAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\authorize_pay\AuthorizeTransactionInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished authorize transaction entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published authorize transaction entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit authorize transaction entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete authorize transaction entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add authorize transaction entities');
  }

}
