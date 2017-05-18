<?php

namespace Drupal\salesforce\Model;

/**
 * Description of Entity
 *
 * @author yousab
 */
class SFQuery {

  protected $entityType;
  protected $fields;
  protected $query;

  public function __construct($entityType, array $fields) {
    $this->entityType = $entityType;
    $this->fields = $fields;
  }

  public function getEntityType() {
    return $this->entityType;
  }

  public function getFields() {
    return (array) $this->fields;
  }

  public function getQuery() {
    return $this->query;
  }

  public function createSelectQuery() {
    $query = 'Select ';
    foreach ($this->fields as $field) {
      $query .= $field . ',';
    }

    $this->query = rtrim($query, ',') . " FROM $this->entityType";

    return $this;
  }

  public function setCondition($field, $value, $op = '=') {
    $this->query .= " WHERE $field $op $value";

    return $this;
  }

}
