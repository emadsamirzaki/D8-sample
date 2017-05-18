<?php

/**
 * @file
 * Contains \Drupal\salesforce\Entity\SfMapping.
 */

namespace Drupal\salesforce\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\salesforce\SfMappingInterface;

/**
 * Defines the Sf mapping entity.
 *
 * @ConfigEntityType(
 *   id = "sf_mapping",
 *   label = @Translation("Sf mapping"),
 *   handlers = {
 *     "list_builder" = "Drupal\salesforce\SfMappingListBuilder",
 *     "form" = {
 *       "add" = "Drupal\salesforce\Form\SfMappingForm",
 *       "edit" = "Drupal\salesforce\Form\SfMappingForm",
 *       "delete" = "Drupal\salesforce\Form\SfMappingDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\salesforce\SfMappingHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "sf_mapping",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/sf_mapping/{sf_mapping}",
 *     "add-form" = "/admin/structure/sf_mapping/add",
 *     "edit-form" = "/admin/structure/sf_mapping/{sf_mapping}/edit",
 *     "delete-form" = "/admin/structure/sf_mapping/{sf_mapping}/delete",
 *     "collection" = "/admin/structure/sf_mapping"
 *   }
 * )
 */
class SfMapping extends ConfigEntityBundleBase implements SfMappingInterface {
  /**
   * The Sf mapping ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Sf mapping label.
   *
   * @var string
   */
  protected $label;

}
