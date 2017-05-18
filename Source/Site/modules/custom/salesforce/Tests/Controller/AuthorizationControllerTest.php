<?php

/**
 * @file
 * Contains \Drupal\salesforce\Tests\AuthorizationController.
 */

namespace Drupal\salesforce\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the salesforce module.
 */
class AuthorizationControllerTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "salesforce AuthorizationController's controller functionality",
      'description' => 'Test Unit for module salesforce and controller AuthorizationController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests salesforce functionality.
   */
  public function testAuthorizationController() {
    // Check that the basic functions of module salesforce.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
