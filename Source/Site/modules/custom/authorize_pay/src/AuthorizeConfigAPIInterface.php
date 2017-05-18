<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\AuthorizeConfigAPIInterface.
 */

namespace Drupal\authorize_pay;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Authorize config api entities.
 */
interface AuthorizeConfigAPIInterface extends ConfigEntityInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Get configuration entity ID
   * @return string Id
   */
  public function getID();

  /**
   * Set configuration entity Label
   * @param string $configLabel
   */
  public function setLabel($configLabel);

  /**
   * Get configuration entity Label
   * @return string Lable
   */
  public function getLabel();

  /**
   * Set Authorize.net configuration entity API Id
   * @param string $apiId
   */
  public function setAPIid($apiId);

  /**
   * @return string Authorize.net configuration entity API Id
   */
  public function getAPIid();

  /**
   * Set Authorize.net configuration entity transaction key
   * @param string $transcationKey
   */
  public function setTranscationKey($transcationKey);

  /**
   * @return string Authorize.net configuration entity transaction key
   */
  public function getTranscationKey();
}
