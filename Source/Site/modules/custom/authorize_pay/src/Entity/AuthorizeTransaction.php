<?php

/**
 * @file
 * Contains \Drupal\authorize_pay\Entity\AuthorizeTransaction.
 */

namespace Drupal\authorize_pay\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\authorize_pay\AuthorizeTransactionInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Authorize transaction entity.
 *
 * @ingroup authorize_pay
 *
 * @ContentEntityType(
 *   id = "authorize_transaction",
 *   label = @Translation("Authorize transaction"),
 *   bundle_label = @Translation("Authorize transaction type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\authorize_pay\AuthorizeTransactionListBuilder",
 *     "views_data" = "Drupal\authorize_pay\Entity\AuthorizeTransactionViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\authorize_pay\Form\AuthorizeTransactionForm",
 *       "add" = "Drupal\authorize_pay\Form\AuthorizeTransactionForm",
 *       "edit" = "Drupal\authorize_pay\Form\AuthorizeTransactionForm",
 *       "delete" = "Drupal\authorize_pay\Form\AuthorizeTransactionDeleteForm",
 *     },
 *     "access" = "Drupal\authorize_pay\AuthorizeTransactionAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\authorize_pay\AuthorizeTransactionHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "authorize_transaction",
 *   admin_permission = "administer authorize transaction entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/authorize_transaction/{authorize_transaction}",
 *     "add-form" = "/admin/structure/authorize_transaction/add",
 *     "edit-form" = "/admin/structure/authorize_transaction/{authorize_transaction}/edit",
 *     "delete-form" = "/admin/structure/authorize_transaction/{authorize_transaction}/delete",
 *     "collection" = "/admin/structure/authorize_transaction",
 *   },
 *   bundle_entity_type = "authorize_transaction_type",
 *   field_ui_base_route = "entity.authorize_transaction_type.edit_form"
 * )
 */
class AuthorizeTransaction extends ContentEntityBase implements AuthorizeTransactionInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'user_id' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? NODE_PUBLISHED : NODE_NOT_PUBLISHED);
    return $this;
  }

  public function storeTransaction() {
    unset($this->field_tx_creditcardnum);
    unset($this->field_tx_creditcardtype);
    unset($this->field_tx_creditcardverf);
    unset($this->field_tx_creditcardexpdate);

    return $this->save() ? $this->id() : false;
  }

  public function updateTransaction(array $params) {
    $typedData = $this->getTypedData();
    foreach ($params as $key => $value) {
      $typedData->set($key, $value);
    }

    return $this->save();
  }

  /**
   * Get entity field value
   * @param string $field
   * @return string
   */
  public function getFieldData($field) {
    return $this->$field->getString();
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
        ->setLabel(t('ID'))
        ->setDescription(t('The ID of the Authorize transaction entity.'))
        ->setReadOnly(TRUE);
    $fields['type'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Type'))
        ->setDescription(t('The Authorize transaction type/bundle.'))
        ->setSetting('target_type', 'authorize_transaction_type')
        ->setRequired(TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
        ->setLabel(t('UUID'))
        ->setDescription(t('The UUID of the Authorize transaction entity.'))
        ->setReadOnly(TRUE);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
        ->setLabel(t('Authored by'))
        ->setDescription(t('The user ID of author of the Authorize transaction entity.'))
        ->setRevisionable(TRUE)
        ->setSetting('target_type', 'user')
        ->setSetting('handler', 'default')
        ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
        ->setTranslatable(TRUE)
        ->setDisplayOptions('view', array(
          'label' => 'hidden',
          'type' => 'author',
          'weight' => 0,
        ))
        ->setDisplayOptions('form', array(
          'type' => 'entity_reference_autocomplete',
          'weight' => 5,
          'settings' => array(
            'match_operator' => 'CONTAINS',
            'size' => '60',
            'autocomplete_type' => 'tags',
            'placeholder' => '',
          ),
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
        ->setLabel(t('Name'))
        ->setDescription(t('The name of the Authorize transaction entity.'))
        ->setSettings(array(
          'max_length' => 50,
          'text_processing' => 0,
        ))
        ->setDefaultValue('')
        ->setDisplayOptions('view', array(
          'label' => 'above',
          'type' => 'string',
          'weight' => -4,
        ))
        ->setDisplayOptions('form', array(
          'type' => 'string_textfield',
          'weight' => -4,
        ))
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
        ->setLabel(t('Publishing status'))
        ->setDescription(t('A boolean indicating whether the Authorize transaction is published.'))
        ->setDefaultValue(TRUE);

    $fields['langcode'] = BaseFieldDefinition::create('language')
        ->setLabel(t('Language code'))
        ->setDescription(t('The language code for the Authorize transaction entity.'))
        ->setDisplayOptions('form', array(
          'type' => 'language_select',
          'weight' => 10,
        ))
        ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
        ->setLabel(t('Created'))
        ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
        ->setLabel(t('Changed'))
        ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
