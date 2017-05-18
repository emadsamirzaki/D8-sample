<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\Entity\AuthorizeTransactionType.
 */

namespace Drupal\authorize_pay\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\authorize_pay\AuthorizeTransactionTypeInterface;

/**
 * Defines the Authorize transaction type entity.
 *
 * @ConfigEntityType(
 *   id = "authorize_transaction_type",
 *   label = @Translation("Authorize transaction type"),
 *   handlers = {
 *     "list_builder" = "Drupal\authorize_pay\AuthorizeTransactionTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\authorize_pay\Form\AuthorizeTransactionTypeForm",
 *       "edit" = "Drupal\authorize_pay\Form\AuthorizeTransactionTypeForm",
 *       "delete" = "Drupal\authorize_pay\Form\AuthorizeTransactionTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\authorize_pay\AuthorizeTransactionTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "authorize_transaction_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "authorize_transaction",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/authorize_transaction_type/{authorize_transaction_type}",
 *     "add-form" = "/admin/structure/authorize_transaction_type/add",
 *     "edit-form" = "/admin/structure/authorize_transaction_type/{authorize_transaction_type}/edit",
 *     "delete-form" = "/admin/structure/authorize_transaction_type/{authorize_transaction_type}/delete",
 *     "collection" = "/admin/structure/authorize_transaction_type"
 *   }
 * )
 */
class AuthorizeTransactionType extends ConfigEntityBundleBase implements AuthorizeTransactionTypeInterface {
  /**
   * The Authorize transaction type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Authorize transaction type label.
   *
   * @var string
   */
  protected $label;

}
