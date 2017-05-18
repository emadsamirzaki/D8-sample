<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\Entity\AuthorizeConfigAPI.
 */

namespace Drupal\authorize_pay\Entity;

use Drupal\authorize_pay\AuthorizeConfigAPIInterface;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Autorize config api entity.
 *
 * @ConfigEntityType(
 *   id = "authorize_config_api",
 *   label = @Translation("Autorize config api"),
 *   handlers = {
 *     "list_builder" = "Drupal\authorize_pay\AuthorizeConfigAPIListBuilder",
 *     "form" = {
 *       "add" = "Drupal\authorize_pay\Form\AuthorizeConfigAPIForm",
 *       "edit" = "Drupal\authorize_pay\Form\AuthorizeConfigAPIForm",
 *       "delete" = "Drupal\authorize_pay\Form\AuthorizeConfigAPIDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\authorize_pay\AuthorizeConfigAPIHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "authorize_config_api",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/authorize_config_api/{authorize_config_api}",
 *     "add-form" = "/admin/structure/authorize_config_api/add",
 *     "edit-form" = "/admin/structure/authorize_config_api/{authorize_config_api}/edit",
 *     "delete-form" = "/admin/structure/authorize_config_api/{authorize_config_api}/delete",
 *     "collection" = "/admin/structure/authorize_config_api"
 *   }
 * )
 */
class AuthorizeConfigAPI extends ConfigEntityBase implements AuthorizeConfigAPIInterface {

  /**
   * The Autorize config api ID.
   * @var string
   */
  protected $id;

  /**
   * The Autorize config api Label.
   * @var string
   */
  protected $label;

  /**
   * Authorize.net configuration API Login ID
   * @var type
   */
  protected $apiId;

  /**
   * Authorize.net configuration Transcation Key
   * @var type
   */
  protected $transactionKey;


  public function getAPIid() {
    return $this->apiId;
  }

  public function getLabel() {
    return $this->label;
  }

  public function getTranscationKey() {
    return $this->transactionKey;
  }

  public function setAPIid($apiId) {
    $this->apiId = $apiId;
  }

  public function setLabel($configLable) {
    $this->lable = $configLable;
  }

  public function setTranscationKey($transcationKey) {
    $this->transactionKey = $transcationKey;
  }

  public function getID() {
    return $this->id;
  }

}
