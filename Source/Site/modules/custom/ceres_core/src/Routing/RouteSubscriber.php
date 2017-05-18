<?php

namespace Drupal\ceres_core\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('entity.contact_form.canonical')) {
      $route->setDefaults([
        '_controller' => '\Drupal\contact\Controller\ContactController::contactSitePage',
        '_title_callback' => '\Drupal\ceres_core\Routing\RouteSubscriber::contactCanonicalTitle',
      ]);
    }
  }

  /**
   * Callback for contact form page title
   * @return string
   */
  public function contactCanonicalTitle() {
    $formId = \Drupal::routeMatch()->getParameter('contact_form')->get('id');
    $form = \Drupal\contact\Entity\ContactForm::load($formId);
    return !empty($form) ? $form->get('label') : 'Contact';
  }

}
