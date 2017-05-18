<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\AuthorizeTransactionInterface.
 */

namespace Drupal\authorize_pay;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Authorize transaction entities.
 *
 * @ingroup authorize_pay
 */
interface AuthorizeTransactionInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Authorize transaction type.
   *
   * @return string
   *   The Authorize transaction type.
   */
  public function getType();

  /**
   * Gets the Authorize transaction name.
   *
   * @return string
   *   Name of the Authorize transaction.
   */
  public function getName();

  /**
   * Sets the Authorize transaction name.
   *
   * @param string $name
   *   The Authorize transaction name.
   *
   * @return \Drupal\authorize_pay\AuthorizeTransactionInterface
   *   The called Authorize transaction entity.
   */
  public function setName($name);

  /**
   * Gets the Authorize transaction creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Authorize transaction.
   */
  public function getCreatedTime();

  /**
   * Sets the Authorize transaction creation timestamp.
   *
   * @param int $timestamp
   *   The Authorize transaction creation timestamp.
   *
   * @return \Drupal\authorize_pay\AuthorizeTransactionInterface
   *   The called Authorize transaction entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Authorize transaction published status indicator.
   *
   * Unpublished Authorize transaction are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Authorize transaction is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Authorize transaction.
   *
   * @param bool $published
   *   TRUE to set this Authorize transaction to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\authorize_pay\AuthorizeTransactionInterface
   *   The called Authorize transaction entity.
   */
  public function setPublished($published);

}
