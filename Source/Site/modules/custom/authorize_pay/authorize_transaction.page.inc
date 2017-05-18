<?php

/**
 * @file
 * Contains authorize_transaction.page.inc..
 *
 * Page callback for Authorize transaction entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Authorize transaction templates.
 *
 * Default template: authorize_transaction.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_authorize_transaction(array &$variables) {
  // Fetch AuthorizeTransaction Entity Object.
  $authorize_transaction = $variables['elements']['#authorize_transaction'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}

/**
* Prepares variables for a custom entity type creation list templates.
*
* Default template: authorize_transaction-content-add-list.html.twig.
*
* @param array $variables
*   An associative array containing:
*   - content: An array of authorize_transaction-types.
*
* @see block_content_add_page()
*/
function template_preprocess_authorize_transaction_content_add_list(&$variables) {
  $variables['types'] = array();
  $query = \Drupal::request()->query->all();
  foreach ($variables['content'] as $type) {
    $variables['types'][$type->id()] = array(
      'link' => Link::fromTextAndUrl($type->label(), new Url('entity.authorize_transaction.add_form', array(
        'authorize_transaction_type' => $type->id()
      ), array('query' => $query))),
      'description' => array(
      '#markup' => $type->label(),
      ),
      'title' => $type->label(),
      'localized_options' => array(
      'query' => $query,
      ),
    );
  }
}
